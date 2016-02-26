(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(TorrentCommentsRoutes);

	function TorrentCommentsRoutes($stateProvider) {

		$stateProvider
			.state('my-torrent-comments', {
				parent		: 'header',
				url			: '/my-torrent-comments?page',
				views		: {
					'content@': {
						templateUrl : '../app/torrent-comments/torrent-comments.template.html',
						controller  : 'TorrentCommentsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: {
					page: {
						value: '1',
						squash: true
					}
				}
			});

	}

}());