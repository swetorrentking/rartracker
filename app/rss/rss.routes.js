(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(RssRoutes);

	function RssRoutes($stateProvider) {

		$stateProvider
			.state('rss', {
				parent		: 'header',
				url			: '/rss',
				views		: {
					'content@': {
						templateUrl : '../app/rss/rss.template.html',
						controller  : 'RssController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

	}());