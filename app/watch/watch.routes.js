(function(){
	'use strict';

	angular
		.module('app.watcher')
		.config(WatchRoutes);

	function WatchRoutes($stateProvider) {

		$stateProvider
			.state('watcher', {
				parent		: 'header',
				url			: '/watcher',
				views		: {
					'content@': {
						templateUrl : '../app/watch/watch-nav.template.html',
					}
				},
				redirectTo	: 'watcher.torrents'
			})
			.state('watcher.torrents', {
				url			: '/torrents?page&sort&order&cats&fc',
				templateUrl : '../app/torrents/torrents.template.html',
				controller  : 'TorrentsController as vm',
				resolve		: {
					user: authService => authService.getPromise(),
					settings: function () {
						return  {
							checkboxCategories: [],
							pageName: 'last_bevakabrowse',
							p2p: null,
							section: 'all'
						};
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '', squash: true },
					fc: { value: 'false', squash: true },
				}
			})
			.state('watcher.mywatch', {
				url			: '/mywatch',
				templateUrl : '../app/watch/watch-my.template.html',
				controller  : 'MyWatchController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('watcher.subtitles', {
				url			: '/subtitles',
				templateUrl : '../app/watch/watch-subtitles.template.html',
				controller  : 'WatchingSubtitlesController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('watcher.rss', {
				url			: '/rss',
				templateUrl : '../app/watch/watch-rss.template.html',
				controller  : 'WatchRssController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('watcher.top', {
				url			: '/top',
				templateUrl : '../app/watch/watch-top.template.html',
				controller  : 'WatchTopController as vm',
				resolve		: { user: authService => authService.getPromise() }
			});

	}

}());
