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
namespace Bss\MultiStoreViewPricing\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
class Price
{
	protected $_resource;
	protected $_storeManager;
	protected $_helper;

	public function __construct(
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Bss\MultiStoreViewPricing\Helper\Data $helper
		) {
		$this->_resource = $resource;
		$this->_storeManager = $storeManager;
		$this->_helper = $helper;
	}

	public function afterSetVisibility(ProductCollection $collection , $visibility)
	{
		if($this->_helper->isScopePrice()) {
			$connection  = $this->_resource->getConnection();
			$tableName = $connection->getTableName('catalog_product_entity_decimal');
			$store_id = $this->_storeManager->getStore()->getId();
			$minimalExpr = $connection->getCheckSql('bss_price_table_1.value IS NOT NULL',
				'bss_price_table_1.value', 'bss_price_table_0.value');

			$collection->getSelect()->joinLeft(
				array('bss_price_table_0' => $tableName),
				"e.entity_id = bss_price_table_0.entity_id 
				AND bss_price_table_0.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_0.store_id = 0", 
				array('bss_default_price' => 'bss_price_table_0.value')
				);

			$collection->getSelect()->joinLeft(
				array('bss_price_table_1' => $tableName),
				"e.entity_id = bss_price_table_1.entity_id 
				AND bss_price_table_1.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_1.store_id = ".$store_id, 
				array('price' => $minimalExpr,  'final_price' => 'bss_price_table_1.value','minimal_price'=> 'bss_price_table_1.value')
				);

			$collection->getSelect()->joinLeft(
				array('bss_price_table_2' => $tableName),
				"e.entity_id = bss_price_table_2.entity_id 
				AND bss_price_table_2.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'special_price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_2.store_id = ".$store_id." 
				AND bss_price_table_2.value < bss_price_table_1.value",
				array('final_price' => 'bss_price_table_2.value','minimal_price'=> 'bss_price_table_2.value')
				);

			// echo $collection->getSelect()->__toString();die;
		}
		return $collection;
	}

	public function afterAddStoreFilter(ProductCollection $collection , $visibility) {
		if($this->_helper->isScopePrice()) {
			$connection  = $this->_resource->getConnection();
			$tableName = $connection->getTableName('catalog_product_entity_decimal');
			$store_id = $this->_storeManager->getStore()->getId();
			$minimalExpr = $connection->getCheckSql('bss_price_table_7.value IS NOT NULL',
				'bss_price_table_7.value', 'bss_price_table_6.value');

			$collection->getSelect()->joinLeft(
				array('bss_price_table_6' => $tableName),
				"e.entity_id = bss_price_table_6.entity_id 
				AND bss_price_table_6.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_6.store_id = 0", 
				array('bss_default_price' => 'bss_price_table_6.value')
				);

			$collection->getSelect()->joinLeft(
				array('bss_price_table_7' => $tableName),
				"e.entity_id = bss_price_table_7.entity_id 
				AND bss_price_table_7.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_7.store_id = ".$store_id, 
				array('price' => $minimalExpr,  'final_price' => 'bss_price_table_7.value','minimal_price'=> 'bss_price_table_7.value')
				);

			$collection->getSelect()->joinLeft(
				array('bss_price_table_8' => $tableName),
				"e.entity_id = bss_price_table_8.entity_id 
				AND bss_price_table_8.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'special_price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_8.store_id = ".$store_id." 
				AND bss_price_table_8.value < bss_price_table_7.value",
				array('final_price' => 'bss_price_table_8.value','minimal_price'=> 'bss_price_table_8.value')
				);

			// echo $collection->getSelect()->__toString();die;
		}
		return $collection;
	}
}