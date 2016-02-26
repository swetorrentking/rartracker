(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('WatchTorrentsController', WatchTorrentsController);

	function WatchTorrentsController($state, $stateParams, TorrentsResource, user) {

		this.itemsPerPage = user['torrentsperpage'] > 0 ? user['torrentsperpage'] : 20;
		this.hideOld = user['visagammalt'] === 0;
		this.lastBrowseDate = user['last_bevakabrowse'];

		this.currentPage = $stateParams.page;

		this.getReleases = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			TorrentsResource.Torrents.query({
				'index': index,
				'p2p': false,
				'limit': this.itemsPerPage,
				'watchview': true,
				'page': 'last_bevakabrowse',
			}, (torrents, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.torrents = torrents;

				if (!this.loadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.loadedFirstTime = true;
				}
			});
		};

		this.pageChanged = function () {
			this.getReleases();
		};

		this.getReleases();
	}

})();