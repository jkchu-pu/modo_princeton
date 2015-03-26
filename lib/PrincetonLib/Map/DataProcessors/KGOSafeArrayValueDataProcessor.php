<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class KGOSafeArrayValueDataProcessor extends KGODataProcessor
{
    static protected $ignoreNullValues = true;

    /* Subclassed */
    protected function processValue($value, $object = null) {

        if (isset($value[$this->getOption('key')])) {
            return $value[$this->getOption('key')];
        }
        return null;
    }

    /* Subclassed */
    protected function canProcessValue($value, $object = null) {
        if (($key = $this->getOption('key')) !== null) {
            return is_array($value) && is_scalar($key);
        }
        throw new KGOException("'key' option is not set");
    }
}
