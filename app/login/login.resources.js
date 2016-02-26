(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('AuthResource', AuthResource)
		.factory('StatusResource', StatusResource);

	function AuthResource(resourceExtension) {
		return resourceExtension('auth');
	}

	function StatusResource(resourceExtension) {
		return resourceExtension('status');
	}

})();