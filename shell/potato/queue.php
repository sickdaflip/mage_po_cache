<?php

require_once __DIR__ . '/../abstract.php';
class Potato_Shell_Warmer_Queue extends Mage_Shell_Abstract
{
    const LOCK_FILE_NAME = 'queue.lock';

    public function run()
    {
        if ($this->_isLocked()) {
            return false;
        }
        ini_set('max_execution_time', -1);
        $this
            ->_addAll()
            ->_addSpecified()
        ;
        $this->_removeLock();
    }

    protected function _addSpecified()
    {
        $result = array(
            'cms'      => array(-1),
            'product'  => array(-1),
            'category' => array(-1)
        );
        if ($cmsIds = Mage::app()->loadCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_CMS_FLAG)) {
            $this->_removeCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_CMS_FLAG);
            $result['cms'] = array_unique(unserialize($cmsIds));
        }
        if ($productIds = Mage::app()->loadCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_PRODUCT_FLAG)) {
            $this->_removeCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_PRODUCT_FLAG);
            $result['product'] = array_unique(unserialize($productIds));
        }
        if ($categoryIds = Mage::app()->loadCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_CATEGORY_FLAG)) {
            $this->_removeCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_CATEGORY_FLAG);
            $result['category'] = array_unique(unserialize($categoryIds));
        }
        if ($cmsIds || $productIds || $categoryIds) {
            try {
                Mage::getModel('po_crawler/cron_queue')->addSpecified($result);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        if ($storeIds = Mage::app()->loadCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_STORES_FLAG)) {
            $this->_removeCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_STORES_FLAG);
            $storeIds = array_unique(unserialize($storeIds));
            foreach ($storeIds as $storeId) {
                try {
                    $store = Mage::app()->getStore($storeId);
                    Mage::getModel('po_crawler/cron_queue')->addStoreUrls($store);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        return $this;
    }

    protected function _addAll()
    {
        if (!Mage::app()->loadCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_ALL_FLAG)) {
            return $this;
        }
        try {
            $this->_removeCache(Potato_Crawler_Model_Observer_Queue::CACHE_QUEUE_ALL_FLAG);
            Mage::getModel('po_crawler/cron_queue')->process();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    protected function _removeCache($index)
    {
        Mage::app()->removeCache($index);
        return $this;
    }

    protected function _isLocked()
    {
        if (file_exists(__DIR__ . '/' . self::LOCK_FILE_NAME)) {
            $diff = time() - filemtime(__DIR__ . '/' . self::LOCK_FILE_NAME);
            if ($diff < 1800) {
                return true;
            }
        }
        file_put_contents(__DIR__ . '/' . self::LOCK_FILE_NAME, getmypid());
        return false;
    }

    protected function _removeLock()
    {
        @unlink(__DIR__ . '/' . self::LOCK_FILE_NAME);
    }
}
$shell = new Potato_Shell_Warmer_Queue();
$shell->run();