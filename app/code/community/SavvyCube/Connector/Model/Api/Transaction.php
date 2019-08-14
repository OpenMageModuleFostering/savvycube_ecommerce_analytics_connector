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
class SavvyCube_Connector_Model_Api_Transaction extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_payment_transaction';


    /**
     * Render response
     *
     * @return array
     */
    public function getMethod()
    {
        $sql = $this->getHelper()->getDbRead()->select()
            ->from(array('main_table' => $this->getHelper()->getTableName($this->mainTable)))
            ->joinLeft(
                array('payment_table' => $this->getHelper()->getTableName('sales_flat_order_payment')),
                "main_table.payment_id = payment_table.entity_id"
            )
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns($this->columnsListForGet());

        return $this->getResult($sql, '`main_table`.created_at');
    }

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    protected function columnsListForGet()
    {
        return array_merge(
            $this->prepareColumns(
                array(
                    'base_shipping_captured',
                    'base_amount_paid',
                    'base_amount_authorized',
                    'base_amount_paid_online',
                    'base_amount_refunded_online',
                    'base_shipping_amount',
                    'base_amount_ordered',
                    'base_shipping_refunded',
                    'base_amount_refunded',
                    'base_amount_canceled',
                    'method',
                    'last_trans_id'
                ),
                'payment_table'
            ),
            $this->prepareColumns(
                array(
                    'entity_id' => 'transaction_id',
                    'order_id',
                    'txn_id',
                    'txn_type',
                    'is_closed',
                    'created_at'
                ),
                'main_table'
            )
        );
    }
}