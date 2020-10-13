/*
* toastr
* Copyright 2012-2015
* Authors: John Papa, Hans Fjällemark, and Tim Ferrell.
* All Rights Reserved.
* Use, reproduction, distribution, and modification of this code is subject to the terms and
* conditions of the MIT license, available at http://www.opensource.org/licenses/mit-license.php
*
* ARIA Support: Greta Krafsig
*
* Project: https://github.com/CodeSeven/toastr
*
* Customized for eDirectory
* Be carefull with the original documentation
* Do not copy the js, less and scss files from the original project
*/
/* global define */
(function (define) {
    define(['jquery'], function ($) {
        return (function () {
            let $container;

            function getDefaults() {
                return {
                    fadeOut: 5000,
                    alignment: { x: 'right', y: 'top' },
                    fullWidth: false,
                    closeButton: true,
                    progressBar: true,
                    updateNotify: true,
                    blockScroll: false,

                    onShown: undefined,
                    onHidden: undefined,
                };
            }
            
            function getContainer(options, create) {
                if (!options) { options = getOptions(); }
                $container = $('#notify-container');
                if ($container.length) {
                    return $container;
                }
                if (create) {
                    $container = createContainer(options);
                }
                return $container;
            }

            function clear($toastElement, clearOptions) {
                var options = getOptions();
                if (!$container) { getContainer(options); }
                if (!clearToast($toastElement, options, clearOptions)) {
                    clearContainer(options);
                }
            }

            function remove($toastElement) {
                var options = getOptions();
                if (!$container) { getContainer(options); }
                if ($toastElement && $(':focus', $toastElement).length === 0) {
                    removeToast($toastElement);
                    return;
                }
                if ($container.children().length) {
                    $container.remove();
                }
            }

            function clearContainer (options) {
                var toastsToClear = $container.children();
                for (var i = toastsToClear.length - 1; i >= 0; i--) {
                    clearToast($(toastsToClear[i]), options);
                }
            }

            function clearToast ($toastElement, options, clearOptions) {
                var force = clearOptions && clearOptions.force ? clearOptions.force : false;
                if ($toastElement && (force || $(':focus', $toastElement).length === 0)) {
                    $toastElement['fadeOut']({
                        duration: 300,
                        easing: 'swing',
                        complete: function () { removeToast($toastElement); }
                    });
                    return true;
                }
                return false;
            }

            function createContainer(options) {
                $container = $('<div/>')
                    .attr('id', 'notify-container')
                    .attr('notify-alignment-x', options.alignment.x)
                    .attr('notify-alignment-y', options.alignment.y)
                    .attr('notify-full-width', options.fullWidth);

                $container.appendTo($('body'));

                if(options.blockScroll){
                    $('html').addClass('scroll-disabled');
                }

                return $container;
            }

            function notifyAlert(map) {
                let options = getOptions();
                let iconClass = map.iconClass || 'info';
                let intervalId = null;
                let $toastElement = $('<div/>');
                let $titleElement = $('<div/>');
                let $messageElement = $('<div/>');
                let $progressElement = $('<div/>');
                let $closeElement = $('<button type="button"><i class="fa fa-close"></i></button>');
                let progressBar = {
                    intervalId: null,
                    hideEta: null,
                    maxHideTime: null
                };

                if (typeof (map.optionsOverride) !== 'undefined') {
                    options = $.extend(options, map.optionsOverride);
                    iconClass = map.optionsOverride.iconClass || iconClass;
                }

                $container = getContainer(options, true);

                function handleEvents() {
                    if (options.closeButton && $closeElement) {
                        $closeElement.click(function (event) {
                            if (event.stopPropagation) {
                                event.stopPropagation();
                            } else if (event.cancelBubble !== undefined && event.cancelBubble !== true) {
                                event.cancelBubble = true;
                            }

                            if (options.onCloseClick) {
                                options.onCloseClick(event);
                            }

                            hideToast(true);
                        });
                    }

                    if (options.onclick) {
                        $toastElement.click(function (event) {
                            options.onclick(event);
                            hideToast();
                        });
                    }
                }

                function displayToast() {
                    $toastElement.hide();

                    $toastElement['fadeIn'](
                        {duration: 300, easing: 'swing', complete: options.onShown}
                    );

                    if(options.updateNotify === true){
                        if($container.children().length > 1){
                            $container.children().last().remove();
                        }
                    }

                    if ((options.fadeOut > 0) && (options.fadeOut !== false)) {
                        intervalId = setTimeout(hideToast, options.fadeOut);
                        progressBar.maxHideTime = parseFloat(options.fadeOut);
                        progressBar.hideEta = new Date().getTime() + progressBar.maxHideTime;
                        if (options.progressBar) {
                            progressBar.intervalId = setInterval(updateProgress, 10);
                        }
                    }
                }

                function setIcon() {
                    if (map.iconClass) {
                        $toastElement.addClass('notify-item').attr('notify-type', iconClass);
                    }
                }

                function setSequence() {
                    $container.prepend($toastElement);
                }

                function setTitle() {
                    if (map.title) {
                        var suffix = map.title;
                        $titleElement.append(suffix).addClass('notify-title');
                        $toastElement.append($titleElement);
                    }
                }

                function setMessage() {
                    if (map.message) {
                        var suffix = map.message;
                        $messageElement.append(suffix).addClass('notify-content');
                        $toastElement.append($messageElement);
                    }
                }

                function setCloseButton() {
                    if (options.closeButton) {
                        $closeElement.addClass('notify-close-button').attr('role', 'button');
                        $toastElement.prepend($closeElement);
                    }
                }

                function setProgressBar() {
                    if (options.progressBar) {
                        $progressElement.addClass('notify-progress');
                        $toastElement.prepend($progressElement);
                    }
                }

                function hideToast(override) {
                    var method = 'fadeOut';
                    var duration = 300;
                    var easing = 'swing';

                    if ($(':focus', $toastElement).length && !override) {
                        return;
                    }
                    clearTimeout(progressBar.intervalId);
                    return $toastElement[method]({
                        duration: duration,
                        easing: easing,
                        complete: function () {
                            removeToast($toastElement, options);
                            clearTimeout(intervalId);

                            if(options.onHidden) options.onHidden();
                        }
                    });
                }

                function updateProgress() {
                    var percentage = ((progressBar.hideEta - (new Date().getTime())) / progressBar.maxHideTime) * 100;
                    $progressElement.width(percentage + '%');
                }

                setIcon();
                setTitle();
                setMessage();
                setCloseButton();
                setProgressBar();
                setSequence();
                displayToast();
                handleEvents();

                return $toastElement;
            }

            function getOptions() {
                return $.extend({}, getDefaults(), notify.options);
            }

            function removeToast($toastElement, options) {
                var options = options || getOptions();
                if (!$container) { $container = getContainer(); }
                if ($toastElement.is(':visible')) {
                    return;
                }
                $toastElement.remove();
                $toastElement = null;
                if ($container.children().length === 0) {
                    $container.remove();

                    if(options.blockScroll){
                        if($('html').hasClass('scroll-disabled')){
                            $('html').removeClass('scroll-disabled');
                        }
                    }
                }
            }

            /**
             * Create notification block
             * @type error
             *
             * @param string message Message of notify element
             * @param string title Title of notify element
             * @param object optionsOverride Object with override options
             * 
             * @return Notify block
             */
            function error(message, title, optionsOverride) {
                return notifyAlert({
                    type: 'danger',
                    iconClass: 'danger',
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            /**
             * Create notification block
             * @type information
             *
             * @param string message Message of notify element
             * @param string title Title of notify element
             * @param object optionsOverride Object with override options
             * 
             * @return Notify block
             */
            function info(message, title, optionsOverride) {
                return notifyAlert({
                    type: 'info',
                    iconClass: 'info',
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            /**
             * Create notification block
             * @type success
             *
             * @param string message Message of notify element
             * @param string title Title of notify element
             * @param object optionsOverride Object with override options
             * 
             * @return Notify block
             */
            function success(message, title, optionsOverride) {
                return notifyAlert({
                    type: 'success',
                    iconClass: 'success',
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }
            
            /**
             * Create notification block
             * @type warning
             *
             * @param string message Message of notify element
             * @param string title Title of notify element
             * @param object optionsOverride Object with override options
             * 
             * @return Notify block
             */
            function warning(message, title, optionsOverride) {
                return notifyAlert({
                    type: 'warning',
                    iconClass: 'warning',
                    message: message,
                    optionsOverride: optionsOverride,
                    title: title
                });
            }

            /**
             * Export notify functions
             * 
             * @return Notify content
             */
            let notify = {
                error: error,
                info: info,
                success: success,
                warning: warning,
                clear: clear,
                remove: remove,
                getContainer: getContainer,
                options: {}
            };

            return notify;
        })();
    });
}(typeof define === 'function' && define.amd ? define : function (deps, factory) {
    if (typeof module !== 'undefined' && module.exports) { //Node
        module.exports = factory(require('jquery'));
    } else {
        window.notify = factory(window.jQuery);
    }
}));