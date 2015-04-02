<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonArcGISGeometryDataRetriever extends KGOURLDataRetriever
{
    protected static $defaultParserClass = 'KGOJSONDataParser';

    protected $geomPlacemarks = array();

    public function getGeometry($locationId, &$targetedParentId, &$response) {
        $baseURL = $this->getArg('baseURL');
        $parentId = $this->getArg('parentId');

        if (!is_array($parentId)) {
            $parentId = array($parentId);
        }

        $geomData = null;
        foreach ($parentId as $targetParentId) {
            $this->clearInternalCache();

            if (!isset($this->geomPlacemarks[$targetParentId])) {
                $this->setBaseURL($baseURL.'/'.$targetParentId.'/query?where=1%3D1&geometryType=esriGeometryEnvelope&spatialRel=esriSpatialRelIntersects&outFields=*&returnGeometry=true&returnIdsOnly=false&returnCountOnly=false&returnZ=false&returnM=false&returnDistinctValues=false&f=pjson');

                $data = $this->getData($response);

                $this->geomPlacemarks[$targetParentId] = $data['features'];
            }

            $placemarks = $this->geomPlacemarks[$targetParentId];

            foreach ($placemarks as &$placemark) {
                if ($placemark['attributes']['Location_ID'] == $locationId) {

                    if (isset($placemark['geometry'])) {
                        $geomData = $placemark['geometry'];
                    } else if (
                        isset($placemark['attributes'])
                        && isset($placemark['attributes']['X_WGS84_web'])
                        && isset($placemark['attributes']['Y_WGS84_web'])
                    ) {
                        $geomData = array(
                            'x' => $placemark['attributes']['X_WGS84_web'],
                            'y' => $placemark['attributes']['Y_WGS84_web'],
                        );
                    }
                    $targetedParentId = $targetParentId;
                    break;
                }
            }

            unset($placemarks);

            if ($geomData) {
                break;
            }
        }

        if (!$geomData) {
            //kgo_flash_debug("No geometry found for location '$locationId'");
            return null;
        }

        return $geomData;
    }
}
