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
class SavvyCube_Connector_Model_Api_Invoiceitem extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_flat_invoice_item';

    protected $versionColumns = array(

        'base_weee_tax_applied_row_amnt' => array(
            'renamed' => array(
                'since' => '1.6.0.0',
                'originally' => 'base_weee_tax_applied_row_amount'
            )
        )
    );

    protected $parentEntity = array(
        'model' => 'wCube/api_invoice',
        'parent_date' => 'updated_at',
        'parent_fk' => 'parent_id'
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
                'parent_id',
                'base_price',
                'base_row_total',
                'base_discount_amount',
                'base_tax_amount',
                'base_price_incl_tax',
                'qty',
                'base_cost',
                'base_row_total_incl_tax',
                'product_id',
                'order_item_id',
                'base_hidden_tax_amount',
                'base_weee_tax_applied_amount',
                'base_weee_tax_applied_row_amnt',
                'base_weee_tax_disposition',
                'base_weee_tax_row_disposition',
            ),
            'main_table'
        );
    }
}