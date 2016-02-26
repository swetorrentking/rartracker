/*
	Describe torrents-pages with the settings object:

	checkboxCategories: Array()			- List of categories limited to the view, with checkboxes visible. Empty equals all
	showHideOldCheckbox: (true, false)	- Show or hide the "hide old" checkbox.
	pageName: 'last_reqbrowse'			- User table column name used for "NEW" tagging.
	p2p: (true, false, null)			- Display p2p-flagged torrents (true = show p2p, false = hide p2p, null = show all)
	section: ('archive', 'new', null)	- Show "new" or "archive" torrents. null = both.

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
				url			: '/search?page&sort&order&search&extended',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/search.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function () {
								return  {
									checkboxCategories: [],
									showHideOldCheckbox: false,
									pageName: 'search',
									p2p: null,
									section: null
								};
							},
							previousState: $state => $state.current.name
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'n',	squash: true },
					order: { value: 'asc', squash: true },
					search: { value: '', squash: true },
					extended: { value: 'false', squash: true }
				}
			})
			.state('movies', {
				parent		: 'header',
				url			: '/movies?page&sort&order&cats&fc',
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
									showHideOldCheckbox: true,
									pageName: 'last_browse',
									p2p: false,
									section: 'new'
								};
							},
							previousState: function () {}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '1,2,4,5,13,15', squash: true },
					fc: { value: 'false', squash: true },
				}
			})
			.state('tvseries', {
				parent		: 'header',
				url			: '/tvseries?page&sort&order&cats&fc',
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
									showHideOldCheckbox: false,
									pageName: 'last_seriebrowse',
									p2p: false,
									section: 'new'
								};
							},
							previousState: function () {}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '3,6,7', squash: true },
					fc: { value: 'false', squash: true },
				}
			})
			.state('other', {
				parent		: 'header',
				url			: '/other?page&sort&order&cats&fc',
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
										categories.SUBPACK,
										categories.MOVIE_4K,
									],
									showHideOldCheckbox: false,
									pageName: 'last_ovrigtbrowse',
									p2p: true,
									section: null
								};
							},
							previousState: function () {}
						}
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '1,2,3,4,5,6,7,8,9,10,11,12,14,15', squash: true },
					fc: { value: 'false', squash: true },
				}
			})
			.state('alltorrents', {
				parent		: 'header',
				url			: '/alltorrents?page&sort&order&cats&fc',
				views		: {
					'content@': {
						templateUrl : '../app/torrents/torrents.template.html',
						controller  : 'TorrentsController as vm',
						resolve		: {
							user: authService => authService.getPromise(),
							settings: function () {
								return  {
									checkboxCategories: [],
									showHideOldCheckbox: false,
									pageName: 'last_allbrowse',
									p2p: null,
									section: null
								};
							},
							previousState: function () {}
						}
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
			.state('requests.archive', {
				url			: '/archive?page&sort&order&cats&fc',
				templateUrl : '../app/torrents/torrents.template.html',
				controller  : 'TorrentsController as vm',
				resolve		: {
					user: authService => authService.getPromise(),
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
								categories.BLURAY,
								categories.SUBPACK,
								categories.MOVIE_4K,
							],
							showHideOldCheckbox: false,
							pageName: 'last_reqbrowse',
							p2p: false,
							section: 'archive'
						};
					},
					previousState: function () {}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'd', squash: true },
					order: { value: 'desc', squash: true },
					cats: { value: '1,2,3,4,5,6,7,8,13,14,15', squash: true },
					fc: { value: 'false', squash: true },
				}
			});

	}

}());
