<?php

/*
 * Copyright © 2010 - 2015 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

class PrincetonAppendNutritionIconsDataProcessor extends KGODataProcessor
{
    private $_iconTemplates = array(
        'text' => '<span class="entree-content %s" title="%s" alt="%s">%s</span>',
        'image' => '<img src="%s" class="entree-property" title="%s" alt="%s">',
    );

    private $_iconData = array(
        'vegan' => array('text', 'vegan', 'vegan (no animal derivatives)', 'vegan', 'VGN'),
        'vegetarian' => array('text', 'vegetarian', 'vegetarian (no meat products)', 'vegetarian', 'VGT'),
        'pork' => array('text', 'pork', 'vegan (no animal derivatives)', 'contains pork', 'P'),
        'ccc' => array('text', 'ccc', 'Conscious&nbsp;Cuisine&reg;&nbsp;Choice', 'Conscious Cuisine Choice', 'CCC'),
        'earth_friendly' => array('image', 'earth&#8209;friendly', 'earth-friendly'),
        'carbonLow' => array('image', 'low carbon emissions', 'low carbon emissions'),
        'carbonMedium' => array('image', 'medium carbon emissions', 'medium carbon emissions'),
        'carbonHigh' => array('image', 'high carbon emissions', 'high carbon emissions'),
    );

    protected function processValue($value, $object = null) {
        $icons = $object->getAttribute('menu:icons');
        $iconHTML = "";

        foreach ($icons as $iconName) {
            if (isset($this->_iconData[$iconName])) {
                $iconData = array_merge($this->_iconData[$iconName]);
                $iconType = array_shift($iconData);
                $iconTemplate = $this->_iconTemplates[$iconType];

                if ($iconType == 'image') {
                    $iconURL = KGOURL::createForImageFile(sprintf("dining/%s.png", $iconName));
                    array_unshift($iconData, $iconURL->getHTTPString());
                }

                $iconHTML .= vsprintf($iconTemplate, $iconData);
            }
        }
        return $value.$iconHTML;
    }

    protected function canProcessValue($value, $object = null) {
        return true;
    }
}
