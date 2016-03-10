(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('InvitesResource', InvitesResource)
		.factory('InviteValidityResource', InviteValidityResource);

	function InvitesResource(resourceExtension) {
		return resourceExtension('invites/:id', { id: '@id' });
	}

	function InviteValidityResource(resourceExtension) {
		return resourceExtension('invite-validity');
	}

})();
