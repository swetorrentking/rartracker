(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('LoginAttemptsController', LoginAttemptsController);

	function LoginAttemptsController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getLoginAttempts = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.LoginAttempts.query({
				'limit': this.itemsPerPage,
				'index': index
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.loginAttempts = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.delete = function (attempt) {
			AdminResource.LoginAttempts.delete({ id: attempt.id }, () => {
				var index = this.loginAttempts.indexOf(attempt);
				this.loginAttempts.splice(index, 1);
			});
		};

		this.getLoginAttempts();
	}

})();
