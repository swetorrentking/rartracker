(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('PickTorrentsDialogController', PickTorrentsDialogController);

	function PickTorrentsDialogController($uibModalInstance, TorrentsResource) {

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.add = function () {
			$uibModalInstance.close(this.torrents.filter(torrent => torrent.selected));
		};

		this.itemsPerPage = 10;
		this.torrents = [];

		this.doSearch = function () {
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			TorrentsResource.Torrents.query({
				'index': index,
				'limit': this.itemsPerPage,
				'page': 'search',
				'sort': this.sort,
				'order': this.order,
				'searchText': this.searchText,
			}, (torrents, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.torrents = torrents;
			});

		};

		this.getNumberChosen = function () {
			return this.torrents.filter(torrent => torrent.selected).length;
		};

	}

})();
