(function(){
	'use strict';

	angular
		.module('app.swetv')
		.config(SweTvRoutes);

	function SweTvRoutes($stateProvider) {

		$stateProvider
			.state('swetv', {
				parent		: 'header',
				url			: '/swetv',
				views		: {
					'content@': {
						templateUrl : '../app/swetv/swetv-nav.template.html',
						controller  : 'SweTvController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					autoSwitchView: false
				}
			})
			.state('swetv.guide', {
				url			: '/guide?page',
				templateUrl : '../app/swetv/swetvguide.template.html',
				controller  : 'SweTvGuideController as vm',
				resolve		: { user: authService => authService.getPromise() },
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('swetv.torrents', {
				url			: '/torrents?page&sort&order&fc&section&p2p&swesub&freeleech',
				templateUrl : '../app/torrents/torrents.template.html',
				controller  : 'TorrentsController as vm',
				resolve		: {
					user: authService => authService.getPromise(),
					settings: function (categories) {
						return {
							checkboxCategories: [
								categories.TV_SWE
							],
							pageName: 'last_tvbrowse',
							stereoscopic: null,
							sweaudio: null,
						};
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					fc: { value: 'false', squash: true },
					section: { value: 'all', squash: true },
					p2p: { value: 'true', squash: true },
					swesub: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			});

	}

}());
