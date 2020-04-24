<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Gateway\Request;

use Fluxx\Magento2\Gateway\Config\Config;
use Fluxx\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use Fluxx\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class PayerDataRequest.
 */
class PayerDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Payer Birth City.
     */
    const PAYER_BIRTH_CITY = 'birthCity';

    /**
     * Payer Birth Region.
     */
    const PAYER_BIRTH_REGION = 'birthState';

    /**
     * Payer RG.
     */
    const PAYER_RG = 'rg';

    /**
     * Payer Offer.
     */
    const PAYER_OFFER = 'offerUUID';

    /**
     * Payer Birth Date.
     */
    const PAYER_BIRTH_DATE = 'birthDate';

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader       $subjectReader
     * @param OrderAdapterFactory $orderAdapterFactory
     * @param Config              $config
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
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();

        $result = [];

        $result = [
            self::PAYER_OFFER        => $payment->getAdditionalInformation('financing')
        ];
        
        $result[CustomerDataRequest::CUSTOMER] = [
            self::PAYER_BIRTH_CITY   => $payment->getAdditionalInformation('birth_city'),
            self::PAYER_BIRTH_REGION => $payment->getAdditionalInformation('birth_region'),
            self::PAYER_BIRTH_DATE   => date('Y-m-d', strtotime($payment->getAdditionalInformation('dob')))
        ];
        
        $result[CustomerDataRequest::CUSTOMER][TaxDocumentDataRequest::TAX_DOCUMENT][1] = [
            TaxDocumentDataRequest::TAX_DOCUMENT_TYPE   => 'RG',
            TaxDocumentDataRequest::TAX_DOCUMENT_NUMBER => $payment->getAdditionalInformation('rg'),
        ];

        return $result;
    }
}
