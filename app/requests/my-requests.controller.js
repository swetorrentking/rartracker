(function(){
	'use strict';

	angular
		.module('app.requests')
		.controller('MyRequestsController', MyRequestsController);

	function MyRequestsController($translate, $stateParams, ErrorDialog, DeleteDialog, $uibModal, RequestsResource, user) {
		this.currentUser = user;

		this.getRequestData = function () {
			RequestsResource.My.get({ id: $stateParams.id }).$promise
				.then((data) => {
					this.myRequests = data.myRequests;
					this.myVotedRequests = data.myVotedRequests;
				});
		};

		this.delete = function (request) {
			DeleteDialog($translate.instant('REQUESTS.DELETE'), $translate.instant('REQUESTS.DELETE_CONFIRM', {name: request.request}), true)
				.then((reason) => {
					return RequestsResource.Requests.delete({ id: request.id, reason: reason }).$promise;
				})
				.then(() => {
					this.getRequestData();
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
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

			modalInstance.result.then(function () {
				this.getRequestData();
			});
		};

		this.getRequestData();

	}

})();
