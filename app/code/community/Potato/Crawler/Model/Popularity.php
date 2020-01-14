<?php

class Potato_Crawler_Model_Popularity extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('po_crawler/popularity');
    }
}