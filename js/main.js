if (typeof jQuery === "undefined") {
    throw new Error("jQuery plugins need to be before this file");
}
$.AdminBSB = {};
$.AdminBSB.options = {
    colors: {
        orange: '#FF9800',
        deepOrange: '#FF5722'
    },
    leftSideBar: {
        scrollColor: 'rgba(0,0,0,0.5)',
        scrollWidth: '4px',
        scrollAlwaysVisible: false,
        scrollBorderRadius: '0',
        scrollRailBorderRadius: '0',
        scrollActiveItemWhenPageLoad: true,
        breakpointWidth: 1170
    },
    dropdownMenu: {
        effectIn: 'fadeIn',
        effectOut: 'fadeOut'
    }
}
$.AdminBSB.leftSideBar = {
    activate: function () {
        var _this = this;
        var $body = $('body');
        var $overlay = $('.overlay');

        $(window).click(function (e) {
            var $target = $(e.target);
            if (e.target.nodeName.toLowerCase() === 'i') { $target = $(e.target).parent(); }

            if (!$target.hasClass('bars') && _this.isOpen() && $target.parents('#leftsidebar').length === 0) {
                if (!$target.hasClass('js-right-sidebar')) $overlay.fadeOut();
                $body.removeClass('overlay-open');
            }
        });

        $.each($('.menu-toggle.toggled'), function (i, val) {
            $(val).next().slideToggle(0);
        });

        $.each($('.menu .list li.active'), function (i, val) {
            var $activeAnchors = $(val).find('a:eq(0)');

            $activeAnchors.addClass('toggled');
            $activeAnchors.next().show();
        });

        $('.menu-toggle').on('click', function (e) {
            var $this = $(this);
            var $content = $this.next();

            if ($($this.parents('ul')[0]).hasClass('list')) {
                var $not = $(e.target).hasClass('menu-toggle') ? e.target : $(e.target).parents('.menu-toggle');

                $.each($('.menu-toggle.toggled').not($not).next(), function (i, val) {
                    if ($(val).is(':visible')) {
                        $(val).prev().toggleClass('toggled');
                        $(val).slideUp();
                    }
                });
            }

            $this.toggleClass('toggled');
            $content.slideToggle(320);
        });

        _this.setMenuHeight(true);
        _this.checkStatusForResize(true);
        $(window).resize(function () {
            _this.setMenuHeight(false);
            _this.checkStatusForResize(false);
        });
		
		var path = window.location.href;	
		var asd = $('.breadcrumb li a:eq(1)').attr("href");
		$('.menu .list li a').each(function() {
			if ($(this).attr("href") === asd) {
				$(this).addClass('active');
				$(this).closest('li').addClass('active');
				$(this).closest('ul').collapse();
			}
			if (this.href === path) {
				$(this).addClass('active');
				$(this).closest('li').addClass('active');
				$(this).closest('ul').collapse();
			}
		});

        Waves.attach('.menu .list a', ['waves-block']);
        Waves.init();
    },
    setMenuHeight: function (isFirstTime) {
        if (typeof $.fn.slimScroll != 'undefined') {
            var configs = $.AdminBSB.options.leftSideBar;
            var height = ($(window).height() - $('.legal').outerHeight());
            var $el = $('.list');

            if (!isFirstTime) {
                $el.slimscroll({
                    destroy: true
                });
            }

            $el.slimscroll({
                height: height + "px",
                color: configs.scrollColor,
                size: configs.scrollWidth,
                alwaysVisible: configs.scrollAlwaysVisible,
                borderRadius: configs.scrollBorderRadius,
                railBorderRadius: configs.scrollRailBorderRadius
            });

            if ($.AdminBSB.options.leftSideBar.scrollActiveItemWhenPageLoad) {
                var item = $('.menu .list li.active')[0];
                if (item) {
                    var activeItemOffsetTop = item.offsetTop;
                    if (activeItemOffsetTop > 150) $el.slimscroll({ scrollTo: activeItemOffsetTop + 'px' });
                }
            }
        }
    },
    checkStatusForResize: function (firstTime) {
        var $body = $('body');
        var $openCloseBar = $('.navbar .navbar-header .bars');
        var width = $body.width();

        if (firstTime) {
            $body.find('.content, .sidebar').addClass('no-animate').delay(1000).queue(function () {
                $(this).removeClass('no-animate').dequeue();
            });
        }
		$body.addClass('ls-closed');
        $openCloseBar.fadeIn();
		/*
        if (width < $.AdminBSB.options.leftSideBar.breakpointWidth) {
            $body.addClass('ls-closed');
            $openCloseBar.fadeIn();
        }
        else {
            $body.removeClass('ls-closed');
            $openCloseBar.fadeOut();
        }
		*/
    },
    isOpen: function () {
        return $('body').hasClass('overlay-open');
    }
};

