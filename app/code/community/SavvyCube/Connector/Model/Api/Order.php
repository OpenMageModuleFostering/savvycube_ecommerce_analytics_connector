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
class SavvyCube_Connector_Model_Api_Order extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_flat_order';

    protected $versionColumns = array(
        'coupon_rule_name' => array(
            'since' => '1.6.0.7'
        ),
        'base_shipping_hidden_tax_amnt' => array(
            'renamed' => array(
                'since' => '1.6.0.7',
                'originally' => 'base_shipping_hidden_tax_amount'
            )
        )
    );

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
                array('group_table' => $this->getHelper()->getTableName('customer_group')),
                "group_table.customer_group_id = main_table.customer_group_id"
            )
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns($this->columnsListForGet());

        return $this->getResult($sql, '`main_table`.updated_at');
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
                    'entity_id',
                    'state',
                    'status',
                    'coupon_code',
                    'shipping_description',
                    'is_virtual',
                    'store_id',
                    'customer_id',
                    'base_discount_amount',
                    'base_discount_canceled',
                    'base_discount_invoiced',
                    'base_discount_refunded',
                    'base_grand_total',
                    'base_shipping_amount',
                    'base_shipping_canceled',
                    'base_shipping_invoiced',
                    'base_shipping_refunded',
                    'base_shipping_tax_amount',
                    'base_shipping_tax_refunded',
                    'base_subtotal',
                    'base_subtotal_canceled',
                    'base_subtotal_invoiced',
                    'base_subtotal_refunded',
                    'base_tax_amount',
                    'base_tax_canceled',
                    'base_tax_invoiced',
                    'base_tax_refunded',
                    'base_to_global_rate',
                    'base_to_order_rate',
                    'base_total_canceled',
                    'base_total_invoiced',
                    'base_total_invoiced_cost',
                    'base_total_offline_refunded',
                    'base_total_online_refunded',
                    'base_total_paid',
                    'base_total_qty_ordered',
                    'base_total_refunded',
                    'customer_is_guest',
                    'customer_note_notify',
                    'billing_address_id',
                    'customer_group_id',
                    'shipping_address_id',
                    'base_adjustment_negative',
                    'base_adjustment_positive',
                    'base_shipping_discount_amount',
                    'base_subtotal_incl_tax',
                    'base_total_due',
                    'weight',
                    'customer_dob',
                    'increment_id',
                    'applied_rule_ids',
                    'base_currency_code',
                    'customer_email',
                    'customer_firstname',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'discount_description',
                    'global_currency_code',
                    'order_currency_code',
                    'shipping_method',
                    'store_name',
                    'customer_note',
                    'created_at',
                    'updated_at',
                    'total_item_count',
                    'customer_gender',
                    'base_hidden_tax_amount',
                    'base_shipping_hidden_tax_amnt',
                    'base_hidden_tax_invoiced',
                    'base_hidden_tax_refunded',
                    'base_shipping_incl_tax',
                    'coupon_rule_name',
                ),
                'main_table'
            ),
            $this->prepareColumns(
                array(
                    'customer_group_code'
                ),
                'group_table'
            )
        );
    }
}