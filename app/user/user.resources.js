(function(){
	'use strict';

	angular
		.module('app.shared')
		.factory('UsersResource', UsersResource);

	function UsersResource(resourceExtension) {
		return {
			Users:				resourceExtension('users/:id'),
			Peers:				resourceExtension('users/:id/peers'),
			Snatchlog:			resourceExtension('users/:id/snatchlog'),
			Bonuslog:			resourceExtension('users/:id/bonuslog'),
			Invitees:			resourceExtension('users/:id/invitees'),
			Iplog:				resourceExtension('users/:id/iplog'),
			Torrents:			resourceExtension('users/:id/torrents'),
			TorrentComments:	resourceExtension('users/:id/torrent-comments'),
			Comments:			resourceExtension('users/:id/comments'),
			Watching:			resourceExtension('users/:id/watching/:watchId', { watchId: '@watchId', id: '@id' }),
			Watch:				resourceExtension('users/:id/watching/imdb/:imdbId', { imdbId: '@imdbId', id: '@id' }),
			WatchTop:			resourceExtension('users/:id/watching/toplist'),
			ForumPosts:			resourceExtension('users/:id/forum-posts'),
			EmailTest:			resourceExtension('users/:id/email-test'),
		};
	}

})();
