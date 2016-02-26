(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(StatisticsRoutes);

	function StatisticsRoutes($stateProvider) {

		$stateProvider
			.state('recover', {
				url			: '/recover/:secret',
				views		: {
					'content@': {
						templateUrl		: '../app/recover/recover.template.html',
						controller		: 'RecoverController',
						controllerAs	: 'vm',
					}
				}
			});

	}

}());