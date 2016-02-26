(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(ToplistRoutes);

	function ToplistRoutes($stateProvider) {

		$stateProvider
			.state('toplists', {
				parent		: 'header',
				url			: '/toplists',
				views		: {
					'content@': {
						templateUrl : '../app/toplists/nav.template.html',
					}
				},
				redirectTo	: 'toplists.torrents',
			})
			.state('toplists.torrents', {
				url			: '/torrents',
				templateUrl : '../app/toplists/top-torrents.template.html',
				controller  : 'TopTorrentsController as vm',
			})
			.state('toplists.leechbonus', {
				url			: '/leechbonus',
				templateUrl : '../app/toplists/top-leechbonus.template.html',
				controller  : 'TopLeechbonusController as vm',
			})
			.state('toplists.seeders', {
				url			: '/seeders',
				templateUrl : '../app/toplists/top-seeders.template.html',
				controller  : 'TopSeedersController as vm',
			});

	}

}());