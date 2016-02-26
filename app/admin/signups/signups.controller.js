(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('SignupsController', SignupsController);

	function SignupsController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getSignups = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.Signups.query({
				'limit': this.itemsPerPage,
				'index': index
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.signups = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.getSignups();
	}

})();
