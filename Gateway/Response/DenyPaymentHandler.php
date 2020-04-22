<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class DenyPaymentHandler
 */
class DenyPaymentHandler implements HandlerInterface
{
    /**
     * @const TXN_ID
     */
    const TXN_ID = 'TXN_ID';

    /**
     * Deny
     * @param array $handlingSubject
     * @param array $response
     * @return deny
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        $order = $payment->getOrder();
        /** @var $payment \Magento\Sales\Model\Order\Payment */

        
        $payment->setIsTransactionApproved(false);
        $payment->setIsTransactionDenied(true);
        $payment->setIsInProcess(true);
        $payment->setIsTransactionClosed(true);
       
    }
}
