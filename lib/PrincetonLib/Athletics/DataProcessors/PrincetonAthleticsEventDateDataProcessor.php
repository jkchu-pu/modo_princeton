<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonAthleticsEventDateDataProcessor extends KGODataProcessor
{
    protected function processValue($value, $object = null) {
        $dateStr = $object->getAttribute('princetonathletics:date') . ' ' . $object->getAttribute('princetonathletics:time');

        $date = DateTime::createFromFormat('m/d/Y g:i A', $dateStr);
        return $date;
    }

    protected function canProcessValue($value, $object = null){
        return $object instanceof KGOCalendarEvent;
    }
}
