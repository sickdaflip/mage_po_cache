<?php

class Potato_Crawler_Model_Source_Url_Sitemap
{
    protected $_store = null;
    protected $_xml = null;

    public function __construct($path, $store)
    {
        $this->_store = $store;
        $this->_xml = @simplexml_load_string(file_get_contents($path));
    }

    /**
     * @return array
     */
    public function getStoreUrls()
    {
        if (!$this->_xml instanceof SimpleXMLElement) {
            return array();
        }
        $_result = array();
        foreach ($this->_xml->url as $key => $node) {
            $_priority = (string)$node->priority * 100;
            $_result[(int)$_priority][] = (string)$node->loc;
        }
        ksort($_result);
        return $_result;
    }
}