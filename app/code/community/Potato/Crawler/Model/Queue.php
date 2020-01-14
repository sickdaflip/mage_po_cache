<?php

class Potato_Crawler_Model_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/queue');
    }

    public function loadByHash($hash)
    {
        return $this->load($hash, 'hash');
    }
}