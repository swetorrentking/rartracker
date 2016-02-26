(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(DonateRoutes);

	function DonateRoutes($stateProvider) {

		$stateProvider
			.state('donate', {
				parent		: 'header',
				url			: '/donate',
				views		: {
					'content@': {
						templateUrl		: '../app/donate/donate.template.html',
						controller		: 'DonateController as vm',
					}
				}
			});

	}

}());