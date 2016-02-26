(function(){
	'use strict';

	angular
		.module('app.watcher')
		.factory('WatchingSubtitlesResource', WatchingSubtitlesResource);

	function WatchingSubtitlesResource(resourceExtension) {
		return resourceExtension('watching-subtitles/:id', { id: '@id' });
	}

})();
