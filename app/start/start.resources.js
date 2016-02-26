(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('StartTorrentsResource', StartTorrentsResource);

	function StartTorrentsResource(resourceExtension) {
		return resourceExtension('start-torrents');
	}

})();