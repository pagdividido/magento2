<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Request;

use PagDividido\Magento2\Gateway\Data\Order\OrderAdapterFactory;
use PagDividido\Magento2\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CustomerAddressDataRequest.
 */
class CustomerDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Customer block name.
     */
    public const CUSTOMER = 'customer';

    /**
     * Unique user id. Using email and not ID can cause migrations that can affect the user.
     */
    const OWN_ID = 'ownId';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The full name value must be less than or equal to 255 characters.
     */
    const FULL_NAME = 'fullname';

    /**
     * The customer birth Date. Date Y-MM-dd.
     */
    const BIRTH_DATE = 'birthDate';

    /**
     * The customer’s company. 255 character maximum.
     */
    const COMPANY = 'company';

    /**
     * The customer’s email address, comprised of ASCII characters.
     */
    const EMAIL = 'email';

    /**
     * Phone block name.
     */
    const PHONE = 'phone';

    /*
     * Phone Country Code. must be 2 haracters and can (DDI)
     */
    const PHONE_CONNTRY_CODE = 'countryCode';

    /*
     * Phone Area code. must be 2 haracters and can (DDD)
     */
    const PHONE_AREA_CODE = 'areaCode';

    /*
     * Phone Number. must be 8 - 9 haracters
     */
    const PHONE_NUMBER = 'number';

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

    /*
     * Number or DDD
     * @param param_telefone full phone number, return_ddd is true return DDD, is false return Number
     */

    public function getNumberOrDDD($param_telefone, $return_ddd = false)
    {
        $cust_ddd = '11';
        $cust_telephone = preg_replace('/[^0-9]/', '', $param_telefone);
        if (strlen($cust_telephone) == 11) {
            $st = strlen($cust_telephone) - 9;
            $indice = 9;
        } else {
            $st = strlen($cust_telephone) - 8;
            $indice = 8;
        }

        if ($st > 0) {
            $cust_ddd = substr($cust_telephone, 0, 2);
            $cust_telephone = substr($cust_telephone, $st, $indice);
        }
        if ($return_ddd === false) {
            $retorno = $cust_telephone;
        } else {
            $retorno = $cust_ddd;
        }

        return $retorno;
    }

    /**
     * StructurePhone.
     *
     * @param  $phone full phone number,
     * @param  $defaultCountryCode,
     *
     * @return array
     */
    public function structurePhone($phone, $defaultCountryCode)
    {
        return [
            self::PHONE_CONNTRY_CODE => (int) $defaultCountryCode,
            self::PHONE_AREA_CODE    => (int) $this->getNumberOrDDD($phone, true),
            self::PHONE_NUMBER       => (int) $this->getNumberOrDDD($phone),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $orderAdapter = $this->orderAdapterFactory->create(
            ['order' => $payment->getOrder()]
        );

        $billingAddress = $orderAdapter->getBillingAddress();

        $defaultCountryCode = '';
        if ($billingAddress->getCountryId() == 'BR') {
            $defaultCountryCode = '55';
        }

        $dob = $orderAdapter->getCustomerDob() ? date('Y-m-d', strtotime($orderAdapter->getCustomerDob())) : '1985-10-10';

        return [
            self::CUSTOMER => [
                self::OWN_ID     => $billingAddress->getEmail(),
                self::FULL_NAME  => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                self::COMPANY    => $billingAddress->getCompany(),
                self::PHONE      => [$this->structurePhone($billingAddress->getTelephone(), $defaultCountryCode)],
                self::EMAIL      => $billingAddress->getEmail(),
                self::BIRTH_DATE => $dob,
            ],
        ];
    }
}
