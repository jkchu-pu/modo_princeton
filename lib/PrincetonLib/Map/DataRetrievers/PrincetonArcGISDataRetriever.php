<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonArcGISDataRetriever extends ModoArcGISDataRetriever
{
    protected static $defaultParserClass = 'PrincetonArcGISDataParser';

    public function search($searchTerms, &$response = null) {
        $this->setOption(KGOItemsDataModel::SEARCH_KEYWORD_ARG, $searchTerms); // setup for parameters()
        $this->getFolders();
        $results = array();
        foreach ($this->folders as $id => $folder) {
            $this->getFolder($id);
            $this->setParentId($id);
            $this->setReturnType(KGOMapDataRetriever::RETURN_TYPE_PLACEMARKS);
            $currentResult = $this->getData($response);
            $results = $results + array_values($currentResult['placemarks']);
        }
        $this->allFoldersParsed = true;
        $searchTerms = strtolower($searchTerms);
        foreach ($results as $i => &$result) {
            if (strpos(strtolower($result->getTitle()), $searchTerms) === false) {
                unset($results[$i]);
            }
        }
        $results = array_merge($results);
        return $results;
    }

    public function searchWithin(KGOMapPoint $center, $radius, &$response=null) {
        $this->getFolders();

        $filters = array(
            KGOMapDataModel::SEARCH_NEARBY_ARG => array(
                'center' => $center,
                'radius' => $radius,
                ),
            );

        $optionOverrides = array(
            'returnGeometry' => true,
            'nearbyOptions' => $filters[KGOMapDataModel::SEARCH_NEARBY_ARG], // get filtered results from server within rectangular envelope
            );

        $results = array();
        foreach ($this->folders as $id => $folder) {
            $this->getFolder($id);
            $this->setParentId($id);
            $this->setReturnType(KGOMapDataRetriever::RETURN_TYPE_PLACEMARKS);

            $this->temporaryOverrideOptions($optionOverrides);
            $currentResult = $this->getData($response);
            $this->restoreOriginalOptions();

            $results = $results + array_values($currentResult['placemarks']);
        }
        $this->allFoldersParsed = true;

        // filter using actual distance
        $results = array_values(
            array_filter($results, function($item) use ($filters) {
                return $item->filterItem($filters);
            })
        );

        usort($results, array('KGOMapPlacemark', 'compareDistances'));
        //kgo_debug($results, true, true);
        return $results;
    }
}
