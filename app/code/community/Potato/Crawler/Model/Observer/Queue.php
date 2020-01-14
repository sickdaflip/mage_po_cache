<?php

class Potato_Crawler_Model_Observer_Queue
{
    const CACHE_QUEUE_ALL_FLAG = 'Potato_Crawler_Model_Observer_Queue::ALL';
    const CACHE_QUEUE_PRODUCT_FLAG = 'Potato_Crawler_Model_Observer_Queue::PRODUCT';
    const CACHE_QUEUE_CATEGORY_FLAG = 'Potato_Crawler_Model_Observer_Queue::CATEGORY';
    const CACHE_QUEUE_CMS_FLAG = 'Potato_Crawler_Model_Observer_Queue::CMS';
    const CACHE_QUEUE_STORES_FLAG = 'Potato_Crawler_Model_Observer_Queue::STORE';

    const CACHE_LIFETIME = 1800;

    public function cronProcess()
    {
        if (!Potato_Crawler_Helper_Queue::isMatchingCronSettings(Potato_Crawler_Helper_Config::getCronJob())) {
            return $this;
        }
        Mage::helper('po_crawler')->log('Warmup via cron.');
        Mage::app()->saveCache(true, self::CACHE_QUEUE_ALL_FLAG, array(), self::CACHE_LIFETIME);
        return $this;
    }

    /**
     * Add all website urls to queue
     *
     * @return $this
     */
    public function addAll()
    {
        Mage::helper('po_crawler')->log('Flushing cache has been registered.');
        Mage::app()->saveCache(true, self::CACHE_QUEUE_ALL_FLAG, array(), self::CACHE_LIFETIME);
        return $this;
    }

    public function addAllByFpcClean()
    {
        $tags = Mage::app()->getRequest()->getParam('types', array());
        if (in_array('po_fpc', $tags) ||
            in_array('fpc', $tags) ||
            in_array('amfpc', $tags) ||
            in_array('full_page', $tags)
        ) {
            Mage::helper('po_crawler')->log('Flushing FPC cache has been registered.');
            Mage::app()->saveCache(true, self::CACHE_QUEUE_ALL_FLAG, array(), self::CACHE_LIFETIME);
        }
        return $this;
    }

    /**
     * Add product page url
     *
     * @param Varien_Event_Observer $observer
     */
    public function addProductPage(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        try {
            Mage::helper('po_crawler')->log('Product %s has been updated.', array($product->getId()));
            $this->_addProduct($product);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addStore(Varien_Event_Observer $observer)
    {
        $stores = $observer->getStores();
        foreach ($stores as $store) {
            try {
                $this->_addToQueue($store->getId(), self::CACHE_QUEUE_STORES_FLAG);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Add category page url
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addCategoryPage(Varien_Event_Observer $observer)
    {
        try {
            Mage::helper('po_crawler')->log('Category %s has been updated.', array($observer->getCategory()->getId()));
            $this->_addToQueue($observer->getCategory()->getId(), self::CACHE_QUEUE_CATEGORY_FLAG);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add Cms page url
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addCms(Varien_Event_Observer $observer)
    {
        try {
            Mage::helper('po_crawler')->log('Cms %s has been updated.', array($observer->getEvent()->getObject()->getId()));
            $this->_addToQueue($observer->getEvent()->getObject()->getId(), self::CACHE_QUEUE_CMS_FLAG);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add product url by stock item
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addProductFromStock(Varien_Event_Observer $observer)
    {
        $stockItem = $observer->getEvent()->getItem();
        try {
            Mage::helper('po_crawler')->log('Product %s has been updated.', array($stockItem->getProductId()));
            $product = Mage::getModel('catalog/product')->load($stockItem->getProductId());
            $this->_addProduct($product);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Add products urls
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addProductPages(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();
        Mage::helper('po_crawler')->log('Products %s have been updated.', array(implode(",", $productIds)));
        foreach ($productIds as $productId) {
            try {
                /** @var Mage_Catalog_Model_Product $product */
                $product = Mage::getModel('catalog/product')->load($productId);
                $this->_addProduct($product);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    /**
     * Add urls by catalog product
     *
     * @param $product
     * @return $this
     */
    protected function _addProduct($product)
    {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $configurableIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($product->getId())
            ;
            $groupedIds = Mage::getModel('catalog/product_type_grouped')
                ->getParentIdsByChild($product->getId())
            ;
            $productIds = array_merge($configurableIds, $groupedIds);
        } else {
            $productIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());
        }
        array_push($productIds, $product->getId());
        foreach ($productIds as $productId) {
            try {
                $this->_addToQueue($productId, self::CACHE_QUEUE_PRODUCT_FLAG);
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            foreach ($product->getCategoryIds() as $categoryId) {
                try {
                    $this->_addToQueue($categoryId, self::CACHE_QUEUE_CATEGORY_FLAG);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

    protected function _addToQueue($objectId, $cacheIndex)
    {
        if (!$objectIds = Mage::app()->loadCache($cacheIndex)) {
            $objectIds = serialize(array());
        }
        $objectIds = unserialize($objectIds);
        array_push($objectIds, $objectId);
        Mage::app()->saveCache(serialize($objectIds), $cacheIndex, array(), self::CACHE_LIFETIME);
        return $this;
    }

    protected function _addProductPages($productId)
    {
        $helper = Mage::helper('po_crawler/queue');
        foreach (Mage::helper('po_crawler/queue')->getStores() as $store) {
            $product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($productId);
            $pagePriority = Potato_Crawler_Helper_Config::getPages($store);
            $helper->addUrlByPath($product->getUrl(),
                $store,
                array_search(Potato_Crawler_Model_Source_PageGroup::PRODUCT_VALUE, $pagePriority)
            );
        }
        return $this;
    }
}