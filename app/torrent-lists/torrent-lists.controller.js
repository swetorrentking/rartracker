(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.controller('TorrentListsController', TorrentListsController);

	function TorrentListsController($stateParams, $state, $uibModal, ErrorDialog, TorrentListsResource) {

		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;
		this.sort = $stateParams.sort;
		this.order = $stateParams.order;

		this.loadLists = function () {
			$state.go($state.current.name, { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			TorrentListsResource.Lists.query({
				'limit': this.itemsPerPage,
				'index': index,
				'sort': this.sort,
				'order': this.order
			}).$promise
				.then((torrentLists) => {
					this.torrentLists = torrentLists;
				});
		};

		this.onSort = function (sort) {
			if (this.sort == sort) {
				if (this.order === 'asc'){
					this.order = 'desc';
				} else {
					this.order = 'asc';
				}
			} else {
				this.sort = sort;
				this.order = 'desc';
			}
			this.loadLists();
		};

		this.loadLists();

	}

})();
