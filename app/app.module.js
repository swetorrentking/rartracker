(function(){
	'use strict';

	angular
		.module('app', [
			/* Shared modules */
			'app.core',
			'app.templates',
			'app.shared',
			/* Feature areas */
			'app.admin',
			'app.mailbox',
			'app.requests',
			'app.forums',
			'app.watcher',
			'app.swetv',
			'app.suggestions',
			'app.torrentLists',
		]);

	angular
		.module('app.core', [
			/* Angular modules */
			'ngSanitize',
			'ngResource',
			'ngCookies',
			/* 3rd-party modules */
			'ui.router',
			'ui.bootstrap',
			'chart.js',
			'pascalprecht.translate',
		]);

	angular
		.module('app.shared', []);

})();
