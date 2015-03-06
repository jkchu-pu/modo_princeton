<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonTransitModule extends KGOModule implements KGOUITabbedContentInterface, KGOUIArrayInterface
{
    public function getTransitFeed() {
        foreach ($this->getAllFeeds() as $feed) {
            if ($feed instanceOf ModoTransitDataModel) {
                return $feed;
            }
        }
        return null;
    }

    public function getNewsFeeds() {
        $feeds = array();
        foreach ($this->getAllFeeds() as $feed) {
            if ($feed instanceOf KGONewsDataModel) {
                $feeds[] = $feed;
            }
        }
        return $feeds;
    }

    public function getNewsFeed(&$index=null) {
        $feed = $this->getFeed($index);
        if ($feed instanceOf KGONewsDataModel) {
            return $feed;
        }
        return null;
    }

    protected function initializeForPageConfigObjects_index(KGOUIPage $page, $objects) {
        if (!($feed = $this->getTransitFeed())) {
            $this->setPageError($page, 'princeton_transit.errors.feedNotConfigured');
            return;
        }

        if (isset($objects['tabs']) && $tabs = $objects['tabs']->createUIObject()) {
            $transitFeed = $this->getTransitFeed();
            $newsFeeds = $this->getNewsFeeds();

            if (isset($transitFeed) && ($agencies = $transitFeed->getAgencies())) {
                $key = count($agencies) > 1 ? 'feeds' : 'feed';

                if (!kgo_is_pagetype_text() && $this->getConfig('module.options.mergedRoutesTab', false)) {
                    if ($runningObjdefs = $this->getConfig("page-index.objdefs.content.routesTab.$key")) {
                        if ($newsFeeds) {
                            $tabs->setRegionField('routes', 'title', kgo_localize('princeton_transit.tabs.routes'));
                            $this->addObjectDefinitionsToObjectRegion($tabs, 'routes', $runningObjdefs, $this);

                        } else {
                            $page->setRegionContents('content', array()); // remove tabs
                            $this->addObjectDefinitionsToPage($page, $runningObjdefs, $this);
                        }
                    }

                } else {
                    if ($runningObjdefs = $this->getConfig("page-index.objdefs.content.runningTab.$key")) {
                        $tabs->setRegionField('running', 'title', kgo_localize('princeton_transit.tabs.running'));
                        $this->addObjectDefinitionsToObjectRegion($tabs, 'running', $runningObjdefs, $this);
                    }
                    if ($offlineObjdefs = $this->getConfig("page-index.objdefs.content.offlineTab.$key")) {
                        $tabs->setRegionField('offline', 'title', kgo_localize('princeton_transit.tabs.offline'));
                        $this->addObjectDefinitionsToObjectRegion($tabs, 'offline', $offlineObjdefs, $this);
                    }
                }
            }

            // Info Tab
            if ($objdefs = $this->getConfig("page-index.objdefs.content.infoTab")) {
                $tabs->setRegionField('info', 'title', kgo_localize('princeton_transit.tabs.info'));
                $this->addObjectDefinitionsToObjectRegion($tabs, 'info', $objdefs, $this);
            }

            // Map Tab
            if ($objdefs = $this->getConfig("page-index.objdefs.content.mapTab")) {
                $tabs->setRegionField('map', 'title', kgo_localize('princeton_transit.tabs.map'));
                $this->addObjectDefinitionsToObjectRegion($tabs, 'map', $objdefs, $this);
            }

            if ($newsFeeds) {
                $key = count($newsFeeds) > 1 ? 'feeds' : 'feed';

                if ($newsObjdefs = $this->getConfig("page-index.objdefs.content.newsTab.$key")) {
                    $tabs->setRegionField('news', 'title', kgo_localize('princeton_transit.tabs.news'));
                    $this->addObjectDefinitionsToObjectRegion($tabs, 'news', $newsObjdefs, $this);
                }
            }
        }
    }

    protected function initializeForPageConfigObjects_directions(KGOUIPage $page, $objects) {
        if (!($feed = $this->getTransitFeed())) {
            $this->setPageError($page, 'princeton_transit.errors.feedNotConfigured');
            return;
        }

        if (!($route = $feed->getRoute())) {
            $this->setPageError($page, 'princeton_transit.errors.noSuchRoute', $feed->getCurrentRouteId());
            return;
        }

        if (($directions = $route->getDirections()) && (count($directions) === 1)) {
            // Only 1 direction, redirect to route page
            $direction = reset($directions);
            kgo_redirect(KGOURL::createForModuleWebPage($this->getId(), 'route', $direction->getURLArgs()));
        }
    }

    protected function initializeForPageConfigObjects_route(KGOUIPage $page, $objects) {
        if (!($feed = $this->getTransitFeed())) {
            $this->setPageError($page, 'princeton_transit.errors.feedNotConfigured');
            return;
        }

        if (!($route = $feed->getRoute())) {
            $this->setPageError($page, 'princeton_transit.errors.noSuchRoute', $feed->getCurrentRouteId());
            return;
        }

        if (!($direction = $route->getDirection())) {
            $this->setPageError($page, 'princeton_transit.errors.noSuchDirection',
                                $route->getTitle(), $feed->getCurrentDirectionId());
            return;
        }
    }

    protected function initializeForPageConfigObjects_stop(KGOUIPage $page, $objects) {
        if (!($feed = $this->getTransitFeed())) {
            $this->setPageError($page, 'princeton_transit.errors.feedNotConfigured');
            return;
        }

        if (!($route = $feed->getRouteDirectionStop())) {
            $this->setPageError($page, 'princeton_transit.errors.noSuchStop', $feed->getCurrentStopId());
            return;
        }
    }

    protected function initializeForPageConfigObjects_announcement(KGOUIPage $page, $objects) {
        if (!($feed = $this->getNewsFeed())) {
            $this->setPageError($page, 'princeton_transit.errors.feedNotConfigured');
            return;
        }
    }

    protected function initializeForPageConfigObjects_info(KGOUIPage $page, $objects) {
    }

    protected function initializeForShellCommand_fetcharrivaltimes() {
        $errors = 0;

        try {
            if ($feed = $this->getTransitFeed()) {
                $feedId = $feed->getId();
                $feedTitle = $feed->getTitle();

                if ($this->getConfig('module.options.prefetchArrivalTimes', false)) {
                    $this->shellOutput("Fetching $feedId $feedTitle");
                    $feed->prefetchArrivalTimes($response);
                    if ($response) {
                        if (!is_array($response)) {
                            $response = array($response);
                        }

                        foreach ($response as $_response) {
                            if ($error = $_response->getResponseError()) {
                                $this->shellError("- Error retrieving data for $feedId: {$error}");
                                $errors++;
                            } else {
                                $result = $_response->getFromCache() ? "- Cache still valid" : sprintf("Fetched in %.2f seconds", $_response->getTimeElapsed());
                                $this->shellOutput($result);
                            }
                        }
                    } else {
                        $this->shellError("- No response returned for $feedId");
                        $errors++;
                    }
                } else {
                    $this->shellOutput("Skipping $feedId $feedTitle. Prefetch arrival times disabled.");
                }
            }
        } catch (KGOException $e) {
            $this->shellError("Error loading feed: {$e->getMessage()}");
            $errors++;
        }

        if ($errors) {
            $message = 'Arrival times failed to load.';
        } else {
            $message = 'Arrival times successfully loaded.';
        }

        return new KGOResult(true, $message, KGOResult::OK);
    }
}
