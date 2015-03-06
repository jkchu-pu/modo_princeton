<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonCoursesDataParser extends KGOSimpleXMLDataParser {

    protected $area;
    protected $areaCode;

    protected $multiValueElements = array('subject', 'course', 'instructor', 'class', 'meeting', 'day');

    public function parseData($data) {
        $data = parent::parseData($data);
        $items = array();
        if(isset($data['term']['subjects']['subject']) && $response = $data['term']['subjects']['subject']) {
            // some of the areas will have no relation courses
            // keep the same data structure by adding an array layer
            if(isset($response["code"])) {
                $response = array($response);
            }
            $this->setTotalItems(count($response));
            foreach($response as $item) {
                $items = array_merge($items, $this->parseSubject($item));
            }
        }
        return $items;
    }

    public function parseSubject($item){
        $items = array();
        $this->area = ModoAcademicArea::factory("ModoAcademicArea", $this->initArgs);
        $this->area->setTitle($item['name']);
        $this->area->setId($item['code']);
        $this->areaCode = $item['code'];
        $this->area->setAttributes($item);
        if(isset($item['courses']['course']) && $courses = $item['courses']['course']) {
            // some of the $classes have only one class
            if(isset($courses["catalog_number"])) {
                $items[] = $this->parseCourse($courses);
            }else {
                foreach($courses as $course) {
                    $items[] = $this->parseCourse($course);
                }
            }
        }
        return $items;
    }

    protected function getCrosslistings($item){
        $crosslistings = array();
        if(isset($item['crosslistings']['crosslisting']) && $crosslisting = $item['crosslistings']['crosslisting']){
            # Single crosslisting
            if(isset($crosslisting['subject'])){
                $crosslistings[] = $crosslisting['subject'][0] . ' ' . $crosslisting['catalog_number'];
            }else{
                foreach ($crosslisting as $listing) {
                    $crosslistings[] = $listing['subject'][0] . ' ' . $listing['catalog_number'];
                }
            }
        }
        return $crosslistings;
    }

    protected function getCourseNumber($item){
        $courseNumber = $this->areaCode . ' ' .$item['catalog_number'];
        $crosslistings = $this->getCrosslistings($item);
        array_unshift($crosslistings, $courseNumber);
        return implode('/', $crosslistings);
    }

    public function parseCourse($item) {
        $course = PrincetonCourseObject::factory("PrincetonCourseObject", $this->initArgs);
        $term = $this->getOption('selectedTerm');
        $course->setTerm($term);
        $course->setID($item['course_id']);
        # Set coursenumber to include crosslisted courses.
        $courseNumber = $this->getCourseNumber($item);
        $course->setCourseNumber($courseNumber);
        //$course->setArea($this->area);
        // $area = ModoAcademicArea::factory("ModoAcademicArea", $this->initArgs);
        // $area->setAttribute('modoarea:areas',$this->area);
        // $area->setItemURLParameter($this->area->getAttribute('kgo:id'));
        // $area->setFeedURLParameter($this->area->getAttribute('kgo:id'));
        //kgo_debug($this->area, true, true);
        $course->setArea($this->area);
        $subject = $this->getOption('subject');
        $course->setTitle($item['title']);
        $course->setDescription($item['detail']['description']);
        $course->setTrack($item['detail']['track']);
        if(isset($item['requirements'])){
            $course->setAttribute('requirements', $item['requirements']);
        }
        if(isset($item['classes']['class']) && $classes = $item['classes']['class']) {
            // some of the $classes have only one class
            if(isset($classes["class_number"])) {
                $course->addClass($this->parseClass($classes, $subject, $this->area));
            }else {
                foreach($classes as $section) {
                    $course->addClass($this->parseClass($section, $subject, $this->area));
                }
            }
        }

        if(isset($item['instructors']['instructor']) && $instructors = $item['instructors']['instructor']) {
            // some of the $instructors have only one instructor
            if(isset($instructors["emplid"])) {
                $instructor = KGOPerson::factory("KGOPerson", $this->initArgs);
                $instructor->setID($instructors['emplid']);
                $instructor->setAttribute('kgoperson:fullname',$instructors['first_name'] . ' ' . $instructors['last_name']);
                $instructor->setAttribute('kgoperson:firstname',$instructors['first_name']);
                $instructor->setAttribute('kgoperson:lastname',$instructors['last_name']);
                $course->addInstructor($instructor);
            }
            else {
                foreach($instructors as $inst) {
                    $instructor = KGOPerson::factory("KGOPerson", $this->initArgs);
                    $instructor->setID($inst['emplid']);
                    $instructor->setAttribute('kgoperson:fullname',$inst['first_name'] . ' ' . $inst['last_name']);
                    $instructor->setAttribute('kgoperson:firstname',$inst['first_name']);
                    $instructor->setAttribute('kgoperson:lastname',$inst['last_name']);
                    $course->addInstructor($instructor);
                }
            }
        }
        return $course;
    }

    public function parseClass($item, $areaCode, $area) {
        $section = PrincetonCourseCatalogClass::factory("PrincetonCourseCatalogClass", $this->initArgs);
        $section->setID($item['section']);
        $section->setTitle($item['section']);
        //$section->setArea($areaCode);
        $section->setClassNumber($item['class_number']);
        $section->setSectionNumber($item['section']);
        $section->setAttribute('modoclass:status', $item['status']);
        $section->setAttribute('modoclass:type_name', $item['type_name']);
        $section->setAttribute('modoclass:enrollment', $item['enrollment']);
        $section->setAttribute('modoclass:capacity', $item['capacity']);
        if(isset($item['schedule']['meetings']['meeting']) && $meetings = $item['schedule']['meetings']['meeting']) {
            $startDate = $item['schedule']['start_date'];
            $endDate = $item['schedule']['end_date'];
            if(isset($meetings["meeting_number"])) {
                $section->addSchedule($this->parseSchedule($meetings, $startDate, $endDate));
            }else {
                foreach($meetings as $meeting) {
                    $section->addSchedule($this->parseSchedule($meeting, $startDate, $endDate));
                }
            }
        }

        return $section;

    }

    public function parseSchedule($item, $startDate, $endDate) {
        $schedule = PrincetonCourseScheduleObject::factory("PrincetonCourseScheduleObject", $this->initArgs);
        $days = '';
        if(isset($item['days']['day'])) {
            if(is_array($item['days']['day'])) {
                $days = implode("", $item['days']['day']);
            }else {
                $days = $item['days']['day'];
            }
        //// This seems to cause problems sometimes, not sure why it's here. KP
        //}elseif($item['days']['day']) {
        //    $days = $item['days']['day'];
        }
        $schedule->setDays($days);
        $schedule->setTitle($item['meeting_number']);
        $schedule->setStartTime(date_create($item['start_time']));
        $schedule->setEndTime(date_create($item['end_time']));
        $schedule->setAttribute('princeton:starttime',$item['start_time']);
        $schedule->setAttribute('princeton:endtime',$item['end_time']);
        if(isset($item['building']['name'])) {
            $schedule->setBuilding($item['building']['name']);
        }
        if(isset($item['building']['location_code'])) {
            $schedule->setBuildingCode($item['building']['location_code']);
        }
        if(isset($item['room'])){
            $schedule->setRoom($item['room']);
        }

        return $schedule;
    }
}

