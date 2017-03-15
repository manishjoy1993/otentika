<?php
/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_OgoneCw
 * 
 */

namespace Customweb\OgoneCw\Model\Authorization\Method\Context;

abstract class AbstractContext implements IContext
{
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Framework\App\RequestInterface
	 */
	protected $_request;

	/**
	 * @var \Customweb\OgoneCw\Model\Authorization\OrderContextFactory
	 */
	protected $_orderContextFactory;

	/**
	 * @var \Customweb\OgoneCw\Model\Authorization\CustomerContextFactory
	 */
	protected $_customerContextFactory;

	/**
	 * @var \Customweb\OgoneCw\Model\Authorization\CustomerContext
	 */
	protected $customerContext;

	/**
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @param \Customweb\OgoneCw\Model\Authorization\OrderContextFactory $orderContextFactory
	 * @param \Customweb\OgoneCw\Model\Authorization\CustomerContextFactory $customerContextFactory
	 */
	public function __construct(
			\Magento\Framework\Registry $coreRegistry,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Framework\App\RequestInterface $request,
			\Customweb\OgoneCw\Model\Authorization\OrderContextFactory $orderContextFactory,
			\Customweb\OgoneCw\Model\Authorization\CustomerContextFactory $customerContextFactory
	) {
		$this->_coreRegistry = $coreRegistry;
		$this->_checkoutSession = $checkoutSession;
		$this->_request = $request;
		$this->_orderContextFactory = $orderContextFactory;
		$this->_customerContextFactory = $customerContextFactory;
	}

	public function getCustomerContext()
	{
		if (!($this->customerContext instanceof \Customweb\OgoneCw\Model\Authorization\CustomerContext)) {
			$this->customerContext = $this->_customerContextFactory->createWithCustomerId($this->getOrderContext()->getCustomerId());
		}
		return $this->customerContext;
	}

	public function getAliasTransaction()
	{
		$alias = null;
		try {
			$alias = $this->getPaymentMethod()->getInfoInstance()->getAdditionalInformation('alias');
		} catch (\Exception $e) {}
		if ($alias == null) {
			$alias = $this->_request->getParam('alias');
		}
		return $alias;
	}

	public function getParameters()
	{
		$parameters = $this->_request->getParams();
		try {
			$additionalData = $this->getPaymentMethod()->getInfoInstance()->getAdditionalInformation();
			if (is_array($additionalData)) {
				$parameters += $additionalData;
			}
		} catch (\Exception $e) {}
		return $parameters;
	}
}