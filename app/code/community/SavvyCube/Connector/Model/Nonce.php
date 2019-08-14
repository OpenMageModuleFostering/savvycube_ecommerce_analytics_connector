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

/**
 * Class SavvyCube_Connector_Model_Nonce
 * @method Mage_Core_Model_Resource getResource()
 * @method SavvyCube_Connector_Model_Nonce setResource(Mage_Core_Model_Resource $model)
 * @method string getTable()
 * @method SavvyCube_Connector_Model_Nonce setTable(string $tableName)
 * @method string getCurrentTimestamp()
 * @method SavvyCube_Connector_Model_Nonce setCurrentTimestamp(string)
 *
 */
class SavvyCube_Connector_Model_Nonce extends Varien_Object
{
    /**
     * @return SavvyCube_Connector_Helper_Authorization
     */
    private function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    protected function _construct()
    {
        $this->setResource(Mage::getSingleton('core/resource'));
        $this->setTable($this->getResource()->getTableName('wCube/nonce'));
        $this->setCurrentTimestamp(gmdate('U'));
        parent::_construct();
    }

    /**
     * remove old nonce from DB table
     *
     * @return Zend_Db_Pdo_Statement
     */
    private function cleanTable()
    {
        $lifeTime = $this->getAuthHelper()->getNonceLifetime(true);
        $query = "DELETE FROM `{$this->getTable()}` WHERE `created` <= " . ($this->getCurrentTimestamp() - $lifeTime);
        return $this->write($query);
    }

    /**
     * store nonce
     *
     * @param string $nonce nonce value
     *
     * @return Zend_Db_Pdo_Statement
     */
    private function storeNonce($nonce)
    {
        $query = "INSERT INTO `{$this->getTable()}` VALUES (:nonce, :current_timestamp)";
        return $this->write(
            $query,
            array(
                'nonce' => $nonce,
                'current_timestamp' => $this->getCurrentTimestamp()
            )
        );
    }

    /**
     * check if nonce already stored in last lifeTime seconds
     *
     * @param string $nonce nonce value
     * @param string $created nonce creation datetime in GMT format
     *
     * @return bool
     */
    private function findDoubling($nonce, $created)
    {
        $date = new DateTime($created);
        $timeDiff = $this->getCurrentTimestamp() - $date->format('U');
        if (!($timeDiff > $this->getAuthHelper()->getNonceLifetime(true))) {
            $query = "SELECT * FROM `{$this->getTable()}` "
                . " WHERE `nonce`=:nonce AND (:current_timestamp - `created`) < :lifetime";
            $bind = array(
                'nonce' => $nonce,
                'current_timestamp' => $this->getCurrentTimestamp(),
                'lifetime' => $this->getAuthHelper()->getNonceLifetime(true),
            );
            $result = $this->read($query, $bind);
            if (!count($result)) {
                return false;
            }
        }
        return true;
    }

    /**
     * write date to DB
     *
     * @param string $query sql statement
     * @param array $bind
     *
     * @return Zend_Db_Pdo_Statement
     */
    private function write($query, $bind = array())
    {
        $connection = $this->getResource()->getConnection('core_write');
        return $connection->query($query, $bind);
    }

    /**
     * read data from DB
     *
     * @param string $query sql statement
     * @param array $bind
     *
     * @return array
     */
    private function read($query, $bind = array())
    {
        $connection = $this->getResource()->getConnection('core_read');
        return $connection->fetchAll($query, $bind);
    }

    /**
     * check nonce, store it and clean old nonce
     *
     * @param string $nonce nonce value
     * @param string $created nonce creation datetime in GMT format
     *
     * @return bool
     */
    public function checkNonce($nonce, $created)
    {
        if (!$this->findDoubling($nonce, $created)) {
            $this->storeNonce($nonce);
            $this->cleanTable();
            return true;
        }
        return false;
    }


}