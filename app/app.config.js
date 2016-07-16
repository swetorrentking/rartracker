(function(){
	'use strict';

	var configs = {
		STATUS_CHECK_TIMER_LIMIT_MINUTES: 60 * 24,
		SITE_URL: 'http://127.0.0.1',
		API_BASE_URL: '/api/v1/',
		SUGGESTIONS_FORUM_ID: 4,
		DEFAULT_LANGUAGE: 'en',
		DONATIONS_CURRENCY: 'USD',
	};

	var languageSupport = [
		{ id: 'en', name: 'English' },
		{ id: 'sv', name: 'Swedish' },
	];

	var userClasses = {
		EXTRA:				{id: 0, name: 'Extra'},
		ACTOR:				{id: 1, name: 'Actor'},
		MOVIE_STAR:			{id: 2, name: 'Movie star'},
		DIRECTOR:			{id: 3, name: 'Director'},
		PRODUCENT:			{id: 4, name: 'Producer'},
		UPLOADER:			{id: 6, name: 'Uploader'},
		VIP: 				{id: 7, name: 'VIP'},
		STAFF:				{id: 8, name: 'Staff'},
	};

	var categories = {
		DVDR_PAL:			{id: 1, text: 'DVDR PAL'},
		DVDR_CUSTOM:		{id: 2, text: 'DVDR CUSTOM'},
		DVDR_TV:			{id: 3, text: 'DVDR TV'},
		MOVIE_720P:			{id: 4, text: '720p Movie'},
		MOVIE_1080P:		{id: 5, text: '1080p Movie'},
		TV_720P: 			{id: 6, text: '720p TV'},
		TV_1080P:			{id: 7, text: '1080p TV'},
		TV_SWE:				{id: 8, text: 'Swedish TV'},
		AUDIOBOOKS:			{id: 9, text: 'Audiobook'},
		EBOOKS:				{id: 10, text: 'E-book'},
		EPAPERS:			{id: 11, text: 'E-paper'},
		MUSIC:				{id: 12, text: 'Music'},
		BLURAY:				{id: 13, text: 'Full BluRay'},
		SUBPACK:			{id: 14, text: 'Subpack'},
		MOVIE_4K:			{id: 15, text: '4K Movie'}
	};

	var cssDesigns = {
		STANDARD:			{ id: 0, text: 'Default'},
		BLUE:				{ id: 2, text: 'Default blue'},
		CUSTOM_EXTERNAL:	{ id: 1, text: 'Anpassad extern CSS'},
	};

	function AppConfig($stateProvider, $urlRouterProvider, $locationProvider, $compileProvider, $httpProvider, $translateProvider, configs) {
		$compileProvider.debugInfoEnabled(false);
		$urlRouterProvider.otherwise('/');
		$locationProvider.html5Mode(true);
		$httpProvider.useApplyAsync(true);
		$translateProvider
			.useStaticFilesLoader({
				prefix: '/locales/locale-',
				suffix: '.json'
			})
			.useSanitizeValueStrategy(null)
			.preferredLanguage(localStorage.getItem('default-language') ? localStorage.getItem('default-language') : configs.DEFAULT_LANGUAGE);
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
		.constant('languageSupport', languageSupport)
		.config(AppConfig)
		.factory('resourceExtension', ResourceExtension)
		.run(AppRun);

})();
