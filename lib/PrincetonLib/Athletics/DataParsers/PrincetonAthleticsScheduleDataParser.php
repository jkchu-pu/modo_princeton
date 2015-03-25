<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonAthleticsScheduleDataParser extends KGOSimpleXMLDataParser
{
    protected $multiValueElements = array(
        'event',
    );

    public function parseData($data) {
        $this->setParsemap(static::scheduleParsemap());
        return parent::parseData($data);
    }

    public function parseResponse(KGODataResponse $response) {
        $events = parent::parseResponse($response);

        $dateProcessor = KGODataProcessor::factory('PrincetonAthleticsEventDateDataProcessor');

        foreach ($events as &$event) {
            $eventDate = $dateProcessor->process(null, $event);
            $event->setAttribute(KGOCalendarEvent::START_ATTRIBUTE, $eventDate);
            $event->setAttribute(KGOCalendarEvent::END_ATTRIBUTE, $eventDate);
            $event->setAttribute(KGOCalendarEvent::OCCURRENCE_DATE_ATTRIBUTE, $eventDate);
        }
        return $events;
    }

    public static function scheduleParsemap() {
        return array(
            'key'              => 'event',
            'array'            => true,
            'class'            => 'KGOCalendarEvent',
            'attributes'       => array(
                KGODataObject::ID_ATTRIBUTE => '@attributes.id',
                KGOCalendarEvent::LOCATION_ATTRIBUTE => 'location',
                'princetonathletics:type' => 'home_visitor',
                'princetonathletics:score' => 'outcome_score',
                'princetonathletics:school' => 'school',
                'princetonathletics:opponent' => 'opponent',
                'princetonathletics:date' => 'event_date',
                'princetonathletics:time' => 'time',
            ),
            'processors' => array(
                KGODataObject::TITLE_ATTRIBUTE => array(
                    array(
                        'class' => 'PrincetonAthleticsEventTitleDataProcessor',
                    ),
                ),
            ),
        );
    }
}
