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
class SavvyCube_Connector_Model_Api_Orderitem extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_flat_order_item';

    protected $versionColumns = array(
        'tax_canceled' => array(
            'since' => '1.6.0.0'
        ),
        'hidden_tax_canceled' => array(
            'since' => '1.6.0.0'
        ),
        'base_tax_refunded' => array(
            'since' => '1.6.0.5'
        ),
        'base_discount_refunded' => array(
            'since' => '1.6.0.5'
        ),
        'base_weee_tax_applied_row_amnt' => array(
            'renamed' => array(
                'since' => '1.6.0.0',
                'originally' => 'base_weee_tax_applied_row_amount'
            )
        )
    );

    /**
     * Return columns list for getMethod select
     *
     * @return string | array
     */
    protected function columnsListForGet()
    {
        return $this->prepareColumns(
            array(
                'entity_id' => 'item_id',
                'order_id',
                'parent_item_id',
                'created_at',
                'updated_at',
                'product_id',
                'product_type',
                'product_options',
                'weight',
                'is_virtual',
                'sku',
                'name',
                'description',
                'applied_rule_ids',
                'additional_data',
                'free_shipping',
                'is_qty_decimal',
                'no_discount',
                'qty_backordered',
                'qty_canceled',
                'qty_invoiced',
                'qty_ordered',
                'qty_refunded',
                'qty_shipped',
                'base_cost',
                'base_price',
                'base_original_price',
                'tax_percent',
                'base_tax_amount',
                'base_tax_invoiced',
                'discount_percent',
                'base_discount_amount',
                'base_discount_invoiced',
                'base_amount_refunded',
                'base_row_total',
                'base_row_invoiced',
                'row_weight',
                'base_tax_before_discount',
                'base_price_incl_tax',
                'base_row_total_incl_tax',
                'base_hidden_tax_amount',
                'base_hidden_tax_invoiced',
                'base_hidden_tax_refunded',
                'tax_canceled',
                'hidden_tax_canceled',
                'base_tax_refunded',
                'base_discount_refunded',
                'gift_message_id',
                'gift_message_available',
                'base_weee_tax_applied_amount',
                'base_weee_tax_applied_row_amnt',
                'base_weee_tax_disposition',
                'base_weee_tax_row_disposition'
            ),
            'main_table'
        );
    }
}