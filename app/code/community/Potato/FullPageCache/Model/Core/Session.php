<?php

class Potato_FullPageCache_Model_Core_Session extends Mage_Core_Model_Session
{
    public function getFormKey()
    {
        if (!Mage::app()->getStore()->isAdmin() &&
            Mage::app()->useCache('po_fpc') &&
            isset($_COOKIE[Potato_FullPageCache_Model_Cache_Page::FORM_KEY_COOKIE_NAME])
        ) {
            return $_COOKIE[Potato_FullPageCache_Model_Cache_Page::FORM_KEY_COOKIE_NAME];
        }
        return parent::getFormKey();
    }
}