<?php

class Potato_FullPageCache_Helper_Url extends Mage_Core_Helper_Abstract
{
    /**
     * @param $storeId
     * @param $limit
     * @param $offset
     *
     * @return mixed
     */
    public function getRequestPaths($storeId, $limit=null, $offset=null)
    {
        return $this->_getReadAdapter()->fetchAll($this->_getRequestSelect($storeId, $limit, $offset));
    }

    protected function _getTable($name)
    {
        return Mage::getSingleton('core/resource')->getTableName($name);
    }

    protected function _getReadAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    public function getTotalRequestPaths($storeId)
    {
        return $this->getTotalRewrites($storeId) + $this->getTotalCmsPages($storeId);
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    public function getTotalRewrites($storeId)
    {
        $idsSelect = $this->_getRequestSelect($storeId);
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns(new Zend_Db_Expr('COUNT(url_rewrite.url_rewrite_id)'));
        return $this->_getReadAdapter()->fetchOne($idsSelect);
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    protected function _getDisabledCategoriesIds($storeId)
    {
        $categories = Mage::getModel('catalog/category')
            ->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('is_active','0')
        ;
        return $categories->getAllIds();
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    protected function _getDisabledProductsIds($storeId)
    {
        $products = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->getCollection()
            ->addAttributeToFilter(
                array(
                    array('attribute'=> 'status', 'eq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED),
                    array('attribute'=> 'visibility', 'eq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE)
                )
            )
        ;
        return $products->getAllIds();
    }

    /**
     * @param $storeId
     *
     * @return int
     */
    public function getTotalCmsPages($storeId)
    {
        return count($this->getCmsUrls($storeId));
    }

    /**
     * @param      $storeId
     * @param null $limit
     * @param null $offset
     *
     * @return mixed
     */
    protected function _getRequestSelect($storeId, $limit=null, $offset=null)
    {
        if (@class_exists('Enterprise_UrlRewrite_Model_Resource_Url_Rewrite', false)) {
            return $this->_getEERequestSelect($storeId, $limit, $offset);
        }
        $select =
            $this->_getReadAdapter()->select()
                ->from(
                    array('url_rewrite' => $this->_getTable('core/url_rewrite')),
                    array('store_id', 'request_path')
                )
                ->where('store_id=?', $storeId)
                ->where('is_system=1')
                ->limit($limit, $offset)
        ;
        $disabledProductsIds = $this->_getDisabledProductsIds($storeId);
        if (count($disabledProductsIds) > 0) {
            $select->where('product_id NOT IN(' . implode(',', $disabledProductsIds) . ') OR product_id IS NULL');
        }
        $disabledCategoriesIds = $this->_getDisabledCategoriesIds($storeId);
        if (count($disabledCategoriesIds) > 0) {
            $select->where('category_id NOT IN(' . implode(',', $disabledCategoriesIds) . ') OR category_id IS NULL');
        }
        return $select;
    }

    /**
     * @param $storeId
     * @param $limit
     * @param $offset
     *
     * @return mixed
     */
    protected function _getEERequestSelect($storeId, $limit=null, $offset=null)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(
                array('url_rewrite' => $this->_getTable('enterprise_urlrewrite/url_rewrite')),
                array('store_id', 'request_path')
            )
            ->where('url_rewrite.store_id = ?', $storeId)
            ->where('url_rewrite.is_system=1')
            ->limit($limit, $offset)
        ;
        $disabledProductsIds = $this->_getDisabledProductsIds($storeId);
        if (count($disabledProductsIds) > 0) {
            $select
                ->joinLeft(array('url_product_default' => $this->_getTable('enterprise_catalog/product')),
                    'url_rewrite.url_rewrite_id = url_product_default.url_rewrite_id AND  url_product_default.product_id NOT IN(' . implode(',', $disabledProductsIds) . ')',
                    array()
                )
            ;
        }
        $disabledCategoriesIds = $this->_getDisabledCategoriesIds($storeId);
        if (count($disabledCategoriesIds) > 0) {
            $select
                ->joinLeft(array('url_category_default' => $this->_getTable('enterprise_catalog/category')),
                    'url_rewrite.url_rewrite_id = url_category_default.url_rewrite_id AND url_category_default.category_id NOT IN(' . implode(',', $disabledCategoriesIds) . ')',
                    array()
                )
            ;
        }
        return $select;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getCmsUrls($storeId)
    {
        $cms = $this->_getReadAdapter()->select()
            ->from(array('cms' => $this->_getTable('cms/page')), array('identifier'))
            ->join(array('store' => $this->_getTable('cms/page_store')),
                'cms.page_id = store.page_id',
                array()
            )
            ->where('store.store_id IN (?)', array(0, $storeId))
            ->where('cms.identifier !=?', 'no-route')
            ->where('cms.is_active =?', '1')
        ;
        return $this->_getReadAdapter()->fetchAll($cms);
    }
}