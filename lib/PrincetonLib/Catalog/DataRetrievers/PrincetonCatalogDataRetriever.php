<?php


class PrincetonCatalogDataRetriever extends KGOURLDataRetriever implements ModoCatalogDataRetriever {

    protected $areasParser;
    protected $areasURL;
    protected $coursesParser;
    protected $coursesURL;
    protected $serverURL;
    const MINIMUM_SEARCH_LENGTH = 3;

    // protected function setMode($mode) {
    //     $parserVar = $mode . 'Parser';
    //     //$urlVar = $mode . 'URL';
    //     //$this->setParser($this->$parserVar);
    //     //$this->setBaseURL($this->$urlVar);
    // }

    public function searchCourses($searchTerms, $options = array()) {
        kgo_debug('', true, true);
        if (strlen($searchTerms)<self::MINIMUM_SEARCH_LENGTH) {
            return array();
        }
        $items = array();

        if(isset($options['area'])) {
            if (!$area = $this->getCatalogArea($options['area'], $options)) {
                return $items;
            }
            $options['area'] = $area->getID();
        } else {
            $options['search'] = trim($searchTerms);
        }

        $courses = $this->getCourses($options);

        $filters['search'] = trim($searchTerms);
        foreach($courses as $course) {
            if($course->filterItem($filters)) {
                $items[] = $course;
            }
        }
        return $items;
    }

    public function getCourses($options = array()) {
        $areaId = $this->getOption('areaId');
        $term = $this->getOption('selectedTerm');
        $term = $this->getOption('term')->getAttribute('kgo:id');
        //$this->setMode('courses');
        if (isset($areaId)) {
            //$this->setOption('subject', $options['area']);
            $this->addParameter('subject', $areaId);
        }

        if (isset($term)) {
            //$this->setOption('selectedTerm', $options['selectedTerm']);
            $this->addParameter('term', $term);
        }

        if (isset($options['search'])) {
            kgo_debug('', true, true);
            $this->setOption('search', $options['search']);
            $this->addParameter('search', $options['search']);
        }

        $data = $this->getData();
        $parser = PrincetonCoursesDataParser::factory("PrincetonCoursesDataParser", $this->initArgs);
        $parser->setOption('selectedTerm',$this->getOption('term'));
        $courses = $parser->parseData($data);
        return $courses;
    }

    public function getCatalogArea($area, $options = array()) {
        kgo_debug('', true, true);

        $areas = $this->getCatalogAreas($options);
        foreach ($areas as $areaObj) {
            if ($areaObj->getID() == $area) {
                return $areaObj;
            }
        }

        return null;
    }

    public function getCatalogAreas($options = array()) {
        if($term = $this->getOption('term')->getAttribute('kgo:id')){
            $options['selectedTerm'] = $term;
        }
        //$this->setMode('areas');
        if (isset($options['selectedTerm'])) {
            $this->addParameter('term', $options['selectedTerm']);
            $this->setOption('selectedTerm', $options['selectedTerm']);
        }

        if (isset($options['parent'])) {
            $this->addParameter('subject', $options['parent']);
        }else {
            $this->addParameter('subject', "list");
        }

        $data =  $this->getData();

        $parser = PrincetonCourseAreasDataParser::factory("PrincetonCourseAreasDataParser", $this->initArgs);
        $areas = $parser->parseData($data);
        return $areas;
    }

    public function getCourseByCommonId($courseID, $options) {
        kgo_debug('', true, true);
        $courses = $this->getCourses($options);
        foreach($courses as $course) {
            if($course->getCommonId() == $courseID) {
                return $course;
            }
        }
        return false;
    }

    public function getCourseById($courseNumber) {
        kgo_debug('', true, true);
        if ($course = $this->getCourses(array('courseNumber' => $courseNumber))) {
            return current($course);
        }
        return false;
    }

    private function setStandardFilters() {
        $this->coursesURL = $this->baseURL;
        $this->areasURL = $this->baseURL;
    }

    protected function init($args) {
        parent::init($args);
        $this->areasParser = KGODataParser::factory('PrincetonCourseAreasDataParser', $this->initArgs);
        $this->coursesParser = KGODataParser::factory('PrincetonCoursesDataParser', $this->initArgs);

        $this->setStandardFilters();
    }

}