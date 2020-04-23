<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Gateway\Request;

use Fluxx\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use Fluxx\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class BillingAddressDataBuilder.
 */
class BillingAddressDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BillingAddress block name.
     */
    private const BILLING_ADDRESS = 'billingAddress';

    /**
     * The street address. Maximum 255 characters
     * Required.
     */
    private const STREET = 'street';

    /**
     * The street number. 1 or 10 alphanumeric digits
     * Required when AVS rules are configured to require street address.
     */
    private const STREET_NUMBER = 'streetNumber';

    /**
     * The district address. Maximum 255 characters
     * Required.
     */
    private const STREET_DISTRICT = 'district';

    /**
     * The complement address. Maximum 255 characters
     * Required.
     */
    private const STREET_COMPLEMENT = 'complement';

    /**
     * The postal code. Postal code must be a string of 5 or 9 alphanumeric digits,
     * optionally separated by a dash or a space. Spaces, hyphens,
     * and all other special characters are ignored.
     */
    private const POSTAL_CODE = 'zipCode';

    /**
     * The ISO 3166-1 alpha-3.
     */
    private const COUNTRY_CODE = 'country';

    /**
     * The locality/city. 255 character maximum.
     */
    private const LOCALITY = 'city';

    /**
     * The state or province. The region must be a 2-letter abbreviation;.
     */
    private const STATE = 'state';

    /**
     * @var OrderAdapterFactory
     */
    private $orderAdapterFactory;

    /**
     * @param SubjectReader       $subjectReader
     * @param OrderAdapterFactory $orderAdapterFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        OrderAdapterFactory $orderAdapterFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderAdapterFactory = $orderAdapterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $result = [];

        $orderAdapter = $this->orderAdapterFactory->create(
            ['order' => $payment->getOrder()]
        );

        $billingAddress = $orderAdapter->getBillingAddress();
        if ($billingAddress) {
            $result[self::BILLING_ADDRESS] = [
                self::POSTAL_CODE       => $billingAddress->getPostcode(),
                self::STREET            => $billingAddress->getStreetLine1(),
                self::STREET_NUMBER     => $billingAddress->getStreetLine2(),
                self::STREET_DISTRICT   => $billingAddress->getStreetLine3(),
                self::STREET_COMPLEMENT => $billingAddress->getStreetLine4(),
                self::STATE             => $billingAddress->getRegionCode(),
                self::COUNTRY_CODE      => $billingAddress->getCountryId(),
            ];
        }

        return $result;
    }
}
