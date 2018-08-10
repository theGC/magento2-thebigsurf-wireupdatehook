<?php
namespace TheBigSurf\WireUpdateHook\Model;

use TheBigSurf\WireUpdateHook\Api\SkuRepositoryInterface;

class SkuRepository implements SkuRepositoryInterface
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
        $table = $connection->getTableName('catalog_product_entity');
        $result = $connection->fetchAll('SELECT sku FROM `'.$table.'`');

        $skus = array_column($result, 'sku');
        return $skus;
    }

    /**
     * {@inheritdoc}
     */
    public function getID($skus) {

        $output = [];

        $connection = $this->_resource->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
        );

        $table = $connection->getTableName('catalog_product_entity');

        foreach ($skus as $sku) {

            $allIDs = $connection->fetchAll("SELECT entity_id FROM `$table` WHERE sku = '$sku'");

            $id = array_column($allIDs, 'entity_id');

            if (count($id)) $output[$sku] = $id[0];

        }

        return json_encode($output);

    }

    /**
     * {@inheritdoc}
     */
    public function getModifiedAfter($date) {

    	if ( ! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date) )

    		throw new NoSuchEntityException(__('Date format is not correct, expects: YYYY-MM-DD'));

		$connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
		$table = $connection->getTableName('catalog_product_entity');
		$result = $connection->fetchAll("SELECT sku FROM `" . $table . "` where updated_at > '" . $date . " 00:00:00'");

		$skus = array_column($result,'sku');
		return $skus;
    }

}