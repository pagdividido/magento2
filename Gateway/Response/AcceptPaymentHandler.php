<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class AcceptPaymentHandler.
 */
class AcceptPaymentHandler implements HandlerInterface
{
    /**
     * @const TXN_ID
     */
    const TXN_ID = 'TXN_ID';

    /**
     * Accpet.
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return accept
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
        $amount = $order->getBaseGrandTotal();
        $baseAmount = $order->getGrandTotal();

        $payment->registerAuthorizationNotification($amount);
        $payment->registerCaptureNotification($amount);
        $payment->setIsTransactionApproved(true);
        $payment->setIsTransactionDenied(false);
        $payment->setIsInProcess(true);
        $payment->setIsTransactionClosed(true);

        $payment->setAmountAuthorized($amount);
        $payment->setBaseAmountAuthorized($baseAmount);
    }
}
