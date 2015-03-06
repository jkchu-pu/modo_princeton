<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

/**
 * @ingroup DataProcessor
 *
 * @brief Process a string into a url to a module search page.
 *
 * Turns a string into an array of KGOLinks containing a single link that goes
 * to the search page of a module.
 *
 * ### Options:
 *
 * + __module__ (_string_)
 *     + The module instance id.
 * + __command__ (_string_)
 *     + The module command (page).
 *     + Defaults to `search`.
 */

class PrincetonTransitStaticPageURLDataProcessor extends KGODataProcessor
{
    static protected $ignoreNullValues = true;

    protected function processValue($value, $object = null) {
        $urlModule = $this->getOption('module');
        $urlCommand = $this->getOption('command', KGOModule::PAGE_SEARCH);

        return KGOURL::createForModuleWebPage($urlModule, $urlCommand);
    }

    protected function canProcessValue($value, $object = null) {
        return true;
    }
}
