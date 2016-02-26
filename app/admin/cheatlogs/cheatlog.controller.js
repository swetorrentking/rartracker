(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('CheatlogController', CheatlogController);

	function CheatlogController($state, $stateParams, AdminResource) {

		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;
		this.sort = $stateParams.sort;
		this.order = $stateParams.order;
		this.userid = $stateParams.userid;

		this.getLogs = function () {
			this.loading = true;
			$state.go('.', {
				page: this.currentPage,
				userid: this.userid,
				sort: this.sort,
				order: this.order
			}, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			AdminResource.CheatLogs.query({
				'limit': this.itemsPerPage,
				'index': index,
				'userid': this.userid,
				'sort': this.sort,
				'order': this.order
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.logs = data;
				this.loading = false;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.sortCol = function (col) {
			if (col === this.sort) {
				this.order = this.order === 'desc' ? 'asc' : 'desc';
			} else {
				this.order = 'desc';
			}
			this.sort = col;
			this.getLogs();
		};

		this.getLogs();
	}

})();
