<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Request;

use PagDividido\Magento2\Gateway\Config\Config;
use PagDividido\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use PagDividido\Magento2\Gateway\SubjectReader;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class MerchantDataRequest.
 */
class MerchantDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Merchant block name.
     */
    public const MERCHANT = 'merchant';

    /**
     * The confirmation Url
     * Required.
     */
    public const CONFIRMATION_URL = 'confirmationUrl';

    /**
     * The Canel Url
     * Required.
     */
    public const CANCEL_URL = 'cancelUrl';

    /**
     * The Method
     * Required.
     */
    public const USER_CONFIRMATION_ACTION = 'userConfirmationAction';

    /**
     * The Store Name
     * Required.
     */
    public const STORE_NAME = 'name';

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param SubjectReader       $subjectReader
     * @param OrderAdapterFactory $orderAdapterFactory
     * @param UrlInterface        $urlBuilder
     * @param Config              $config
     */
    public function __construct(
        SubjectReader $subjectReader,
        OrderAdapterFactory $orderAdapterFactory,
        UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderAdapterFactory = $orderAdapterFactory;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $result = [];
        $typeDocument = 'CPF';

        $orderAdapter = $this->orderAdapterFactory->create(
            ['order' => $payment->getOrder()]
        );

        $result[self::MERCHANT] = [
            self::CONFIRMATION_URL         => $this->urlBuilder->getUrl('pagdividido/webhooks/accept', ['_secure' => true]),
            self::CANCEL_URL               => $this->urlBuilder->getUrl('pagdividido/webhooks/deny', ['_secure' => true]),
            self::USER_CONFIRMATION_ACTION => 'POST',
            self::STORE_NAME               => 'Loja',
        ];

        return $result;
    }
}
