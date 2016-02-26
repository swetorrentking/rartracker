(function(){
	'use strict';

	var configs = {
		STATUS_CHECK_TIMER_LIMIT_MINUTES: 60 * 24,
		SITE_URL: 'http://127.0.0.1:1332/',
		API_BASE_URL: '/api/v1/',
		SUGGESTIONS_FORUM_ID: 25,
	};

	var userClasses = {
		STATIST:			{id: 0, name: 'Statist'},
		SKADIS:				{id: 1, name: 'Skådis'},
		FILMSTJARNA:		{id: 2, name: 'Filmstjärna'},
		REGISSAR:			{id: 3, name: 'Regissör'},
		PRODUCENT:			{id: 4, name: 'Producent'},
		UPLOADER:			{id: 6, name: 'Uploader'},
		VIP: 				{id: 7, name: 'VIP'},
		STAFF:				{id: 8, name: 'Staff'},
	};

	var categories = {
		DVDR_PAL:			{id: 1, text: 'DVDR PAL'},
		DVDR_CUSTOM:		{id: 2, text: 'DVDR CUSTOM'},
		DVDR_TV:			{id: 3, text: 'DVDR TV'},
		MOVIE_720P:			{id: 4, text: '720p Film'},
		MOVIE_1080P:		{id: 5, text: '1080p Film'},
		TV_720P: 			{id: 6, text: '720p TV'},
		TV_1080P:			{id: 7, text: '1080p TV'},
		TV_SWE:				{id: 8, text: 'Svensk TV'},
		AUDIOBOOKS:			{id: 9, text: 'Ljudböcker'},
		EBOOKS:				{id: 10, text: 'E-böcker'},
		EPAPERS:			{id: 11, text: 'E-tidningar'},
		MUSIC:				{id: 12, text: 'Musik'},
		BLURAY:				{id: 13, text: 'Full BluRay'},
		SUBPACK:			{id: 14, text: 'Subpack'},
		MOVIE_4K:			{id: 15, text: '4K Film'}
	};

	var cssDesigns = {
		STANDARD:			{ id: 0, text: 'Standard'},
		BLUE:				{ id: 2, text: 'Standard blå'},
		CUSTOM_EXTERNAL:	{ id: 1, text: 'Anpassad extern CSS'},
	};

	function AppConfig($stateProvider, $urlRouterProvider, $locationProvider, $compileProvider) {
		$compileProvider.debugInfoEnabled(false);
		$urlRouterProvider.otherwise('/');
		$locationProvider.html5Mode(true);
	}

	function ResourceExtension($resource, configs) {
		/* Extending angular ngResource with PATCH (update) method */
		return function (url, params, methods) {
			var defaults = {
				update: { method: 'patch', isArray: false }
			};
			methods = angular.extend(defaults, methods);
			return $resource(configs.API_BASE_URL + url, params, methods);
		};
	}

	function AppRun($rootScope, $state) {
		// To get ui-sref-active to work on child states
		// https://github.com/angular-ui/ui-router/issues/948#issuecomment-75342784
		$rootScope.$on('$stateChangeStart', function(evt, to, params) {
			if (to.redirectTo) {
				evt.preventDefault();
				$state.go(to.redirectTo, params);
			}
		});
	}

	angular
		.module('app.shared')
		.constant('configs', configs)
		.constant('userClasses', userClasses)
		.constant('categories', categories)
		.constant('cssDesigns', cssDesigns)
		.config(AppConfig)
		.factory('resourceExtension', ResourceExtension)
		.run(AppRun);

})();
