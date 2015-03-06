<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonAthleticsEventTitleDataProcessor extends KGODataProcessor
{
    protected function processValue($value, $object = null) {
        $type = $object->getAttribute('princetonathletics:type');
        $score = $object->getAttribute('princetonathletics:score');
        $school = $object->getAttribute('princetonathletics:school');
        $opponent = $object->getAttribute('princetonathletics:opponent');

        $title = '';
        switch ($type) {
            case 'H':
                $title = sprintf("%s @ %s", $opponent, $school);
                break;
            case 'V':
                $title = sprintf("%s @ %s", $school, $opponent);
                break;
            default:
                $title = $opponent;
                break;
        }

        if ($score && strlen($score)) {
            $title .= sprintf(" (%s)", $score);
        }

        return $title;
    }

    protected function canProcessValue($value, $object = null){
        return $object instanceof KGOCalendarEvent;
    }
}
