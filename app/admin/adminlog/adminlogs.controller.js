(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('AdminLogsController', AdminLogsController);

	function AdminLogsController($state, $stateParams, AdminResource) {

		this.currentPage = $stateParams.page;
		this.itemsPerPage = 25;
		this.searchText = '';

		this.getLogs = function () {
			$state.go('.', {
				page: this.currentPage,
				search: this.searchText,
			}, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.Logs.query({
				'limit': this.itemsPerPage,
				'index': index,
				'searchText': this.searchText,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.logs = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.doSearch = function (){
			this.getLogs();
			this.currentPage = 1;
		};

		this.getLogs();
	}

})();
