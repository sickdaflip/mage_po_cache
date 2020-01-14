<?php

class Potato_Crawler_Model_Source_Currency
{
    /**
     * @return array
     */
    static function toOptionArray()
    {
        $options = array();
        $currencies = Mage::app()->getLocale()->getTranslationList('currencytoname');
        $allowedCodes = explode(',', self::getCodes());

        foreach ($currencies as $name => $code) {
            if (!in_array($code, $allowedCodes)) {
                continue;
            }

            $options[] = array(
                'label' => $name,
                'value' => $code,
            );
        }
        return $options;
    }

    /**
     * @return string
     */
    static function getCodes()
    {
        $storeCode   = Mage::app()->getRequest()->getParam('store');
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        $path        = Mage_Directory_Model_Currency::XML_PATH_CURRENCY_ALLOW;

        if ($storeCode) {
            return Mage::app()->getStore($storeCode)->getConfig($path);
        }
        if ($websiteCode) {
            return Mage::app()->getWebsite($websiteCode)->getConfig($path);
        }
        return (string) Mage::getConfig()->getNode('default/' . $path);
    }
}