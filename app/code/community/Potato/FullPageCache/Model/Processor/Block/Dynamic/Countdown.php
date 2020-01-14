<?php

class Potato_FullPageCache_Model_Processor_Block_Dynamic_Countdown extends Potato_FullPageCache_Model_Processor_Block_Dynamic_Abstract
{
    public function getAttributes($block)
    {
        return array(
            'type' => 'awcountdown/countdown',
            'params' => array(
                'product_id' => $block->getProductId(),
            )
        );
    }
}