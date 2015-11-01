(function(){
	'use strict';

	angular.module('tracker.resources', ['ngResource'])
		/* Extending angular ngResource with PATCH (update) method */
		.factory('trackerResource', function($resource) {
			return function (url, params, methods) {
				var defaults = {
					update: { method: 'patch', isArray: false }
				};
				methods = angular.extend(defaults, methods);
				return $resource(url, params, methods);
			};
		})
		.factory('StatusResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'status');
		})
		.factory('AuthResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'auth');
		})
		.factory('LogsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'logs/:id', { id: '@id' });
		})
		.factory('MailboxResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'mailbox/:id', { id: '@id' });
		})
		.factory('BonusShopResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'bonus-shop/:id', { id: '@id' });
		})
		.factory('InvitesResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'invites/:id', { id: '@id' });
		})
		.factory('FriendsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'friends/:id', { id: '@id' });
		})
		.factory('BlocksResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'blocked/:id', { id: '@id' });
		})
		.factory('BookmarksResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'bookmarks/:id', { id: '@id' });
		})
		.factory('WatchingSubtitlesResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'watching-subtitles/:id', { id: '@id' });
		})
		.factory('SubtitlesResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'subtitles/:id', { id: '@id' });
		})
		.factory('DonationsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'donations/:id', { id: '@id' });
		})
		.factory('ReportsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'reports/:id', { id: '@id' });
		})
		.factory('NewsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'news/:id');
		})
		.factory('StartTorrentsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'start-torrents');
		})
		.factory('StatisticsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'statistics/:id');
		})
		.factory('SqlErrorsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'sqlerrors/:id');
		})
		.factory('AdminLogResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'adminlogs/:id');
		})
		.factory('AdminMailboxResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'admin-mailbox/:id');
		})
		.factory('CommentsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'comments/:id');
		})
		.factory('RecoverResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'recover/:id', { id: '@id' });
		})
		.factory('FaqResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'faq/:id', { id: '@id' });
		})
		.factory('RulesResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'rules/:id', { id: '@id' });
		})
		.factory('ReseedRequestsResource', function (trackerResource, configs) {
			return trackerResource(configs.API_BASE_URL + 'reseed-requests/:id');
		})
		.factory('PollsResource', function (trackerResource, configs) {
			return {
				Polls:				trackerResource(configs.API_BASE_URL + 'polls/:id', { id: '@id' }),
				Latest:				trackerResource(configs.API_BASE_URL + 'polls/latest'),
				Votes:				trackerResource(configs.API_BASE_URL + 'polls/votes/:id', { id: '@id' }),
			};
		})
		.factory('TorrentsResource', function (trackerResource, configs) {
			return {
				Torrents: 			trackerResource(configs.API_BASE_URL + 'torrents/:id'),
				Related: 			trackerResource(configs.API_BASE_URL + 'related-torrents/:id'),
				TorrentsMulti:		trackerResource(configs.API_BASE_URL + 'torrents/:id/multi'),
				Files: 				trackerResource(configs.API_BASE_URL + 'torrents/:id/files'),
				Peers: 				trackerResource(configs.API_BASE_URL + 'torrents/:id/peers'),
				Snatchlog:			trackerResource(configs.API_BASE_URL + 'torrents/:id/snatchlog'),
				Comments:			trackerResource(configs.API_BASE_URL + 'torrents/:id/comments/:commentId', { id: '@id', commentId: '@commentId' }),
				SweTvGuide:			trackerResource(configs.API_BASE_URL + 'sweTvGuide'),
				PackFiles:			trackerResource(configs.API_BASE_URL + 'torrents/:id/pack-files'),
				Multi:				trackerResource(configs.API_BASE_URL + 'torrents/multi'),
			};
		})
		.factory('UsersResource', function (trackerResource, configs) {
			return {
				Users:				trackerResource(configs.API_BASE_URL + 'users/:id'),
				Peers:				trackerResource(configs.API_BASE_URL + 'users/:id/peers'),
				Snatchlog:			trackerResource(configs.API_BASE_URL + 'users/:id/snatchlog'),
				Bonuslog:			trackerResource(configs.API_BASE_URL + 'users/:id/bonuslog'),
				Invitees:			trackerResource(configs.API_BASE_URL + 'users/:id/invitees'),
				Iplog:				trackerResource(configs.API_BASE_URL + 'users/:id/iplog'),
				Torrents:			trackerResource(configs.API_BASE_URL + 'users/:id/torrents'),
				TorrentComments:	trackerResource(configs.API_BASE_URL + 'users/:id/torrent-comments'),
				Comments:			trackerResource(configs.API_BASE_URL + 'users/:id/comments'),
				Watching:			trackerResource(configs.API_BASE_URL + 'users/:id/watching/:watchId', { watchId: '@watchId', id: '@id' }),
				WatchTop:			trackerResource(configs.API_BASE_URL + 'users/:id/watching/toplist'),
				ForumPosts:			trackerResource(configs.API_BASE_URL + 'users/:id/forum-posts'),
			};
		})
		.factory('MovieDataResource', function (trackerResource, configs) {
			return {
				Data:				trackerResource(configs.API_BASE_URL + 'moviedata/:id', { id: '@id' }),
				Imdb:				trackerResource(configs.API_BASE_URL + 'moviedata/imdb/:id', { id: '@id' }),
				Search:				trackerResource(configs.API_BASE_URL + 'moviedata/search'),
				Refresh:			trackerResource(configs.API_BASE_URL + 'moviedata/:id/refresh'),
			};
		})
		.factory('ForumResource', function (trackerResource, configs) {
			return {
				Forums:				trackerResource(configs.API_BASE_URL + 'forums/:id', { id: '@id' }),
				Topics:				trackerResource(configs.API_BASE_URL + 'forums/:forumid/topics/:id', { forumid: '@forumid' }),
				Posts:				trackerResource(configs.API_BASE_URL + 'forums/:forumid/topics/:topicid/posts/:id', { topicid: '@topicid', forumid: '@forumid', id: '@id' }),
				Online:				trackerResource(configs.API_BASE_URL + 'forums/users-online'),
				UnreadTopics:		trackerResource(configs.API_BASE_URL + 'forums/unread-topics'),
				Search:				trackerResource(configs.API_BASE_URL + 'forums/search'),
				AllPosts:			trackerResource(configs.API_BASE_URL + 'forums/posts'),
				MarkTopicsAsRead:	trackerResource(configs.API_BASE_URL + 'forums/mark-all-topics-as-read'),
			};
		})
		.factory('SweTvResource', function (trackerResource, configs) {
			return {
				Channels:			trackerResource(configs.API_BASE_URL + 'swetv/channels/:id', { id: '@id' }),
				Programs:			trackerResource(configs.API_BASE_URL + 'swetv/programs/:id', { id: '@id' }),
				Guess:				trackerResource(configs.API_BASE_URL + 'swetv/guess/:name', { name: '@name' }),
			};
		})
		.factory('SuggestionsResource', function (trackerResource, configs) {
			return {
				Votes:				trackerResource(configs.API_BASE_URL + 'suggestions/:id/votes/:voteId', { id: '@id', voteId: '@voteId'  }),
				Suggest:			trackerResource(configs.API_BASE_URL + 'suggestions/:id', { id: '@id' }),
			};
		})
		.factory('RequestsResource', function (trackerResource, configs) {
			return {
				Votes:				trackerResource(configs.API_BASE_URL + 'requests/:id/votes/:voteId', { id: '@id', voteId: '@voteId'  }),
				Requests:			trackerResource(configs.API_BASE_URL + 'requests/:id', { id: '@id' }),
				My:					trackerResource(configs.API_BASE_URL + 'requests/my'),
			};
		})
		.factory('AdminResource', function (trackerResource, configs) {
			return {
				LoginAttempts:		trackerResource(configs.API_BASE_URL + 'login-attempts/:id', { id: '@id' }),
				RecoveryLogs:		trackerResource(configs.API_BASE_URL + 'recovery-logs/:id', { id: '@id' }),
				Signups:			trackerResource(configs.API_BASE_URL + 'signups/:id', { id: '@id' }),
				IpChanges:			trackerResource(configs.API_BASE_URL + 'ipchanges/:id', { id: '@id' }),
				Reports:			trackerResource(configs.API_BASE_URL + 'reports/:id', { id: '@id' }),
				Search:				trackerResource(configs.API_BASE_URL + 'search/'),
				Nonscene:			trackerResource(configs.API_BASE_URL + 'nonscene/:id'),
				CheatLogs:			trackerResource(configs.API_BASE_URL + 'cheatlogs/:id'),
			};
		});
})();