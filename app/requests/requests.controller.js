(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('RequestsController', RequestsController);

	function RequestsController($state, $uibModal, $stateParams, RequestsResource, user) {

		this.currentUser = user;
		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;
		this.sort = $stateParams.sort;
		this.order = $stateParams.order;

		this.getRequests = function () {
			$state.go($state.current.name, {
				page: this.currentPage,
				sort: this.sort,
				order: this.order
			}, { notify: false });

			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			RequestsResource.Requests.query({
				'index': index,
				'limit': this.itemsPerPage,
				'sort': this.sort,
				'order': this.order
			}, (requests, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.requests = requests;
				if (!this.loadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.loadedFirstTime = true;
				}
			});
		};

		this.vote = function (request) {
			RequestsResource.Votes.save({
				id: request.id
			}, (response) => {
				request.reward = response.reward;
				request.votes = response.votes;
			});
		};

		this.giveReward = function (request) {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/requests/request-reward-dialog.template.html',
				controller: 'RequestRewardController',
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'sm',
				resolve: {
					request: () => request
				}
			});

			modalInstance.result.then((result) => {
				request.reward = result.reward;
				request.votes = result.votes;
			});
		};

		this.sortRequests = function (sort) {
			if (this.sort == sort) {
				if (this.order === 'asc'){
					this.order = 'desc';
				} else {
					this.order = 'asc';
				}
			} else {
				this.sort = sort;
				if (sort == 'n') {
					this.order = 'asc';
				} else {
					this.order = 'desc';
				}
			}
			this.getRequests();
		};

		this.getRequests();
	}

})();
