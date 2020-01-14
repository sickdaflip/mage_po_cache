<?php

class Potato_FullPageCache_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_MAX_ALLOWED_SIZE   = 'default/po_fpc/general/max_allowed_size';
    const GENERAL_MOBILE_DETECT      = 'default/po_fpc/general/mobile_detect';
    const GENERAL_MOBILE_SEPARATE    = 'default/po_fpc/general/mobile_separate';
    const GENERAL_AUTO_CLEAN         = 'po_fpc/general/auto_clean';
    const GENERAL_CRONJOB            = 'po_fpc/general/cronjob';
    const GENERAL_DEFAULT_CRONJOB    = '1 0 * * *';

    const DEBUG_ENABLED              = 'default/po_fpc/debug/enabled';
    const DEBUG_IP_ADDRESSES         = 'default/po_fpc/debug/ip_addresses';
    const DEBUG_BLOCK_NAME_HINT      = 'default/po_fpc/debug/block_name_hint';

    static function getCatalogRuleCronJob()
    {
        $value = Mage::getStoreConfig(self::GENERAL_CRONJOB);
        if (empty($value)) {
            $value = self::GENERAL_DEFAULT_CRONJOB;
        }
        return $value;
    }

    /**
     * @return int
     */
    static function getIsMobileDetectEnabled()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::GENERAL_MOBILE_DETECT);
    }

    /**
     * @return int
     */
    static function canSeparateMobileDevices()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::GENERAL_MOBILE_SEPARATE);
    }

    /**
     * @return int
     */
    static function getIsDebugEnabled()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::DEBUG_ENABLED);
    }

    /**
     * @return int
     */
    static function canShowBlockHint()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::DEBUG_BLOCK_NAME_HINT);
    }

    /**
     * @param null $store
     *
     * @return array
     */
    static function getAutoClean($store = null)
    {
        $value = trim((string)Mage::app()->getStore($store)->getConfig(self::GENERAL_AUTO_CLEAN));
        $_result = array();
        if (null !== $value && false !== $value) {
            $_result = explode(',', $value);
        }
        return $_result;
    }

    /**
     * @return array
     */
    static function getDebugIpAddresses()
    {
        $value = trim((string)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::DEBUG_IP_ADDRESSES));
        $_result = array();
        if ($value) {
            $_result = explode(',', $value);
        }
        return $_result;
    }

    /**
     * @return int
     */
    static function getMaxAllowedSize()
    {
        return (int)Potato_FullPageCache_Model_Cache::getCacheConfig()->getNode(self::GENERAL_MAX_ALLOWED_SIZE) * 1024 * 1024;
    }

    /**
     * Switch mode (ajax) hidden option
     *
     * @return bool
     */
    static function canUseAjax()
    {
        return true;
    }

    /**
     * Category sorting compatibility hidden option
     *
     * @return bool
     */
    static function includeSorting()
    {
        return false;
    }

    /**
     * @return bool
     */
    static function canDocumentLoadSuspend()
    {
        return false;
    }

    /**
     * @return bool
     */
    static function canUpdateSessionBlocksCacheWithoutAjax()
    {
        return true;
    }

    /**
     * @return bool
     */
    static function useProductReferrerPage()
    {
        return false;
    }
}