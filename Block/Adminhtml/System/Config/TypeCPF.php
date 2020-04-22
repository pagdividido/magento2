<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Block\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class TypeCPF
 */
class TypeCPF implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns Options
     * @return array attributesArrays
     */
    public function toOptionArray()
    {
        return [
            null => __('Please select'),
            'customer' => __('by customer form (customer account)'),
            'address' => __('by address form (checkout)'),
        ];
    }
}
