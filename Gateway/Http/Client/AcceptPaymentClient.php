<?php

declare(strict_types=1);
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Gateway\Http\Client;

use Fluxx\Magento2\Gateway\Config\Config;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class AcceptPaymentClient.
 */
class AcceptPaymentClient implements ClientInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Logger            $logger
     * @param ZendClientFactory $httpClientFactory
     * @param Config            $config
     * @param Json              $json
     */
    public function __construct(
        Logger $logger,
        ZendClientFactory $httpClientFactory,
        Config $config,
        Json $json
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * Places request to gateway. Returns result as ENV array.
     *
     * @param TransferInterface $transferObject
     *
     * @throws \Magento\Payment\Gateway\Http\ClientException
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        return  [
            'RESULT_CODE' => 1,
        ];
    }
}
