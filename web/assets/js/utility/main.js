$(document).ready(function() {
    'use strict';
    let HeaderFunctions = function (el) {
        this.attrs = {};
        this.headerHeight = el.height();
        this.siblingElement = el.next();
        this.topSpace = el.css('top');

        this.getAttributes = function () {
            if (el.get(0)) {
                let attrs = el.get(0).attributes;

                for (let i in attrs) {
                    let index = attrs[i];
                    if (typeof index.nodeValue !== 'undefined') this.attrs[index.nodeName] = index.nodeValue;
                }
            }
        };

        this.setAttributes = function (attrs) {
            el.each(function (i, e) {
                let element = $(e);
                for (let attr in attrs) {
                    element.attr(attr, attrs[attr]);
                }
            });
        };

        this.setElementSpaces = function () {
            this.paddingHeader = parseInt(el.find('.header-content').css('padding-bottom'));
            this.paddingElement = parseInt(this.siblingElement.css('padding-top'));
            this.siblingElement.css('padding-top', (this.headerHeight + this.paddingElement) - this.paddingHeader);
        };

        this.setElementLeadGen = function () {
            this.paddingHeader = parseInt(el.find('.header-content').css('padding-bottom'));
            this.paddingElement = parseInt(this.siblingElement.css('padding-top'));
            el.next().children('[data-type="3"]').find('.carousel-cell').css('padding-top', (this.headerHeight + this.paddingElement) - this.paddingHeader);
        };

        this.setHeaderVariation = function () {
            if (this.attrs['is-sticky'] === 'true' || this.attrs['has-opacity'] === 'true') {
                this.setElementSpaces();
            }
        };

        this.setLeadGenVariation = function () {
            if (this.attrs['is-sticky'] === 'true' || this.attrs['has-opacity'] === 'true') {
                this.setElementLeadGen();
            }
        };

        this.changeBackgroundScroll = function (scrollOut) {
            if (this.attrs['is-sticky'] === 'true' && this.attrs['has-opacity'] === 'true') {
                this.setAttributes({
                    'has-opacity': (scrollOut ? 'false' : 'true'),
                });

                if (this.attrs['is-inverse'] === 'true') {
                    this.setAttributes({
                        'is-inverse': (scrollOut ? 'true' : 'false'),
                    });
                }

                if (el.prev().is('.admin-bar')) {
                    el.css('top', $('.admin-bar').height());
                }
            }
        };

        this.unsetHeader = function () {
            this.setAttributes({
                'has-opacity': 'false'
            });
        };

        this.clearAttrHeader = function () {
            this.setAttributes({
                'has-opacity': 'false',
                'is-sticky': 'false'
            });
        };

        this.checkPrevElement = function () {
            if (el.prev().is('.admin-bar')) {
                el.css('top', $('.admin-bar').height());
            }
        };

        this.init = function () {
            this.getAttributes();

            if ((!el.next().hasClass('hero-with-slider')) && (!el.next().hasClass('hero-leadgen'))) {
                this.setHeaderVariation();
            }

            if (el.next().hasClass('hero-leadgen')) {
                this.setLeadGenVariation();
            }

            if (this.attrs['is-sticky'] === 'true' || this.attrs['has-opacity'] === 'true') {
                this.checkPrevElement();
            }

            if (this.attrs['is-inverse'] === 'true' && this.attrs['has-opacity'] === 'true') {
                this.setAttributes({
                    'is-inverse': 'false'
                });
            }
        };

        if (!el.next().is('.hero-default, .hero-wrapper, .hero-leadgen')) {
            this.unsetHeader();

            if ($(window).width() <= 768) {
                this.clearAttrHeader();
            }
        }

        this.init();
    };

    let MenuMore = function () {
        this.navBar = undefined;
        this.navBarItems = [];
        this.navBarMore = undefined;
        this.navBarMoreLabel = undefined;
        this.navBarMoreContent = undefined;

        this.init = function (navBar) {
            let obj = this;
            this.navBar = navBar;
            this.navBarItems = [];
            this.navBar.find('.navbar-link').each(function (index, item) {
                obj.navBarItems.push({
                    width: $(item).outerWidth(true),
                    name: $(item).html(),
                    link: $(item).attr('href'),
                    index: index,
                    ref: $(item)
                });
            });
            this.navBarMore = this.navBar.find('.navbar-more').first();
            this.navBarMoreLabel = this.navBar.find('.more-label');
            this.navBarMoreContent = this.navBar.find('.more-content');
            this.navBarMore.removeClass('has-more');
            this.navBarMoreLabel.removeClass('is-open');
            this.navBarMoreContent.hide();
        };

        this.measure = function () {
            this.setNavBarMoreItems([]);
            this.invalidateVisibleItems([]);
            if (this.hasMore()) {
                let exceedItems = this.getNavBarMoreItems();
                this.setNavBarMoreItems(exceedItems);
                this.invalidateVisibleItems(exceedItems);
                this.navBarMore.addClass('has-more');
                // this.navBarMoreLabel.addClass("is-open");
                $('.more-content').css('right', (-$('.navbar-more').outerWidth(true)) - 12);
            } else {
                this.navBarMore.removeClass('has-more');
                this.navBarMoreLabel.removeClass('is-open');
            }
        };

        this.invalidateVisibleItems = function (invisibleItems) {
            this.navBarItems.forEach(function (item) {
                let includes = false;
                invisibleItems.forEach(function (invisibleItem) {
                    if (invisibleItem === item) {
                        includes = true;
                    }
                });

                if (includes) {
                    item.ref.hide();
                } else {
                    item.ref.show();
                }
            });
        };

        this.setNavBarMoreItems = function (items) {
            let content = this.navBarMore.find('.more-content');
            content.html('');

            items.forEach(function (item) {
                content.append('<a href="' + item.link + '" class="more-link">' + item.name + '</a>');
            });
        };

        this.getNavBarMoreItems = function () {
            if (this.hasMore()) {
                let navBarWidth = this.getNavBarWidth();
                let width = 0;
                let itensFitInNavBar = this.navBarItems
                    .filter(function (item) {
                        width += item.width;
                        return width <= navBarWidth;
                    });
                return this.navBarItems.slice(itensFitInNavBar.length - 1);
            }
            return [];
        };

        this.hasMore = function () {
            return Math.floor(this.getNavBarItemsWidth()) > Math.floor(this.getNavBarWidth());
        };

        this.getNavBarItemsWidth = function () {
            if (this.navBarItems.length === 0) return;
            return this.navBarItems.reduce(function (acc, act) {
                return (acc + act.width);
            }, 0);
        };

        this.getNavBarWidth = function () {
            return this.navBar.width();
        };
    };

    if ($(window).width() >= 992) {
        if ($.isFunction(MenuMore)) {
            window.menuMore = new MenuMore();
        } else {
            console.error('MenuMore function not found.');
        }
    }

    let headerFunctions;
    if ($.isFunction(HeaderFunctions)) {
        headerFunctions = new HeaderFunctions($('header'));
    } else {
        console.error('HeaderFunctions function not found.');
    }

    $(window).on('scroll', function () {
        if (headerFunctions !== undefined && $.isFunction(headerFunctions.changeBackgroundScroll)) {
            let headerHeight = 60;

            if ($(this).scrollTop() > headerHeight) {
                headerFunctions.changeBackgroundScroll(true);
            } else {
                headerFunctions.changeBackgroundScroll(false);
            }
        } else {
            console.error('HeaderFunctions instance not found or with unexpected content.');
        }
    });

    window.menuMoreOnResizeFunction = function () {
        if (window.menuMore !== undefined && $.isFunction(window.menuMore.measure)) {
            if ($(window).width() >= 992 && $('.header-navbar').length > 0) {
                window.menuMore.measure();
            }
        } else {
            console.error('MenuMore instance not found or with unexpected content.');
        }
    };

    $(window).on('resize', window.menuMoreOnResizeFunction);

    window.btnReset = function ($button) {
        if ($button.length) {
            let size = $button.attr('data-size');
            let content = $button.attr('data-content');

            $button.removeClass('is-loading');
            $button.html(content);
            $button.width(size);
            $button.removeAttr('disabled');
        }
    };

    if ($(window).width() >= 992 && $('.header-navbar').length > 0) {
        if (window.menuMore !== undefined && $.isFunction(window.menuMore.init) && $.isFunction(window.menuMore.measure)) {
            let navBar = $('.header-navbar');
            window.menuMore.init(navBar);
            window.menuMore.measure();
        } else {
            console.error('MenuMore instance not found or with unexpected content.');
        }
    }

    $('form').on('submit', function () {
        let btn = $(this).find('.button[data-loading]');

        if (btn.length) {
            let size = btn.width();
            let content = btn.html();

            btn.attr('data-content', content);
            btn.attr('data-size', size);
            btn.attr('disabled', true);
            // btn.width(size);
            btn.addClass('is-loading');
            btn.html(btn.attr('data-loading'));
        }
    });

    $('.search-toggler').on('click', function () {
        $('.thin-strip-base[data-type="4"]').slideToggle(400);
    });

    if ($(window).width() <= 768) {
        if ($('.hero-default[data-type="4"]').length > 0) {
            $('.search-toggler').hide();
        }

        if ($('#thin-strip-search').length <= 0) {
            $('.search-toggler').hide();
        }
    }

    if ($.fn.select2) {
        $('select:not(#feed):not(.custom-sample-select)').select2({
            minimumResultsForSearch: Infinity,
        });

        $('.custom-sample-select').select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $('.listing-template-selection'),
        });
    }

    $('.more-label').on('click', function () {
        let el = $(this);
        if (!el.next('.more-content').is(':visible')) {
            $('.more-label').next('.more-content').slideUp(400);
            $('.more-label').removeClass('is-open');
        }
        el.toggleClass('is-open');
        if ($(window).width() >= 992) {
            el.next('.more-content').fadeToggle('fast', function () {
                if ($(this).is(':visible'))
                    $(this).css('display', 'flex');
            });
        } else {
            el.next('.more-content').slideToggle(400);
        }
    });

    $('.user-button').on('click', function () {
        $(this).toggleClass('is-open');
        $(this).find('.user-content').fadeToggle(400);
    });

    $('.navbar-toggler').on('click', function () {
        $('.search-mobile').slideUp(400);
        $('.navbar-mobile').slideToggle(400, function () {
            $('.navbar-toggler').toggleClass('is-open');
        });
    });

    if ($(window).width() <= 768) {
        $('.footer-newsletter-toggler').on('click', function () {
            $(this).next().slideToggle(400);
        });
    }

    $('.sidebar-toggler').on('click', function () {
        $(this).next().slideToggle(400);
        $(this).find('.fa').toggleClass('fa-minus').toggleClass('fa-plus');
        $(this).toggleClass('is-closed');
    });

    $('.alert-message[is-dismissible="true"]').click(function (e) {
        let alertWidth = $(this).width();
        let clickedPosition = (e.pageX - $(this).position().left);

        if (clickedPosition > alertWidth) {
            $(this).fadeOut();
        }
    });

    $('.categories-dropdown-toggle').on('click', function () {
        if ($(this).hasClass('centralized-dropdown-toggle')) {
            $(this).fadeOut(function () {
                $(this).next().fadeIn(function () {
                    $('.card-centralized').each(function () {
                        let titleHeight = $(this).find('.title').height();
                        let contentHeight = $(this).find('.content').height();
                        let bottomValue = (contentHeight - titleHeight) * -1;
                        $(this).find('.content').css('bottom', (bottomValue - 10));
                    });
                });
            });
        } else {
            $(this).next().fadeToggle();
        }
    });

    $('.summary-categories-item-toggle').on('click', function () {
        $(this).next().fadeToggle();
    });

    if($.isFunction(LazyLoad)) {
        var lazyLoadInstance = new LazyLoad({
            elements_selector: '.lazy',
        });
    }
});
