<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonArcGISFolderDataRetriever extends KGOURLDataRetriever
{
    protected static $defaultParserClass = 'KGOJSONDataParser';

    protected $parentId;

    protected function init($args) {
        parent::init($args);
        $this->parentId = $this->getArg('parentId');

        if (is_array($this->parentId)) {
            $this->parentId = reset($this->parentId);
        }
    }

    public function getFolderData(&$response) {
        $this->setBaseURL($this->getArg('baseURL').'/'.$this->parentId.'?f=pjson');

        $data = $this->getData($response);
        return $data;
    }

    public function getGeometryType($layerId, &$response) {
        $this->clearInternalCache();

        $this->setBaseURL($this->getArg('baseURL').'/'.$layerId.'?f=pjson');

        $data = $this->getData($response);
        return $data['geometryType'];
    }
}
