(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('FriendsResource', FriendsResource)
		.factory('BlocksResource', BlocksResource);

	function FriendsResource(resourceExtension) {
		return resourceExtension('friends/:id', { id: '@id' });
	}

	function BlocksResource(resourceExtension) {
		return resourceExtension('blocked/:id', { id: '@id' });
	}

})();
