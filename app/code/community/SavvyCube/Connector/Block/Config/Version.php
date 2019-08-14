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
class SavvyCube_Connector_Block_Config_Version
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset_Modules_DisableOutput
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $html = $this->_getHeadFieldHtml($element);
        /** @var SavvyCube_Connector_Helper_Data $helper */
        $helper = Mage::helper('wCube');

        foreach ($helper->getVersionData() as $data) {
            $html.= $this->_getFieldHtml($element, $data);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getHeadFieldHtml($fieldset)
    {
        $html  = '<tr>';
        $html .= '<td class="label"/>';
        $html .= '<td class="value"/>';
        $html .= '</tr>';
        return $html;
    }

    protected function _getFieldHtml($fieldset, $data)
    {
        $field = $fieldset->addField((string)$data->id, 'label',
            array(
                'name'          => $data->id,
                'label'         => $data->name,
                'value'         => $data->version,
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }

}