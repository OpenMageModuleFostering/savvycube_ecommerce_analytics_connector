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
class SavvyCube_Connector_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Authorization Helper
     *
     * @return SavvyCube_Connector_Helper_Authorization
     */
    protected function getAuthHelper()
    {
        return Mage::helper('wCube/authorization');
    }

    public function indexAction()
    {
        $this->getAuthHelper()->initConsumerKey();
        header("Location: ".$this->getAuthHelper()->getActivateUrl());
        die();
    }
}