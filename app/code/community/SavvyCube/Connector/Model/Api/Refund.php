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
class SavvyCube_Connector_Model_Api_Refund extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_flat_creditmemo';

    protected $versionColumns = array(
        'discount_description' => array(
            'check_existence' => true
        ),
        'base_shipping_hidden_tax_amnt' => array(
            'renamed' => array(
                'since' => '1.6.0.0',
                'originally' => 'base_shipping_hidden_tax_amount'
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
                'entity_id',
                'base_shipping_tax_amount',
                'store_to_order_rate',
                'base_discount_amount',
                'base_to_order_rate',
                'base_adjustment_negative',
                'base_subtotal_incl_tax',
                'base_shipping_amount',
                'store_to_base_rate',
                'base_to_global_rate',
                'base_adjustment',
                'base_subtotal',
                'base_grand_total',
                'base_adjustment_positive',
                'base_tax_amount',
                'order_id',
                'creditmemo_status',
                'state',
                'shipping_address_id',
                'billing_address_id',
                'invoice_id',
                'store_currency_code',
                'order_currency_code',
                'base_currency_code',
                'global_currency_code',
                'transaction_id',
                'increment_id',
                'created_at',
                'updated_at',
                'base_hidden_tax_amount',
                'base_shipping_hidden_tax_amnt',
                'base_shipping_incl_tax',
                'discount_description',
            ),
            'main_table'
        );
    }
}