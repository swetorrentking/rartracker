(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('ForumSearchController', ForumSearchController);

	function ForumSearchController($state, $stateParams, ForumResource) {
		this.itemsPerPage = 15;

		this.searchText = $stateParams.search;
		this.currentPage = $stateParams.page;
		this.table = $stateParams.table;

		this.posts = [];
		this.topics = [];

		this.doSearch = function () {
			if (this.searchText.length < 2) {
				this.posts = [];
				this.topics = [];
				this.totalItems = 0;
				this.numberOfPages = 0;
				return;
			}
			$state.go('.', { search: this.searchText, page: this.currentPage, table: this.table }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			ForumResource.Search.query({
				table: this.table,
				search: this.searchText,
				limit: this.itemsPerPage,
				index: index,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				if (this.table === 'topics') {
					this.topics = data;
					this.posts = [];
				} else {
					this.posts = data;
					this.topics = [];
				}
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.switchTable = function () {
			this.currentPage = 1;
			this.doSearch();
		};

		if (this.searchText) {
			this.doSearch(this.currentPage);
		}

	}

})();