<?php
namespace TheBigSurf\WireUpdateHook\Api;

interface SkuRepositoryInterface
{

    /**
     * get a list of all SKUs within Magento Store
     * @return array $skus
     */
    public function get();

	/**
	 * get a list of IDs with the passed SKUs (pipe deliminated)
     * @param string[] $skus
	 * @return string
	 */
    public function getID($skus);

    /**
     * get a list of all SKUs modified after $date within Magento Store
     * @param string $date
     * @return array $skus
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getModifiedAfter($date);

}
