<?php


class PrincetonTermsDataRetriever extends KGOURLDataRetriever implements ModoAcademicTermsDataRetriever {

    protected $DEFAULT_PARSER_CLASS = 'PrincetonTermsDataParser';

    protected function cacheKey() {
        return '';
    }

    public function getAvailableTerms() {
        $this->addFilter('term', 'list');
        //$this->setOption('action', 'getAvailableTerms');
        $data = $this->getData();

        $parser = PrincetonTermsDataParser::factory('PrincetonTermsDataParser', $this->initArgs);
        $parser->setOption('action', 'getAvailableTerms');
        $availableTerms = $parser->parseData($data);
        return $availableTerms;
    }

    public function getTerm($termCode) {
        // if ($termCode == ModoAcademicCoursesDataModel::CURRENT_TERM) {
        //     return $this->getCurrentTerm();
        // }

        $terms = $this->getAvailableTerms();
        foreach ($terms as $term) {
            if ($term->getID() == $termCode) {
                return $term;
            }
        }

        return null;
    }

    public function getCurrentTerm(){
        $this->clearInternalCache();
        $data = $this->getData();
        $this->setOption('action', 'getCurrentTerm');
        $parser = new PrincetonTermsDataParser();
        $parser->setOption('action', 'getCurrentTerm');
        $currentTerm = $parser->parseData($data);
        return $currentTerm;
    }

    public function clearInternalCache(){
        parent::clearInternalCache();
        $this->removeAllFilters();
    }

}


class PrincetonTermsDataParser extends KGOSimpleXMLDataParser {

    public function parseData($data) {
        $data = parent::parseData($data);
        $action = $this->getOption('action');

        if (isset($data['term']) && $data['term']) {
            switch ($action) {
                case 'getAvailableTerms':
                    $items = array();
                    foreach ($data['term'] as $value) {
                        $term = ModoAcademicTerm::factory('ModoAcademicTerm', $this->initArgs);
                        $term->setID($value['code']);
                        $term->setTitle($value['cal_name']);
                        $term->setStartDate(new DateTime($value['start_date']));
                        $term->setEndDate(new DateTime($value['end_date']));
                        $term->setAttributes($value);
                        $items[] = $term;
                    }
                    $this->setTotalItems(count($items));
                    return $items;
                    break;
                case 'getCurrentTerm':
                    $term = ModoAcademicTerm::factory('ModoAcademicTerm', $this->initArgs);
                    $term->setID($data['term']['code']);
                    $term->setTitle($data['term']['cal_name']);
                    $term->setStartDate(new DateTime($data['term']['start_date']));
                    $term->setEndDate(new DateTime($data['term']['end_date']));
                    $term->setAttributes($data['term']);
                    return $term;
                    break;
            }
        }
    }
}