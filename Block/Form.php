<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Block;

use PagDividido\Magento2\Gateway\Config\Config as GatewayConfig;
use PagDividido\Magento2\Gateway\Http\Client\CheckClient;
use PagDividido\Magento2\Gateway\Request\CustomerDataRequest as PagDivididoCustomerDataRequest;
use PagDividido\Magento2\Gateway\Request\TaxDocumentDataRequest as PagDivididoTaxDocumentDataRequest;
use PagDividido\Magento2\Model\Ui\ConfigProvider;
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
     * @var PagDivididoCustomerDataRequest
     */
    private $PagDivididoCustomerDataRequest;

    /**
     * @var PagDivididoTaxDocumentDataRequest
     */
    private $PagDivididoTaxDocumentDataRequest;

    /**
     * @param Context                     $context
     * @param Config                      $paymentConfig
     * @param Quote                       $sessionQuote
     * @param GatewayConfig               $gatewayConfig
     * @param ConfigProvider              $configProvider
     * @param Data                        $paymentDataHelper
     * @param PagDivididoCustomerDataRequest    $PagDivididoCustomerDataRequest
     * @param PagDivididoTaxDocumentDataRequest $PagDivididoTaxDocumentDataRequest
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
        PagDivididoCustomerDataRequest $PagDivididoCustomerDataRequest,
        PagDivididoTaxDocumentDataRequest $PagDivididoTaxDocumentDataRequest,
        CheckClient $command,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->configProvider = $configProvider;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->PagDivididoCustomerDataRequest = $PagDivididoCustomerDataRequest;
        $this->PagDivididoTaxDocumentDataRequest = $PagDivididoTaxDocumentDataRequest;
        $this->command = $command;
    }

    /**
     * PagDividido Provider Config.
     *
     * @return class Ui Config
     */
    public function getPagDivididoProviderConfig()
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
            'cpf'         => $this->PagDivididoTaxDocumentDataRequest->getValueForTaxDocument($quote),
            'name'        => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
            'email'       => $billingAddress->getEmail(),
            'phone'       => $this->PagDivididoCustomerDataRequest->structurePhone($billingAddress->getTelephone(), $defaultCountryCode),
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
            foreach ($result['offers'] as $offers) {
                $financing['offers'][$offers['uuid']] = $offers['offerDescription'];
            }
            $financing['institution'] = $result['institution']['bankName'];
            $financing['availability'] = true;
        } else if ($result['RESULT_CODE'] == 2){
            $financing['conditionalValue'] = $result['conditionalValue'];
            $financing['conditionalAvailability'] = true;
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
