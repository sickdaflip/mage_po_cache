<?php

class Potato_Crawler_Model_Source_PageGroup
{
    const CMS_VALUE      = 1;
    const CATEGORY_VALUE = 2;
    const PRODUCT_VALUE  = 3;
    const CMS_LABEL      = 'Cms';
    const CATEGORY_LABEL = 'Category';
    const PRODUCT_LABEL  = 'Product';

    /**
     * @return array
     */
    static function toOptionArray()
    {
        return array (
            array (
                'value' => self::CMS_VALUE,
                'label' => Mage::helper('po_crawler')->__(self::CMS_LABEL)
            ),
            array (
                'value' => self::CATEGORY_VALUE,
                'label' => Mage::helper('po_crawler')->__(self::CATEGORY_LABEL)
            ),
            array (
                'value' => self::PRODUCT_VALUE,
                'label' => Mage::helper('po_crawler')->__(self::PRODUCT_LABEL)
            )
        );
    }
}