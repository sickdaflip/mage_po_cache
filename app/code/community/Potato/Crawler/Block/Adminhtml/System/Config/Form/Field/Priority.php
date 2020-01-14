<?php

class Potato_Crawler_Block_Adminhtml_System_Config_Form_Field_Priority extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $element
            ->setRenderer(Mage::getBlockSingleton('po_crawler/adminhtml_system_config_form_field_renderer_priority'))
            ->getHtml();
    }
}