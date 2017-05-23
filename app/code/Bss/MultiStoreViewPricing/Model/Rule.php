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
namespace Bss\MultiStoreViewPricing\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Rule extends \Magento\CatalogRule\Model\ResourceModel\Rule
{
    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param \DateTime $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param array $productIds
     * @return array
     */
    public function getRulePrices(\DateTime $date, $websiteId, $customerGroupId, $productIds)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
        if(!$helper->isScopePrice()) {
            return parent::getRulePrices($date, $websiteId, $customerGroupId, $productIds);
        }
        
        $storeId = $this->_storeManager->getStore()->getId();

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('bss_catalogrule_product_price'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('website_id = ?', $websiteId)
            ->where('store_id = ?', $storeId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);

           return $connection->fetchPairs($select);
       }
   }