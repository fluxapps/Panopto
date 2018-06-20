/**
 * srWaiter
 *
 * GUI-Overlay
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
var srWaiter = {
	possible_types: ['waiter', 'percentage'],
	type: 'waiter',
	count: 0,
	timer: null,

	init: function (type) {
		this.type = type ? type : this.type;
		if (this.type == 'waiter') {
			console.log('srWaiter: added sr_waiter to body');
			$('body').append('<div id="sr_waiter" class="sr_waiter"></div>')
		} else {
			console.log('srWaiter: added sr_percentage to body');
			$('body').append('<div id="sr_waiter" class="sr_percentage">' +
				'<div class="progress" >' +
				'<div id="sr_progress" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">' +
				'</div></div></div>')
		}
	},

	show: function () {
		if (this.count == 0) {
			this.timer = setTimeout(function () {
				$('#sr_waiter').show();
			}, 10);

		}
		this.count = this.count + 1;
	},
	/**
	 *
	 * @param type
	 */
	reinit: function (type) {
		var type = type ? type : this.type;
		this.count = 0;

		$('#sr_waiter').attr('id', 'sr_waiter2');
		this.init(type);
		$('#sr_waiter2').remove();
	},

	hide: function () {
		this.count = this.count - 1;
		if (this.count == 0) {
			window.clearTimeout(this.timer);
			$('#sr_waiter').fadeOut(200);
		}
	},
	/**
	 * @param percent
	 */
	setPercentage: function (percent) {
		$('#sr_progress').css('width', percent + '%').attr('aria-valuenow', percent);
	},
	/**
	 * @param dom_selector_string
	 */
	addListener: function (dom_selector_string) {
		var self = this;
		$(document).ready(function () {
			$(dom_selector_string).on("click", function () {

				self.show();
			});
		});
	},
	addLinkOverlay: function (dom_selector_string) {
		var self = this;
		console.log('srWaiter: registered LinkOverlay: ' + dom_selector_string);
		$(document).ready(function () {
			$(dom_selector_string).on("click", function (e) {
				e.preventDefault();
				console.log('srWaiter: clicked on registered link');
				self.show();
				var href = $(this).attr('href');
				setTimeout(function () {
					document.location.href = href;
				}, 1000);
			});
		});
	},
};