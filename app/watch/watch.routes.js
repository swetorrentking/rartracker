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
				url			: '/torrents?page',
				templateUrl : '../app/torrents/torrents.template.html',
				controller  : 'WatchTorrentsController as vm',
				resolve		: { user: authService => authService.getPromise() },
				params		: {
					page: {
						value: '1',
						squash: true
					}
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