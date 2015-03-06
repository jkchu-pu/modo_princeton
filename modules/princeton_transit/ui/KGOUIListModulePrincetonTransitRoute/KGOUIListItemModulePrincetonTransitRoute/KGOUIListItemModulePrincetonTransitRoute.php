<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

/**
 * @ingroup UI_List Location_View
 *
 * @brief List items for routes
 *
 * ### Fields:
 *
 * + __status__ (_string_)
 *     + The status of the route.
 *     + If non-`null` will display a status icon indicating the state of the route.
 *     + One of:
 *         + `running` - An icon indicating the route is open.
 *         + `offline` - An icon indicating the route is closed.
 *     + Defaults to `null`.
 *
 * @see KGOUIListModulePrincetonTransitRoute
 */

class KGOUIListItemModulePrincetonTransitRoute extends KGOUIListItem
{
    /* Subclassed */
    protected function init() {
        parent::init();

        $this->addField('status');
    }

    /* Subclassed */
    public function getCSSClasses() {
        $classes = parent::getCSSClasses();
        if ($status = $this->getField('status')) {
            $classes[] = "kgo_route_$status";
        }
        return $classes;
    }

    public function getScreenReaderStatusText() {
        switch ($this->getField('status')) {
            case 'running':
                return kgo_localize('princeton_transit.subtitles.screenReaderRouteIsRunning');

            case 'offline':
                return kgo_localize('princeton_transit.subtitles.screenReaderRouteIsOffline');
        }
        return '';
    }
}
