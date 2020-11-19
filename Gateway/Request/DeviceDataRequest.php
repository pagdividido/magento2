<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Gateway\Request;

use PagDividido\Magento2\Gateway\SubjectReader;
use Magento\Framework\HTTP\Header as HeaderClient;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class DeviceDataRequest.
 */
class DeviceDataRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Device data.
     */
    private const DEVICE_DATA = 'device';

    /**
     * RemoteIP data.
     */
    private const REMOTE_IP = 'ip';

    /**
     * RemoteUserAgent data.
     */
    private const REMOTE_USER_AGENT = 'userAgent';

    /**
     * @var remoteAddress
     */
    private $remoteAddress;

    /**
     * @var headerClient
     */
    private $headerClient;

    /**
     * @param RemoteAddress $remoteAddress
     * @param HeaderClient  $headerClient
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        HeaderClient $headerClient,
        SubjectReader $subjectReader
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->headerClient = $headerClient;
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $result = [];
        $result[self::DEVICE_DATA] = [
            self::REMOTE_IP         => $this->remoteAddress->getRemoteAddress(),
            self::REMOTE_USER_AGENT => $this->headerClient->getHttpUserAgent(),
        ];

        $paymentInfo = $paymentDO->getPayment();

        $paymentInfo->setAdditionalInformation(
            self::DEVICE_DATA,
            $result[self::DEVICE_DATA]
        );

        return $result;
    }
}
