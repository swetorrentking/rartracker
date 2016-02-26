(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('LogsResource', LogsResource);

	function LogsResource(resourceExtension) {
		return resourceExtension('logs/:id', { id: '@id' });
	}

})();
