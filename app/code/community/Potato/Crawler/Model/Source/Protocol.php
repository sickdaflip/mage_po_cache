<?php

class Potato_Crawler_Model_Source_Protocol
{
    const HTTP_VALUE  = 'http';
    const HTTPS_VALUE = 'https';
    const HTTP_LABEL  = 'HTTP';
    const HTTPS_LABEL = 'HTTPS';

    /**
     * @return array
     */
    static function toOptionArray()
    {
        return array (
            array (
                'value' => self::HTTPS_VALUE,
                'label' => Mage::helper('po_crawler')->__(self::HTTPS_LABEL)
            ),
            array (
                'value' => self::HTTP_VALUE,
                'label' => Mage::helper('po_crawler')->__(self::HTTP_LABEL)
            )
        );
    }
}