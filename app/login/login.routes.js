(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(LoginRoutes);

	function LoginRoutes($stateProvider) {

		$stateProvider
			.state('login', {
				url				: '/login',
				views		: {
					'content@': {
						templateUrl 	: '../app/login/login.template.html',
						controller  	: 'LoginController',
						controllerAs	: 'vm',
					}
				}
			});

	}

}());