<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonOfficeMapNameDataProcessor extends KGODataProcessor
{
    private $_officeAPIUrl = "https://atsweb.princeton.edu/mobile/map/search/json.php?short=yes&office=%s";

    protected function processValue($value, $object = null) {
        $retriever = KGODataRetriever::factory('KGOURLDataRetriever', array(
            'useCurl' => true,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'baseURL' => sprintf($this->_officeAPIUrl, urlencode($value)),
            'dataParser' => 'KGOJSONDataParser',
        ));
        $data = $retriever->getData($response);

        return $data['name'];
    }

    protected function canProcessValue($value, $object = null) {
        return true;
    }
}
