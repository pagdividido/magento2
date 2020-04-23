<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Block\Adminhtml\System\Config;

/**
 * Class Street.
 */
class Street implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns Options.
     *
     * @return array attributesArrays
     */
    public function toOptionArray()
    {
        return [
            null => __('Please select'),
            '0'  => __('1st line of the street'),
            '1'  => __('2st line of the street'),
            '2'  => __('3st line of the street'),
            '3'  => __('4st line of the street'),
        ];
    }
}
