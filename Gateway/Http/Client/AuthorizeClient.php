<?php

declare(strict_types=1);
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Gateway\Http\Client;

use Fluxx\Magento2\Gateway\Config\Config;
use InvalidArgumentException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class AuthorizeClient.
 */
class AuthorizeClient implements ClientInterface
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
        $client = $this->httpClientFactory->create();
        $request = $transferObject->getBody();
        $url = $this->config->getApiUrl();
        $apiUsername = $this->config->getMerchantGatewayUsername();
        $apiKey = $this->config->getMerchantGatewayKey();

        try {
            $client->setUri($url.'/checkout');
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $client->setAuth($apiUsername, $apiKey, 'basic');
            $client->setRawData($this->json->serialize($request), 'application/json');
            $client->setMethod(ZendClient::POST);

            $responseBody = $client->request()->getBody();
            $data = $this->json->unserialize($responseBody);
            if (isset($data['orderUUID'])) {
                $response = array_merge(
                    [
                        'RESULT_CODE' => 1,
                        'TXN_ID'      => $data['orderUUID'],
                        'EXT_ORD_ID'  => $data['orderUUID'],
                    ],
                    $data
                );
            } else {
                $response = array_merge(
                    [
                        'RESULT_CODE' => 0,
                    ],
                    $data
                );
            }
            $this->logger->debug(
                [
                    'request'  => $this->json->serialize($transferObject->getBody()),
                    'response' => $responseBody,
                ]
            );
        } catch (InvalidArgumentException $e) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Invalid JSON was returned by the gateway');
        }

        return $response;
    }
}
