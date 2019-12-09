<?php
namespace TheBigSurf\WireUpdateHook\Api;

interface AttributeRepositoryInterface
{

    /**
     * get a list of all Attribute IDs within Magento Store
     * @return array $ids
     */
    public function get();

}
