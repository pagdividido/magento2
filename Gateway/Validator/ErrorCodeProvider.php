<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Fluxx\Magento2\Gateway\Validator;

use Magento2\Error\ErrorCollection;
use Magento2\Error\Validation;
use Magento2\Result\Error;
use Magento2\Result\Successful;
use Magento2\Transaction;

/**
 * Class ErrorCodeProvider
 */
class ErrorCodeProvider
{
    /**
     * Error list
     * @param Successful|Error $response
     * @return array
     */
    public function getErrorCodes($response): array
    {
        $result = [];
        if (!$response instanceof Error) {
            return $result;
        }

        /** @var ErrorCollection $collection */
        $collection = $response->errors;

        /** @var Validation $error */
        foreach ($collection->deepAll() as $error) {
            $result[] = $error->code;
        }

        if (isset($response->transaction) && $response->transaction) {
            if ($response->transaction->status === Transaction::GATEWAY_REJECTED) {
                $result[] = $response->transaction->gatewayRejectionReason;
            }

            if ($response->transaction->status === Transaction::PROCESSOR_DECLINED) {
                $result[] = $response->transaction->processorResponseCode;
            }
        }

        return $result;
    }
}
