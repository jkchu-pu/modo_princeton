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
 * ### Fields:
 *
 * + __serviceName__ (_string_)
 *     + The human-readable name of the service which generated the transit data.
 *     + Defaults to `null` (no service).
 *
 * + __service__ (_string_)
 *     + A computer-readable identifier for the service.
 *     + Defaults to `null` (no service).
 *
 * + __serviceImage__ (_KGOUIImage_)
 *     + An icon for the service which generated the transit data.
 *     + Defaults to `null` (no service).
 *
 * + __serviceURL__ (_KGOURL_)
 *     + A url for the service which generated the transit data.
 *     + Defaults to `null` (no service).
 *
 * + __status__ (_string_)
 *     + The status of the route.
 *     + If non-`null` will display a status icon indicating the state of the route.
 *     + One of:
 *         + `running` - An icon indicating the route is open.
 *         + `offline` - An icon indicating the route is closed.
 *     + Defaults to `null`.
 *
 * ### Options:
 *
 * + __external__ (_boolean_)
 *     + Whether or not to open the service url in an external browser.
 *     + Only used if the serviceURL field is set.
 *     + Defaults to `null` (set based on the service url using KGOURL::getShouldTargetNewWindow()).
 */

class KGOUIDetailModulePrincetonTransit extends KGOUIDetail
{
    protected static $hasFieldDescription = true;
    protected static $hasFocalBackground = false;

    protected static $optionDefaultResponsiveMargins = false;
    protected static $optionDefaultResponsiveTypography = false;

    /* Subclassed */
    protected function init() {
        parent::init();

        $this->addField('serviceName');

        $this->addField('service');

        $this->addField('serviceImage');

        $this->addField('serviceURL', array(
            self::FIELD_FORMATTERS => KGOFormatter::factory('KGOURLFormatter'),
        ));

        $this->addField('status');

        $this->addOption('external');


        $this->addRegion('status');
    }

    /* Subclassed */
    public function defaultDataSourceFieldCallback($field, KGOUIObjectInterface $dataSource) {
        if ($field === 'serviceImage') {
            $serviceImage = new KGOUIImage();
            $serviceImage->setUIInterfaceDataSource($dataSource);
            return $serviceImage;

        } else {
            return parent::defaultDataSourceFieldCallback($field, $dataSource);
        }
    }

    /* Subclassed */
    public function getCSSClassesForRegion($region) {
        $classes = parent::getCSSClassesForRegion($region);
        if ($region == 'status') {
            $classes[] = 'kgo_secondary_text';
        }
        return $classes;
    }

    /* Subclassed */
    protected function prepareForExport() {
        parent::prepareForExport();

        $serviceURL = $this->getField('serviceURL');
        if (($serviceURL instanceOf KGOURL) && ($this->getOption('external') === null)) {
            // Auto-set external option based on the url
            $this->setOption('external', $serviceURL->getShouldTargetNewWindow());
        }

        if (!($service = $this->getField('serviceImage')) && ($service = $this->getField('service'))) {
            $serviceImage = new KGOUIImage();
            $serviceImage->setOption('style', 'detail_service_icon');
            $serviceImage->setField('url', KGOURL::createForModuleImageFile("princeton_transit/$service.png"));
            $this->setField('serviceImage', $serviceImage);
        }

        if (($image = $this->getField('serviceImage')) && ($title = $this->getField('serviceName'))) {
            $image->setField('alt', $title);
        }

        if ($this->willLoadRegionContents('status') && ($this->getField('status') == 'offline')) {
            $offlineText = new KGOUIHTML();
            $offlineText->setField('html', kgo_localize('princeton_transit.titles.routeOffline'));
            $offlineText->setOption('inset', false);
            $this->appendToRegionContents('status', $offlineText);
        }
    }
}
