<?php

/**
 *  * You are allowed to use this API in your web application.
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
 */

//require_once 'Customweb/Ogone/Configuration.php';
//require_once 'Customweb/Ogone/IAdapter.php';
//require_once 'Customweb/Ogone/BackendOperation/Form/StaticTemplate.php';
//require_once 'Customweb/Payment/Authorization/Moto/IAdapter.php';
//require_once 'Customweb/Ogone/Authorization/Transaction.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Ogone/Util.php';
//require_once 'Customweb/Payment/Util.php';
//require_once 'Customweb/I18n/Translation.php';

abstract class Customweb_Ogone_AbstractParameterBuilder {
	private $transactionContext;
	private $transaction;
	private $configuration;
	private $internalCustomParameters = array();
	
	/**
	 *
	 * @var Customweb_DependencyInjection_IContainer
	 */
	private $container;

	public function __construct(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_DependencyInjection_IContainer $container){
		$this->transactionContext = $transaction->getTransactionContext();
		$this->transaction = $transaction;
		$this->container = $container;
		$this->configuration = new Customweb_Ogone_Configuration($this->container->getBean('Customweb_Payment_IConfigurationAdapter'));
	}

	protected function getControllerUrl($controllerName, $actionName, $parameters = array()){
		return $this->getEndpointAdapter()->getUrl($controllerName, $actionName, $parameters);
	}

	/**
	 *
	 * @return Customweb_Payment_Endpoint_IAdapter
	 */
	protected function getEndpointAdapter(){
		return $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter');
	}

	/**
	 *
	 * @return Customweb_Payment_Authorization_ITransactionContext
	 */
	protected function getTransactionContext(){
		return $this->transactionContext;
	}

	/**
	 *
	 * @return Customweb_Ogone_Authorization_Transaction
	 */
	protected function getTransaction(){
		return $this->transaction;
	}

	/**
	 *
	 * @return Customweb_Ogone_Configuration
	 */
	protected function getConfiguration(){
		return $this->configuration;
	}

	abstract public function buildParameters();

	protected function addShopIdToCustomParameters(){
		$shopId = $this->getConfiguration()->getShopId();
		if (!empty($shopId)) {
			$this->internalCustomParameters['shop_id'] = $shopId;
		}
	}

	protected function getPayIdParameter(){
		$payId = $this->getTransaction()->getPaymentId();
		if (empty($payId)) {
			throw new Exception(Customweb_I18n_Translation::__('For a maintenance request a payment id must be set on the transaction.'));
		}
		
		return array(
			'PAYID' => $payId 
		);
	}

	protected function getAuthParameters(){
		$parameters = array();
		
		$userId = $this->getConfiguration()->getApiUserId();
		$password = $this->getConfiguration()->getApiPassword();
		
		if (empty($userId)) {
			throw new Exception(Customweb_I18n_Translation::__('No API username was provided.'));
		}
		
		if (empty($password)) {
			throw new Exception(Customweb_I18n_Translation::__('No API password was provided.'));
		}
		
		return array(
			'USERID' => $userId,
			'PSWD' => $password 
		);
	}

	protected function addShaSignToParameters(&$parameters){
		$parameters['SHASIGN'] = Customweb_Ogone_Util::calculateHash($parameters, 'IN', $this->getConfiguration());
	}

	protected function getAmountParameter($amount){
		return array(
			'AMOUNT' => number_format($amount, 2, '', '') 
		);
	}

	protected function getCurrencyParameter(){
		$currency = $this->getTransaction()->getCurrencyCode();
		if (strlen($currency) != 3) {
			throw new Exception(
					Customweb_I18n_Translation::__('The given currency (!currency) is not 3 chars long. It must be in the ISO 4217 format.', 
							array(
								'!currency' => $currency 
							)));
		}
		
		return array(
			'CURRENCY' => $currency 
		);
	}

