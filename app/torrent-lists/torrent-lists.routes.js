(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.config(TorrentListsRoutes);

	function TorrentListsRoutes($stateProvider) {

		$stateProvider
			.state('torrent-lists', {
				parent		: 'header',
				url			: '/torrent-lists',
				views		: {
					'content@': {
						templateUrl : '../app/torrent-lists/torrent-lists-nav.template.html',
					}
				},
				redirectTo	: 'torrent-lists.torrent-lists'
			})
			.state('torrent-lists.torrent-lists', {
				url			: '/',
				templateUrl : '../app/torrent-lists/torrent-lists.template.html',
				controller  : 'TorrentListsController as vm',
				resolve		: { user: authService => authService.getPromise() },
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'added', squash: true },
					order: { value: 'desc', squash: true }
				}
			})
			.state('torrent-lists.torrent-list', {
				url			: '/{id:[0-9]+}/:slug',
				templateUrl : '../app/torrent-lists/torrent-list.template.html',
				controller  : 'TorrentListController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('torrent-lists.add', {
				url			: '/add',
				templateUrl : '../app/torrent-lists/add-edit-torrent-list.template.html',
				controller  : 'AddEditTorrentListController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('torrent-lists.edit', {
				url			: '/edit/:id',
				templateUrl : '../app/torrent-lists/add-edit-torrent-list.template.html',
				controller  : 'AddEditTorrentListController as vm',
				resolve		: { user: authService => authService.getPromise() },
				params		: { torrentList: null }
			})
			.state('torrent-lists.my', {
				url			: '/my',
				templateUrl : '../app/torrent-lists/my-torrent-lists.template.html',
				controller  : 'TorrentListBookmarksController as vm',
				resolve		: { user: authService => authService.getPromise() }
			});

	}

}());
