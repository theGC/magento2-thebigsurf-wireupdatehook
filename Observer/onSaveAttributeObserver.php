<?php

namespace TheBigSurf\WireUpdateHook\Observer;

require_once(  dirname(__FILE__) . DIRECTORY_SEPARATOR . 'UpdateHookObserver.php' );

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class onSaveAttributeObserver extends UpdateHookObserver
{

    /**
     * URL for API endpoint
     * @return string
     *
     */
    protected function getApiUrl() {

        return $this->getAttributeApiUrl();

    }


    /**
     * build the JSON data to pass with the request
     * @return string - JSON formatted
     *
     */
    protected function getData( $observer ) {

        $attribute     = $observer->getEvent()->getAttribute();
        $attr_id       = $attribute->getAttributeId();
        $attributeJSON = 'data={"id":["'.$attr_id.'"]}';

        return $attributeJSON;

    }

}