$.AdminBSB.navbar = {
    activate: function () {
        var $body = $('body');
        var $overlay = $('.overlay');

        $('.bars').on('click', function () {
            $body.toggleClass('overlay-open');
            if ($body.hasClass('overlay-open')) { $overlay.fadeIn(); } else { $overlay.fadeOut(); }
        });
    }
}
$.AdminBSB.input = {
    activate: function ($parentSelector) {
        $parentSelector = $parentSelector || $('body');

        $parentSelector.find('.form-control').focus(function () {
            $(this).closest('.form-line').addClass('focused');
        });

        $parentSelector.find('.form-control').focusout(function () {
            var $this = $(this);
            if ($this.parents('.form-group').hasClass('form-float')) {
                if ($this.val() == '') { $this.parents('.form-line').removeClass('focused'); }
            }
            else {
                $this.parents('.form-line').removeClass('focused');
            }
        });

        $parentSelector.on('click', '.form-float .form-line .form-label', function () {
            $(this).parent().find('input').focus();
        });
    }
}

$.AdminBSB.select = {
    activate: function () {
        if ($.fn.selectpicker) { $('select:not(.ms)').selectpicker(); }
    }
}

$.AdminBSB.dropdownMenu = {
    activate: function () {
        var _this = this;

        $('.dropdown, .dropup, .btn-group').on({
            "show.bs.dropdown": function () {
                var dropdown = _this.dropdownEffect(this);
                _this.dropdownEffectStart(dropdown, dropdown.effectIn);
            },
            "shown.bs.dropdown": function () {
                var dropdown = _this.dropdownEffect(this);
                if (dropdown.effectIn && dropdown.effectOut) {
                    _this.dropdownEffectEnd(dropdown, function () { });
                }
            },
            "hide.bs.dropdown": function (e) {
                var dropdown = _this.dropdownEffect(this);
                if (dropdown.effectOut) {
                    e.preventDefault();
                    _this.dropdownEffectStart(dropdown, dropdown.effectOut);
                    _this.dropdownEffectEnd(dropdown, function () {
                        dropdown.dropdown.removeClass('open');
                    });
                }
            }
        });

        Waves.attach('.dropdown-menu li a', ['waves-block']);
        Waves.init();
    },
    dropdownEffect: function (target) {
        var effectIn = $.AdminBSB.options.dropdownMenu.effectIn, effectOut = $.AdminBSB.options.dropdownMenu.effectOut;
        var dropdown = $(target), dropdownMenu = $('.dropdown-menu', target);

        if (dropdown.length > 0) {
            var udEffectIn = dropdown.data('effect-in');
            var udEffectOut = dropdown.data('effect-out');
            if (udEffectIn !== undefined) { effectIn = udEffectIn; }
            if (udEffectOut !== undefined) { effectOut = udEffectOut; }
        }

        return {
            target: target,
            dropdown: dropdown,
            dropdownMenu: dropdownMenu,
            effectIn: effectIn,
            effectOut: effectOut
        };
    },
    dropdownEffectStart: function (data, effectToStart) {
        if (effectToStart) {
            data.dropdown.addClass('dropdown-animating');
            data.dropdownMenu.addClass('animated dropdown-animated');
            data.dropdownMenu.addClass(effectToStart);
        }
    },
    dropdownEffectEnd: function (data, callback) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        data.dropdown.one(animationEnd, function () {
            data.dropdown.removeClass('dropdown-animating');
            data.dropdownMenu.removeClass('animated dropdown-animated');
            data.dropdownMenu.removeClass(data.effectIn);
            data.dropdownMenu.removeClass(data.effectOut);

            if (typeof callback == 'function') {
                callback();
            }
        });
    }
}

