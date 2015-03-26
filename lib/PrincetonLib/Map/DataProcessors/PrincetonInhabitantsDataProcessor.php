<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonInhabitantsDataProcessor extends KGODataProcessor
{
    private $_inhabitantsAPIUrl = "https://atsweb.princeton.edu/mobile/map/arcgis/orgs.php/ALL/query";

    public static function inhabitantsParsemap() {
        return array(
            'key' => 'features',
            'array' => true,
            'class' => 'KGODataObject',
            'attributes' => array(
                KGODataObject::ID_ATTRIBUTE    => 'attributes.OBJECTID',
                KGODataObject::TITLE_ATTRIBUTE    => 'attributes.Name',
                'inhabitant:locationid'    => 'attributes.LOCATIONID',
                'inhabitant:category'    => 'attributes.Category',
                'inhabitant:webpage'    => 'attributes.Webpage',
                'inhabitant:phone'    => 'attributes.Phone',
            ),
            'processors' => array(
                'inhabitant:webpage' => array(
                    array(
                        'class' => 'KGOTrimToNullDataProcessor',
                    ),
                    array(
                        'class' => 'KGOURLDataProcessor',
                    ),
                ),
                'inhabitant:phone' => array(
                    array(
                        'class' => 'KGOTrimToNullDataProcessor',
                    ),
                    array(
                        'class' => 'KGOPhoneNumberToURLDataProcessor',
                    ),
                ),
            ),
        );
    }

    protected function processValue($value, $object = null) {
        $retriever = KGODataRetriever::factory('KGOURLDataRetriever', array(
            'useCurl' => true,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'baseURL' => $this->_inhabitantsAPIUrl,
            'dataParser' => 'KGOJSONDataParser',
        ));
        $retriever->setParsemap(static::inhabitantsParsemap());
        $locations = $retriever->getData($response);

        $placeInfo = $object->getPlaceInfo();
        $locationId = $placeInfo['LOCATIONID'];

        foreach ($locations as $i => &$location) {
            if ($locationId != $location->getAttribute('inhabitant:locationid')) {
                unset($locations[$i]);
            }
        }

        return array_merge($locations);
    }

    protected function canProcessValue($value, $object = null) {
        return true;
    }
}
