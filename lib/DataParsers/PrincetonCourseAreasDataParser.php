<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonCourseAreasDataParser extends KGOSimpleXMLDataParser {

    protected $multiValueElements = array('subject', 'course', 'instructor', 'class', 'meeting', 'day');

    public function parseData($data) {
        $data = parent::parseData($data);

        $areas = array();
        if(isset($data["term"]["subjects"]["subject"]) && $response = $data["term"]["subjects"]["subject"]) {

            foreach($response as $item) {
                $courseArea = ModoAcademicArea::factory('ModoAcademicArea', $this->initArgs);
                $courseArea->setTitle($item['name']);
                $courseArea->setId($item['code']);
                $courseArea->setAttributes($item);
                //kgo_debug($this->getResponseRetriever(), true, true);
                //$courseArea->setDataRetriever($this->getResponseRetriever());
                $areas[] = $courseArea;
            }
        }
        return $areas;
    }

}

