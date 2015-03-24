<?php


class PrincetonCatalogFederatedSearchDataRetriever extends PrincetonCatalogDataRetriever implements KGOItemByArgsDataRetriever, KGOSearchDataRetriever
{
    public function getItem(array $args, &$response=null) {
        return array();
    }

    public function getItems(&$response=null) {
        return array();
    }

    public function search($searchTerms, &$response = null) {
        $termFeed = KGOSite::currentSite()->getFeed('terms');
        $this->setOption('term', $termFeed->getCurrentTerm());
        $courses = $this->searchCourses($searchTerms, array(), $response);

        foreach ($courses as &$course) {
            //TODO: This is an internal API...should find a supported way to do this
            $course->internalModelUpdateRenamedURLParameters(array(
                'id' => 'course',
            ));
        }

        return $courses;
    }
}
