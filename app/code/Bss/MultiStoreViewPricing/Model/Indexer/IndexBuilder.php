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
namespace Bss\MultiStoreViewPricing\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder extends \Magento\CatalogRule\Model\Indexer\IndexBuilder
{
	/**
     * @param Rule $rule
     * @param Product $product
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	protected function applyRule(Rule $rule, $product)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
		if(!$helper->isScopePrice()) {
			return parent::applyRule($rule, $product);
		}

		$ruleId = $rule->getId();
		$productEntityId = $product->getId();
		$websiteIds = array_intersect($product->getWebsiteIds(), $rule->getWebsiteIds());

		if (!$rule->validate($product)) {
			return $this;
		}

		$this->connection->delete(
			$this->resource->getTableName('bss_catalogrule_product'),
			[
			$this->connection->quoteInto('rule_id = ?', $ruleId),
			$this->connection->quoteInto('product_id = ?', $productEntityId)
			]
			);

		$customerGroupIds = $rule->getCustomerGroupIds();
		$fromTime = strtotime($rule->getFromDate());
		$toTime = strtotime($rule->getToDate());
		$toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
		$sortOrder = (int)$rule->getSortOrder();
		$actionOperator = $rule->getSimpleAction();
		$actionAmount = $rule->getDiscountAmount();
		$actionStop = $rule->getStopRulesProcessing();

		$rows = [];
		try {
			foreach ($websiteIds as $websiteId) {
				$website = $this->storeManager->getWebsite($websiteId);
				foreach ($website->getGroups() as $group) {
					$stores = $group->getStores();
					foreach ($stores as $store) {
						foreach ($customerGroupIds as $customerGroupId) {
							$rows[] = [
							'rule_id' => $ruleId,
							'from_time' => $fromTime,
							'to_time' => $toTime,
							'website_id' => $websiteId,
							'store_id' => $store->getId(),
							'customer_group_id' => $customerGroupId,
							'product_id' => $productEntityId,
							'action_operator' => $actionOperator,
							'action_amount' => $actionAmount,
							'action_stop' => $actionStop,
							'sort_order' => $sortOrder,
							];

							if (count($rows) == $this->batchCount) {
								$this->connection->insertMultiple($this->getTable('bss_catalogrule_product'), $rows);
								$rows = [];
							}
						}
					}
				}
			}

			if (!empty($rows)) {
				$this->connection->insertMultiple($this->resource->getTableName('bss_catalogrule_product'), $rows);
			}
		} catch (\Exception $e) {
			throw $e;
		}

		$this->applyAllRules($product);

		return $this;
	}

	/**
     * Clean by product ids
     *
     * @param array $productIds
     * @return void
     */
	protected function cleanByIds($productIds)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
		if(!$helper->isScopePrice()) {
			return parent::cleanByIds($productIds);
		}

		$query = $this->connection->deleteFromSelect(
			$this->connection
			->select()
			->from($this->resource->getTableName('bss_catalogrule_product'), 'product_id')
			->distinct()
			->where('product_id IN (?)', $productIds),
			$this->resource->getTableName('bss_catalogrule_product')
			);
		$this->connection->query($query);

