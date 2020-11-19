<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class ExtOrdIdHandler.
 */
class ExtOrdIdHandler implements HandlerInterface
{
    /**
     * @const EXT_ORD_ID
     */
    const EXTERNAL_ORDER_ID = 'EXT_ORD_ID';

    /**
     * Handles External Order Id.
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return ExtOrderId
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
        $order->setExtOrderId($response[self::EXTERNAL_ORDER_ID]);
    }
}
