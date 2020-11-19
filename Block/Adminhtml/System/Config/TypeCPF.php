<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Block\Adminhtml\System\Config;

/**
 * Class TypeCPF.
 */
class TypeCPF implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns Options.
     *
     * @return array attributesArrays
     */
    public function toOptionArray()
    {
        return [
            null       => __('Please select'),
            'customer' => __('by customer form (customer account)'),
            'address'  => __('by address form (checkout)'),
        ];
    }
}
