/*
	Describe torrents-pages with the settings object:

	checkboxCategories: Array()			- List of categories limited to the view, with checkboxes visible. Empty equals all. Params 'cats' below should reflect this list.
	pageName: 'last_reqbrowse'			- User table column used for store last read date for "NEW" tagging.

	p2p (true, false, null)				- Optionally force to only show p2p, non p2p or all. Will remove buttons and override user settings.
	section ('all', 'new', 'archive')	- Optionally force to only show a certain section. Will remove buttons and override user settings.
*/

(function(){
	'use strict';

	angular
		.module('app.shared')
		.config(Torrentlists);

	function Torrentlists($stateProvider) {

		$stateProvider
			.state('search', {
				parent		: 'header',
				url			: '/search?page&sort&order&search&cats&fc&extended&p2p&swesub&sweaudio&freeleech&stereoscopic',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function () {
								return  {
									checkboxCategories: [],
									pageName: 'search',
									section: 'all'
								};
							}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'n',	squash: true },
					order: { value: 'asc', squash: true },
					search: { value: '', squash: true },
					cats: { value: '', squash: true },
					fc: { value: 'false', squash: true },
					extended: { value: 'false', squash: true },
					p2p: { value: 'true', squash: true },
					swesub: { value: 'false', squash: true },
					sweaudio: { value: 'false', squash: true },
					stereoscopic: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			})
			.state('movies', {
				parent		: 'header',
				url			: '/movies?page&sort&order&cats&p2p&fc&section&swesub&freeleech&sweaudio&stereoscopic',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function (categories) {
								return  {
									checkboxCategories: [
										categories.DVDR_PAL,
										categories.DVDR_CUSTOM,
										categories.MOVIE_720P,
										categories.MOVIE_1080P,
										categories.BLURAY,
										categories.MOVIE_4K,
									],
									pageName: 'last_browse',
								};
							}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '1,2,4,5,13,15', squash: true },
					fc: { value: 'false', squash: true },
					p2p: { value: 'true', squash: true },
					section: { value: 'all', squash: true },
					swesub: { value: 'false', squash: true },
					sweaudio: { value: 'false', squash: true },
					stereoscopic: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			})
			.state('tvseries', {
				parent		: 'header',
				url			: '/tvseries?page&sort&order&cats&p2p&fc&section&swesub&freeleech&sweaudio&stereoscopic',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function (categories) {
								return  {
									checkboxCategories: [
										categories.DVDR_TV,
										categories.TV_720P,
										categories.TV_1080P,
									],
									pageName: 'last_seriebrowse',
								};
							}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '3,6,7', squash: true },
					fc: { value: 'false', squash: true },
					p2p: { value: 'true', squash: true },
					section: { value: 'all', squash: true },
					swesub: { value: 'false', squash: true },
					sweaudio: { value: 'false', squash: true },
					stereoscopic: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			})
			.state('other', {
				parent		: 'header',
				url			: '/other?page&sort&order&cats&p2p&fc&section&swesub&freeleech&sweaudio&stereoscopic',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function (categories) {
								return  {
									checkboxCategories: [
										categories.AUDIOBOOKS,
										categories.EBOOKS,
										categories.EPAPERS,
										categories.MUSIC,
										categories.SUBPACK,
									],
									pageName: 'last_ovrigtbrowse',
									sweaudio: null,
									stereoscopic: null,
								};
							}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '9,10,11,12,14', squash: true },
					fc: { value: 'false', squash: true },
					p2p: { value: 'true', squash: true },
					section: { value: 'all', squash: true },
					swesub: { value: 'false', squash: true },
					sweaudio: { value: 'false', squash: true },
					stereoscopic: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			})
			.state('alltorrents', {
				parent		: 'header',
				url			: '/alltorrents?page&sort&order&cats&fc&p2p&swesub&sweaudio&stereoscopic&freeleech',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function () {
								return  {
									checkboxCategories: [],
									pageName: 'last_allbrowse',
									p2p: null,
									section: 'all'
								};
							}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '', squash: true },
					fc: { value: 'false', squash: true },
					swesub: { value: 'false', squash: true },
					sweaudio: { value: 'false', squash: true },
					stereoscopic: { value: 'false', squash: true },
					freeleech: { value: 'false', squash: true },
				}
			});

	}

}());
