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
class SavvyCube_Connector_Block_Config_Activate
extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getLayout()->createBlock('adminhtml/widget_button') //create the add button
            ->setData(array(
                'label'     => Mage::helper('wCube')->__('Activate Connector / Refresh Activation'),
                'onclick'   => "setLocation('".$this->getUrl('wCubeAdmin/adminhtml_index/index')."')",
                'class'   => 'task'
            ))->toHtml();

    }
}