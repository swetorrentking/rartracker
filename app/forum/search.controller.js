(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ForumSearchController', function ($stateParams, $location, ForumResource) {
			this.itemsPerPage = 15;

			this.searchText = $location.search().search || '';
			this.currentPage = $location.search().page || 1;
			this.table = $location.search().table || 'topics';

			this.posts = [];
			this.topics = [];

			this.doSearch = function (initPage) {
				if (this.searchText.length < 2) {
					this.posts = [];
					this.topics = [];
					this.totalItems = 0;
					this.numberOfPages = 0;
					return;
				}
				$location.search('search', this.searchText).replace();
				$location.search('table', this.table).replace();
				$location.search('page', this.currentPage).replace();
				var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
				ForumResource.Search.query({
					table: this.table,
					search: this.searchText,
					limit: this.itemsPerPage,
					index: index,
				}, function (data, responseHeaders) {
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
					if (initPage) {
						this.currentPage = initPage;
					}
				}.bind(this));
			};

			this.switchTable = function () {
				this.currentPage = 1;
				this.doSearch();
			};

			this.pageChanged = function () {
				this.doSearch();
			};

			if (this.searchText) {
				this.doSearch(this.currentPage);
			}

		});
})();