(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(SignupRoutes);

	function SignupRoutes($stateProvider) {

		$stateProvider
			.state('signup', {
				url				: '/signup/:id',
				views		: {
					'content@': {
						templateUrl		: '../app/signup/signup.template.html',
						controller		: 'SignupController as vm',
					}
				}
			});

	}

}());
