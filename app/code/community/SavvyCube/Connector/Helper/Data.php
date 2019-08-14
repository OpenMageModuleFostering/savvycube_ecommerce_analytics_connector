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
class SavvyCube_Connector_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $dbRead;

    private $resource;

    private $tableName;

    const AFFECTED_ORDER_CACHE_PATH = 'savvy_affected_orders';

    /**
     * return module log name
     *
     * @return string
     */
    public function getErrorLog()
    {
        return 'wCube-error.log';
    }

    public function getCurrentModuleVersion()
    {
        return (string)Mage::getConfig()->getNode()->modules->SavvyCube_Connector->version;
    }

    public function getDesiredVersion()
    {
        $configVersion = Mage::getStoreConfig('wCube/module/desired');
        if ($configVersion) {
            return $configVersion;
        }

        return $this->getCurrentModuleVersion();
    }

    public function setDesiredVersion($version)
    {
        Mage::getConfig()->saveConfig('wCube/module/desired', $version);
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    public function getVersionData()
    {
        return array(
            new Varien_Object(
                array(
                    'id' => 'current_version',
                    'name' => 'Current Module Version',
                    'version' => $this->getCurrentModuleVersion()
                )
            ),
            new Varien_Object(
                array(
                    'id' => 'desired_version',
                    'name' => 'Required Version',
                    'version' => $this->getDesiredVersion()
                )
            )
        );
    }

    public function addAdminNotification($title, $description)
    {
        /** @var Mage_AdminNotification_Model_Inbox $inbox */
        $inbox = Mage::getModel('adminNotification/inbox');
        $inbox->add(
            Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
            $title,
            $description
        );
    }

    public function getAffectedOrdersIds($fromDate = false, $toDate = false)
    {
        $affectedOrders = Mage::app()->getCache()->load(self::AFFECTED_ORDER_CACHE_PATH);
        if ($affectedOrders) {
            $affectedOrders = unserialize($affectedOrders);
        }
        if (!$affectedOrders || $affectedOrders['from_date'] != $fromDate || $affectedOrders['to_date'] != $toDate) {
            $affectedOrders = array();
            $affectedOrders['from_date'] = $fromDate;
            $affectedOrders['to_date'] = $toDate;
            $affectedOrders['ids'] = array();
            $conditions = array();
            $bind = array();
            if ($fromDate) {
                $conditions[] = "updated_at > :fromDate";
                $bind[":fromDate"] = $fromDate;
            }
            if ($fromDate) {
                $conditions[] = "updated_at <= :toDate";
                $bind[":toDate"] = $toDate;
            }

            /** @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            /** ordered */

            $union = array();
            $union[] = $connection->select()
                ->from(array('ordered' => $this->getTableName('sales_flat_order')))
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id' => 'entity_id'));

            /** invoices */
            $union[] = $connection->select()
                ->from(array('invoices' => $this->getTableName('sales_flat_invoice')))
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'));

            /** shipments */
            $union[] = $connection->select()
                ->from(array('shipments' => $this->getTableName('sales_flat_shipment')))
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'));

            /** refunds */
            $union[] = $connection->select()
                ->from(array('refunds' => $this->getTableName('sales_flat_creditmemo')))
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'));

            foreach ($union as $query) {
                foreach ($conditions as $condition) {
                    $query->where($condition);
                }
            }

            $affectedSql = $connection->select()->union($union);

            foreach ($connection->fetchAll($affectedSql, $bind) as $row) {
                if (isset($row['order_id']) && !in_array($row['order_id'], $affectedOrders)) {
                    $affectedOrders['ids'][] = $row['order_id'];
                }
            }

            Mage::app()->getCache()->save(serialize($affectedOrders), self::AFFECTED_ORDER_CACHE_PATH);
        }

        return $affectedOrders['ids'];
    }

    /**
     * get db read adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getDbRead()
    {
        if (!$this->dbRead) {
            $this->dbRead = $this->getResource()->getConnection('core_read');
        }
        return $this->dbRead;
    }

    /**
     * get db resource object
     *
     * @return Mage_Core_Model_Resource
     */
    public function getResource()
    {
        if (!$this->resource) {
            $this->resource = Mage::getSingleton('core/resource');
        }
        return $this->resource;
    }

    /**
     * return table name with prefix
     *
     * @param string $name table name without prefix
     *
     * @return string
     */
    public function getTableName($name)
    {
        if (!isset($this->tableName[$name])) {
            $this->tableName[$name] = $this->getResource()->getTableName($name);
        }
        return $this->tableName[$name];
    }
}