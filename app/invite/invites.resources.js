(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('InvitesResource', InvitesResource);

	function InvitesResource(resourceExtension) {
		return resourceExtension('invites/:id', { id: '@id' });
	}

})();