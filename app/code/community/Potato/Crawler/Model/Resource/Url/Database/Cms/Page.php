<?php

class Potato_Crawler_Model_Resource_Url_Database_Cms_Page extends Mage_Sitemap_Model_Resource_Cms_Page
{
    protected $_ids = null;

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
     * @param unknown_type $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        $pages = array();

        $select = $this->_getWriteAdapter()->select()
            ->from(array('main_table' => $this->getMainTable()), array($this->getIdFieldName(), 'identifier AS url'))
            ->join(
                array('store_table' => $this->getTable('cms/page_store')),
                'main_table.page_id=store_table.page_id',
                array()
            )
            ->where('main_table.is_active=1')
            ->where('store_table.store_id IN(?)', array(0, $storeId));
        if (!empty($this->_ids)) {
            $select->where('main_table.page_id IN(?)', $this->_ids);
        }
        $query = $this->_getWriteAdapter()->query($select);
        while ($row = $query->fetch()) {
            if ($row['url'] == Mage_Cms_Model_Page::NOROUTE_PAGE_ID) {
                continue;
            }
            if ($row['url'] == "home") {
                $row['url'] = "";
            }
            $page = $this->_prepareObject($row);
            $pages[$page->getId()] = $page;
        }
        return $pages;
    }
}