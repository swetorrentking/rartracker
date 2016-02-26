(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(LogRoutes);

	function LogRoutes($stateProvider) {

		$stateProvider
			.state('log', {
				parent		: 'header',
				url			: '/log?page&search',
				views		: {
					'content@': {
						templateUrl : '../app/log/log.template.html',
						controller  : 'LogController as vm'
					}
				},
				params: {
					page: {
						value: '1',
						squash: true
					},
					search: {
						value: '',
						squash: true
					}
				}
			});

	}

}());