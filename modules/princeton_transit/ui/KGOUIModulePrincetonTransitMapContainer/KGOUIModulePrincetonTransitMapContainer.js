/*
 * Copyright © 2010 - 2014 Modo Labs Inc. All rights reserved.
 *
 * The license governing the contents of this file is located in the LICENSE
 * file located at the root directory of this distribution. If the LICENSE file
 * is missing, please contact sales@modolabs.com.
 *
 */

var KGOUIModulePrincetonTransitMapContainer = KGOUIObject.extend({
    onResize: function (event) {
        var height = this.getOption('height');
        if (height === '100%') {
            this.element.css('height', height);

        } else if (height === 'auto') {
            // see first comment on http://bugs.jquery.com/ticket/6724
            var windowHeight = window.innerHeight ? window.innerHeight : $(window).height();

            // Set height with css because this element is box-sizing: border-box.
            // jQuery.height() always applies to the content height regardless of
            // the box model and will add the padding back into the height to compensate.
            this.element.css('height', (windowHeight - this.element.offset().top)+'px');

        } else if (height) {
            // pixel heights do not include the padding.
            this.element.height(height);
        }
    },

    onDOMChange: function (element, object) {
        this.onResize();
    }
});
