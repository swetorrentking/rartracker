(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('NewsResource', NewsResource);

	function NewsResource(resourceExtension) {
		return resourceExtension('news/:id');
	}

})();