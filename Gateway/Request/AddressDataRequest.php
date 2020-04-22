<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Fluxx\Magento2\Gateway\SubjectReader;
use Fluxx\Magento2\Gateway\Request\CustomerDataRequest;
use Fluxx\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use Fluxx\Magento2\Gateway\Config\Config;

/**
 * Class BillingAddressDataBuilder
 */
class AddressDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * BillingAddress block name
     */
    private const BILLING_ADDRESS = 'billingAddresses';

    /**
    * BillingAddress block name
    */
    private const SHIPPING_ADDRESS = 'shippingAddress';

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
     * The ISO 3166-1 alpha-3
     *
     */
    private const COUNTRY_CODE = 'country';


    /**
     * The locality/city. 255 character maximum.
     */
    private const LOCALITY = 'city';

    /**
     * The state or province. The region must be a 2-letter abbreviation;
     */
    private const STATE = 'state';

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
     * @param Config $config
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
     * Value For Field Address
     * @param $adress
     * @param $orderAdapter
     * @param $field
     * @return string value
     */
    public function getValueForAddress($adress, $orderAdapter, $field)
    {
        $value = $this->config->getAddtionalValue('attributes', $field);
        
        if ($value == 0) {
            return $adress->getStreetLine1();
        } elseif ($value == 1) {
            return $adress->getStreetLine2();
        } elseif ($value == 2) {
            return $adress->getStreetLine3();
        } elseif ($value == 3) {
            return $adress->getStreetLine4();
        } else {
            return $adress->getStreetLine1();
        }
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

        $billingAddress = $orderAdapter->getBillingAddress();
        if ($billingAddress) {
            $result[CustomerDataRequest::CUSTOMER][self::BILLING_ADDRESS][] = [
                self::POSTAL_CODE => $billingAddress->getPostcode(),
                self::STREET => $this->getValueForAddress($billingAddress, $orderAdapter, self::STREET),
                self::STREET_NUMBER => $this->getValueForAddress($billingAddress, $orderAdapter, self::STREET_NUMBER),
                self::STREET_DISTRICT => $this->getValueForAddress($billingAddress, $orderAdapter, self::STREET_DISTRICT),
                self::STREET_COMPLEMENT => $this->getValueForAddress($billingAddress, $orderAdapter, self::STREET_COMPLEMENT),
                self::LOCALITY => $billingAddress->getCity(),
                self::STATE => $billingAddress->getRegionCode(),
                self::COUNTRY_CODE => 'BRA'
            ];
        }

        $shippingAddress = $orderAdapter->getShippingAddress();
        if ($shippingAddress) {
            $result[CustomerDataRequest::CUSTOMER][self::SHIPPING_ADDRESS] = [
                self::POSTAL_CODE => $shippingAddress->getPostcode(),
                self::STREET => $this->getValueForAddress($shippingAddress, $orderAdapter, self::STREET),
                self::STREET_NUMBER => $this->getValueForAddress($shippingAddress, $orderAdapter, self::STREET_NUMBER),
                self::STREET_DISTRICT => $this->getValueForAddress($shippingAddress, $orderAdapter, self::STREET_DISTRICT),
                self::STREET_COMPLEMENT => $this->getValueForAddress($shippingAddress, $orderAdapter, self::STREET_COMPLEMENT),
                self::LOCALITY => $shippingAddress->getCity(),
                self::STATE => $shippingAddress->getRegionCode(),
                self::COUNTRY_CODE => 'BRA'
            ];
        }
        return $result;
    }
}
