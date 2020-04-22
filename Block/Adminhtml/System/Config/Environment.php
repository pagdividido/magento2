<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Block\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

/**
 * Class Environment
 */
class Environment implements \Magento\Framework\Option\ArrayInterface
{
	/**
     * Returns Options
     * @return array attributesArrays
     */
    public function toOptionArray()
    {
        return [
            'production' => __('Production'),
            'sandbox' => __('Sandbox - Environment for tests'),
        ];
    }
}
