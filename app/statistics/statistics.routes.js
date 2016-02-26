(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(StatisticsRoutes);

	function StatisticsRoutes($stateProvider) {

		$stateProvider
			.state('statistics', {
				parent		: 'header',
				url			: '/statistics',
				views		: {
					'content@': {
						templateUrl : '../app/statistics/statistics.template.html',
						controller  : 'StatisticsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());