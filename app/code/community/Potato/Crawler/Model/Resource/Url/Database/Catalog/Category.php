<?php

class Potato_Crawler_Model_Resource_Url_Database_Catalog_Category extends Mage_Sitemap_Model_Resource_Catalog_Category
{
    protected $_ids = array();

    /**
     * @param $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->_ids = $ids;
        return $this;
    }

    /**
     * @return array
     */
    protected function _loadEntities()
    {
        if (!empty($this->_ids)) {
            $this->_select->where('main_table.entity_id IN(?)', $this->_ids);
        }
        return parent::_loadEntities();
    }
}