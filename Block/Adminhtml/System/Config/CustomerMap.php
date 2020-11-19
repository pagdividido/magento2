<?php
/**
 * Copyright © pagdividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Block\Adminhtml\System\Config;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class CustomerMap.
 */
class CustomerMap implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var objectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $interface
    ) {
        $this->objectManager = $interface;
    }

    /**
     * Returns Options.
     *
     * @return array | attributesArrays
     */
    public function toOptionArray()
    {
        $customer_attributes = $this->objectManager->get('Magento\Customer\Model\Customer')->getAttributes();
        $attributesArrays = [];
        $attributesArrays[] = ['label' => __('Please select'), 'value' => null];

        foreach ($customer_attributes as $cal=>$val) {
            $attributesArrays[] = [
                'label' => $cal,
                'value' => $cal,
            ];
        }

        return $attributesArrays;
    }
}