	protected function getLanguageParameter(){
		return array(
			'LANGUAGE' => Customweb_Payment_Util::getCleanLanguageCode($this->getTransactionContext()->getOrderContext()->getLanguage()->getIetfCode(), 
					array(
						'en_US',
						'ar_AR',
						'ar_SA',
						'cs_CZ',
						'dk_DK',
						'de_DE',
						'de_CH',
						'de_AT',
						'el_GR',
						'es_ES',
						'fi_FI',
						'fr_FR',
						'fr_CH',
						'he_IL',
						'hu_HU',
						'it_IT',
						'it_CH',
						'ja_JP',
						'ko_KR',
						'nl_BE',
						'nl_NL',
						'no_NO',
						'pl_PL',
						'pt_PT',
						'ru_RU',
						'se_SE',
						'sk_SK',
						'tr_TR',
						'zh_CN' 
					)) 
		);
	}

	protected function getECIParameters(){
		if ($this->getTransaction()->getAuthorizationMethod() == Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME) {
			return array(
				'ECI' => '1' 
			);
		}
		else {
			return array();
		}
	}

	protected function getPspParameter(){
		$pspid = null;
		$storage = $this->getContainer()->getBean('Customweb_Storage_IBackend');
		$configuredCurrencies = $storage->read('Ogone_PSPIDs', 'configuredCurrencies');
		$currency = $this->getTransactionContext()->getOrderContext()->getCurrencyCode();
		if(!empty($configuredCurrencies) && isset($configuredCurrencies[$currency])) {
			$settingsHandler = $this->getContainer()->getBean('Customweb_Payment_SettingHandler');
			if ($this->getConfiguration()->isTestMode()) {
				$pspid = $settingsHandler->getSettingValue($currency.'_test');
			}
			else {
				$pspid = $settingsHandler->getSettingValue($currency.'_live');
			}
		}
		if(empty($pspid)) {
			$pspid =  $this->getConfiguration()->getActivePspId();
		}
		if(!empty($pspid)) {
			return array( 'PSPID' => $pspid);
		}
		else {
			throw new Exception(Customweb_I18n_Translation::__('No PSPID was provided.'));
		}
	}

	protected function getOrderIdParameter(){
		$id = $this->getTransaction()->getExternalOrderId();
		if ($id === null) {
			$id = Customweb_Ogone_Util::substrUtf8($this->getTransactionIdAppliedSchema(), 0, 30);
			$this->getTransaction()->setExternalOrderId($id);
		}
		return array(
			'ORDERID' => $id 
		);
	}

	protected function getOrderDescriptionParameter(){
		$desc = Customweb_Ogone_Util::substrUtf8($this->getOrderDescriptionAppliedSchema(), 0, 30);
		return array(
			'COM' => $desc 
		);
	}

	protected function getTransactionIdAppliedSchema(){
		return Customweb_Ogone_Util::applyOrderSchema($this->getConfiguration(), $this->getTransaction()->getExternalTransactionId());
	}

	protected function getOrderDescriptionAppliedSchema(){
		return Customweb_Ogone_Util::applyOrderDescriptionSchema($this->getConfiguration(), 
				$this->getTransaction()->getExternalTransactionId());
	}

	/**
	 *
	 * @return Customweb_Ogone_Method_Factory
	 */
	public function getPaymentMethodFactory(){
		return $this->getContainer()->getBean('Customweb_Ogone_Method_Factory');
	}

	protected function getPaymentMethod(){
		return $this->getPaymentMethodFactory()->getPaymentMethod($this->getTransactionContext()->getOrderContext()->getPaymentMethod(), 
				$this->getTransaction()->getAuthorizationMethod());
	}

