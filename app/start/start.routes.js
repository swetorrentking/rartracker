(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(StartRoutes);

	function StartRoutes($stateProvider) {

		$stateProvider
			.state('start', {
				parent		: 'header',
				url			: '/',
				views		: {
					'content@': {
						templateUrl:	'../app/start/start.template.html',
						controller:		'StartController as vm',
					}
				}
			})
			.state('start-edit', {
				parent		: 'header',
				url			: '/start-edit',
				views		: {
					'content@': {
						templateUrl : '../app/start/edit-start.template.html',
						controller  : 'EditStartController as vm',
					}
				},
				resolve		: { user: authService => authService.getPromise() }
			});

	}

}());