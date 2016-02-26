(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('SqlErrorsController', SqlErrorsController);

	function SqlErrorsController($state, $stateParams, AdminResource) {
		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getLogs = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.SqlErrors.query({
				'limit': this.itemsPerPage,
				'index': index,
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

		this.getLogs();
	}

})();
