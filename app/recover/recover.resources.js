(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('RecoverResource', RecoverResource);

	function RecoverResource(resourceExtension) {
		return resourceExtension('recover/:id', { id: '@id' });
	}

})();