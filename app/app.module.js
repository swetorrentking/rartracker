(function(){
	'use strict';

	angular.module('tracker', [
		'tracker.templates',
		'tracker.configs',
		'tracker.services',
		'tracker.resources',
		'tracker.filters',
		'tracker.directives',
		'tracker.controllers',
		'ui.router',
		'ui.bootstrap',
		'chart.js']);

	/* To put it first in load order */
	angular.module('tracker.controllers', []);
	angular.module('tracker.directives', []);
	angular.module('tracker.templates', []);
})();