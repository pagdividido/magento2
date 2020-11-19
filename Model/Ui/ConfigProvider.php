<?php
/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace PagDividido\Magento2\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider.
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'pagdividido_magento2';

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param Repository       $assetRepo
     * @param RequestInterface $request
     * @param UrlInterface     $urlBuilder
     */
    public function __construct(
        Repository $assetRepo,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->assetRepo = $assetRepo;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * Retrieve assoc array of checkout configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'birthRegion' => [
                        'AC' => __('Acre'),
                        'AL' => __('Alagoas'),
                        'AP' => __('Amapá'),
                        'AM' => __('Amazonas'),
                        'BA' => __('Bahia'),
                        'CE' => __('Ceará'),
                        'DF' => __('Distrito Federal'),
                        'ES' => __('Espirito Santo'),
                        'GO' => __('Goiás'),
                        'MA' => __('Maranhão'),
                        'MS' => __('Mato Grosso do Sul'),
                        'MT' => __('Mato Grosso'),
                        'MG' => __('Minas Gerais'),
                        'PA' => __('Pará'),
                        'PB' => __('Paraíba'),
                        'PR' => __('Paraná'),
                        'PE' => __('Pernambuco'),
                        'PI' => __('Piauí'),
                        'RJ' => __('Rio de Janeiro'),
                        'RN' => __('Rio Grande do Norte'),
                        'RS' => __('Rio Grande do Sul'),
                        'RO' => __('Rondônia'),
                        'RR' => __('Roraima'),
                        'SC' => __('Santa Catarina'),
                        'SP' => __('São Paulo'),
                        'SE' => __('Sergipe'),
                        'TO' => __('Tocantins'),
                    ],
                    'checkOffers' => ['availability' => true],
                    'logo'        => $this->getLoggImageUrl(),

                ],
            ],
        ];
    }

    /**
     * Retrieve Logo image url.
     *
     * @return string
     */
    public function getLoggImageUrl()
    {
        return $this->getViewFileUrl('pagdividido_magento2::images/logo.svg');
    }

    /**
     * Retrieve url of a view file.
     *
     * @param string $fileId
     * @param array  $params
     *
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);

            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);

            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

    /**
     * Create a file asset that's subject of fallback system.
     *
     * @param string $fileId
     * @param array  $params
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);

        return $this->assetRepo->createAsset($fileId, $params);
    }
}
