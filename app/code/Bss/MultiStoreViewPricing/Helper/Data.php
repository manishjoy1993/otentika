<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_MultiStoreViewPricing
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\MultiStoreViewPricing\Helper;

/**
 * Visitor Observer
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_scopeConfig;

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
		$this->_scopeConfig = $scopeConfig;
	}

	public function isScopePrice() {
		$active =  $this->_scopeConfig->getValue('catalog/price/scope', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($active == 2) {
			return true;
		}
		return false;
	}
}