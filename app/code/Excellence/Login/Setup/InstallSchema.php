<?php


namespace Excellence\Login\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup;
		$installer->startSetup();

		/**
		 * Creating table excellence_login
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('excellence_login')
		)->addColumn(
			'login_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'News Title'
		)->addColumn(
			'author',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Author'
		)->addColumn(
			'content',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Content'
		)->addColumn(
			'image',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => true,'default' => null],
			'Login image media path'
		)->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false],
			'Created At'
		)->addColumn(
			'published_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
			null,
			['nullable' => true,'default' => null],
			'World publish date'
		)->addIndex(
			$installer->getIdxName(
				'excellence_login',
				['published_at'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
			),
			['published_at'],
			['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
		)->setComment(
			'Login item'
		);
		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}