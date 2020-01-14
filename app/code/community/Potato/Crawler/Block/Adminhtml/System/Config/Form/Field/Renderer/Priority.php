<?php

class Potato_Crawler_Block_Adminhtml_System_Config_Form_Field_Renderer_Priority
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var Potato_Crawler_Block_Adminhtml_Priority $block */
        $block = Mage::app()->getLayout()->createBlock('po_crawler/adminhtml_priority');
        $block
            ->setElement($element)
        ;
        return $block->toHtml();
    }
}