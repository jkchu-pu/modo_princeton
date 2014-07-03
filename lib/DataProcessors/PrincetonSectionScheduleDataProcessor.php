<?php

class PrincetonSectionScheduleDataProcessor extends KGODataProcessor{

    protected function canProcessValue($value, $object = null){
        return true;
    }

    protected function processValue($value, $object = null){
        $schedObj = $object->getAttribute('modoclass:schedules');
        $schedObj = $schedObj[0];
        $days = $schedObj->getAttribute('modoschedule:days');
        $startTime = $schedObj->getAttribute('princeton:starttime');
        $endTime = $schedObj->getAttribute('princeton:endtime');
        $building = $schedObj->getAttribute('modoschedule:building');
        $room = $schedObj->getAttribute('modoschedule:room');

        if($days && $startTime && $endTime){
            $schedule = 'Schedule: ' . $days . ' ' . $startTime . '-' . $endTime;
            if($building){
                $schedule = $schedule . ' - ' .$building . ' ' . $room;
            }
            return $schedule;
        }
        else{
            return NULL;
        }
    }

}