<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonLocationsDataParser extends KGOXMLLocationsDataParser
{
    public function parseData($data) {
        $data = preg_replace(
            array(
                '/events>/',
                '/maploc>/',
                '/base_url>/',
                '/maploc>/',
                '/<baseURL>/'
            ),array(
                'eventsFeedConfig>',
                'mapLoc>',
                'baseURL>',
                'mapName>',
                '<itemtype>princeton-dining-event</itemtype><baseURL>',
            ),
        $data);

        return parent::parseData($data);
    }
}
