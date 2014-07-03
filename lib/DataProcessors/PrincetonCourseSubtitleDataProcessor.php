<?php

class PrincetonCourseSubtitleDataProcessor extends KGODataProcessor{

    protected function canProcessValue($value, $object = null){
        return true;
    }

    protected function processValue($value, $object = null){
        $term = $object->getAttribute('modocourse:term')->getAttribute('kgo:title');
        $courseNumber = $object->getAttribute('modocourse:coursenumber');
        $subtitle = $courseNumber . '<br><br>' . $term;
        return $subtitle;
    }

}