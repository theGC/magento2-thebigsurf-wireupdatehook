<?php

namespace TheBigSurf\WireUpdateHook\Observer;

require_once(  dirname(__FILE__) . DIRECTORY_SEPARATOR . 'UpdateHookObserver.php' );

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class onProductAttrUpdateBefore extends UpdateHookObserver
{

    /**
     * URL for API endpoint
     * @return string
     *
     */
    protected function getApiUrl() {

        return $this->getSkuApiUrl();

    }


    /**
     * build the JSON data to pass with the request
     * @return string - JSON formatted
     *
     */
    protected function getData( $observer ) {

        $productids    = $observer->getProductIds();

        $skus          = [];

        $objectManager = \magento\Framework\App\ObjectManager::getInstance();

        foreach($productids as $key => $productid) {
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productid);
            $skus[]  = $product->getSku();
        }

        $productjson = '{"data":{"sku":["'.implode('","',$skus).'"]}}';

        return $productjson;

    }

}