	protected function getAliasManagerParameters(){
		$parameters = array();
		
		if ($this->getTransaction()->getAliasGatewayAlias() !== NULL) {
			return array(
				'ALIAS' => $this->getTransaction()->getAliasGatewayAlias() 
			);
		}
		
		if ($this->getTransactionContext()->getAlias() == 'new' || ($this->getTransactionContext()->createRecurringAlias() && $this->getTransactionContext()->getAlias() == null)) {
			$parameters = $this->getPaymentMethod()->getAliasCreationParameters($this->getTransaction());

		}
		else if ($this->getTransactionContext()->getAlias() !== NULL &&
				 $this->getTransactionContext()->getAlias() instanceof Customweb_Ogone_Authorization_Transaction) {
			$parameters['ALIAS'] = $this->getTransactionContext()->getAlias()->getAliasIdentifier();
		}
		
		// In case we add an alias, we need to add the alias usage message.
		if (isset($parameters['ALIAS'])) {
			$message = $this->getConfiguration()->getAliasUsageMessage(
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getLanguage());
			if (!empty($message)) {
				$parameters['ALIASUSAGE'] = $message;
			}
			else {
				$parameters['ALIASUSAGE'] = Customweb_I18n_Translation::__(
						'You accept that your credit card informations are stored securly for future orders.');
			}
		}
		
		return $parameters;
	}

	protected function getOrigParameter(){
		$parameters = array();
		$origin = 'CMAG17620';
		$parameters['ORIG'] = substr($origin, 0, 10);
		
		return $parameters;
	}

	protected function getCapturingModeParameter(){
		if ($this->getTransactionContext()->getCapturingMode() == null) {
			if ($this->getTransactionContext()->getOrderContext()->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing')) {
				$capturingMode = $this->getTransactionContext()->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue(
						'capturing');
			}
			else {
				return array();
			}
		}
		else {
			$capturingMode = $this->getTransactionContext()->getCapturingMode();
		}
		if (strtolower($capturingMode) == 'direct') {
			return array(
				'OPERATION' => Customweb_Ogone_IAdapter::OPERATION_DIRECT_SALE 
			);
		}
		else {
			return array(
				'OPERATION' => Customweb_Ogone_IAdapter::OPERATION_AUTHORISATION 
			);
		}
	}

