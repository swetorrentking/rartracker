(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('BookmarksResource', BookmarksResource);

	function BookmarksResource(resourceExtension) {
		return resourceExtension('bookmarks/:id', { id: '@id' });
	}

})();