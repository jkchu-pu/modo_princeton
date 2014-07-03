<?php

class PrincetonCourseInstructorsDataProcessor extends KGODataProcessor{

    protected function canProcessValue($value, $object = null){
        return true;
    }

    protected function processValue($value, $object = null){
        $instructors = $object->getInstructors();
        return $object->getInstructors();
    }

}