<?php

/**
 * Class Potato_FullPageCache_Model_Cache_Page_Block_Dynamic
 */
class Potato_FullPageCache_Model_Cache_Page_Block_Dynamic extends Potato_FullPageCache_Model_Cache_Page_Block
{
    protected $_blocks = array();

    public function isDynamic($block)
    {
        if (!$this->_config->getNode('dynamic_blocks')) {
            return false;
        }
        $dynamicBlocks = $this->_config->getNode('dynamic_blocks')->asArray();
        if (array_key_exists(get_class($block), $dynamicBlocks)) {
            $block->setFpcData($dynamicBlocks[get_class($block)]);
            return true;
        }
        return false;
    }

    public function saveBlock($block)
    {
        if (!$fpcData = $block->getFpcData()) {
            return $this;
        }
        if (!array_key_exists('processor', $fpcData)) {
            return $this;
        }
        try {
            $processor = new $fpcData['processor'];
        } catch (Exception $e) {
            return $this;
        }
        $attributes = $processor->getAttributes($block);
        $name = md5(get_class($block) . json_encode($attributes));
        $block->setFrameTags('!--[' . $name . ']--', '!--[' . $name . ']--');
        $this->_blocks[$name] = $attributes;
        return $this;
    }

    public function getBlocks()
    {
        return $this->_blocks;
    }

    public function setBlocks($blocks)
    {
        $this->_blocks = $blocks;
        return $this;
    }
}