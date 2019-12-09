<?php
namespace TheBigSurf\WireUpdateHook\Model;

use TheBigSurf\WireUpdateHook\Api\AttributeRepositoryInterface;

class AttributeRepository implements AttributeRepositoryInterface
{
    protected $_resource;

	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		$resourcePrefix = null
	) {

        $this->transactionManager = $context->getTransactionManager();

        $this->_resource = $context->getResources();

    }

    /**
     * {@inheritdoc}
     */
    public function get() {

        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $table = $connection->getTableName('eav_attribute');

        $result = $connection->fetchAll("SELECT attribute_code FROM `$table`"
            . ' WHERE entity_type_id=4'
            . ' AND frontend_input IS NOT NULL'
            . ' AND frontend_label IS NOT NULL'
            . ' AND attribute_code NOT IN ("image_label", "merchant_center_category", "minimal_price", "small_image_label", "thumbnail_label")');

        $codes = array_column($result, 'attribute_code');

        return $codes;

    }

}