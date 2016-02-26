(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(TorrentRoutes);

	function TorrentRoutes($stateProvider) {

		$stateProvider
			.state('torrent', {
				parent		: 'header',
				url			: '/torrent/:id/:name',
				views		: {
					'content@': {
						templateUrl : '../app/torrent/torrent.template.html',
						controller  : 'TorrentController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: {uploaded: false, scrollTo: ''},
			})
			.state('editTorrent', {
				parent		: 'header',
				url			: '/torrent/:id/:name/edit',
				views		: {
					'content@': {
						templateUrl : '../app/torrent/edit-torrent.template.html',
						controller  : 'EditTorrentController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: {uploaded: false},
			});

	}

}());