(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(LeechbonusRoutes);

	function LeechbonusRoutes($stateProvider) {

		$stateProvider
			.state('leechbonus', {
				parent		: 'header',
				url			: '/leechbonus',
				views		: {
					'content@': {
						templateUrl : '../app/leechbonus/leechbonus.template.html',
						controller	: 'LeechbonusController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());
