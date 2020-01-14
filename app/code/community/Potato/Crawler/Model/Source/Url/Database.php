<?php

class Potato_Crawler_Model_Source_Url_Database
{
    /**
     * @var Mage_Core_Model_Store null
     */
    protected $_store = null;

    public function __construct($store)
    {
        $this->_store = $store;
    }

    /**
     * Prepare product urls
     *
     * @param array $ids
     * @return array
     */
    public function getProductUrls($ids=array())
    {
        $_result = array();
        /** @var Potato_Crawler_Model_Resource_Url_Database_Catalog_Product $products */
        $products = Mage::getResourceModel('po_crawler/url_database_catalog_product');
        if (!empty($ids)) {
            $products->addFilterByIds($ids);
        }
        if (!Potato_Crawler_Helper_Config::useShortProductUrls($this->_store)) {
            $products->setWithCategoryFlag(true);
        }
        /** @var array $collection */
        $collection = $products->getCollection($this->_store->getId());
        foreach ($collection as $product) {
            $_result[] = $product->getUrl();
        }
        unset($collection);
        unset($products);
        return $_result;
    }

    /**
     * Prepare category urls
     *
     * @param array $ids
     * @return array
     */
    public function getCategoryUrls($ids=array())
    {
        $_result = array();
        /** @var Potato_Crawler_Model_Resource_Url_Database_Catalog_Category $categories */
        $categories = Mage::getResourceModel('po_crawler/url_database_catalog_category');
        if (!empty($ids)) {
            $categories->addFilterByIds($ids);
        }
        /** @var array $collection */
        $collection = $categories->getCollection($this->_store->getId());
        foreach ($collection as $category) {
            $_result[] = $category->getUrl();
        }
        unset($collection);
        unset($categories);
        return $_result;
    }

    /**
     * Prepare CMS urls
     *
     * @param array $ids
     * @return array
     */
    public function getCmsUrls($ids=array())
    {
        $_result = array();
        /** @var Potato_Crawler_Model_Resource_Url_Database_Cms_Page $categories */
        $cms = Mage::getResourceModel('po_crawler/url_database_cms_page');
        if (!empty($ids)) {
            $cms->addFilterByIds($ids);
        }
        $collection = $cms->getCollection($this->_store->getId());
        foreach ($collection as $item) {
            $_result[] = $item->getUrl();
        }
        unset($collection);
        return $_result;
    }
}