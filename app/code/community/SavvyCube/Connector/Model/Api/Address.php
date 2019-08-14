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
class SavvyCube_Connector_Model_Api_Address extends SavvyCube_Connector_Model_Api_Abstract {

    protected $mainTable = 'sales_flat_order_address';
    /**
     * Render response on wCube/api/address get query
     *
     * @return array
     */
    public function getMethod()
    {
        $sql = $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($this->mainTable)))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns($this->columnsListForGet());

        $affectedOrders = $this->getAffectedOrders();
        if ($affectedOrders !== true && is_array($affectedOrders)) {
            if (count($affectedOrders)) {
                $sql->where("`main_table`.parent_id in (?)", $affectedOrders);
            } else {
                return array();
            }
        }

        return $this->getResult($sql);
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    protected function columnsListForGet()
    {
        return $this->prepareColumns(
            array(
                'entity_id',
                'parent_id',
                'region_id',
                'customer_id',
                'fax',
                'region',
                'postcode',
                'lastname',
                'street',
                'city',
                'email',
                'telephone',
                'country_id',
                'firstname',
                'address_type',
                'prefix',
                'middlename',
                'suffix',
                'company'
            ),
            'main_table'
        );
    }


}