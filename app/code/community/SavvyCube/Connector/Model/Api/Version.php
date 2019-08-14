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
class SavvyCube_Connector_Model_Api_Version extends SavvyCube_Connector_Model_Api_Abstract
{


    /**
     * Render response on wCube/api/version get query
     *
     * @return array
     */
    public function getMethod()
    {
        /** @var SavvyCube_Connector_Helper_Data $helper */
        $helper = Mage::helper('wCube');
        $currentVersion = $helper->getCurrentModuleVersion();
        $currentTimezone = Mage::app()->getDefaultStoreView()->getConfig('general/locale/timezone');

        if (array_key_exists('version', $this->request)
            && $this->request['version'] != $currentVersion
        ) {
            $helper->setDesiredVersion($this->request['version']);
        }

        $bottomDateSql = $this->getHelper()->getDbRead()->select()
            ->from(array('order' => $this->getHelper()->getTableName('sales_flat_order')))
            ->reset(Varien_Db_Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date');

        $bottomDate = $this->getHelper()->getDbRead()->fetchOne($bottomDateSql);

        return array(
            'module_version' => $currentVersion,
            'magento_version' => Mage::getVersion(),
            'source_bottom' => $bottomDate,
            'timezone' => $currentTimezone
        );
    }

}