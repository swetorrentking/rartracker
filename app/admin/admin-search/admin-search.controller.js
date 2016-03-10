(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('AdminSearchController', AdminSearchController);

	function AdminSearchController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 15;
		this.currentPage = $stateParams.page;
		this.search = {
			ip: $stateParams.ip,
			name: $stateParams.name,
			email: $stateParams.email
		};

		this.loadUsers = function () {
			$state.go('.', {
				page: this.currentPage,
				ip: this.search.ip,
				name: this.search.name,
				email: this.search.email
			}, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			AdminResource.Search.get({
				'limit': this.itemsPerPage,
				'index': index,
				'username': this.search.name,
				'ip': this.search.ip,
				'email': this.search.email,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.users = data.users;
				this.loginAttempts = data.loginAttempts;
				this.iplog = data.iplog;
				this.recoveryLog = data.recoveryLog;
				this.emailLog = data.emailLog;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.doSearch = function () {
			this.currentPage = 1;
			this.loadUsers();
		};

		this.searchForIp = function (ip) {
			this.search = {
				ip: ip,
				name: '',
				email: ''
			};
			this.loadUsers();
		};

		this.loadUsers();

	}

})();
