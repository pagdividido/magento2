<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Request;

use PagDividido\Magento2\Gateway\Config\Config;
use PagDividido\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class PurchasedItemsDataRequest.
 */
class PurchasedItemsDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BillingAddress block name.
     */
    private const PURCHASED_ITEMS = 'items';

    /**
     * The street address. Maximum 255 characters
     * Required.
     */
    private const PURCHASED_ITEM_PRODUCT = 'product';

    /**
     * The street number. 1 or 10 alphanumeric digits
     * Required when AVS rules are configured to require street address.
     */
    private const PURCHASED_ITEM_QUANTITY = 'quantity';

    /**
     * The district address. Maximum 255 characters
     * Required.
     */
    private const PURCHASED_ITEM_DETAIL = 'detail';

    /**
     * The complement address. Maximum 255 characters
     * Required.
     */
    private const PURCHASED_ITEM_PRICE = 'price';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param Config        $config
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $result = [];
        $order = $paymentDO->getOrder();
        $items = $order->getItems();
        $itemcount = count($items);
        if ($itemcount) {
            foreach ($items as $itemId => $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getPrice() == 0) {
                    continue;
                }
                if ($item->getPrice() > 0) {
                    $result[self::PURCHASED_ITEMS][] = [
                        self::PURCHASED_ITEM_PRODUCT  => $item->getName(),
                        self::PURCHASED_ITEM_QUANTITY => $item->getQtyOrdered(),
                        self::PURCHASED_ITEM_DETAIL   => $item->getSku(),
                        self::PURCHASED_ITEM_PRICE    => $this->config->formatPrice($item->getPrice()),
                    ];
                }
            }
        }

        return $result;
    }
}
