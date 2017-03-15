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
namespace Bss\MultiStoreViewPricing\Pricing\Price;


use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;

/**
 * Visitor Observer
 */
class CatalogRulePrice extends \Magento\CatalogRule\Pricing\Price\CatalogRulePrice
{
	public function getValue()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
    	if($helper->isScopePrice()) {
    		if (null === $this->value) {
                $this->value = $this->resourceRuleFactory->create()
                ->getRulePrice(
                    $this->dateTime->scopeDate($this->storeManager->getStore()->getId()),
                    $this->storeManager->getStore()->getWebsiteId(),
                    $this->customerSession->getCustomerGroupId(),
                    $this->product->getId(),
                    $this->storeManager->getStore()->getId()
                    );
                $this->value = $this->value ? floatval($this->value) : false;
                if ($this->value) {
                    $this->value = $this->priceCurrency->convertAndRound($this->value);
                }
            }
            return $this->value;
            
        }else {
          return parent::getValue();
      }
  }
}