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
 * @brief Route Lists
 */

class KGOUIListModulePrincetonTransitRoute extends KGOUIList
{
    protected static $itemClass = 'KGOUIListItemModulePrincetonTransitRoute';

    protected static $fieldDefaultNoItemsMessageKey = 'princeton_transit.titles.noRoutes';
    protected static $optionDefaultFixedHeight = false;
}
