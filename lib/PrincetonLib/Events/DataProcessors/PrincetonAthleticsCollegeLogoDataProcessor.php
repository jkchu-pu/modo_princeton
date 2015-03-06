<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonAthleticsCollegeLogoDataProcessor extends KGODataProcessor
{
    protected function processValue($value, $object = null) {
        $location = reset(explode(' ', current($object->getAttribute('ics:CATEGORIES'))));

        $locationFile = 'crests/'.strtolower(preg_replace(array('/\s/', '/[^a-zA-Z0-9_]/'), array('_', ''), $location)) . '.png';

        $imageURL = KGOURL::createForImageFile($locationFile);

        if (!$imageURL->getFilePath()) {
            $imageURL = KGOURL::createForImageFile('schools/_no_logo_available.png');
        }

        return $imageURL;
    }

    protected function canProcessValue($value, $object = null){
        return $object instanceof KGOCalendarEvent;
    }
}
