<?php

/**
 * Class Potato_FullPageCache_Model_Observer
 */
class Potato_FullPageCache_Model_Observer
{
    /**
     * set block frames in block html
     *
     * @param $observer
     *
     * @return $this
     */
    public function setFrameTags($observer)
    {
        if (!Mage::app()->useCache('po_fpc') ||
            Potato_FullPageCache_Helper_Data::isUpdater() ||
            !Potato_FullPageCache_Model_Processor::getIsAllowed()
        ) {
            return $this;
        }
        $block = $observer->getBlock();
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache(true);
        if ($pageCache->getDynamicBlockCache()->isDynamic($block)) {
            $pageCache->getDynamicBlockCache()->saveBlock($block);
            return $this;
        }
        if ($pageCache->getBlockCache()->getIsCanCache($block->getNameInLayout())) {
            return $this;
        }
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        $pageCache->setFrameTags($block);
        return $this;
    }

    /**
     * cache skipped blocks content
     *
     * @param $observer
     *
     * @return $this
     */
    public function cacheSkippedBlocks($observer)
    {
        if (!Mage::app()->useCache('po_fpc') || empty($_COOKIE)) {
            return $this;
        }
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache(true);
        if (!$pageCache->getBlockCache()->getIsCanCache($block->getNameInLayout())) {
            try {
                $pageCache->getBlockCache()->saveSkippedBlockCache(
                    array(
                        'html'           => $transport->getHtml(),
                        'name_in_layout' => $block->getNameInLayout()
                    ),
                    $block->getNameInLayout()
                );
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        if (Potato_FullPageCache_Helper_Data::isDebugModeEnabled() &&
            Potato_FullPageCache_Helper_Config::canShowBlockHint()
        ) {
            $blockHtml = '<div style="border:1px solid green;width:auto;height:auto;"><div style="color:green;">'
                . $block->getNameInLayout() . '</div>' . $transport->getHtml() . '</div>'
            ;
            $transport->setHtml($blockHtml);
        }
        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function cacheResponse(Varien_Event_Observer $observer)
    {
        if (!Potato_FullPageCache_Helper_Data::canCache()) {
            if (isset($_GET['___store']) || isset($_GET['___from_store'])) {
                //set store cookie
                Potato_FullPageCache_Model_Cache::setStoreCookie(Mage::app()->getStore()->getId());//save current store id
            }
            return $this;
        }

        if ($toolbarBlock = Mage::app()->getLayout()->getBlock('toolbar')) {
            //no cache if not default order | dir
            if ($toolbarBlock->getData('_current_grid_direction') != Mage::getSingleton('catalog/session')->getSortDirection() ||
                Mage::getSingleton('catalog/session')->getDisplayMode()
            ) {
                return $this;
            }
        }

        //save current store id
        Potato_FullPageCache_Model_Cache::setStoreCookie(Mage::app()->getStore()->getId());

        //save current customer group id
        Potato_FullPageCache_Model_Cache::setCustomerGroupCookie(Mage::getSingleton('customer/session')->getCustomerGroupId());

        //save current currency
        Potato_FullPageCache_Model_Cache::setCurrencyCookie(Mage::app()->getStore()->getCurrentCurrencyCode());

        if (Potato_FullPageCache_Helper_Config::includeSorting() && (isset($_GET['order']) || isset($_GET['dir']))) {
            //skip caching its sort order|sort dir switching
            return $this;
        }

        //save mage config
        Potato_FullPageCache_Model_Cache::saveMageConfigXml();

        $response = $observer->getEvent()->getResponse();
        $pageCache = Potato_FullPageCache_Model_Cache::getPageCache();
        if (null === $pageCache->getId()) {
            return $this;
        }
        $pageCache->setCanUseStoreDataFlag(true);
        $content = $response->getBody();

        //replace old form key
        $content = preg_replace('/' . Mage::getSingleton('core/session')->getFormKey() . '/', 'PO_FPC_FORM_KEY', $content);
        try {
            //save response body
            $pageCache
                ->resetId()
                ->save($content, null, Potato_FullPageCache_Helper_Data::getCacheTags(), false, Potato_FullPageCache_Helper_Data::getPrivateTag())
            ;
            Mage::app()->getResponse()
                ->setHeader('X-Cache', 'MISS', true)
            ;
        } catch (Exception $e) {
            Mage::logException($e);
        }

        //set response
        $response->setBody($response->getBody());
        return $this;
    }

    /**
     * save current cms - used for cache tags
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function registerCmsPage(Varien_Event_Observer $observer)
    {
        if (Mage::app()->useCache('po_fpc')) {
            Mage::register('current_cms', $observer->getEvent()->getPage(), true);
        }
        return $this;
    }

    /**
     * update customer group cookie after customer login logout
     *
     * @return $this
     */
    public function updateCustomerGroupCookie()
    {
        try {
            Potato_FullPageCache_Model_Cache::setCustomerGroupCookie(Mage::getSingleton('customer/session')->getCustomerGroupId());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
}