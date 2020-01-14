<?php

class Potato_Crawler_Model_Resource_Url_Database_Catalog_Product extends Mage_Sitemap_Model_Resource_Catalog_Product
{
    protected $_ids = null;
    protected $_withCategoryFlag = false;

    /**
     * @param $ids
     * @return $this
     */
    public function addFilterByIds($ids)
    {
        $this->_ids = $ids;
        return $this;
    }

    public function setWithCategoryFlag($value)
    {
        $this->_withCategoryFlag = $value;
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
        if ($this->_withCategoryFlag) {
            $part = $this->_select->getPart(Zend_Db_Select::FROM);
            if (array_key_exists('url_rewrite', $part) && array_key_exists('joinCondition', $part['url_rewrite'])) {
                $part['url_rewrite']['joinCondition'] = str_replace("AND url_rewrite.category_id IS NULL",
                    "", $part['url_rewrite']['joinCondition']
                );
                $this->_select->setPart(Zend_Db_Select::FROM, $part);
            }
        }
        $entities = array();
        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $entity = $this->_prepareObject($row);
            array_push($entities, $entity);
        }
        return $entities;
    }
}