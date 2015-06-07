(function($) {
    var is_upress_available = $('#upress-available').val() === '1' ? true : false;
    var is_valid_api_key = $('#api-key-valid').val() === '1' ? true : false;
    var api_key = $('#api_key').val();

    var toggleSwitchSpinner = function(input, val) {
        val = (typeof val === "undefined" || typeof val === null) ? false : val;

        var lc_switch = $(input);
        var spinner = lc_switch.parents('.lcs_wrap').find('.spinner');
        var the_switch = lc_switch.parents('.lcs_wrap').find('.lcs_switch');
        var is_shown = spinner.is(':visible');

        if(val === "hide" || (!val && is_shown)) {
            spinner.fadeOut('fast', function() {
                the_switch.fadeIn('fast');
            });
            return;
        }
        if(val === "show" || (!val && !is_shown)) {
            the_switch.fadeOut('fast', function() {
                spinner.fadeIn('fast');
            });
            return;
        }
    };

    var flashErrorMessage = function(message) {
        var error_wrap = $('.api-error');
        var error_text = $('span', error_wrap);

        error_text.text(message);
        error_wrap.fadeIn('fast', function() {
            setTimeout(function() {
                error_wrap.fadeOut('fast');
            }, 10000);
        });

        $('body').scrollTop(0);
    };

    var flashSuccessMessage = function(message) {
        var msg_wrap = $('.api-success');

        msg_wrap.find('p').text(message);
        msg_wrap.fadeIn('fast', function() {
            setTimeout(function() {
                msg_wrap.fadeOut('fast');
            }, 10000);
        });

        $('body').scrollTop(0);
    };

    var initAjax = function() {
        $('body').on('lcs-statuschange', '.switch', function() {
            var status = ($(this).is(':checked')) ? 1 : 0;
            var self = $(this);

            if(self.data('noevent')) {
                self.data('noevent', false);
                return;
            }

            toggleSwitchSpinner(self, 'show');

            $.post(ajaxurl, {
                'action': 'send_request',
                'api_key': api_key,
                'api_action': self.data('action'),
                'value': status,
                'type': 'set',
                '_nonce': upressAjax._nonce
            }, function(response) {
                //console.log(response);

                if(response.status == "fail") {
                    flashErrorMessage(response.data.message);
                    self.data('noevent', true);
                    if(status) {
                        self.lcs_off();
                    } else {
                        self.lcs_on();
                    }
                }

                toggleSwitchSpinner(self, 'hide');
            });
        }).on('click', 'button[data-action], input[type=button][data-action]', function(e) {
            e.preventDefault();

            var self = $(this);
            var wrapper = self.parents('.ajax-button-wrapper');
            self.css({
                width: self.outerWidth(),
                height: self.outerHeight()
            }).find('.text').fadeOut('fast', function() {
                self.find('.spinner').fadeIn('fast');
            }).end().attr('disabled', true);

            $.post(ajaxurl, {
                'action': 'send_request',
                'api_key': api_key,
                'api_action': self.data('action'),
                'value': 1,
                'type': 'set',
                '_nonce': upressAjax._nonce
            }, function(response) {
                //console.log(response);

                if (response.status == "fail") {
                    flashErrorMessage(response.data.message);
                }
                if(response.status == "success") {
                    if(self.data('success-message')) {
                        flashSuccessMessage(self.data('success-message'));
                    } else {
                        flashSuccessMessage(upressAjax.requestSuccess);
                    }
                }

                self.find('.spinner').fadeOut('fast', function () {
                    self.find('.text').fadeIn('fast').end().css( {
                        width: '',
                        height: ''
                    }).removeAttr('disabled');
                });
            });
        });
    };

    var initButtons = function() {
        $('button[data-action]').each(function() {
            var self = $(this);
            var text = self.text();
            self.text('');
            self.wrap($('<div class="ajax-button-wrapper" />')).append('<span class="text">'+text+'</span>').append($('<div class="spinner" />'));
        });
    };

    var initSwitches = function() {
        $('input.switch').lc_switch(upressAjax.on, upressAjax.off);

        $('input.switch').each(function() {
            var self = $(this);
            $('<div class="spinner" />').insertBefore(self);
        });
    };

    var getInitialValues = function(sectionId) {
        var requests = [];
        var section = $('#' + sectionId);
        $('.overlay-loader', section).fadeIn('fast');
        $('.reload-btn', section).fadeOut('fast');

        $.when($('input.switch', section).each(function() {
            var self = $(this);

            // initial value update
            requests.push($.post(ajaxurl, {
                'action': 'send_request',
                'api_key': api_key,
                'api_action': self.data('action'),
                'value': 0,
                'type': 'get',
                '_nonce': upressAjax._nonce
            }, function(response) {
                //console.log(response);

                if(response.status == "success") {
                    var data = response.data.data;
                    self.data('noevent', true);
                    if(data && data.value === "1") {
                        self.lcs_on();
                    } else {
                        self.lcs_off();
                    }
                } else {
                    flashErrorMessage(response.data.message);
                }
            }));
        })).done($.when.apply(null, requests).done(function() {
            // wait for all requests to finish and then hide loader
            $('.overlay-loader', section).fadeOut('fast');
            $('.reload-btn', section).fadeIn('fast');
        }));

    };

    var initSections = function() {
        postboxes.add_postbox_toggles( 'upress-link' );

        $('body').on('click', '.reload-btn a', function(e) {
            e.preventDefault();
            var section = $(this).parents('.upress-section');
            if(!$('.overlay-loader', section).is(':visible')) {
                getInitialValues(section.attr('id'));
            }
        });

        $('.upress-section').each(function() {
            var self = $(this);
            var loaders = $('<div class="overlay"><p>'+ self.data('not-available-text') +'</p></div><div class="overlay-loader"><p><i class="spinner"></i></p></div>');
            loaders.appendTo(self.find('.inside'));

            self.find('.overlay-loader').fadeIn('fast');
            $('.reload-btn', self).fadeOut('fast');

            if(self.data('check-availability')) {
                // check if section is available and only if it is initialize data
                $.post(ajaxurl, {
                    'action': 'send_request',
                    'api_key': api_key,
                    'api_action': self.data('check-availability'),
                    'value': 0,
                    'type': 'get',
                    '_nonce': upressAjax._nonce
                }, function(response) {
                    //console.log(response);

                    if(response.status == "success") {
                        var data = response.data.data
                        if(data && data.value === "1") {
                            getInitialValues(self.attr('id'));
                        } else {
                            $('.overlay', self).fadeIn('fast');
                            $('.overlay-loader', self).fadeOut('fast');
                            $('.reload-btn', self).fadeOut('fast');
                        }
                    } else {
                        flashErrorMessage(response.data.message);
                    }
                });
            } else {
                // if the section always available initialize data
                getInitialValues(self.attr('id'));
            }
        });
    };

    var init = function() {
        initSwitches();
        initButtons();
        initAjax();
        initSections();

        if(api_key.length <= 0) {
            $('.no-api-key').show();
        }
        if(is_upress_available && api_key.length > 0 && is_valid_api_key) {
            //$('.postbox .overlay').hide();
        } else {
            $('.postbox .overlay').show();
        }
    };

    return init();
}(jQuery));