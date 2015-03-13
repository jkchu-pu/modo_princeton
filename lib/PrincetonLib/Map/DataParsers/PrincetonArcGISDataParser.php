<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonArcGISDataParser extends ModoArcGISDataParser
{
    protected $geometryRetriever;
    protected $folderRetriever;

    public function init($args) {
        parent::init($args);
        // Princeton //
        // Create a extent and geometry retriever to be used later to fetch geometry from Princeton's geometry server
        $geometryRetriever = KGODataRetriever::factory('PrincetonArcGISGeometryDataRetriever',
            array(
                'baseURL' => $this->getArg('geometryURL'),
                'parentId' => $this->getArg('geometryParentId'),
            )
        );
        $geometryRetriever->setOption(KGOMapDataModel::OPTION_PLACEMARK_CLASS, 'KGOMapPlacemark');
        $geometryRetriever->setOption(KGOMapDataModel::OPTION_SUPPRESS_FIELDS, array());

        $folderRetriever = KGODataRetriever::factory('PrincetonArcGISFolderDataRetriever',
            array(
                'baseURL' => $this->getArg('geometryURL'),
                'parentId' => $this->getArg('geometryParentId'),
            )
        );

        $this->geometryRetriever = $geometryRetriever;
        $this->folderRetriever = $folderRetriever;
        // \Princeton //
    }

    private function getCurrentFolder() {
        $parentId = $this->getParentId();
        return kgo_array_val($this->folders, $parentId, null);
    }

    /// helper function for parseFeatures()
    private function getCandidatesForField($option, $fieldAliases) {
        $fieldCandidates = array();
        if (($field = $this->getOption($option))) {
            $fieldCandidates[] = $field;
            if (isset($fieldAliases[$field])) {
                // convert all criteria keys to uppercase since they are not returned consistently
                $fieldCandidates[] = $fieldAliases[$field];
            }
        }
        return $fieldCandidates;
    }

    /// helper function for parseFeatures()
    private static function detectPlacemarkAttributeField($placemark, $attributes, $attributeName, $fieldCandidates) {
        foreach ($fieldCandidates as $field) {
            if (isset($attributes[$field])) {
                $placemark->setAttribute($attributeName, $attributes[$field]);
                return $field;
            }
        }
    }

    private static function detectPlacemarkFunctionField($placemark, $attributes, $functionName, $fieldCandidates) {
        $callable = array($placemark, $functionName);
        if (is_callable($callable)) {
            foreach ($fieldCandidates as $field) {
                if (isset($attributes[$field])) {
                    call_user_func($callable, $attributes[$field]);
                    return $field;
                }
            }
        }
    }

    protected function parseFolder($data) {
        if (!isset($data['id'])) {
            return null;
        }

        // Princeton //
        // Fetch additional folder info from ArcGIS geometry server
        $folderData = $this->folderRetriever->getFolderData($response);
        $data = array_merge($folderData, $data);
        // \Princeton //

        $id = $data['id'];
        if (!isset($this->folders[$id])) {
            $this->folders[$id] = KGODataObject::factory('KGOMapCategory', $this->initArgs);
        }
        $folder = $this->folders[$id];
        $folder->setID($id);

        $folder->setTitle($data['name']);
        $folder->setDescription(kgo_array_val($data, 'description', null));

        $parentID = isset($data['parentLayerId']) ? $data['parentLayerId'] : null;
        if ($parentID != -1) {
            $folder->setParentID($parentID);
        }

        /// everything below this should only apply to the FOLDER_DETAIL return type

        if (isset($data['subLayers'])) {
            foreach ($data['subLayers'] as $layerData) {
                $folder = $this->parseFolder($layerData);
                $folder->setParentId($id);
            }
        }

        if (isset($data['extent'])) {
            $folder->setDisplayRule('extent', $data['extent']);
            if (isset($data['extent']['spatialReference']['wkid'])) {
                $projection = $data['extent']['spatialReference']['wkid'];
            } else if (isset($data['extent']['spatialReference']['wkt'])) {
                $projection = $data['extent']['spatialReference']['wkt'];
            }
            if ($projection) {
                $folder->setDisplayRule('projection', KGOMapProjection::getInstance($projection));
            }
        }

        if (isset($data['drawingInfo'])) {
            $folderStyles = array();
            $folderStyleCriteria = array();

            // TODO find out what can appear in labelingInfo and if we need it
            $labelingInfo = $data['drawingInfo']['labelingInfo'];

            if (isset($data['drawingInfo']['transparency'])) {
                $alpha = 1 - $data['drawingInfo']['transparency'];
            }

            // in ArcMap, if transparency is defined on a layer group,
            // transparency levels of individual layers in the group is
            // ignored.  we assume this rule also applies to ArcGIS server.
            $ignoreAlpha = isset($alpha) && $alpha != 1;

            // http://resources.arcgis.com/en/help/rest/apiref/renderer.html
            $renderer = $data['drawingInfo']['renderer'];
            $styleURLPrefix = $folder->getID();

            switch ($renderer['type']) {
                case 'simple':
                    $this->styles[$styleURLPrefix] = $this->parseMapSymbol($renderer['symbol'], $ignoreAlpha);
                    break;

                case 'uniqueValue':
                    if (isset($renderer['defaultSymbol'])) {
                        $this->styles[$styleURLPrefix] = $this->parseMapSymbol($renderer['defaultSymbol'], $ignoreAlpha);
                    }

                    // ArcGIS 10 allows up to 3 fields for symbology, but because
                    // the documentation doesn't show what the output looks like
                    // if there is more than one field, we will assume 1 field
                    $folder->setDisplayRule('styleField', $renderer['field1']);
                    $folder->setDisplayRule('styleCriteria', array());

                    $valueField = $renderer['field1'];
                    foreach ($renderer['uniqueValueInfos'] as $info) {
                        if (($symbol = $this->parseMapSymbol($info['symbol'], $ignoreAlpha))) {
                            $symbol->setTitle($info['label']);
                            // use underscores as delimiters to avoid characters that might show up as values
                            $styleId = "{$styleURLPrefix}_value_{$valueField}_{$info['value']}";
                            $symbol->setId($styleId);
                            $this->styles[$styleId] = $symbol;

                            $folderStyles[$info['label']] = $styleId;
                            $folderStyleCriteria[$info['label']] = $info['value'];
                        }
                    }
                    break;

                case 'classBreaks':
                    if (isset($renderer['defaultSymbol'])) {
                        $this->styles[$styleURLPrefix] = $this->parseMapSymbol($renderer['defaultSymbol'], $ignoreAlpha);
                    }

                    $folder->setDisplayRule('styleField', $renderer['field']);
                    $folder->setDisplayRule('styleCriteria', array());

                    $valueField = $renderer['field'];
                    $minValue = $renderer['minValue'];
                    foreach ($renderer['classBreakInfos'] as $info) {
                        if (($symbol = $this->parseMapSymbol($info['symbol'], $ignoreAlpha))) {
                            $symbol->setTitle($info['label']);

                            if (isset($info['classMinValue'])) {
                                $minValue = $info['classMinValue'];
                            }
                            $maxValue = $info['classMaxValue'];

                            // use underscores as delimiters to avoid characters that might show up as values
                            $styleId = "{$styleURLPrefix}_range_{$valueField}_{$minValue}_{$maxValue}";
                            $symbol->setId($styleId);
                            $this->styles[$styleId] = $symbol;

                            $folderStyles[$info['label']] = $styleId;
                            $folderStyleCriteria[$info['label']] = array($minValue, $maxValue);
                        }
                        // assumes class breaks are sorted in increasing order
                        $minValue = $info['classMaxValue'];
                    }
                    break;
            }

            if (isset($this->styles[$styleURLPrefix])) {
                $folder->setDisplayRule('defaultStyle', $styleURLPrefix);

                $this->styles[$styleURLPrefix]->setId($styleURLPrefix);
                if (isset($renderer['label']) && strlen($renderer['label'])) {
                    $this->styles[$styleURLPrefix]->setTitle($renderer['label']);
                } else {
                    $this->styles[$styleURLPrefix]->setTitle('Default');
                }
            }

            $folder->setDisplayRule('styles', $folderStyles);
            $folder->setDisplayRule('styleCriteria', $folderStyleCriteria);
        }

        if (isset($data['displayField'])) {
            $displayFields = $folder->getDisplayRule('displayFields', array());
            if (!in_array($data['displayField'], $displayFields)) {
                $displayFields[] = $data['displayField'];
            }
            $folder->setDisplayRule('displayFields', $displayFields);
        }

        if (isset($data['geometryType'])) {
            $folder->setDisplayRule('geometryType', $data['geometryType']);
        }

        if (isset($data['fields'])) {
            $feedHasGeometry = false; // check for unusable feeds
            $fieldAliases = $folder->getDisplayRule('fields', array());
            foreach ($data['fields'] as $fieldInfo) {
                $name = $fieldInfo['name'];
                if (strpos(strtoupper($name), 'SHAPE') === 0 || $fieldInfo['type'] == 'esriFieldTypeGeometry') {
                    $feedHasGeometry = true;
                    continue;
                }

                if ($fieldInfo['type'] == 'esriFieldTypeOID') {
                    $folder->setDisplayRule('idField', $name);
                }

                $fieldAliases[$name] = $fieldInfo['alias'];
            }
            $folder->setDisplayRule('fields', $fieldAliases);
            if (!$feedHasGeometry) {
                $this->folderErrors[$id][self::ERROR_NO_GEOMETRY] = true;
            }
        }

        return $folder;
    }

    protected function parseFeatures($data) {
        $parentId = $this->getParentId();
        $placemarks = array();

        $parentFolder = $this->getCurrentFolder();
        if ($parentFolder !== null) {
            $geometryType = kgo_array_val($data, 'geometryType', $parentFolder->getDisplayRule('geometryType'));
            $idField = $parentFolder->getDisplayRule('idField');
            $fieldAliases = $parentFolder->getDisplayRule('fields', array());
        } else {
            // TODO: find out if/how we get in this state
            // the comment here before said it happens from nearby search in the detail screen
            $geometryType = kgo_array_val($data, 'geometryType', 'esriGeometryPoint');
            $idField = null;
            $fieldAliases = kgo_array_val($data, 'fieldAliases', array());
        }

        $suppressFields = $this->getOption(KGOMapDataModel::OPTION_SUPPRESS_FIELDS);

        $titleFieldCandidates = $this->getCandidatesForField(KGOMapDataModel::OPTION_TITLE_FIELD, $fieldAliases);
        $titleFieldCandidates = array_merge($titleFieldCandidates, $this->getDisplayFields());
        if (isset($data['displayFieldName']) && !in_array($data['displayFieldName'], $titleFieldCandidates)) {
            $titleFieldCandidates[] = $data['displayFieldName'];
        }

        $subtitleFieldCandidates = $this->getCandidatesForField(KGOMapDataModel::OPTION_SUBTITLE_FIELD, $fieldAliases);
        $photoFieldCandidates = $this->getCandidatesForField(KGOMapDataModel::OPTION_PHOTO_FIELD, $fieldAliases);

        if (!isset($data['features'])) {
            kgo_log(LOG_WARNING, "error fetching data from " + $this->getOption('baseURL'), 'arcgis');
            return $placemarks;
        }

        foreach ($data['features'] as $featureInfo) {

            $placemark = KGODataObject::factory($this->getOption(KGOMapDataModel::OPTION_PLACEMARK_CLASS), $this->initArgs);
            if ($this->getOption(KGOItemsDataModel::SEARCH_KEYWORD_ARG)) {
                $placemark->setIsSearchResult(true);
            }
            $placemark->setParentID($parentId);
            $attributes = $featureInfo['attributes'];

            $usedFields = array($idField);

            if (($field = self::detectPlacemarkAttributeField($placemark, $attributes,
                    KGODataObject::TITLE_ATTRIBUTE, $titleFieldCandidates))) {
                $usedFields[] = $field;
            }

            if (($field = self::detectPlacemarkAttributeField($placemark, $attributes,
                    KGODataObject::DESCRIPTION_ATTRIBUTE, $subtitleFieldCandidates))) {
                $usedFields[] = $field;
            }

            if (($field = self::detectPlacemarkFunctionField($placemark, $attributes,
                    'setPhotoURL', $photoFieldCandidates))) {
                $usedFields[] = $field;
            }

            // everything else gets entered in a blob of fields
            $otherInfo = array();
            foreach ($fieldAliases as $name => $alias) {
                if (!in_array($name, $suppressFields)) {
                    $fieldReturned = null;
                    if (isset($attributes[$name])) {
                        $fieldReturned = $name;
                    } else if (isset($attributes[$alias])) {
                        $fieldReturned = $alias;
                    }
                    if (isset($fieldReturned) && !in_array($fieldReturned, $usedFields)) {
                        $otherInfo[$alias] = $attributes[$fieldReturned];
                    }
                }
            }
            $placemark->setPlaceInfo($otherInfo);

            if (isset($attributes[$idField])) {
                $placemark->setID(strval($attributes[$idField]));
            } else {
                $placemark->setID(strval($placemark->getTitle()));
            }

            // Princeton //
            $locationId = $featureInfo['attributes']['LOCATIONID'];

            if (!isset($featureInfo['geometry']) && isset($locationId)) {
                // Try to get geometry from other princeton service

                $targetParentId = null;
                $geomData = $this->geometryRetriever->getGeometry($locationId, $targetParentId, $response);
                $featureInfo['geometry'] = $geomData;

                if (!is_null($targetParentId)) {
                    $geometryType = $this->folderRetriever->getGeometryType($targetParentId, $response);
                }
            }
            // \Princeton //

            if ($this->getOption('returnGeometry') && isset($featureInfo['geometry'])) {
                $geometry = null; // clean up values from previous loop
                $geometryJSON = $featureInfo['geometry'];
                switch ($geometryType) {
                    case 'esriGeometryPoint':
                        if (is_numeric($geometryJSON['x']) && is_numeric($geometryJSON['y'])) {
                            $geometry = new KGOMapPoint($geometryJSON['y'], $geometryJSON['x']);
                        }
                        break;
                    case 'esriGeometryPolyline':
                        if (isset($geometryJSON['paths'])) {
                            $geometry = new KGOMapPolyline();
                            foreach ($geometryJSON['paths'] as $currentPath) {
                                $this->parseLine($currentPath, $geometry);
                            }
                        }
                        break;
                    case 'esriGeometryPolygon':
                        if (count($geometryJSON['rings'])) {
                            $geometry = new KGOMapPolygon();
                            $geometry->outerRing = $this->parseLine($geometryJSON['rings'][0], new KGOMapPolyline());
                            for ($i = 1; $i < count($geometryJSON['rings']) - 1; $i++) {
                                $currentRing = $geometryJSON['rings'][$i];
                                $geometry->innerRings[] = $this->parseLine($currentRing, new KGOMapPolyline());
                            }
                        }
                        break;
                }
                //kgo_debug($geometry, true, true);
                // Princeton //
                if (!isset($geometry)) {
                    kgo_log(LOG_NOTICE, "ArcGIS folder {$this->baseURL}/$parentId has bad placemark geometries");
                    continue;
                }
                // \Princeton //
                $placemark->setGeometry($this->projectGeometry($geometry));

                $placemark->setAttribute(KGOMapPlacemark::STYLEID_ATTRIBUTE, $this->styleForPlacemark($parentFolder, $placemark));
            }
            if(is_object($placemark->getGeometry())) {
                $placemarks[$placemark->getId()] = $placemark;
            }
        }

        // if ($this->getOption('returnGeometry')) {
        //     kgo_debug($placemarks, true, true);
        // }

        return $placemarks;
    }
}
