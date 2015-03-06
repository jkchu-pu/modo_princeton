<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonTransitMapImageURLDataProcessor extends KGODataProcessor
{
    protected function processValue($value, $object = null) {
        return KGOURL::createForImageFile('transit/RouteMap.jpg');
    }

    protected function canProcessValue($value, $object = null) {
        return true;
    }
}
