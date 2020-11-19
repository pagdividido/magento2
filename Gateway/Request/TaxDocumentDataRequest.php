<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Request;

use PagDividido\Magento2\Gateway\Config\Config;
use PagDividido\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use PagDividido\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TaxDocumentDataRequest.
 */
class TaxDocumentDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BillingAddress block name.
     */
    public const TAX_DOCUMENT = 'taxDocument';

    /**
     * The street address. Maximum 255 characters
     * Required.
     */
    public const TAX_DOCUMENT_TYPE = 'type';

    /**
     * The street number. 1 or 10 alphanumeric digits
     * Required when AVS rules are configured to require street address.
     */
    public const TAX_DOCUMENT_NUMBER = 'number';

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
     * ValueForTaxDocument.
     *
     * @param $orderAdapter
     *
     * @return value
     */
    public function getValueForTaxDocument($orderAdapter)
    {
        $obtainTaxDocumentFrom = $this->config->getAddtionalValue('attributes', 'type_cpf');

        if ($obtainTaxDocumentFrom == 'customer') {
            $attributeTaxDocumentCustomer = $this->config->getAddtionalValue('attributes', 'cpf_for_customer');
            if ($attributeTaxDocumentCustomer == 'taxvat') {
                $taxDocument = $orderAdapter->getCustomerTaxvat();
            } else {
                $taxDocument = $orderAdapter->getData($attributeTaxDocumentCustomer);
            }
        } else {
            $attributeTaxDocumentAddress = $this->config->getAddtionalValue('attributes', 'cpf_for_address');
            if ($attributeTaxDocumentAddress == 'vat_id') {
                $taxDocument = $orderAdapter->getBillingAddress()->getVatId();
            } else {
                $taxDocument = $orderAdapter->getBillingAddress()->getData($attributeTaxDocumentAddress);
            }
        }

        return preg_replace('/[^0-9]/', '', $taxDocument);
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

        $taxDocument = $this->getValueForTaxDocument($orderAdapter);

        if (strlen($taxDocument) === 14) {
            $typeDocument = 'CNPJ';
        }

        if ($taxDocument) {
            $result[CustomerDataRequest::CUSTOMER][self::TAX_DOCUMENT][0] = [
                self::TAX_DOCUMENT_TYPE   => $typeDocument,
                self::TAX_DOCUMENT_NUMBER => $taxDocument,
            ];
        }

        return $result;
    }
}
