(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.factory('TorrentListsResource', TorrentListsResource);

	function TorrentListsResource(resourceExtension) {
		return {
			Lists:			resourceExtension('torrent-lists/:id', { id: '@id' }),
			Popular:		resourceExtension('torrent-lists/popular'),
			Votes:			resourceExtension('torrent-lists/:id/votes', { id: '@id' }),
			Bookmarks:		resourceExtension('torrent-list-bookmarks/:id', { id: '@id' })
		};
	}

})();
