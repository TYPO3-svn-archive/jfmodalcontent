/*!
 * jQuery ModalContent v0.1
 * http://typo3.cms-jack.ch/
 *
 * Copyright 2012, Juergen Furrer
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Date: 2012-03-15 21:06:00
 */
(function($){
	$.fn.modalContent = function(opts) {
		var defaults = {
			inAnimation: 'top',     // from where will the content slide in (top, right, bottom, right)

			inDelay: 0,             // wait until the animation begins
			inTransition: 'swing',  // transition of the inAnimation
			inDuration: 1000,       // duration of the inAnimation

			outDelay: 5000,         // time to show the content
			outTransition: 'swing', // transition of the inAnimation
			outDuration: 1000       // duration of the inAnimation
		};

		var options = $.extend(defaults, opts);

		// there will be no scroll bar...
		$(this).addClass('modalContent').wrap('<div class="modalContent-outer"></div>').parent().css({
			width: $(window).width(),
			height: $(window).height()
		});

		$(window).resize(function() {
			$('.modalContent-outer').css({
				width: $(window).width(),
				height: $(window).height()
			});
		});

		return this.each(function() {
			var obj = $(this);
			var offset = {};
			var cssHide = {};
			var animateIn = {};
			var animateOut = {};

			if (options.inAnimation == 'fade') {
				obj.addClass('modalContent-fade');
				obj.
					hide().
					delay(options.inDelay).
					fadeIn(options.inDuration, options.inTransition).
					delay(options.outDelay).
					fadeOut(options.outDuration, options.outTransition, function() {
						$(this).parent().remove();
					});
			} else if (options.inAnimation == 'slide') {
				obj.addClass('modalContent-slide');
				obj.
					hide().
					delay(options.inDelay).
					slideDown(options.inDuration, options.inTransition).
					delay(options.outDelay).
					slideUp(options.outDuration, options.outTransition, function() {
						$(this).parent().remove();
					});
			} else {
				switch (options.inAnimation) {
					case 'bottom' :
						obj.addClass('modalContent-bottom');
						offset = obj.offset();
						var hideBottom = $(document).outerHeight() - offset.top;
						cssHide = {top: hideBottom};
						animateIn = {top: 0};
						animateOut = cssHide;
						break;
					case 'left' :
						obj.addClass('modalContent-left');
						offset = obj.offset();
						var hideLeft = (obj.outerWidth()*-1) - offset.left;
						cssHide = {left: hideLeft};
						animateIn = {left: 0};
						animateOut = cssHide;
						break;
					case 'right' :
						obj.addClass('modalContent-right');
						offset = obj.offset();
						var hideRight = $(document).outerWidth() - offset.left;
						cssHide = {left: hideRight};
						animateIn = {left: 0};
						animateOut = cssHide;
						break;
					default : // default = top
						obj.addClass('modalContent-top');
						offset = obj.offset();
						var hideTop = (obj.outerHeight()*-1) - offset.top;
						cssHide = {top: hideTop};
						animateIn = {top: 0};
						animateOut = cssHide;
						break;
				}
				obj.
					css(cssHide).
					hide().
					delay(options.inDelay).
					show().
					animate(animateIn, options.inDuration, options.inTransition).
					delay(options.outDelay).
					animate(animateOut, options.outDuration, options.outTransition, function() {
						$(this).parent().remove();
					});
			}
		});
	};
})(jQuery);