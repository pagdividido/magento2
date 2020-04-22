<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fluxx\Magento2\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * Round up
     * @const int
     */
    const ROUND_UP = 100;

    /**
     * endpoint of production.
     *
     * @const string
     */
    const ENDPOINT_PRODUCTION = 'https://api.moip.com.br';

    /**
     * endpoint of sandbox.
     *
     * @const string
     */
    const ENDPOINT_SANDBOX = 'https://sandbox.fluxx.com.br/v1';

    /**
     * Client name.
     *
     * @const string
     * */
    const CLIENT = 'Magento2';

    /**
     * Client Version.
     *
     * @const string
     */
    const CLIENT_VERSION = '1.0.0';


    /**
     * Config Pattern for Atribute.
     *
     * @const string
     */
    const PATTERN_FOR_ATTRIBUTES = 'fluxx_attribute_relationship';

    /**
     * Config Pattern for Credentials.
     *
     * @const string
     */
    const PATTERN_FOR_CREDENTIALS = 'fluxx_credentials';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }
    
    /**
     * Formant Price
     * @param int $amount
     * @return  int
     */
    public static function formatPrice($amount)
    {
        return $amount * self::ROUND_UP;
    }
    
    /**
     * Gets the API endpoint URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null): string
    {
        $environment = $this->getAddtionalValue('credentials', 'environment', $storeId);

        return $environment === 'sandbox'
            ? self::ENDPOINT_SANDBOX
            : self::ENDPOINT_PRODUCTION;
    }

    /**
     * Gets the Merchant Gateway Key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantGatewayKey($storeId = null): string
    {
        $environment = $this->getAddtionalValue('credentials', 'environment', $storeId);
        if ($environment === 'sandbox') {
            return  $this->encryptor->decrypt($this->getAddtionalValue('credentials', 'merchant_gateway_key_sandbox', $storeId));
        } else {
            return  $this->encryptor->decrypt($this->getAddtionalValue('credentials', 'merchant_gateway_key', $storeId));
        }
    }

    /**
     * Gets the Merchant Gateway Username
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantGatewayUsername($storeId = null): string
    {
        $environment = $this->getAddtionalValue('credentials', 'environment', $storeId);
        if ($environment === 'sandbox') {
            return  $this->getAddtionalValue('credentials', 'merchant_gateway_username_sandbox', $storeId);
        } else {
            return  $this->getAddtionalValue('credentials', 'merchant_gateway_username', $storeId);
        }
    }

    /**
     * Gets the AddtionalValues
     *
     * @param string $typePattern
     * @param string $field
     * @param int|null $storeId
     * @return string
     */
    
    public function getAddtionalValue($typePattern, $field, $storeId = null): string
    {
        $pathPattern = 'payment/%s/%s';

        if ($typePattern == 'attributes') {
            $typePattern = self::PATTERN_FOR_ATTRIBUTES;
        } elseif ($typePattern == 'credentials') {
            $typePattern = self::PATTERN_FOR_CREDENTIALS;
        }

        return $this->scopeConfig->getValue(
            sprintf($pathPattern, $typePattern, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
