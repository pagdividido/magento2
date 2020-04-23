<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Block;

use Fluxx\Magento2\Gateway\Config\Config as GatewayConfig;
use Fluxx\Magento2\Gateway\Http\Client\CheckClient;
use Fluxx\Magento2\Gateway\Request\CustomerDataRequest as FluxxCustomerDataRequest;
use Fluxx\Magento2\Gateway\Request\TaxDocumentDataRequest as FluxxTaxDocumentDataRequest;
use Fluxx\Magento2\Model\Ui\ConfigProvider;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;

/**
 * Class Form.
 */
class Form extends Cc
{
    /**
     * @var Quote
     */
    protected $sessionQuote;

    /**
     * @var Config
     */
    protected $gatewayConfig;

    /**
     * @var configProvider
     */
    protected $configProvider;

    /**
     * @var paymentDataHelper
     */
    private $paymentDataHelper;

    /**
     * @var fluxxCustomerDataRequest
     */
    private $fluxxCustomerDataRequest;

    /**
     * @var fluxxTaxDocumentDataRequest
     */
    private $fluxxTaxDocumentDataRequest;

    /**
     * @param Context                     $context
     * @param Config                      $paymentConfig
     * @param Quote                       $sessionQuote
     * @param GatewayConfig               $gatewayConfig
     * @param ConfigProvider              $configProvider
     * @param Data                        $paymentDataHelper
     * @param FluxxCustomerDataRequest    $fluxxCustomerDataRequest
     * @param FluxxTaxDocumentDataRequest $fluxxTaxDocumentDataRequest
     * @param CheckClient                 $command
     * @param array                       $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        ConfigProvider $configProvider,
        Data $paymentDataHelper,
        FluxxCustomerDataRequest $fluxxCustomerDataRequest,
        FluxxTaxDocumentDataRequest $fluxxTaxDocumentDataRequest,
        CheckClient $command,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->configProvider = $configProvider;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->fluxxCustomerDataRequest = $fluxxCustomerDataRequest;
        $this->fluxxTaxDocumentDataRequest = $fluxxTaxDocumentDataRequest;
        $this->command = $command;
    }

    /**
     * Fluxx Provider Config.
     *
     * @return class Ui Config
     */
    public function getFluxxProviderConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Data for Check.
     *
     * @return array
     */
    private function getDataForCheck()
    {
        $quote = $this->sessionQuote->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $defaultCountryCode = '';
        if ($billingAddress->getCountryId() == 'BR') {
            $defaultCountryCode = '55';
        }

        return [
            'amount'      => ['total' => $this->gatewayConfig->formatPrice($quote->getGrandTotal()), 'subtotal' => ['shipping' => $this->gatewayConfig->formatPrice($quote->getShippingAmount()), 'discount' => $this->gatewayConfig->formatPrice($quote->getDiscountAmount()), 'addition' => $this->gatewayConfig->formatPrice($quote->getTaxAmount())]],
            'cpf'         => $this->fluxxTaxDocumentDataRequest->getValueForTaxDocument($quote),
            'name'        => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
            'email'       => $billingAddress->getEmail(),
            'phone'       => $this->fluxxCustomerDataRequest->structurePhone($billingAddress->getTelephone(), $defaultCountryCode),
            'dateOfBirth' => $quote->getCustomerDob() ? date('Y-m-d', strtotime($quote->getCustomerDob())) : '1985-10-10',
        ];
    }

    /**
     * Command CheckClient.
     *
     * @return array
     */
    public function getRequestFinancing()
    {
        $data = $this->getDataForCheck();
        $financing = [];

        $result = $this->command->placeRequest($data);
        if ($result['RESULT_CODE'] == 1) {
            foreach ($result['offers'] as $key => $offers) {
                $financing['offers'][$offers['uuid']] = $offers['offerDescription'];
            }
            $financing['institution'] = $result['institution']['bankName'];
            $financing['availability'] = true;
        } else {
            $this->processBadRequest($result);
        }

        return $financing;
    }

    /**
     * Return response for bad request.
     *
     * @param array $response
     *
     * @return array
     */
    private function processBadRequest($response)
    {
        $financing = [];
        $financing['availability'] = false;

        return $financing;
    }
}
