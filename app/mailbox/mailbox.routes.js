(function(){
	'use strict';

	angular
		.module('app.mailbox')
		.config(MailboxRoutes);

	function MailboxRoutes($stateProvider) {

		$stateProvider
			.state('mailbox', {
				parent		: 'header',
				url			: '/mailbox?page&view',
				views		: {
					'content@': {
						templateUrl : '../app/mailbox/mailbox.template.html',
						controller  : 'MailboxController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: {
						value: '1',
						squash: true
					},
					view: {
						value: '0',
						squash: true
					}
				}
			});

	}

}());
