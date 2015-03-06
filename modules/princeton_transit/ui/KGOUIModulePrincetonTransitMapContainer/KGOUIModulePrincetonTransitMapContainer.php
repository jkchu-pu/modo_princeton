<?php

/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

/*
 * ### Options:
 *
 * + __height__ (_string_)
 *     + Sets the height of the map container.
 *     + Can either be in pixels or `auto` which will attempt to fill
 *       to the bottom of the window when the page is scrolled to the top.
 *     + Defaults to `auto`.
 *
 * ### Regions:
 *
 * + __map__
 *     + The map container.
 */

class KGOUIModulePrincetonTransitMapContainer extends KGOUIObject
{
    /* Subclassed */
    protected function init() {
        parent::init();

        $this->addOption('height', array(
            self::DEFAULT_VALUE => 'auto',
            self::JAVASCRIPT_VISIBLE => true,
        ));

        $this->addRegion('map');
    }
}
