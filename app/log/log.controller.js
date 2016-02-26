(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('LogController', LogController);

	function LogController($stateParams, $state, LogsResource) {
		this.itemsPerPage = 25;
		this.firstLoad = true;
		this.searchText = $stateParams.search;
		this.currentPage = $stateParams.page;

		this.getLogs = function () {
			$state.go($state.current.name, { page: this.currentPage, search: this.searchText }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			LogsResource.query({
				'limit': this.itemsPerPage,
				'index': index,
				'search': this.searchText,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.logs = data;
				if (this.firstLoad) {
					this.currentPage = $stateParams.page;
					this.firstLoad = false;
				}
			});
		};

		this.doSearch = function (){
			this.currentPage = 1;
			this.getLogs();
		};

		this.getLogs();
	}

})();
