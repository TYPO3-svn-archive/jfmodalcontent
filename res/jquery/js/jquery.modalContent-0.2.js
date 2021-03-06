/*!
 * jQuery ModalContent v0.2
 * http://typo3.cms-jack.ch/
 *
 * Copyright 2012, Juergen Furrer
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * Date: 2012-03-21 21:40
 */
(function($) {
	$.fn.modalContent = function(opts) {
		var defaults = {
			inAnimation: 'top',       // Used animation (top, right, bottom, right, fade, slide)
			overlayFadeDuration: 200, // fade duration for the overlay div

			inDelay: 0,             // delay in order to show the content
			inTransition: 'swing',  // transition of the inAnimation
			inDuration: 1000,       // duration of the inAnimation

			outDelay: 5000,         // delay in order to hide the content
			outTransition: 'swing', // transition of the inAnimation
			outDuration: 1000,      // duration of the inAnimation

			// Callbacks function (options)
			onBefore: null, // callback before all starts
			onBegin: null,  // callback at the begin of the animation
			onEnd: null,    // callback at the end of the animation
			onClose: null,  // callback after closing

			disabled: false,  // if true, the animation will not start (set it in "onBefore"-callback)
			debug: false,     // console debugging
			closeOnEsc: false // close content if esc is pressed
		};

		var options = $.extend(defaults, opts);

		$(window).resize(function() {
			var $maxSize = $.getMaxSize();
			$('.modalContent-modal, .modalContent-overlay').css({
				width: $maxSize.width,
				height: $maxSize.height
			});
		});

		$.getMaxSize = function() {
			return {
				width:  $(window).width() > $(document).width() ? $(window).width() : $(document).width(),
				height: $(window).height() > $(document).height() ? $(window).height() : $(document).height()
			};
		};

		$.callOnBefore = function() {
			var onBefore = options.onBefore;
			if ($.isFunction(onBefore)) {
				onBefore(options);
			}
		};

		$.callOnBegin = function() {
			var onBegin = options.onBegin;
			if ($.isFunction(onBegin)) {
				onBegin(options);
			}
		};

		$.callOnEnd = function() {
			var onEnd = options.onEnd;
			if ($.isFunction(onEnd)) {
				onEnd(options);
			}
		};

		$.callOnClose = function() {
			var onClose = options.onClose;
			if ($.isFunction(onClose)) {
				onClose(options);
			}
		};

		return this.each(function() {
			var $obj = $(this);
			var $parent = $obj.parent();

			$.callOnBefore();

			if (options.disabled === true) {
				if (options.debug) {
					console.log("disabled: true");
				}
				return true;
			}

			// Add the overlay if not exists
			if ($('.modalContent-overlay').length < 1) {
				$('body').append('<div class="modalContent-overlay"></div>');
			}

			// get the max size for outer and overlay
			var $maxSize = $.getMaxSize();

			// define Overlay calss and size
			var $overlay = $('.modalContent-overlay');
			$overlay.css({
				width: $maxSize.width,
				height: $maxSize.height
			}).hide();

			// Close Delegate
			var closeDelegate = function() {
				$obj.stop();
				$overlay.fadeOut(options.overlayFadeDuration, function() {
					$parent.remove();
				});
			};

			// define DOM of the content
			$obj.addClass('modalContent').wrap('<div class="modalContent-outer"></div>').parent().css({
				width: $maxSize.width,
				height: $maxSize.height,
				zIndex: 1
			});

			// close if click element with class "close"
			$parent.find('.close').bind("click", function() {
				closeDelegate();
			});

			if (options.inAnimation == 'fade') {
				$obj.addClass('modalContent-fade').hide();
				$overlay.
					delay(options.inDelay).
					fadeIn(options.overlayFadeDuration, function() {
						if (options.closeOnEsc) {
							$(window).keypress(function(e) { if (e.keyCode == 27) { closeDelegate(); } });
						}
						$.callOnBegin();
						$obj.
							fadeIn(options.inDuration, options.inTransition).
							delay(options.outDelay).
							fadeOut(options.outDuration, options.outTransition, function() {
								$.callOnEnd();
								$overlay.fadeOut(options.overlayFadeDuration, function() {
									$.callOnClose();
									$parent.remove();
								});
							});
					});
			} else if (options.inAnimation == 'slide') {
				$obj.addClass('modalContent-slide').hide();
				$overlay.
					delay(options.inDelay).
					fadeIn(options.overlayFadeDuration, function() {
						if (options.closeOnEsc) {
							$(window).keypress(function(e) { if (e.keyCode == 27) { closeDelegate(); } });
						}
						$.callOnBegin();
						$obj.
							slideDown(options.inDuration, options.inTransition).
							delay(options.outDelay).
							slideUp(options.outDuration, options.outTransition, function() {
								$.callOnEnd();
								$overlay.fadeOut(options.overlayFadeDuration, function() {
									$.callOnClose();
									$parent.remove();
								});
							});
					});
			} else {
				var offset = {};
				var cssHide = {};
				var animateIn = {};
				var animateOut = {};
				$obj.show();
				switch (options.inAnimation) {
					case 'bottom' :
						$obj.addClass('modalContent-bottom');
						offset = $obj.offset();
						var hideBottom = $(document).outerHeight() - offset.top;
						cssHide = {top: hideBottom};
						animateIn = {top: 0};
						animateOut = cssHide;
						break;
					case 'left' :
						$obj.addClass('modalContent-left');
						offset = $obj.offset();
						var hideLeft = ($obj.outerWidth()*-1) - offset.left;
						cssHide = {left: hideLeft};
						animateIn = {left: 0};
						animateOut = cssHide;
						break;
					case 'right' :
						$obj.addClass('modalContent-right');
						offset = $obj.offset();
						var hideRight = $(document).outerWidth() - offset.left;
						cssHide = {left: hideRight};
						animateIn = {left: 0};
						animateOut = cssHide;
						break;
					default : // default = top
						$obj.addClass('modalContent-top');
						offset = $obj.offset();
						var hideTop = ($obj.outerHeight()*-1) - offset.top;
						cssHide = {top: hideTop};
						animateIn = {top: 0};
						animateOut = cssHide;
						break;
				}
				$obj.hide();
				$overlay.
					delay(options.inDelay).
					fadeIn(options.overlayFadeDuration, function() {
						if (options.closeOnEsc) {
							$(window).keypress(function(e) { if (e.keyCode == 27) { closeDelegate(); } });
						}
						$.callOnBegin();
						$obj.
							css(cssHide).
							show().
							animate(animateIn, options.inDuration, options.inTransition).
							delay(options.outDelay).
							animate(animateOut, options.outDuration, options.outTransition, function() {
								$.callOnEnd();
								$overlay.fadeOut(options.overlayFadeDuration, function() {
									$.callOnClose();
									$parent.remove();
								});
							});
					});
			}
			return true;
		});
	};
})(jQuery);