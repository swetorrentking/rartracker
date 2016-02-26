(function(){
	'use strict';

	angular
		.module('app.mailbox')
		.factory('MailboxResource', MailboxResource);

	function MailboxResource(resourceExtension) {
		return resourceExtension('mailbox/:id', { id: '@id' });
	}

})();