<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Controller\Webhooks;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Check.
 */
class Deny extends Action implements CsrfAwareActionInterface
{
    /**
     * createCsrfValidationException.
     *
     * @param RequestInterface $request
     *
     * @return null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * validateForCsrf.
     *
     * @param RequestInterface $request
     *
     * @return bool true
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @var logger
     */
    protected $logger;

    /**
     * @var orderFactory
     */
    protected $orderFactory;

    /**
     * @var resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context                                          $context
     * @param LoggerInterface                                  $logger
     * @param OrderInterfaceFactory                            $orderFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        OrderInterfaceFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Command Accept.
     *
     * @return json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = file_get_contents('php://input');
        $originalNotification = json_decode($response, true);

        $gatewayDataOrderId = $originalNotification['ownId'];
        $gatewayDataOfferId = $originalNotification['offerUUID'];
        $order = $this->orderFactory->create()->loadByIncrementId($gatewayDataOrderId);
        $storeDataOfferId = $order->getExtOrderId();
        // verificação de segurança
        if ($storeDataOfferId == $gatewayDataOfferId) {
            $this->logger->debug('Deny '.$storeDataOfferId);
            $this->logger->debug($response);

            $payment = $order->getPayment();
            $transactionId = $payment->getLastTransId();
            if (!$order->canCancel()) {
                try {
                    $payment->deny();
                    $payment->save();
                    $order->save();
                } catch (\Exception $e) {
                    return $resultJson->setData(['success' => 0, 'error' => $e]);
                }
            } else {
                return $resultJson->setData(['success' => 0, 'error' => 'The transaction could not be denied']);
            }

            return $resultJson->setData(['success' => 1, 'status' => $order->getStatus(), 'state' => $order->getState()]);
        }
    }
}
