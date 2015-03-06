<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonTransitRouteIconDataProcessor extends KGODataProcessor
{
    protected function processValue($value, $object = null) {
        $routeId = $object->getId();

        $imageURL = KGOURL::createForImageFile(sprintf("transit/transloc__%s.png", $routeId));

        return $imageURL;
    }

    protected function canProcessValue($value, $object = null){
        return $object instanceof ModoTransitRoute;
    }
}