	protected function get3DSecureParameters(){
		if ($this->getTransaction()->getAuthorizationMethod() != Customweb_Payment_Authorization_Moto_IAdapter::AUTHORIZATION_METHOD_NAME) {
			return array(
				'FLAG3D' => 'Y',
				'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'],
				'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
				'WIN3DS' => 'MAINW' 
			);
		}
		else {
			return array();
		}
	}

	protected function getParamPlusParameters(){
		$paramplus = '';
		$customParameters = array_merge($this->internalCustomParameters, $this->getTransactionContext()->getCustomParameters());
		
		$customParameters['cw_transaction_id'] = $this->getTransaction()->getExternalTransactionId();
		
		foreach ($customParameters as $key => $value) {
			$paramplus .= $key . '=' . $value . '&';
		}
		
		if (strlen($paramplus) > 0) {
			$paramplus = Customweb_Ogone_Util::substrUtf8($paramplus, 0, -1);
		}
		
		return array(
			'PARAMPLUS' => $paramplus,
			'COMPLUS' => sha1($paramplus) 
		);
	}

	protected function getCustomerParameters(){
		$parameters = array(
			'CN' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingFirstName() . ' ' .
							 $this->getTransactionContext()->getOrderContext()->getBillingLastName(), 0, 35)),
			'EMAIL' => Customweb_Ogone_Util::substrUtf8($this->getTransactionContext()->getOrderContext()->getCustomerEMailAddress(), 0, 
					50),
			'OWNERZIP' => Customweb_Ogone_Util::substrUtf8($this->getTransactionContext()->getOrderContext()->getBillingPostCode(), 0, 
					10),
			'OWNERADDRESS' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8($this->getTransactionContext()->getOrderContext()->getBillingStreet(), 0, 
					50)),
			'OWNERTOWN' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8($this->getTransactionContext()->getOrderContext()->getBillingCity(), 0, 40)),
			'OWNERCTY' => $this->getTransactionContext()->getOrderContext()->getBillingCountryIsoCode() 
		);
		return $parameters;
	}

	protected function getDeliveryAndInvoicingParameters(){
		$parameters = array(
			'ECOM_BILLTO_POSTAL_CITY' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingCity(), 0, 40)),
			'ECOM_BILLTO_POSTAL_COUNTRYCODE' => $this->cleanStringParameter($this->getTransactionContext()->getOrderContext()->getBillingCountryIsoCode()),
			'ECOM_BILLTO_POSTAL_NAME_FIRST' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingFirstName(), 0, 35)),
			'ECOM_BILLTO_POSTAL_NAME_LAST' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingLastName(), 0, 35)),
			'ECOM_BILLTO_POSTAL_POSTALCODE' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingPostCode(), 0, 10)),
			'ECOM_BILLTO_POSTAL_STREET_LINE1' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getBillingStreet(), 0, 35)),
			
			'ECOM_SHIPTO_POSTAL_CITY' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getShippingCity(), 0, 25)),
			'ECOM_SHIPTO_POSTAL_COUNTRYCODE' => $this->getTransactionContext()->getOrderContext()->getShippingCountryIsoCode(),
			'ECOM_SHIPTO_POSTAL_NAME_FIRST' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getShippingFirstName(), 0, 35)),
			'ECOM_SHIPTO_POSTAL_NAME_LAST' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getShippingLastName(), 0, 35)),
			'ECOM_SHIPTO_POSTAL_POSTALCODE' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getShippingPostCode(), 0, 10)),
			'ECOM_SHIPTO_POSTAL_STREET_LINE1' => $this->cleanStringParameter(Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getShippingStreet(), 0, 35)),
			'ECOM_SHIPTO_ONLINE_EMAIL' => Customweb_Ogone_Util::substrUtf8(
					$this->getTransactionContext()->getOrderContext()->getCustomerEMailAddress(), 0, 50) 
		);
		
		$state = $this->getTransactionContext()->getOrderContext()->getShippingAddress()->getState();
		if (!empty($state)) {
			$parameters['ECOM_SHIPTO_POSTAL_STATE'] = $state;
		}
		
		return $parameters;
	}
	
	protected final function cleanStringParameter($string) {
		return preg_replace('/[[:space:]]{2,}/', ' ', $string);
	}

	protected final function getTemplateParameter(){		
		$templateUrl = $this->getConfiguration()->getTemplateUrl();
		if (!empty($templateUrl)) {
			if ($templateUrl === 'default') {
				return array(
					'TP' => $this->getControllerUrl('template', 'index') 
				);
			}
			if($templateUrl === 'static') {
				$settingsHandler = $this->getContainer()->getBean('Customweb_Payment_SettingHandler');
				$fields = Customweb_Ogone_BackendOperation_Form_StaticTemplate::getTemplateFields();
				$parameters = array();
				foreach ($fields as $key => $fieldValues) {
					$value = $settingsHandler->getSettingValue($key);
					if($value === null){
						$parameters[$key] = $fieldValues['default'];						
					}
					else {
						$parameters[$key] = $value;
					}
				}
				return $parameters;		
			}
			else {
				return array(
					'TP' => $templateUrl 
				);
			}
		}
		
		return array();
	}

	protected function getFailedUrl(){
		return Customweb_Util_Url::appendParameters($this->getTransactionContext()->getFailedUrl(), 
				$this->getTransactionContext()->getCustomParameters());
	}

	protected function getSuccessUrl(){
		return Customweb_Util_Url::appendParameters($this->getTransactionContext()->getSuccessUrl(), 
				$this->getTransactionContext()->getCustomParameters());
	}

	/**
	 *
	 * @return Customweb_DependencyInjection_IContainer
	 */
	protected function getContainer(){
		return $this->container;
	}
}
