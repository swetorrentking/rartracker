(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('MyRequestsController', function ($scope, $state, $stateParams, ErrorDialog, DeleteDialog, $uibModal, RequestsResource) {
			
			var getRequestData = function () {
				RequestsResource.My.get({ id: $stateParams.id }).$promise
					.then(function (data) {
						$scope.myRequests = data.myRequests;
						$scope.myVotedRequests = data.myVotedRequests;
					})
					.catch(function (error) {
						$scope.notFoundMessage = error.data;
					});
			};

			$scope.vote = function (request) {
				RequestsResource.Votes.save({
					id: request.id
				}, function (){
					getRequestData();
				});
			};

			$scope.delete = function (request) {
				var dialog = DeleteDialog('Radera request', 'Vill du radera requesten \''+request.request+'\'?', true);

				dialog.then(function (reason) {
					RequestsResource.Requests.delete({ id: request.id, reason: reason }).$promise
						.then(function () {
							getRequestData();
						})
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.giveReward = function (request) {
				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/request-reward-dialog.html',
					controller: 'RequestRewardController',
					size: 'sm',
					resolve: {
						request: function () {
							return request;
						}
					}
				});

				modalInstance.result.then(function () {
					getRequestData();
				});
			};
			getRequestData();
		});
})();