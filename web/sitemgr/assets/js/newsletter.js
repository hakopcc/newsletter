var eDirectory = eDirectory || {};
eDirectory.Arcamailer = eDirectory.Arcamailer || {};

eDirectory.Arcamailer = {
    login: function (user, pwd, callback) {
        var self = this;
        var data = {
            email: user,
            password: pwd
        };

        $.post('/arcamailer/login', data, function (response) {
            if (response && response.customerId) {
                self.customerId = response.customerId;

                return callback({success: true});
            }

            callback({success: false, message: response.message});
        });
    },
    register: function (name, email, country, timezone, callback) {
        var self = this;
        var data = {
            email: email,
            name: name,
            country: country,
            timezone: timezone
        };

        $.post('/arcamailer/register', data, function (response) {
            if (response && response.success) {
                self.customerId = response.customerId;

                return callback({success: true});
            }

            callback({success: false, message: response.message});
        });
    },
    createList: function (name, callback) {
        var self = this;

        if (!self.customerId) {
            console.error('Not logged in');
        }

        var data = {
            name: name,
            customerId: self.customerId
        };

        $.post('/arcamailer/list', data, function (response) {
            if (response.success) {
                self.listId = response.listId;

                return callback({success: true});
            }

            callback({success: false, message: response.message});
        });
    },
    getInfo: function (callback) {
        $.get('/arcamailer/info', callback);
    },
    customerId: null,
    listId: null
};

$(function () {
    var tab = $('#tab-newsletter');
    eDirectory.Arcamailer.customerId = tab.data('customer-id');
    eDirectory.Arcamailer.listId = tab.data('customer-listid');

    var Navigation = {
        panels: document.querySelectorAll('.newsletter-panel'),
        tabs: document.querySelectorAll('#newsletter-widget, #newsletter-form'),
        showPane: function (paneId) {
            notify.clear();
            for (var i = 0; i < this.panels.length; i++) {
                this.panels[i].style.display = this.panels[i].id === paneId ? 'block' : 'none';
            }
        },
        showNewsletterForm: function () {
            notify.clear();
            for (var i = 0; i < this.tabs.length; i++) {
                if (this.tabs[i].id === 'newsletter-form') {
                    return $(this.tabs[i]).show();
                }

                $(this.tabs[i]).hide();
            }
        },
        showNewsletterWidget: function () {
            notify.clear();
            for (var i = 0; i < this.tabs.length; i++) {
                if (this.tabs[i].id === 'newsletter-widget') {
                    return $(this.tabs[i]).show();
                }

                $(this.tabs[i]).hide();
            }
        }
    };

    $('#new-account-button').on('click', function () {
        eDirectory.Arcamailer.getInfo(function (response) {
            $('#newsletter_country').html(response.countries);
            $('#newsletter_timezone').html(response.timezones);
        });

        Navigation.showPane('new-account-panel');
    });

    $('#login-button').on('click', function () {
        Navigation.showPane('newsletter-login-panel');
    });

    $('button.cancel').on('click', function () {
        Navigation.showPane('first-panel');
    });

    $('#newsletter-login-form').on('submit', function (e) {
        e.preventDefault();

        var user = $('#newsletter_user').val(),
            pwd = $('#newsletter_password').val();

        $('#loading_ajax').fadeIn('fast');
        eDirectory.Arcamailer.login(user, pwd, function (response) {
            notify.clear();

            $('#loading_ajax').fadeOut('fast');

            if (response.success) {
                return Navigation.showPane('newsletter-create-list');
            }

            if (response.message) {
                notify.error(response.message, '', { fadeOut: 0 });
            }
        });
    });

    $('#newsletter-register-form').on('submit', function (e) {
        e.preventDefault();

        var name = $('#newsletter_name').val(),
            email = $('#newsletter_email').val(),
            country = $('#newsletter_country').val(),
            timezone = $('#newsletter_timezone').val();

        $('#loading_ajax').fadeIn('fast');
        eDirectory.Arcamailer.register(name, email, country, timezone, function (response) {
            notify.clear();

            $('#loading_ajax').fadeOut('fast');

            if (response.success) {
                return Navigation.showPane('newsletter-create-list');
            }

            if (response.message) {
                notify.error(response.message, '', { fadeOut: 0 });
            }
        });
    });

    $('#newsletter-list-form').on('submit', function (e) {
        e.preventDefault();

        $('#loading_ajax').fadeIn('fast');
        eDirectory.Arcamailer.createList($('#newsletter-list-name').val(), function (response) {
            notify.clear();

            $('#newsletter-create-list').hide();

            $('#loading_ajax').fadeOut('fast');

            if (response.success) {
                return Navigation.showNewsletterWidget();
            }

            if (response.message) {
                notify.error(response.message, '', { fadeOut: 0 });
            }
        });
    });

    $('a[href="#tab-newsletter"]').on('click', function () {
        if (!eDirectory.Arcamailer.customerId) {
            Navigation.showNewsletterForm();
            Navigation.showPane('first-panel');
        } else if (!eDirectory.Arcamailer.listId) {
            Navigation.showNewsletterForm();
            Navigation.showPane('newsletter-create-list');
        } else {
            Navigation.showNewsletterWidget();
        }
    });
});
