<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info.
 */
class Info extends ConfigurableInfo
{
    /**
     * Returns label.
     *
     * @param string $field
     *
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Returns value view.
     *
     * @param string $field
     * @param string $value
     *
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        if (is_array($value)) {
            return implode('; ', $value);
        }

        return parent::getValueView($field, $value);
    }
}
