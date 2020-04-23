<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Controller\CreditRating;

use Fluxx\Magento2\Gateway\Config\Config;
use Fluxx\Magento2\Gateway\Http\Client\CheckClient;
use Fluxx\Magento2\Gateway\Request\CustomerDataRequest as FluxxCustomerDataRequest;
use Fluxx\Magento2\Gateway\Request\TaxDocumentDataRequest as FluxxTaxDocumentDataRequest;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Check.
 */
class Check extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CheckCommand
     */
    private $command;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var config
     */
    private $config;

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
     * @param Config                      $config
     * @param CheckoutSession             $checkoutSession
     * @param FluxxCustomerDataRequest    $fluxxCustomerDataRequest
     * @param FluxxTaxDocumentDataRequest $fluxxTaxDocumentDataRequest
     * @param CheckClient                 $command
     * @param LoggerInterface             $logger
     */
    public function __construct(
        Context $context,
        Config $config,
        CheckoutSession $checkoutSession,
        FluxxCustomerDataRequest $fluxxCustomerDataRequest,
        FluxxTaxDocumentDataRequest $fluxxTaxDocumentDataRequest,
        CheckClient $command,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->fluxxCustomerDataRequest = $fluxxCustomerDataRequest;
        $this->fluxxTaxDocumentDataRequest = $fluxxTaxDocumentDataRequest;
        $this->logger = $logger;
        $this->command = $command;
    }

    /**
     * Data For Check.
     *
     * @param string|date $dob
     *
     * @return array
     */
    public function getDataForCheck($dob)
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$dob) {
            $dob = $quote->getCustomerDob();
        }

        $billingAddress = $quote->getBillingAddress();

        $defaultCountryCode = '';
        if ($billingAddress->getCountryId() == 'BR') {
            $defaultCountryCode = '55';
        }

        return [

            'cpf'         => $this->fluxxTaxDocumentDataRequest->getValueForTaxDocument($quote),
            'name'        => $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
            'email'       => $quote->getCustomerEmail() ? $quote->getCustomerEmail() : 'quote@fluxx.com.br',
            'phone'       => $this->fluxxCustomerDataRequest->structurePhone($billingAddress->getTelephone(), $defaultCountryCode),
            'dateOfBirth' => $dob ? date('Y-m-d', strtotime($dob)) : '1985-10-10',
            'amount'      => ['total' => $this->config->formatPrice($quote->getGrandTotal()), 'subtotal' => ['shipping' => $this->config->formatPrice($quote->getShippingAmount()), 'discount' => $this->config->formatPrice($quote->getDiscountAmount()), 'addition' => $this->config->formatPrice($quote->getTaxAmount())]],
        ];
    }

    /**
     * Command Check Client.
     *
     * @return array
     */
    public function execute()
    {
        $dob = $this->getRequest()->getParam('dob');
        $data = $this->getDataForCheck($dob);
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $financing = [];

        try {
            $result = $this->command->placeRequest($data);
            if ($result['RESULT_CODE'] == 1) {
                foreach ($result['offers'] as $key => $offers) {
                    $financing['offers'][$offers['uuid']] = $offers['offerDescription'];
                }
                $financing['institution'] = $result['institution']['bankName'];
                $financing['availability'] = true;
                $response->setData($financing);
            } else {
                $this->processBadRequest($response);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return $this->processBadRequest($response);
        }

        return $response;
    }

    /**
     * Return response for bad request.
     *
     * @param array $response
     *
     * @return array
     */
    private function processBadRequest(ResultInterface $response)
    {
        // $response->setHttpResponseCode(401);
        $response->setData(['availability' => 0, 'message' => __('Sorry, but something went wrong')]);

        return $response;
    }
}
