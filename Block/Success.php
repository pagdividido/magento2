<?php
/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fluxx\Magento2\Block;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

class Success extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Checkout\Model\Session
    */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;
    
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Sales\Model\Order\Config                $orderConfig
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param array                                            $data
     */
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
    }

    /**
     * getPayment
     * 
     * @return MethodInstance
     */
    public function getPayment(){
    	$order = $this->_checkoutSession->getLastRealOrder();
    	return $order->getPayment()->getMethodInstance();
    }

    /**
     * Method Code 
     * 
     * @return string
     */
    public function getMethodCode()
    {
    	return $this->getPayment()->getCode();
    }

    /**
     * Info payment
     * 
     * @param  $info 
     * @return string
     */
    public function getInfo($info)
    {
        return  $this->getPayment()->getInfoInstance()->getAdditionalInformation($info);
    }
}