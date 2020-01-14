<?php

class Potato_Crawler_Helper_Config extends Mage_Core_Helper_Data
{
    const GENERAL_ENABLED            = 'po_crawler/general/enabled';
    const GENERAL_ACCEPTABLE_CPU     = 'po_crawler/general/acceptable_cpu_load';
    const ADVANCED_STORE_PRIORITY    = 'po_crawler/priority/store_priority';
    const ADVANCED_PAGE_PRIORITY     = 'po_crawler/priority/page_group_priority';
    const ADVANCED_CURRENCY_PRIORITY = 'po_crawler/priority/currency_priority';
    const ADVANCED_CUSTOMER_GROUP    = 'po_crawler/priority/customer_group';
    const ADVANCED_PROTOCOL          = 'po_crawler/priority/protocol';
    const ADVANCED_USERAGENT         = 'po_crawler/advanced/useragent';
    const ADVANCED_SOURCE            = 'po_crawler/advanced/source';
    const ADVANCED_SOURCE_PATH       = 'po_crawler/advanced/source_path';
    const ADVANCED_USE_SHORT_PRODUCT_URL = 'po_crawler/advanced/short_product_url';
    const ADVANCED_DEBUG             = 'po_crawler/advanced/debug';
    const ADVANCED_USE_POPULARITY    = 'po_crawler/advanced/use_popularity';
    const GENERAL_DEFAULT_CRONJOB    = '0 2 * * *';
    const GENERAL_CRONJOB            = 'po_crawler/general/cronjob';

    static function getCronJob()
    {
        $value = Mage::getStoreConfig(self::GENERAL_CRONJOB);
        if (empty($value)) {
            $value = self::GENERAL_DEFAULT_CRONJOB;
        }
        return $value;
    }

    static function isEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ENABLED, $store);
    }

    static function usePopularity($store = null)
    {
        return false;
    }

    static function useShortProductUrls($store = null)
    {
        return (bool)Mage::getStoreConfig(self::ADVANCED_USE_SHORT_PRODUCT_URL, $store);
    }

    static function getAcceptableCpu($store = null)
    {
        return (int)Mage::getStoreConfig(self::GENERAL_ACCEPTABLE_CPU, $store);
    }

    static function getPriority($store = null)
    {
        return (int)Mage::getStoreConfig(self::ADVANCED_STORE_PRIORITY, $store);
    }

    static function getPages($store = null)
    {
        return self::getMultiSelectOptionValues(self::ADVANCED_PAGE_PRIORITY, $store);
    }

    static function getCurrency($store = null)
    {
        return self::getMultiSelectOptionValues(self::ADVANCED_CURRENCY_PRIORITY, $store);
    }

    static function getCustomerGroup($store = null)
    {
        $options = self::getMultiSelectOptionValues(self::ADVANCED_CUSTOMER_GROUP, $store);
        $key = array_search(Potato_Crawler_Model_Source_CustomerGroup::GUEST_VALUE, $options);
        if ($key != False) {
            $options[$key] = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }
        return $options;
    }

    static function getProtocol($store = null)
    {
        return self::getMultiSelectOptionValues(self::ADVANCED_PROTOCOL, $store);
    }

    static function getSource($store = null)
    {
        return (int)Mage::getStoreConfig(self::ADVANCED_SOURCE, $store);
    }

    static function getSourcePath($store = null)
    {
        return Mage::getStoreConfig(self::ADVANCED_SOURCE_PATH, $store);
    }

    static function getUserAgents($store = null)
    {
        $excludes = Mage::getStoreConfig(self::ADVANCED_USERAGENT, $store);
        return unserialize($excludes);
    }

    static function getMultiSelectOptionValues($xmlPath, $store=null)
    {
        $value = trim((string)Mage::app()->getStore($store)->getConfig($xmlPath));
        $_result = array();
        if (null !== $value && false !== $value) {
            $_result = explode(',', $value);
        }
        return $_result;
    }

    static function canDebug($store = null)
    {
        return (bool)Mage::getStoreConfig(self::ADVANCED_DEBUG, $store);
    }
}