(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(UploadRoutes);

	function UploadRoutes($stateProvider) {

		$stateProvider
			.state('bonus', {
				parent		: 'header',
				url			: '/bonus',
				views		: {
					'content@': {
						templateUrl : '../app/bonus/bonus.template.html',
						controller  : 'BonusController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());