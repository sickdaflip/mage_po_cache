<?php

class Potato_Crawler_Adminhtml_Potato_Crawler_QueueController extends Mage_Adminhtml_Controller_Action
{
    public function addAction()
    {
        $storeCode = $this->getRequest()->getParam('store', null);
        $websiteCode = $this->getRequest()->getParam('website', null);
        $stores = Mage::helper('po_crawler/queue')->getStores();
        if ($storeCode) {
            $stores = array(Mage::app()->getStore($storeCode));
        }
        if ($websiteCode) {
            $stores = Mage::app()->getWebsite($websiteCode)->getStores();
        }

        Mage::dispatchEvent('potato_crawler_add_to_queue', array(
            'stores' => $stores
        ));

        $this->_getSession()->addSuccess(
            $this->__('Store urls have been added to queue.')
        );
        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return true;
    }
}