var edge = 'Microsoft Edge';
var ie10 = 'Internet Explorer 10';
var ie11 = 'Internet Explorer 11';
var opera = 'Opera';
var firefox = 'Mozilla Firefox';
var chrome = 'Google Chrome';
var safari = 'Safari';

$.AdminBSB.browser = {
    activate: function () {
        var _this = this;
        var className = _this.getClassName();

        if (className !== '') $('html').addClass(_this.getClassName());
    },
    getBrowser: function () {
        var userAgent = navigator.userAgent.toLowerCase();

        if (/edge/i.test(userAgent)) {
            return edge;
        } else if (/rv:11/i.test(userAgent)) {
            return ie11;
        } else if (/msie 10/i.test(userAgent)) {
            return ie10;
        } else if (/opr/i.test(userAgent)) {
            return opera;
        } else if (/chrome/i.test(userAgent)) {
            return chrome;
        } else if (/firefox/i.test(userAgent)) {
            return firefox;
        } else if (!!navigator.userAgent.match(/Version\/[\d\.]+.*Safari/)) {
            return safari;
        }

        return undefined;
    },
    getClassName: function () {
        var browser = this.getBrowser();

        if (browser === edge) {
            return 'edge';
        } else if (browser === ie11) {
            return 'ie11';
        } else if (browser === ie10) {
            return 'ie10';
        } else if (browser === opera) {
            return 'opera';
        } else if (browser === chrome) {
            return 'chrome';
        } else if (browser === firefox) {
            return 'firefox';
        } else if (browser === safari) {
            return 'safari';
        } else {
            return '';
        }
    }
}

function activateNotificationAndTasksScroll(heightxd) {
    $('.navbar-right .dropdown-menu .body .menu#okunmamiskonusmalar').slimscroll({
        height: heightxd,
        color: 'rgba(0,0,0,0.5)',
        size: '4px',
        alwaysVisible: false,
        borderRadius: '0',
        railBorderRadius: '0'
    });
}