		$query = $this->connection->deleteFromSelect(
			$this->connection->select()
			->from($this->resource->getTableName('bss_catalogrule_product_price'), 'product_id')
			->distinct()
			->where('product_id IN (?)', $productIds),
			$this->resource->getTableName('bss_catalogrule_product_price')
			);
		$this->connection->query($query);
	}

	/**
     * @param Rule $rule
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	protected function updateRuleProductData(Rule $rule)
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
		if(!$helper->isScopePrice()) {
			return parent::updateRuleProductData($rule);
		}


		$ruleId = $rule->getId();
		if ($rule->getProductsFilter()) {
			$this->connection->delete(
				$this->getTable('bss_catalogrule_product'),
				['rule_id=?' => $ruleId, 'product_id IN (?)' => $rule->getProductsFilter()]
				);
		} else {
			$this->connection->delete(
				$this->getTable('bss_catalogrule_product'),
				$this->connection->quoteInto('rule_id=?', $ruleId)
				);
		}

		if (!$rule->getIsActive()) {
			return $this;
		}

		$websiteIds = $rule->getWebsiteIds();
		if (!is_array($websiteIds)) {
			$websiteIds = explode(',', $websiteIds);
		}
		if (empty($websiteIds)) {
			return $this;
		}

		\Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
		$productIds = $rule->getMatchingProductIds();
		\Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');

		$customerGroupIds = $rule->getCustomerGroupIds();
		$fromTime = strtotime($rule->getFromDate());
		$toTime = strtotime($rule->getToDate());
		$toTime = $toTime ? $toTime + self::SECONDS_IN_DAY - 1 : 0;
		$sortOrder = (int)$rule->getSortOrder();
		$actionOperator = $rule->getSimpleAction();
		$actionAmount = $rule->getDiscountAmount();
		$actionStop = $rule->getStopRulesProcessing();

		$rows = [];

		foreach ($productIds as $productId => $validationByWebsite) {
			foreach ($websiteIds as $websiteId) {
				if (empty($validationByWebsite[$websiteId])) {
					continue;
				}

				$website = $this->storeManager->getWebsite($websiteId);
				foreach ($website->getGroups() as $group) {
					$stores = $group->getStores();
					foreach ($stores as $store) {
						foreach ($customerGroupIds as $customerGroupId) {
							$rows[] = [
							'rule_id' => $ruleId,
							'from_time' => $fromTime,
							'to_time' => $toTime,
							'website_id' => $websiteId,
							'store_id' => $store->getId(),
							'customer_group_id' => $customerGroupId,
							'product_id' => $productId,
							'action_operator' => $actionOperator,
							'action_amount' => $actionAmount,
							'action_stop' => $actionStop,
							'sort_order' => $sortOrder,
							];

							if (count($rows) == $this->batchCount) {
								$this->connection->insertMultiple($this->getTable('bss_catalogrule_product'), $rows);
								$rows = [];
							}
						}
					}
				}
			}
		}
		if (!empty($rows)) {
			$this->connection->insertMultiple($this->getTable('bss_catalogrule_product'), $rows);
		}

		return $this;
	}

	/**
     * Clean rule price index
     *
     * @return $this
     */
	protected function deleteOldData()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
		if(!$helper->isScopePrice()) {
			return parent::deleteOldData();
		}

		$this->connection->delete($this->getTable('bss_catalogrule_product_price'));
		return $this;
	}


	/**
     * @param int $websiteId
     * @param Product|null $product
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function getRuleProductsStmt($websiteId, Product $product = null, $storeId = null)
	{
        /**
         * Sort order is important
         * It used for check stop price rule condition.
         * website_id   customer_group_id   product_id  sort_order
         *  1           1                   1           0
         *  1           1                   1           1
         *  1           1                   1           2
         * if row with sort order 1 will have stop flag we should exclude
         * all next rows for same product id from price calculation
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
        if(!$helper->isScopePrice()) {
        	return parent::getRuleProductsStmt($websiteId, $product = null);
        }

        $select = $this->connection->select()->from(
        	['rp' => $this->getTable('bss_catalogrule_product')]
        	)->order(
        	['rp.website_id','rp.store_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id']
        	);

        	if ($product && $product->getEntityId()) {
        		$select->where('rp.product_id=?', $product->getEntityId());
        	}

        	if ($storeId !== null) {
        		$select->where('rp.store_id=?', $storeId);
        	}

        /**
         * Join default price and websites prices to result
         */
        $priceAttr = $this->eavConfig->getAttribute(Product::ENTITY, 'price');
        $priceTable = $priceAttr->getBackend()->getTable();
        $attributeId = $priceAttr->getId();

        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select->join(
        	['e' => $this->getTable('catalog_product_entity')],
        	sprintf('e.entity_id = rp.product_id'),
        	[]
        	);
        $joinCondition = '%1$s.' . $linkField . '=e.' . $linkField . ' AND (%1$s.attribute_id='
        . $attributeId
        . ') and %1$s.store_id=%2$s';

        $select->join(
        	['pp_default' => $priceTable],
        	sprintf($joinCondition, 'pp_default', \Magento\Store\Model\Store::DEFAULT_STORE_ID),
        	[]
        	);

        // $website = $this->storeManager->getWebsite($websiteId);
        // $defaultGroup = $website->getDefaultGroup();
        // if ($defaultGroup instanceof \Magento\Store\Model\Group) {
        // 	$storeId = $defaultGroup->getDefaultStoreId();
        // } else {
        // 	$storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        // }

        $select->joinInner(
        	['product_website' => $this->getTable('catalog_product_website')],
        	'product_website.product_id=rp.product_id '
        	. 'AND product_website.website_id = rp.website_id '
        	. 'AND product_website.website_id='
        	. $websiteId,
        	[]
        	);

        $tableAlias = 'pp' . $websiteId;
        $select->joinLeft(
        	[$tableAlias => $priceTable],
        	sprintf($joinCondition, $tableAlias, $storeId),
        	[]
        	);
        $select->columns([
        	'default_price' =>$this->connection->getIfNullSql($tableAlias . '.value', 'pp_default.value'),
        	]);

        return $this->connection->query($select);
    }

    /**
     * @param array $arrData
     * @return $this
     * @throws \Exception
     */
    protected function saveRuleProductPrices($arrData)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
    	if(!$helper->isScopePrice()) {
    		return parent::saveRuleProductPrices($arrData);
    	}


    	if (empty($arrData)) {
    		return $this;
    	}

    	$productIds = [];

    	try {
    		foreach ($arrData as $key => $data) {
    			$productIds['product_id'] = $data['product_id'];
    			$arrData[$key]['rule_date'] = $this->dateFormat->formatDate($data['rule_date'], false);
    			$arrData[$key]['latest_start_date'] = $this->dateFormat->formatDate($data['latest_start_date'], false);
    			$arrData[$key]['earliest_end_date'] = $this->dateFormat->formatDate($data['earliest_end_date'], false);
    		}
    		$this->connection->insertOnDuplicate($this->getTable('bss_catalogrule_product_price'), $arrData);
    	} catch (\Exception $e) {
    		throw $e;
    	}

    	return $this;
    }

    /**
     * @param Product|null $product
     * @throws \Exception
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function applyAllRules(Product $product = null)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$helper = $objectManager->create('Bss\MultiStoreViewPricing\Helper\Data');
    	if(!$helper->isScopePrice()) {
    		return parent::applyAllRules($product = null);
    	}


    	$fromDate = mktime(0, 0, 0, date('m'), date('d') - 1);
    	$toDate = mktime(0, 0, 0, date('m'), date('d') + 1);

        /**
         * Update products rules prices per each website separately
         * because of max join limit in mysql
         */
        foreach ($this->storeManager->getWebsites() as $website) {
        	foreach ($website->getGroups() as $group) {
        		$stores = $group->getStores();
        		foreach ($stores as $store) {
        			$productsStmt = $this->getRuleProductsStmt($website->getId(), $product, $store->getId());

        			$dayPrices = [];
        			$stopFlags = [];
        			$prevKey = null;

        			while ($ruleData = $productsStmt->fetch()) {
        				$ruleProductId = $ruleData['product_id'];
        				$productKey = $ruleProductId .
        				'_' .
        				$ruleData['website_id'] .
        				'_' .
        				$ruleData['customer_group_id'];

        				if ($prevKey && $prevKey != $productKey) {
        					$stopFlags = [];
        					if (count($dayPrices) > $this->batchCount) {
        						$this->saveRuleProductPrices($dayPrices);
        						$dayPrices = [];
        					}
        				}

        				$ruleData['from_time'] = $this->roundTime($ruleData['from_time']);
        				$ruleData['to_time'] = $this->roundTime($ruleData['to_time']);
		                /**
		                 * Build prices for each day
		                 */
		                for ($time = $fromDate; $time <= $toDate; $time += self::SECONDS_IN_DAY) {
		                	if (($ruleData['from_time'] == 0 ||
		                		$time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
		                		$time <= $ruleData['to_time'])
		                		) {
		                		$priceKey = $time . '_' . $productKey;

		                	if (isset($stopFlags[$priceKey])) {
		                		continue;
		                	}

		                	if (!isset($dayPrices[$priceKey])) {
		                		$dayPrices[$priceKey] = [
		                		'rule_date' => $time,
		                		'website_id' => $ruleData['website_id'],
		                		'store_id' => $ruleData['store_id'],
		                		'customer_group_id' => $ruleData['customer_group_id'],
		                		'product_id' => $ruleProductId,
		                		'rule_price' => $this->calcRuleProductPrice($ruleData),
		                		'latest_start_date' => $ruleData['from_time'],
		                		'earliest_end_date' => $ruleData['to_time'],
		                		];
		                	} else {
		                		$dayPrices[$priceKey]['rule_price'] = $this->calcRuleProductPrice(
		                			$ruleData,
		                			$dayPrices[$priceKey]
		                			);
		                		$dayPrices[$priceKey]['latest_start_date'] = max(
		                			$dayPrices[$priceKey]['latest_start_date'],
		                			$ruleData['from_time']
		                			);
		                		$dayPrices[$priceKey]['earliest_end_date'] = min(
		                			$dayPrices[$priceKey]['earliest_end_date'],
		                			$ruleData['to_time']
		                			);
		                	}

		                	if ($ruleData['action_stop']) {
		                		$stopFlags[$priceKey] = true;
		                	}
		                }
		            }

		            $prevKey = $productKey;
		        }
		        $this->saveRuleProductPrices($dayPrices);
		    }
		}
	}

	return $this->updateCatalogRuleGroupWebsiteData();
}
	
	/**
     * @param int $timeStamp
     * @return int
     */
	private function roundTime($timeStamp)
    {
        if (is_numeric($timeStamp) && $timeStamp != 0) {
            $timeStamp = $this->dateTime->timestamp($this->dateTime->date('Y-m-d 00:00:00', $timeStamp));
        }

        return $timeStamp;
    }

    /**
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\EntityManager\MetadataPool');
        }
        return $this->metadataPool;
    }
}