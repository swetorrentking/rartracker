(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TorrentsController', TorrentsController);

	function TorrentsController($scope, $rootScope, $state, $stateParams, user, $timeout, TorrentsResource, authService, settings) {

		/* URL params */
		this.sort = $stateParams.sort;
		this.order = $stateParams.order;
		this.searchText = $stateParams.search;
		this.currentPage = $stateParams.page;
		this.extended = $stateParams.extended === 'true';
		this.swesub = $stateParams.swesub === 'true';
		this.freeleech = $stateParams.freeleech === 'true';
		this.stereoscopic = settings.stereoscopic !== undefined ? null : $stateParams.stereoscopic == 'true';
		this.sweaudio = settings.sweaudio !== undefined ? null : $stateParams.sweaudio == 'true';

		/* Settings */
		this.currentUser = user;
		this.defaultCategories = settings.checkboxCategories;
		this.forceCats = $stateParams.fc === 'true';
		this.checkboxCategories = this.forceCats && $stateParams.cats && $stateParams.cats.split(',').map(cat => parseInt(cat, 10)) || angular.copy(user.notifs);

		/* Settings params will override */
		this.section = settings.section ? settings.section : this.forceCats ? $stateParams.section : user.section;
		if (settings.p2p !== undefined) {
			this.p2p = settings.p2p;
		} else {
			this.p2p = this.forceCats ? $stateParams.p2p === 'true' : user.p2p === 1;
			this.p2p = this.p2p ? null : false;
		}

		this.settings = settings;
		this.deleteVars = {};
		this.checkMode = false;
		this.deletingMulti = false;
		this.itemsPerPage = user['torrentsperpage'] > 0 ? user['torrentsperpage'] : 15;
		this.lastBrowseDate = user[settings.pageName];
		this.checkboxChannels = settings.checkboxChannels;

		/* Change sort of torrents based on user settings in search view */
		if (!this.forceCats && settings.pageName === 'search' && user['search_sort'] === 'added') {
			this.sort = 'd';
			this.order = 'desc';
		}

		if (settings.pageName === 'search') {
			$scope.$on('doSearch', (event, options) => {
				this.searchText = options.searchText;
				this.extended = options.extended;
				this.currentPage = 1;
				this.getTorrents();
			});
			$timeout(() => {
				$rootScope.$broadcast('updateSearchOptions', {searchText: this.searchText, extended: this.extended});
			});
		}

		this.getTorrents = function () {
			if (!this.hasLoadedFirstTime) {
				this.currentPage = $stateParams.page;
			}
			$state.go('.', {
				page: this.currentPage,
				order: this.order,
				sort: this.sort,
				search: this.searchText,
				extended: this.extended,
				fc: this.forceCats,
				cats: this.checkboxCategories.join(),
				p2p: this.p2p,
				section: this.section,
				swesub: this.swesub,
				freeleech: this.freeleech,
				stereoscopic: this.stereoscopic
			}, { notify: false, location: (!this.hasLoadedFirstTime ? 'replace' : true) });

			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			TorrentsResource.Torrents.query({
				'categories[]': this.checkboxCategories,
				'index': index,
				'p2p': this.p2p,
				'section': this.section,
				'limit': this.itemsPerPage,
				'page': settings.pageName,
				'sort': this.sort,
				'order': this.order,
				'searchText': this.searchText,
				'extendedSearch': this.extended === true,
				'watchview': settings.pageName === 'last_bevakabrowse',
				'swesub': this.swesub,
				'freeleech': this.freeleech,
				'stereoscopic': this.stereoscopic,
				'sweaudio': this.sweaudio
			}, (torrents, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.torrents = torrents;
				this.checkMode = false;
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.filterCategory = function (category) {
			this.checkboxCategories = [category];
			this.currentPage = 1;
			this.forceCats = true;
			this.getTorrents();
		};

		this.getSelectedTorrents = function () {
			return this.torrents && this.torrents.filter(torrent => torrent.selected === 'yes');
		};

		this.getCheckedAmount = function () {
			var selectedTorrents = this.getSelectedTorrents();
			return selectedTorrents && selectedTorrents.length;
		};

		this.sortTorrents = function (sort) {
			if (this.sort == sort) {
				this.order = (this.order == 'asc' ? 'desc' : 'asc');
			} else {
				this.sort = sort;
				this.order = (sort == 'n' ? 'asc' : 'desc');
			}
			this.forceCats = true;
			this.getTorrents();
		};

		this.multiDelete = function () {
			this.deletingMulti = true;
			var torrents = this.getSelectedTorrents();
			torrents = torrents.map(torrent => torrent.id);

			TorrentsResource.Multi.remove({
				reason: this.deleteVars.reason,
				pmUploader: this.deleteVars.pmUploader,
				pmPeers: this.deleteVars.pmPeers,
				attachTorrentId: this.deleteVars.attachTorrentId,
				'torrents[]': torrents
			}, () => {
				this.deletingMulti = false;
				this.checkMode = false;
				this.torrents = this.torrents.filter(torrent => torrents.indexOf(torrent.id) === -1);
			});
		};

		this.loadRelated = function () {
			if (this.torrents && this.checkMode) {
				var imdbId;
				for (var i = 0; i < this.torrents.length; i++) {
					if (this.torrents[i].imdbid) {
						imdbId = this.torrents[i].imdbid;
						break;
					}
				}
				if (imdbId) {
					TorrentsResource.Related.query({id: imdbId}, (torrents) => {
						this.relatedTorrents = torrents;
					});
				} else {
					this.relatedTorrents = null;
				}
			}
		};

		/* Wait for category checkboxes to populate */
		$timeout(() => {
			this.getTorrents();
		});

	}

})();
