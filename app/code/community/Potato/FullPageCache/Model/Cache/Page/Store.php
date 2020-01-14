<?php

class Potato_FullPageCache_Model_Cache_Page_Store
{
    const DEFAULT_SCOPE_ID = 0;

    protected $_storeId = null;
    protected $_storeCode = null;
    protected $_currency = null;
    protected $_order = null;
    protected $_dir = null;


    public function getDefaultStoreId()
    {
        if (null === $this->_storeId) {
            try {
                $this->_initStore();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this->_storeId;
    }

    public function getDefaultStoreCode()
    {
        if (null === $this->_storeCode) {
            try {
                $this->_initStore();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this->_storeCode;
    }

    public function getDefaultCurrency()
    {
        if (null === $this->_currency) {
            try {
                if(!$currency = $this->_getConfigData($this->getDefaultStoreId(), Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT)) {
                    $currency = $this->_getConfigData(self::DEFAULT_SCOPE_ID, Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
                }
                $this->_currency = $currency;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this->_currency;
    }

    protected function _initStore()
    {
        $params = Mage::registry('application_params');
        $scopeCode = '';
        if (array_key_exists('scope_code', $params)) {
            $scopeCode = $params['scope_code'];
        }
        $scopeType = 'website';
        if (array_key_exists('scope_type', $params)) {
            $scopeType = $params['scope_type'];
        }

        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $websiteTable = Mage::getSingleton('core/resource')->getTableName('core_website');
        $storeTable = Mage::getSingleton('core/resource')->getTableName('core_store');
        $groupTable = Mage::getSingleton('core/resource')->getTableName('core_store_group');
        /** @var Zend_Db_Select $select */
        $select = $read->select();
        if (!empty($scopeCode) && $scopeType == 'group') {
            $select
                ->from(array('group_table' => $groupTable), array('id' => 'store_table.store_id', 'code' => 'store_table.code'))
                ->joinLeft(array('store_table' => $storeTable), 'store_table.store_id = group_table.default_store_id')
                ->where('group_table.group_id = ?', $scopeCode)
            ;
        }
        if (!empty($scopeCode) && $scopeType == 'store') {
            $select
                ->from(array('store_table' => $storeTable), array('id' => 'store_table.store_id', 'code' => 'store_table.code'))
                ->where('store_table.code = ?', $scopeCode)
            ;
        }
        if (!empty($scopeCode) && $scopeType == 'website') {
            $select
                ->from(array('main_table' => $websiteTable), array('id' => 'store_table.store_id', 'code' => 'store_table.code'))
                ->joinLeft(array('group_table' => $groupTable), 'group_table.group_id = main_table.default_group_id AND group_table.website_id = main_table.website_id')
                ->joinLeft(array('store_table' => $storeTable), 'store_table.store_id = group_table.default_store_id AND store_table.website_id = main_table.website_id')
                ->where('main_table.code = ?', $scopeCode)
            ;
        }
        if (empty($scopeCode)) {
            $select
                ->from(array('main_table' => $websiteTable), array('id' => 'store_table.store_id', 'code' => 'store_table.code'))
                ->joinLeft(array('group_table' => $groupTable), 'main_table.default_group_id = group_table.group_id AND group_table.website_id = main_table.website_id')
                ->joinLeft(array('store_table' => $storeTable), 'group_table.default_store_id = store_table.store_id AND store_table.website_id = main_table.website_id')
                ->where('main_table.is_default = ?', 1)
            ;
        }

        if ($row = $read->fetchRow($select)) {
            $this->_storeId = $row['id'];
            $this->_storeCode = $row['code'];
        }
        return $this;
    }

    protected function _getConfigData($store, $path)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $configTable = Mage::getSingleton('core/resource')->getTableName('core_config_data');
        /** @var Zend_Db_Select $select */
        $select = $read->select();
        $select
            ->from(array('main_table' => $configTable), array('value' => 'main_table.value'))
            ->where('main_table.path = ?', $path)
            ->where('main_table.scope_id = ?', $store)
        ;
        return $read->fetchOne($select);
    }
}