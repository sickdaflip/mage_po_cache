<?php

/**
 * Class Potato_FullPageCache_Model_Observer_Updater
 */
class Potato_FullPageCache_Model_Observer_Updater
{
    const EVENT_LOGIN        = 1;
    const EVENT_COMPARE      = 2;
    const EVENT_PRODUCT_VIEW = 3;
    const EVENT_CART         = 4;
    const EVENT_VOTE         = 5;
    const EVENT_WISHLIST     = 6;
    const EVENT_MESSAGE      = 7;

    public function login()
    {
        return $this->_update(self::EVENT_LOGIN);
    }

    public function cartUpdate()
    {
        $collection = Mage::getResourceModel('po_fpc/storage_collection')->loadByRequestUrl(
            Potato_FullPageCache_Helper_Data::getReferrerPage(true)
        );
        $cache = Potato_FullPageCache_Model_Cache::getOutputCache(null);
        foreach ($collection as $storage) {
            $cache->getFrontend()->remove($storage->getCacheId());
            Potato_FullPageCache_Helper_CacheStorage::unregisterCache($storage->getCacheId());
        }
        return $this->_update(self::EVENT_CART);
    }

    public function compare()
    {
        return $this->_update(self::EVENT_COMPARE);
    }

    public function wishlist()
    {
        return $this->_update(self::EVENT_WISHLIST);
    }

    public function productViewUpdater()
    {
        $blockCache = Potato_FullPageCache_Model_Cache::getPageCache(true)->getBlockCache();
        $sessionBlocks = $blockCache->getSessionBlocks();
        foreach ($sessionBlocks as $index => $blockData) {
            $blockProcessor = $blockCache->getBlockCacheProcessor($index);
            if (!$blockProcessor instanceof Potato_FullPageCache_Model_Processor_Block_Session_Viewed) {
                continue;
            }

            try {
                $blockProcessor
                    ->remove($index)
                ;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }

    public function messagesUpdate()
    {
        return $this->_update(self::EVENT_MESSAGE);
    }

    public function productView()
    {
        return $this->_update(self::EVENT_PRODUCT_VIEW);
    }

    public function pollVoteAdd()
    {
        return $this->_update(self::EVENT_VOTE);
    }

    protected function _update($eventName)
    {
        if (!Mage::app()->useCache('po_fpc')) {
            return $this;
        }

        $blockCache = Potato_FullPageCache_Model_Cache::getPageCache(true)->getBlockCache();
        $sessionBlocks = $blockCache->getSessionBlocks();
        foreach ($sessionBlocks as $index => $blockData) {
            $blockProcessor = $blockCache->getBlockCacheProcessor($index);
            try {
                $blockProcessor
                    ->update($index, $blockData, $eventName)
                ;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }
}