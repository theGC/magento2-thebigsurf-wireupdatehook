<?php

namespace TheBigSurf\WireUpdateHook\Observer;

require_once(  dirname(__FILE__) . DIRECTORY_SEPARATOR . 'UpdateHookObserver.php' );

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class onProductSaveAfter extends UpdateHookObserver
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

        $product     = $observer->getProduct();
        $sku         = $product->getSku();
        $productjson = 'data={"sku":["'.$sku.'"]}';

        return $productjson;

    }

}