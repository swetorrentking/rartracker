(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('RecoveryLogsController', RecoveryLogsController);

	function RecoveryLogsController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getLogs = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.RecoveryLogs.query({
				'limit': this.itemsPerPage,
				'index': index
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.recoveryLogs = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.getLogs();

	}

})();
