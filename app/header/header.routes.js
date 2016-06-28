(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(HeaderRoutes);

	function HeaderRoutes($stateProvider) {

		$stateProvider
			.state('header', {
				abstract		: true,
				views			: {
					'menu': {
						templateUrl		: '../app/header/header.template.html',
						resolve			: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());
