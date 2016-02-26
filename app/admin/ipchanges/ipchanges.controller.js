(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('IpChangesController', IpChangesController);

	function IpChangesController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getIpChanges = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.IpChanges.query({
				'limit': this.itemsPerPage,
				'index': index
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.ipchanges = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.getIpChanges();

	}

})();
