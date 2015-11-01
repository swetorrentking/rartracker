(function(){
	'use strict';

	angular.module('tracker')
		.config(function($stateProvider, $urlRouterProvider, $locationProvider, $compileProvider) {

		$compileProvider.debugInfoEnabled(false);
		$urlRouterProvider.otherwise('/');
		$locationProvider.html5Mode(true);

		$stateProvider
			.state('start', {
				url			: '/',
				templateUrl : '../app/start/start.html',
				controller  : 'StartController',
			})
			.state('search', {
				url			: '/search',
				templateUrl : '../app/torrentlists/search.html',
				controller  : 'CommonTorrentsController',
				params		: {searchText: null, extended: false},
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function () {
						return  {
							checkboxCategories: [
							],
							defaultSelectedCats:[
							],
							showHideOldCheckbox: false,
							pageName: 'search',
							p2p: null,
							section: null
						};
					},
				}
			})
			.state('log', {
				url			: '/log/:page',
				templateUrl : '../app/log/log.html',
				controller  : 'LogController'
			})
			.state('mailbox', {
				url			: '/mailbox',
				templateUrl : '../app/mailbox/mailbox.html',
				controller  : 'MailboxController'
			})
			.state('sendMessage', {
				url			: '/sendmessage',
				templateUrl : '../app/mailbox/sendmessage.html',
				controller  : 'SendmessageController',
				params		: {msg: null},
			})
			.state('login', {
				url			: '/login',
				templateUrl : '../app/auth/login.html',
				controller  : 'LoginController'
			})
			.state('suggestions', {
				url			: '/suggestions',
				templateUrl : '../app/suggestions/suggestions.html',
				controller  : 'SuggestionsController'
			})
			.state('info', {
				url			: '/info',
				templateUrl : '../app/info/nav.html',
				redirectTo	: 'info.info'
			})
			.state('info.info', {
				url			: '/info',
				templateUrl	: '../app/info/info.html',
				controller	: 'InfoController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('info.rules', {
				url			: '/rules',
				templateUrl	: '../app/info/rules.html',
				controller	: 'RulesController'
			})
			.state('info.faq', {
				url			: '/faq',
				templateUrl	: '../app/info/faq.html',
				controller	: 'FaqController'
			})
			.state('info.irc', {
				url			: '/irc',
				templateUrl	: '../app/info/irc.html',
			})
			.state('movies', {
				url			: '/movies',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
								categories.DVDR_PAL,
								categories.DVDR_CUSTOM,
								categories.MOVIE_720P,
								categories.MOVIE_1080P
							],
							defaultSelectedCats:[
								categories.DVDR_PAL.id,
								categories.DVDR_CUSTOM.id,
								categories.MOVIE_720P.id,
								categories.MOVIE_1080P.id,
							],
							showHideOldCheckbox: true,
							pageName: 'last_browse',
							p2p: false,
							section: 'new'
						};
					},
				}
			})
			.state('tvseries', {
				url			: '/tvseries',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
								categories.DVDR_TV,
								categories.TV_720P,
								categories.TV_1080P,
							],
							defaultSelectedCats:[
								categories.DVDR_TV.id,
								categories.TV_720P.id,
								categories.TV_1080P.id,
							],
							showHideOldCheckbox: false,
							pageName: 'last_seriebrowse',
							p2p: false,
							section: 'new'
						};
					},
				}
			})
			.state('swetv', {
				url			: '/swetv',
				templateUrl : '../app/torrentlists/swetv-nav.html',
				controller  : 'SweTvController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('swetv.guide', {
				url			: '/guide',
				templateUrl : '../app/torrentlists/swetvguide.html',
				controller  : 'SweTvGuideController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('swetv.torrents', {
				url			: '/torrents',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
							],
							defaultSelectedCats:[
								categories.TV_SWE.id
							],
							showHideOldCheckbox: false,
							pageName: 'last_tvbrowse',
							p2p: null,
							section: 'new'
						};
					},
				}
			})
			.state('other', {
				url			: '/other',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
								categories.DVDR_PAL,
								categories.DVDR_CUSTOM,
								categories.DVDR_TV,
								categories.MOVIE_720P,
								categories.MOVIE_1080P,
								categories.TV_720P,
								categories.TV_1080P,
								categories.TV_SWE,
								categories.AUDIOBOOKS,
								categories.EBOOKS,
								categories.EPAPERS,
								categories.MUSIC,
							],
							defaultSelectedCats:[
								categories.DVDR_PAL.id,
								categories.DVDR_CUSTOM.id,
								categories.DVDR_TV.id,
								categories.MOVIE_720P.id,
								categories.MOVIE_1080P.id,
								categories.TV_720P.id,
								categories.TV_1080P.id,
								categories.TV_SWE.id,
								categories.AUDIOBOOKS.id,
								categories.EBOOKS.id,
								categories.EPAPERS.id,
								categories.MUSIC.id,
							],
							showHideOldCheckbox: false,
							pageName: 'last_ovrigtbrowse',
							p2p: true,
							section: null
						};
					},
				}
			})
			.state('requests', {
				url			: '/archive',
				templateUrl : '../app/requests/requests-nav.html',
				redirectTo	: 'requests.torrents'
			})
			.state('alltorrents', {
				url			: '/alltorrents',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
								categories.DVDR_PAL,
								categories.DVDR_CUSTOM,
								categories.DVDR_TV,
								categories.MOVIE_720P,
								categories.MOVIE_1080P,
								categories.TV_720P,
								categories.TV_1080P,
								categories.TV_SWE,
								categories.AUDIOBOOKS,
								categories.EBOOKS,
								categories.EPAPERS,
								categories.MUSIC,
							],
							defaultSelectedCats:[
							],
							showHideOldCheckbox: false,
							pageName: 'last_allbrowse',
							p2p: null,
							section: null
						};
					},
				}
			})
			.state('torrent', {
				url			: '/torrent/:id/:name',
				templateUrl : '../app/torrent/torrent.html',
				controller  : 'TorrentController',
				params		: {uploaded: false, scrollTo: ''},
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('editTorrent', {
				url			: '/torrent/:id/:name/edit',
				templateUrl : '../app/torrent/edit-torrent.html',
				controller  : 'EditTorrentController',
				params		: {uploaded: false},
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('user', {
				url			: '/user/:id/:username',
				templateUrl : '../app/user/user.html',
				controller  : 'UserController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('userEdit', {
				url			: '/user/:id/:username/edit',
				templateUrl : '../app/user/edit.html',
				controller  : 'UserEditController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('toplists', {
				url			: '/toplists',
				templateUrl : '../app/toplists/nav.html',
				redirectTo	: 'toplists.movies'
			})
			.state('toplists.movies', {
				url			: '/movies',
				templateUrl : '../app/toplists/top-movies.html',
				controller  : 'TopMoviesController'
			})
			.state('toplists.torrents', {
				url			: '/torrents',
				templateUrl : '../app/toplists/top-torrents.html',
				controller  : 'TopTorrentsController'
			})
			.state('toplists.leechbonus', {
				url			: '/leechbonus',
				templateUrl : '../app/toplists/top-leechbonus.html',
				controller  : 'TopLeechbonusController'
			})
			.state('toplists.seeders', {
				url			: '/seeders',
				templateUrl : '../app/toplists/top-seeders.html',
				controller  : 'TopSeedersController'
			})
			.state('forum', {
				abstract	: true,
				url			: '',
				templateUrl : '../app/forum/forum.html',
				controller  : 'ForumController',
			})
			.state('forum.forums', {
				url			: '/forum',
				templateUrl : '../app/forum/forums.html',
				controller  : 'ForumsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum.topics', {
				url			: '/forum/:id/:page',
				templateUrl : '../app/forum/topics.html',
				controller  : 'TopicsController',
				params		: {page: null},
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum.topic', {
				url			: '/forum/:forumid/topic/:id/:page',
				templateUrl : '../app/forum/topic.html',
				controller  : 'TopicController',
				params		: {page: null, lastpost: null},
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum.newTopic', {
				url			: '/forum/:id/new-topic/',
				templateUrl : '../app/forum/new-topic.html',
				controller  : 'NewTopicController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum-user-posts', {
				url			: '/user/:id/:username/posts',
				templateUrl : '../app/forum/user-posts.html',
				controller  : 'UserForumPostsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('user-comments', {
				url			: '/user/:id/:username/comments',
				templateUrl : '../app/user/comments.html',
				controller  : 'UserTorrentComments',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum-unread-topics', {
				url			: '/forums/unread-topics',
				templateUrl : '../app/forum/unread-topics.html',
				controller  : 'UnreadTopicsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('forum-search', {
				url			: '/forums/search',
				templateUrl : '../app/forum/search.html',
				controller  : 'ForumSearchController',
				controllerAs: 'vm',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('upload', {
				url			: '/upload',
				templateUrl : '../app/upload/upload.html',
				controller  : 'UploadController',
				params		: {requestId: null, requestName: null},
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('watcher', {
				url			: '/watcher',
				templateUrl : '../app/watch/watch-nav.html',
				redirectTo	: 'watcher.torrents'
			})
			.state('watcher.torrents', {
				url			: '/torrents',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'WatchTorrentsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('watcher.mywatch', {
				url			: '/mywatch',
				templateUrl : '../app/watch/watch-my.html',
				controller  : 'MyWatchController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('watcher.subtitles', {
				url			: '/subtitles',
				templateUrl : '../app/watch/watch-subtitles.html',
				controller  : 'WatchingSubtitlesController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('watcher.rss', {
				url			: '/rss',
				templateUrl : '../app/watch/watch-rss.html',
				controller  : 'WatchRssController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('watcher.top', {
				url			: '/top',
				templateUrl : '../app/watch/watch-top.html',
				controller  : 'WatchTopController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('requests.torrents', {
				url			: '/torrents',
				templateUrl : '../app/torrentlists/torrents-table.html',
				controller  : 'CommonTorrentsController',
				resolve		: { 
					user: function (AuthService) { return AuthService.getPromise(); },
					settings: function (categories) {
						return  {
							checkboxCategories: [
								categories.DVDR_PAL,
								categories.DVDR_CUSTOM,
								categories.DVDR_TV,
								categories.MOVIE_720P,
								categories.MOVIE_1080P,
								categories.TV_720P,
								categories.TV_1080P,
								categories.TV_SWE,
							],
							defaultSelectedCats:[
								categories.DVDR_PAL.id,
								categories.DVDR_CUSTOM.id,
								categories.DVDR_TV.id,
								categories.MOVIE_720P.id,
								categories.MOVIE_1080P.id,
								categories.TV_720P.id,
								categories.TV_1080P.id,
								categories.TV_SWE.id,
							],
							showHideOldCheckbox: false,
							pageName: 'last_reqbrowse',
							p2p: false,
							section: 'archive'
						};
					},
				}
			})
			.state('requests.requests', {
				url			: '/requests/:page',
				templateUrl : '../app/requests/requests.html',
				controller  : 'RequestsController',
			})
			.state('requests.request', {
				url			: '/request/:id/:name',
				templateUrl : '../app/requests/request.html',
				controller  : 'RequestController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('requests.add', {
				url			: '/add-request',
				templateUrl : '../app/requests/add-request.html',
				controller  : 'AddRequestController'
			})
			.state('requests.edit', {
				url			: '/edit-request/:id',
				templateUrl : '../app/requests/edit-request.html',
				controller  : 'EditRequestController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('requests.my', {
				url			: '/my-requests',
				templateUrl : '../app/requests/my-requests.html',
				controller  : 'MyRequestsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('bonus', {
				url			: '/bonus',
				templateUrl : '../app/bonus/bonus.html',
				controller  : 'BonusController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('leechbonus', {
				url			: '/leechbonus',
				templateUrl : '../app/leechbonus/leechbonus.html',
			})
			.state('invite', {
				url			: '/invite',
				templateUrl : '../app/invite/invite.html',
				controller  : 'InviteController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('friends', {
				url			: '/friends',
				templateUrl : '../app/friends/friends.html',
				controller  : 'FriendsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('bookmarks', {
				url			: '/bookmarks',
				templateUrl : '../app/bookmarks/bookmarks.html',
				controller  : 'BookmarksController',
			})
			.state('rss', {
				url			: '/rss',
				templateUrl : '../app/rss/rss.html',
				controller  : 'RssController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('signup', {
				url			: '/signup/:id/',
				templateUrl : '../app/auth/signup.html',
				controller  : 'SignupController',
			})
			.state('start-edit', {
				url			: '/start-edit',
				templateUrl : '../app/start/edit-start.html',
				controller  : 'EditStartController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('news', {
				url			: '/news',
				templateUrl : '../app/news/news.html',
				controller  : 'NewsController',
			})
			.state('polls', {
				url			: '/polls',
				templateUrl : '../app/polls/polls.html',
				controller  : 'PollsController',
			})
			.state('donate', {
				url			: '/donate',
				templateUrl : '../app/donate/donate.html',
				controller  : 'DonateController',
			})
			.state('my-torrent-comments', {
				url			: '/my-torrent-comments',
				templateUrl : '../app/torrent-comments/torrent-comments.html',
				controller  : 'TorrentCommentsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('admin-login-attempts', {
				url			: '/admin-login-attempts',
				templateUrl : '../app/admin/login-attempts/login-attempts.html',
				controller  : 'LoginAttemptsController',
			})
			.state('admin-signups', {
				url			: '/admin-signups',
				templateUrl : '../app/admin/signups/signups.html',
				controller  : 'SignupsController',
			})
			.state('admin-ipchanges', {
				url			: '/admin-ipchanges',
				templateUrl : '../app/admin/ipchanges/ipchanges.html',
				controller  : 'IpChangesController',
			})
			.state('admin-reports', {
				url			: '/admin-reports',
				templateUrl : '../app/admin/reports/reports.html',
				controller  : 'ReportsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('admin-logs', {
				url			: '/admin-logs',
				templateUrl : '../app/admin/adminlog/adminlogs.html',
				controller  : 'AdminLogsController',
			})
			.state('recovery-logs', {
				url			: '/recovery-logs',
				templateUrl : '../app/admin/recovery-log/recovery-log.html',
				controller  : 'RecoveryLogsController',
			})
			.state('admin-sqlerrors', {
				url			: '/admin-sqlerrors',
				templateUrl : '../app/admin/sqlerrors/sqlerrors.html',
				controller  : 'SqlErrorsController',
			})
			.state('admin-mailbox', {
				url			: '/admin-mailbox',
				templateUrl : '../app/admin/admin-mailbox/admin-mailbox.html',
				controller  : 'AdminMailboxController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('admin-donations', {
				url			: '/admin-donations',
				templateUrl : '../app/admin/donations/donations.html',
				controller  : 'DonationsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('admin-search', {
				url			: '/admin-search/:ip',
				templateUrl : '../app/admin/admin-search/admin-search.html',
				controller  : 'AdminSearchController',
			})
			.state('cheatlog', {
				url			: '/cheatlog/:userid',
				templateUrl : '../app/admin/cheatlogs/cheatlog.html',
				controller  : 'CheatlogController',
			})
			.state('admin-banned', {
				url			: '/banned',
				templateUrl : '../app/admin/banned/banned.html',
				controller  : 'BannedController',
			})
			.state('forum-posts', {
				url			: '/forum-posts',
				templateUrl : '../app/admin/posts/forum-posts.html',
				controller  : 'ForumPostsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('torrent-comments', {
				url			: '/torrent-comments',
				templateUrl : '../app/admin/torrent-comments/all-torrent-comments.html',
				controller  : 'AllTorrentCommentsController',
				resolve		: { user: function (AuthService) { return AuthService.getPromise(); } }
			})
			.state('recover', {
				url			: '/recover/:secret',
				templateUrl : '../app/recover/recover.html',
				controller  : 'RecoverController'
			})
			.state('statistics', {
				url			: '/statistics',
				templateUrl : '../app/statistics/statistics.html',
				controller  : 'StatisticsController'
			});
	});
})();