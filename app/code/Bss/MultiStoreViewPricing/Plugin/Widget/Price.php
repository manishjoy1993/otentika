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
namespace Bss\MultiStoreViewPricing\Plugin\Widget;

use Magento\CatalogWidget\Block\Product\ProductsList as ProductsList;
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

	public function afterCreateCollection(ProductsList $list, $result)
	{
		if($this->_helper->isScopePrice()) {

			$connection  = $this->_resource->getConnection();
			$tableName = $connection->getTableName('catalog_product_entity_decimal');
			$store_id = $this->_storeManager->getStore()->getId();
			$minimalExpr = $connection->getCheckSql('bss_price_table_4.value IS NOT NULL',
				'bss_price_table_4.value', 'bss_price_table_3.value');

			$result->getSelect()->joinLeft(
				array('bss_price_table_3' => $tableName),
				"e.entity_id = bss_price_table_3.entity_id 
				AND bss_price_table_3.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_3.store_id = 0", 
				array('bss_default_price' => 'bss_price_table_3.value')
				);

			$result->getSelect()->joinLeft(
				array('bss_price_table_4' => $tableName),
				"e.entity_id = bss_price_table_4.entity_id 
				AND bss_price_table_4.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_4.store_id = ".$store_id, 
				array('price' => $minimalExpr,  'final_price' => 'bss_price_table_4.value','minimal_price'=> 'bss_price_table_4.value')
				);

			$result->getSelect()->joinLeft(
				array('bss_price_table_5' => $tableName),
				"e.entity_id = bss_price_table_5.entity_id 
				AND bss_price_table_5.attribute_id = (SELECT attribute_id FROM " . $connection->getTableName('eav_attribute') . " WHERE attribute_code = 'special_price' AND backend_model != '' LIMIT 1)  
				AND bss_price_table_5.store_id = ".$store_id." 
				AND bss_price_table_5.value < bss_price_table_1.value",
				array('final_price' => 'bss_price_table_5.value','minimal_price'=> 'bss_price_table_5.value')
				);
			// echo $result->getSelect()->__toString();die;
	        return $result;

		}
		return $result;
	}
}