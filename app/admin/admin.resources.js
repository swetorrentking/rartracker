(function(){
	'use strict';

	angular
		.module('app.admin')
		.factory('AdminResource', AdminResource);

	function AdminResource(resourceExtension) {
		return {
			LoginAttempts:		resourceExtension('login-attempts/:id', { id: '@id' }),
			RecoveryLogs:		resourceExtension('recovery-logs/:id', { id: '@id' }),
			Signups:			resourceExtension('signups/:id', { id: '@id' }),
			IpChanges:			resourceExtension('ipchanges/:id', { id: '@id' }),
			Reports:			resourceExtension('reports/:id', { id: '@id' }),
			Search:				resourceExtension('search/'),
			Nonscene:			resourceExtension('nonscene/:id'),
			CheatLogs:			resourceExtension('cheatlogs/:id'),
			AdminMailbox:		resourceExtension('mailbox-admin/:id', { id: '@id' }),
			MailboxAdmin:		resourceExtension('admin-mailbox/:id'),
			Logs:				resourceExtension('adminlogs/:id'),
			SqlErrors:			resourceExtension('sqlerrors/:id'),
		};
	}

})();
