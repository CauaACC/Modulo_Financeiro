/**
 * Bootstrap 3 → 5 JavaScript Compatibility Layer (v2)
 * Load AFTER jQuery 3 and Bootstrap 5 bundle.
 */
(function () {
    'use strict';

    // ================================================================
    // 1. Convert data-toggle → data-bs-toggle (and related BS3 attrs)
    // ================================================================
    var attrMap = {
        'data-toggle':    'data-bs-toggle',
        'data-dismiss':   'data-bs-dismiss',
        'data-target':    'data-bs-target',
        'data-parent':    'data-bs-parent',
        'data-ride':      'data-bs-ride',
        'data-slide':     'data-bs-slide',
        'data-slide-to':  'data-bs-slide-to',
        'data-backdrop':  'data-bs-backdrop',
        'data-keyboard':  'data-bs-keyboard',
        'data-spy':       'data-bs-spy',
        'data-offset':    'data-bs-offset',
        'data-content':   'data-bs-content',
        'data-placement': 'data-bs-placement',
        'data-container': 'data-bs-container',
        'data-trigger':   'data-bs-trigger'
    };

    function convertAttributes(root) {
        var keys = Object.keys(attrMap);
        for (var i = 0; i < keys.length; i++) {
            var oldAttr = keys[i];
            var newAttr = attrMap[oldAttr];
            var elements = root.querySelectorAll('[' + oldAttr + ']');
            for (var j = 0; j < elements.length; j++) {
                var el = elements[j];
                if (!el.hasAttribute(newAttr)) {
                    el.setAttribute(newAttr, el.getAttribute(oldAttr));
                }
            }
        }
    }

    // ================================================================
    // 2. Add .dropdown-item to <a> tags inside .dropdown-menu
    //    BS5 requires this class for proper styling/behavior
    // ================================================================
    function fixDropdownItems(root) {
        var links = root.querySelectorAll('.dropdown-menu > li > a');
        for (var i = 0; i < links.length; i++) {
            if (!links[i].classList.contains('dropdown-item')) {
                links[i].classList.add('dropdown-item');
            }
        }
        // Also fix dividers
        var dividers = root.querySelectorAll('.dropdown-menu > .divider, .dropdown-menu > li.divider');
        for (var j = 0; j < dividers.length; j++) {
            dividers[j].classList.add('dropdown-divider');
        }
    }

    // ================================================================
    // 3. Convert .collapse.in → .collapse.show
    //    Convert .tab-pane.in → .tab-pane.show (for fade tabs)
    // ================================================================
    function fixInToShow(root) {
        var collapseIn = root.querySelectorAll('.collapse.in');
        for (var i = 0; i < collapseIn.length; i++) {
            collapseIn[i].classList.remove('in');
            collapseIn[i].classList.add('show');
        }
        var tabIn = root.querySelectorAll('.tab-pane.in');
        for (var j = 0; j < tabIn.length; j++) {
            tabIn[j].classList.remove('in');
            tabIn[j].classList.add('show');
        }
    }

    // ================================================================
    // 4. Fix modal header structure
    //    BS3: <button class="close" data-dismiss="modal">&times;</button>
    //    The .close class is re-styled in CSS compat, and data-dismiss
    //    is converted by attr mapping above. No further action needed.
    // ================================================================

    // ================================================================
    // Run all DOM fixes
    // ================================================================
    function runAllFixes(root) {
        convertAttributes(root);
        fixDropdownItems(root);
        fixInToShow(root);
    }

    // Run on initial page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            runAllFixes(document);
        });
    } else {
        runAllFixes(document);
    }

    // ================================================================
    // 5. MutationObserver: apply fixes to dynamically added content
    // ================================================================
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            for (var m = 0; m < mutations.length; m++) {
                var added = mutations[m].addedNodes;
                for (var n = 0; n < added.length; n++) {
                    if (added[n].nodeType === 1) {
                        runAllFixes(added[n]);
                    }
                }
            }
        });
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                observer.observe(document.body, { childList: true, subtree: true });
            });
        } else {
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    // ================================================================
    // 6. jQuery compatibility polyfills
    // ================================================================
    if (typeof jQuery !== 'undefined') {
        var $ = jQuery;

        // $.parseJSON — removed in jQuery 3
        if (!$.parseJSON) {
            $.parseJSON = JSON.parse;
        }

        // $.isNumeric — removed in jQuery 3.x
        if (!$.isNumeric) {
            $.isNumeric = function (val) {
                var type = typeof val;
                return (type === 'number' || type === 'string') &&
                       !isNaN(val - parseFloat(val));
            };
        }

        // $.expr.filters alias (used by some old plugins)
        if ($.expr && $.expr.pseudos && !$.expr.filters) {
            $.expr.filters = $.expr.pseudos;
        }

        // $.browser — removed in jQuery 1.9 (used by tablesorter)
        if (!$.browser) {
            var ua = navigator.userAgent.toLowerCase();
            $.browser = {
                msie: /msie|trident/.test(ua),
                mozilla: /mozilla/.test(ua) && !/compatible|webkit/.test(ua),
                webkit: /webkit/.test(ua),
                opera: /opera/.test(ua),
                version: (ua.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [0, '0'])[1]
            };
        }

        // $.fn.bind / $.fn.unbind — restore if removed (needed by old plugins)
        if (!$.fn.bind) {
            $.fn.bind = function (types, data, fn) {
                return this.on(types, null, data, fn);
            };
        }
        if (!$.fn.unbind) {
            $.fn.unbind = function (types, fn) {
                return this.off(types, null, fn);
            };
        }
        if (!$.fn.delegate) {
            $.fn.delegate = function (selector, types, data, fn) {
                return this.on(types, selector, data, fn);
            };
        }
        if (!$.fn.undelegate) {
            $.fn.undelegate = function (selector, types, fn) {
                return this.off(types, selector, fn);
            };
        }

        // $.fn.andSelf → $.fn.addBack
        if (!$.fn.andSelf && $.fn.addBack) {
            $.fn.andSelf = $.fn.addBack;
        }

        // $.fn.size() — removed in jQuery 3
        if (!$.fn.size) {
            $.fn.size = function () {
                return this.length;
            };
        }

        // ================================================================
        // 7. Bootstrap 5 jQuery plugin wrappers
        //    Re-expose BS5 vanilla JS APIs as jQuery plugins
        //    so that existing code like $('#modal').modal('show') works.
        // ================================================================
        if (typeof bootstrap !== 'undefined') {

            function getBsInstance(Constructor, el, options) {
                return Constructor.getInstance(el) || new Constructor(el, options || {});
            }

            // .modal()
            if (bootstrap.Modal && !$.fn.modal) {
                $.fn.modal = function (option) {
                    return this.each(function () {
                        var inst = getBsInstance(bootstrap.Modal, this);
                        if (typeof option === 'string') {
                            if (typeof inst[option] === 'function') {
                                inst[option]();
                            }
                        }
                    });
                };
            }

            // .tab()
            if (bootstrap.Tab && !$.fn.tab) {
                $.fn.tab = function (option) {
                    return this.each(function () {
                        var inst = getBsInstance(bootstrap.Tab, this);
                        if (option === 'show') inst.show();
                    });
                };
            }

            // .collapse()
            if (bootstrap.Collapse && !$.fn.collapse) {
                $.fn.collapse = function (option) {
                    return this.each(function () {
                        var inst = getBsInstance(bootstrap.Collapse, this);
                        if (typeof option === 'string' && typeof inst[option] === 'function') {
                            inst[option]();
                        }
                    });
                };
            }

            // .tooltip()
            if (bootstrap.Tooltip && !$.fn.tooltip) {
                $.fn.tooltip = function (option) {
                    return this.each(function () {
                        if (option === 'destroy' || option === 'dispose') {
                            var existing = bootstrap.Tooltip.getInstance(this);
                            if (existing) existing.dispose();
                        } else {
                            var inst = getBsInstance(bootstrap.Tooltip, this, typeof option === 'object' ? option : {});
                            if (typeof option === 'string' && typeof inst[option] === 'function') {
                                inst[option]();
                            }
                        }
                    });
                };
            }

            // .popover()
            if (bootstrap.Popover && !$.fn.popover) {
                $.fn.popover = function (option) {
                    return this.each(function () {
                        if (option === 'destroy' || option === 'dispose') {
                            var existing = bootstrap.Popover.getInstance(this);
                            if (existing) existing.dispose();
                        } else {
                            var inst = getBsInstance(bootstrap.Popover, this, typeof option === 'object' ? option : {});
                            if (typeof option === 'string' && typeof inst[option] === 'function') {
                                inst[option]();
                            }
                        }
                    });
                };
            }

            // .dropdown()
            if (bootstrap.Dropdown && !$.fn.dropdown) {
                $.fn.dropdown = function (option) {
                    return this.each(function () {
                        var inst = getBsInstance(bootstrap.Dropdown, this);
                        if (option === 'toggle') inst.toggle();
                    });
                };
            }

            // .alert()
            if (bootstrap.Alert && !$.fn.alert) {
                $.fn.alert = function (option) {
                    return this.each(function () {
                        if (option === 'close') {
                            var inst = getBsInstance(bootstrap.Alert, this);
                            inst.close();
                        }
                    });
                };
            }

        } // end bootstrap check

        // ================================================================
        // 8. BS5 events → jQuery events bridge
        //    BS5 fires native DOM events, but code uses jQuery .on()
        //    to listen. We need to bridge them.
        // ================================================================
        var bsEvents = [
            'show.bs.modal', 'shown.bs.modal', 'hide.bs.modal', 'hidden.bs.modal',
            'show.bs.collapse', 'shown.bs.collapse', 'hide.bs.collapse', 'hidden.bs.collapse',
            'show.bs.dropdown', 'shown.bs.dropdown', 'hide.bs.dropdown', 'hidden.bs.dropdown',
            'show.bs.tab', 'shown.bs.tab', 'hide.bs.tab', 'hidden.bs.tab',
            'show.bs.toast', 'shown.bs.toast', 'hide.bs.toast', 'hidden.bs.toast'
        ];

        function bridgeBsEvent(eventName) {
            document.addEventListener(eventName, function (e) {
                // Trigger the same event on the jQuery wrapper so .on() listeners work
                var jqEvent = $.Event(eventName);
                jqEvent.relatedTarget = e.relatedTarget;
                $(e.target).trigger(jqEvent);
            });
        }

        // Only bridge if DOMContentLoaded has fired or fires
        function setupBridge() {
            for (var i = 0; i < bsEvents.length; i++) {
                bridgeBsEvent(bsEvents[i]);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupBridge);
        } else {
            setupBridge();
        }

    } // end jQuery check

})();