class PrincetonCourseObject extends ModoCatalogCourse {
    protected $commonID_field = 'courseNumber';
    protected $instructors = array();
    protected $track;

    public function addInstructor(KGOPerson $instructor) {
        $this->instructors[] = $instructor;
    }

    public function getInstructors() {
        return $this->instructors;
    }


    /**
     * Get track.
     *
     * @return track.
     */
    public function getTrack() {
        return $this->track;
    }

    /**
     * Set track.
     *
     * @param track the value to set.
     */
    public function setTrack($track) {
        $this->track = $track;
    }

    /* add filter-by-instructor */
    public function filterItem($filters) {
        if ( ! parent::filterItem($filters) ) {
            foreach ($filters as $filter=>$value) {
                switch ($filter) {
                    case 'search':
                        foreach ( $this->getInstructors() as $instructor ) {
                            // getEmail() nor any instructor email attributes seem to exist?
                            if ( (stripos($instructor->getAttribute('kgoperson:fullname'), $value)!==FALSE) /*||
                                (stripos($instructor->getEmail(), $value)!==FALSE)*/ ) {
                                    return true;
                            }
                        }
                        return false;
                        break;
                }
            }
        }

        return true;
    }
}

class PrincetonCourseCatalogClass extends ModoCatalogClass {
    protected $status;
    protected $typeName;

    /**
     * Get status.
     *
     * @return status.
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param status the value to set.
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Get typeName.
     *
     * @return typeName.
     */
    public function getTypeName() {
        return $this->typeName;
    }

    /**
     * Set typeName.
     *
     * @param typeName the value to set.
     */
    public function setTypeName($typeName) {
        $this->typeName = $typeName;
    }

    public function getSchedule() {
        $sched = parent::getSchedule();
        return (($this->getStatus() === 'Cancelled' ? 'Cancelled' . ($sched ? ' - ' : '') : '') . $sched);
    }
}

class PrincetonCourseScheduleObject extends ModoAcademicClassSchedule {
    protected $buildingCode;

    /**
     * Get buildingCode.
     *
     * @return buildingCode.
     */
    public function getBuildingCode() {
        return $this->buildingCode;
    }

    /**
     * Set buildingCode.
     *
     * @param buildingCode the value to set.
     */
    public function setBuildingCode($buildingCode) {
        $this->buildingCode = $buildingCode;
    }
}