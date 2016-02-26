(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(NewsRoutes);

	function NewsRoutes($stateProvider) {

		$stateProvider
			.state('news', {
				parent		: 'header',
				url			: '/news',
				views		: {
					'content@': {
						templateUrl : '../app/news/news.template.html',
						controller  : 'NewsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());