<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonMapGoogleDirectionsURLDataProcessor extends KGODataProcessor
{
    private $_directionsURLTemplate = "http://maps.google.com/?dirflg=w&daddr=%s@%s,%s";

    protected function processValue($value, $object = null) {
        $lat = $object->getAttribute('kgomap:latitude');
        $lon = $object->getAttribute('kgomap:longitude');
        $name = $object->getTitle();

        if (!$name || !$lat || !$lon) {
            return null;
        }

        return sprintf($this->_directionsURLTemplate, urlencode($name), $lat, $lon);
    }

    protected function canProcessValue($value, $object = null) {
        return $object instanceOf KGOMapPlacemark;
    }
}
