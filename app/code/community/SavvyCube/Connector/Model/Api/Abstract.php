<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@savvycube.com so we can send you a copy immediately.
 *
 * @category   SavvyCube
 * @package    SavvyCube_Connector
 * @copyright  Copyright (c) 2014 SavvyCube (http://www.savvycube.com). SavvyCube is a trademark of Webtex Solutions, LLC (http://www.webtexsoftware.com).
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SavvyCube_Connector_Model_Api_Abstract
{
    const EXISTENCE_PREFIX = 'wcube_if_exists';

    protected $request;

    protected $mainTable = '';

    protected $versionColumns = array();

    protected $parentEntity = array();

    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        if (!empty($this->parentEntity)) {
            /** @var SavvyCube_Connector_Model_Api_Abstract $parentModel */
            $parentModel = Mage::getModel($this->parentEntity['model']);
            $affectedParent = $parentModel->generateQuery()->columns('entity_id');
            $this->applyDateLimit($affectedParent, $this->parentEntity['parent_date']);
            $affectedParentIds = $this->getHelper()->getDbRead()->fetchAll(
                $affectedParent,
                $affectedParent->getBind(),
                Zend_Db::FETCH_COLUMN
            );

            if (count($affectedParentIds)) {
                return $this->getResult(
                    $this->generateQuery()
                        ->columns($this->columnsListForGet())
                        ->where("`main_table`.{$this->parentEntity['parent_fk']} in (?)", $affectedParentIds)
                );
            } else {
                return array();
            }
        } else {
            $sql = $this->generateQuery()
                ->columns($this->columnsListForGet());

            return $this->getResult($sql, '`main_table`.updated_at');
        }
    }

    public function generateQuery()
    {
        return $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($this->mainTable)))
            ->reset(Varien_Db_Select::COLUMNS);
    }

    /**
     * @param Zend_Db_Select $query
     * @param $dateColumn
     */
    public function applyDateLimit($query, $dateColumn)
    {
        $bind = array();

        $fromDate = false;
        $toDate = false;

        if ($this->request['from_date'] !== null) {
            $fromDate = urldecode($this->request['from_date']);
        }
        if ($this->request['to_date'] !== null) {
            $toDate = urldecode($this->request['to_date']);
        }

        $conditions = array();
        if ($fromDate) {
            $conditions[] = "{$dateColumn} > :fromDate";
            $bind[":fromDate"] = $fromDate;
        }
        if ($fromDate) {
            $conditions[] = "{$dateColumn} <= :toDate";
            $bind[":toDate"] = $toDate;
        }

        foreach ($conditions as $condition) {
            $query->where($condition);
        }
        $query->bind(array_merge($query->getBind(), $bind));
    }

    /**
     * init model and set $request array
     *
     * @param array $request
     *
     * @return $this
     */
    public function init($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * init sql select with order status date filter
     *
     * @return Varien_Db_Select
     */
    protected function getAffectedOrders()
    {
        $fromDate = false;
        $toDate = false;

        if ($this->request['from_date'] !== null) {
            $fromDate = urldecode($this->request['from_date']);
        }
        if ($this->request['to_date'] !== null) {
            $toDate = urldecode($this->request['to_date']);
        }

        if (!$fromDate && !$toDate) {
            return true;
        } else {
            /** @var SavvyCube_Connector_Helper_Data $helper */
            $helper = Mage::helper('wCube');
            return $helper->getAffectedOrdersIds($fromDate, $toDate);
        }
    }

    protected function getResult($query, $dateColumn = false)
    {
        if ($dateColumn) {
            $this->applyDateLimit($query, $dateColumn);
        }
        $this->renderParameters($query);
        return $this->getHelper()->getDbRead()->fetchAll($query, $query->getBind());
    }

    /**
     * Render where condition by current request parameters
     *
     * @param Varien_Db_Select $sql select object
     */
    protected function renderParameters($sql)
    {
        if ($this->request['count'] !== null && $this->request['offset'] !== null) {
            $sql->limit($this->request['count'], $this->request['offset']);
        }
    }

    /**
     * @return SavvyCube_Connector_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('wCube');
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    protected function columnsListForGet()
    {
        return '*';
    }

    public function prepareColumns($columns, $tableAlias = false)
    {
        $result = array();
        foreach ($columns as $key => $column) {
            if (is_string($key)) {
                $columnAlias = $key;
            }
            if (isset($this->versionColumns[$column])) {
                $versionInfo = $this->versionColumns[$column];
                if (isset($versionInfo['check_existence']) && !$this->checkColumn($this->mainTable, $column)
                    || isset($versionInfo['since']) && Mage::getVersion() < $versionInfo['since']
                ) {
                    /** skip missing columns */
                    continue;
                } elseif (isset($versionInfo['renamed']) && Mage::getVersion() < $versionInfo['renamed']['since']) {
                    if (!isset($columnAlias)) {
                        $columnAlias = $column;
                    }
                    $column = $versionInfo['renamed']['originally'];
                }
            }
            if (!isset($columnAlias)) {
                $columnAlias = $column;
            }
            if ($tableAlias) {
                $column = "{$tableAlias}.{$column}";
            }

            $result[$columnAlias] = $column;
            unset($columnAlias);
        }
        return $result;
    }

    public function checkColumn($table, $columnName)
    {
        $result = Mage::app()->getCache()->load(self::EXISTENCE_PREFIX . $table . $columnName);
        if (!$result) {
            $columns = $this->getHelper()->getDbRead()->describeTable(
                $this->getHelper()->getTableName($this->mainTable)
            );
            $result = '';
            foreach ($columns as $column) {
                if ($column['COLUMN_NAME'] == $columnName) {
                    $result = $columnName;
                    break;
                }
            }

            Mage::app()->getCache()->save($result, self::EXISTENCE_PREFIX . $table . $columnName);
        }

        return $result == $columnName;
    }
}
