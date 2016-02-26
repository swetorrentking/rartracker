(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(InfoRoutes);

	function InfoRoutes($stateProvider) {

		$stateProvider
			.state('info', {
				parent		: 'header',
				url			: '/info',
				views			: {
					'content@': {
						templateUrl : '../app/info/nav.template.html',
					}
				},
				redirectTo	: 'info.info'
			})
			.state('info.info', {
				url			: '/',
				templateUrl	: '../app/info/info.template.html',
				controller	: 'InfoController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('info.rules', {
				url			: '/rules',
				templateUrl	: '../app/info/rules.template.html',
				controller	: 'RulesController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('info.faq', {
				url			: '/faq',
				templateUrl	: '../app/info/faq.template.html',
				controller	: 'FaqController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('info.irc', {
				url			: '/irc',
				templateUrl	: '../app/info/irc.template.html',
			});

	}

}());
