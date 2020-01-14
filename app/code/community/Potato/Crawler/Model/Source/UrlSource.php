<?php

class Potato_Crawler_Model_Source_UrlSource
{
    const DATABASE_VALUE  = 1;
    const SITEMAP_VALUE   = 2;
    const DATABASE_LABEL  = 'Database';
    const SITEMAP_LABEL   = 'Sitemap';

    /**
     * @return array
     */
    static function toOptionArray()
    {
        return array (
            self::DATABASE_VALUE => Mage::helper('po_crawler')->__(self::DATABASE_LABEL),
            self::SITEMAP_VALUE  => Mage::helper('po_crawler')->__(self::SITEMAP_LABEL)
        );
    }

    static function getInstance($store)
    {
        $source = Potato_Crawler_Helper_Config::getSource($store);
        if ($source == self::SITEMAP_VALUE) {
            $path = Potato_Crawler_Helper_Config::getSourcePath($store);
            return new Potato_Crawler_Model_Source_Url_Sitemap($path, $store);
        }
        return new Potato_Crawler_Model_Source_Url_Database($store);
    }
}