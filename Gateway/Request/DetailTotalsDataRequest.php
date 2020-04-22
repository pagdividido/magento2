<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Fluxx\Magento2\Gateway\SubjectReader;
use Fluxx\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use Fluxx\Magento2\Gateway\Config\Config;

/**
 * Class BillingAddressDataBuilder
 */
class DetailTotalsDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Amount block name
     */
    private const TOTALS_AMOUNT = 'amount';

    /**
     *  Grand Total Amount.
     *  Require
     */
    private const TOTALS_AMOUNT_GRAND_TOTAL = 'total';

    /**
     * The Currency. ISO 4217
     * Required.
     */
    private const TOTALS_AMOUNT_CURRENCY = 'currency';

    /**
     * Subtotals block name
     */
    private const TOTALS_AMOUNT_SUBTOTALS = 'subtotals';

    /**
     * The Shipping.
     */
    private const TOTALS_AMOUNT_SUBTOTALS_SHIPPING = 'shipping';

    /**
     * The Discount.
     */
    private const TOTALS_AMOUNT_SUBTOTALS_DISCOUNT = 'discount';
    
    /**
     * The Addition.
     */
    private const TOTALS_AMOUNT_SUBTOTALS_ADDITION = 'addition';

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param OrderAdapterFactory $orderAdapterFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        OrderAdapterFactory $orderAdapterFactory,
        Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderAdapterFactory = $orderAdapterFactory;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        
        $result = [];
        
        $orderAdapter = $this->orderAdapterFactory->create(
            ['order' => $payment->getOrder()]
        );
        
        $order = $paymentDO->getOrder();

        $result[self::TOTALS_AMOUNT] = [
            self::TOTALS_AMOUNT_CURRENCY => $order->getCurrencyCode(),
            self::TOTALS_AMOUNT_GRAND_TOTAL => $this->config->formatPrice($order->getGrandTotalAmount()),
            self::TOTALS_AMOUNT_SUBTOTALS => [
                self::TOTALS_AMOUNT_SUBTOTALS_SHIPPING => $this->config->formatPrice($orderAdapter->getShippingAmount()),
                self::TOTALS_AMOUNT_SUBTOTALS_DISCOUNT => $this->config->formatPrice($orderAdapter->getDiscountAmount()),
                self::TOTALS_AMOUNT_SUBTOTALS_ADDITION => $this->config->formatPrice($orderAdapter->getTaxAmount())
            ]
        ];

        return $result;
    }
}