$(function () {
    $.AdminBSB.browser.activate();
    $.AdminBSB.leftSideBar.activate();
    $.AdminBSB.navbar.activate();
    $.AdminBSB.dropdownMenu.activate();
    $.AdminBSB.input.activate();
    $.AdminBSB.select.activate();



    setTimeout(function () { $('.page-loader-wrapper').fadeOut(); }, 50);
	
	jQuery(document).ready(function($)
	{
		$.ajaxSetup({
			headers: { 'sbmtoken': $('meta[name="sbmtoken"]').attr('content') }
		});
		$(".LogOutButton").on('click',(function(e)
		{
			$.ajax(
			{
				url: "signout",
				type: "POST",
				contentType: false,
				cache: false,
				processData:false,
				success: function(data)
				{
					if(data == 1)
					{
						window.location.href = 'signin';
					}
				}	 						
			});
		}));
        (function BildirimleriGetir() {
            $.ajax(
                {
                    url: "notifications",
                    type: "POST",
                    contentType: false,
                    cache: false,
                    processData:false,
                    dataType: 'json',
                    success: function(data)
                    {
                        if(data.hamburger == 1)
                        {
                            $(".notification-icon").addClass("yanipsonen");
                            $("#okunmamiskonusmalar").html(data.konusmalar);
                            activateNotificationAndTasksScroll(254);
                        }
                        else
                        {
                            if($(".notification-icon").hasClass("yanipsonen"))
                            {
                                $(".notification-icon").removeClass("yanipsonen");
                            }
                            if($("#okunmamiskonusmalar").find("li").length === 0) {
                                $("#okunmamiskonusmalar").append("<li style='height:100%;padding-top:15px;text-align:center;'>No new messages.</li>");
                                activateNotificationAndTasksScroll(50);
                            }
                        }
                        if(data.menu != 0)
                        {
                            if($(".notification-icon > .label-count").is(":hidden"))
                            {
                                $(".notification-icon > .label-count").show();
                            }
                            $(".notification-icon > .label-count").text(data.menu);
                        }
                        else if(data.menu == 0)
                        {
                            if($(".notification-icon > .label-count").is(":visible"))
                            {
                                $(".notification-icon > .label-count").hide();
                            }
                        }
                    },
                    complete: function() {
                        setTimeout(BildirimleriGetir, 7500);
                    }
                });
        })();
        if (document.fullscreenEnabled) {
            var btn = document.getElementById("fullscreen-toggle");
            btn.addEventListener("click", function (event) {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }, false);
        }
        $('body').on('submit', '#editProfileForm', function (e) {
            e.preventDefault();

            $('.editProfileButton').prop('disabled', true);
            $('.editProfileButton').html("Profile editing...");

            $("#editProfileResult").empty();

            $.ajax(
                {
                    url: "edit-profile",
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    dataType: 'json',
                    success: function (data) {
                        setTimeout(function () {
                            $('.editProfileButton').prop('disabled', false);
                            $('.editProfileButton').html("Edit Profile");
                            if (data.sonuc == 0) {
                                $("#editProfileResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                            }
                            if (data.sonuc == 1) {
                                $("#editProfileResult").html("<div class='alert alert-success'><strong>Successful!</strong> Profile successfully edited.</div>");
                                $("#editProfileForm #image").val("");
                                $("ul.navbar-right li#profileDropdown .uye-avatar-yeri").attr('src', data.photo);
                                $("ul.navbar-right li#profileDropdown #profilePhoto").attr('src', data.photo);
                                $("ul.navbar-right li#profileDropdown #profileName").html("<strong>"+data.name+"</strong>");
                            }
                            if (data.sonuc == 2) {
                                $("#editProfileResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill your name in the form.</div>");
                            }
                            if (data.sonuc == 3) {
                                $("#editProfileResult").html("<div class='alert alert-danger'><strong>Error:</strong> Name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                            }
                            if (data.sonuc == 4) {
                                $("#editProfileResult").html("<div class='alert alert-danger'><strong>Error:</strong> Profile photo can only be in jpeg, png and jpg format.</div>");
                            }
                            if (data.sonuc == 5) {
                                $("#editProfileResult").html("<div class='alert alert-success'><strong>Successful!</strong> Profile successfully edited.</div>");
                                $("#editProfileForm #image").val("");
                                $("ul.navbar-right li#profileDropdown #profileName").html("<strong>"+data.name+"</strong>");
                            }
                        }, 1000);
                    }
                });
        });
        $('body').on('submit', '#editSchoolForm', function (e) {
            e.preventDefault();

            $('.editSchoolButton').prop('disabled', true);
            $('.editSchoolButton').html("Profile editing...");

            $("#editSchoolResult").empty();

            $.ajax(
                {
                    url: "edit-school",
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        setTimeout(function () {
                            $('.editSchoolButton').prop('disabled', false);
                            $('.editSchoolButton').html("Edit School");
                            if (data == 0) {
                                $("#editSchoolResult").html("<div class='alert alert-danger'><strong>Error:</strong> Teknik bir problem yaşandı. Lütfen tekrar deneyin.</div>");
                            }
                            if (data == 1) {
                                $("#editSchoolResult").html("<div class='alert alert-success'><strong>Successful!</strong> School successfully edited.</div>");
                            }
                            if (data == 2) {
                                $("#editSchoolResult").html("<div class='alert alert-danger'><strong>Error:</strong> Please fill school name in the form.</div>");
                            }
                            if (data == 3) {
                                $("#editSchoolResult").html("<div class='alert alert-danger'><strong>Error:</strong> School name can have a minimum of 3 characters and a maximum of 64 characters.</div>");
                            }
                        }, 1000);
                    }
                });
        });
	});
});
