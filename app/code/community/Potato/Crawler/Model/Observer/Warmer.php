<?php

class Potato_Crawler_Model_Observer_Warmer
{
    public function setCurrency()
    {
        if (isset($_COOKIE[Potato_Crawler_Helper_Warmer::STORE_COOKIE_NAME])) {
            Mage::app()->setCurrentStore($_COOKIE[Potato_Crawler_Helper_Warmer::STORE_COOKIE_NAME]);
        }
        if (isset($_COOKIE[Potato_Crawler_Helper_Warmer::CURRENCY_COOKIE_NAME])) {
            $coreSession = Mage::getModel('core/session')
                ->init('store_' . Mage::app()->getStore()->getCode())
            ;
            $coreSession->setCurrencyCode($_COOKIE[Potato_Crawler_Helper_Warmer::CURRENCY_COOKIE_NAME]);
            Mage::app()->getStore()->setCurrentCurrencyCode($_COOKIE[Potato_Crawler_Helper_Warmer::CURRENCY_COOKIE_NAME]);
        }
        return $this;
    }

    public function setCustomer()
    {
        if (isset($_COOKIE[Potato_Crawler_Helper_Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME]) &&
            $_COOKIE[Potato_Crawler_Helper_Warmer::CUSTOMER_GROUP_ID_COOKIE_NAME] != Mage_Customer_Model_Group::NOT_LOGGED_IN_ID
        ) {
            //set session id
            session_id();

            //start session
            Mage::getSingleton('core/session', array('name' => Mage_Core_Controller_Front_Action::SESSION_NAMESPACE))->start();

            //register fake crawler customer
            Mage::register('_singleton/customer/session', Mage::getModel('po_crawler/customer_session'), true);
        }
        return $this;
    }
}