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
class SavvyCube_Connector_Model_Api_Shipment extends SavvyCube_Connector_Model_Api_Abstract
{
    protected $mainTable = 'sales_flat_shipment';

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
                'store_id',
                'total_weight',
                'total_qty',
                'order_id',
                'customer_id',
                'shipping_address_id',
                'billing_address_id',
                'shipment_status',
                'increment_id',
                'created_at',
                'updated_at',
                'shipping_label',
            ),
            'main_table'
        );
    }
}