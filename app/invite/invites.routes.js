(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(InvitesRoutes);

	function InvitesRoutes($stateProvider) {

		$stateProvider
			.state('invite', {
				parent		: 'header',
				url			: '/invite',
				views		: {
					'content@': {
						templateUrl : '../app/invite/invite.template.html',
						controller  : 'InviteController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				}
			});

	}

}());