(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RequestController', function ($scope, $state, $stateParams, DeleteDialog, ErrorDialog, $uibModal, RequestsResource) {
			
			var getRequestData = function () {
				RequestsResource.Requests.get({ id: $stateParams.id }).$promise
					.then(function (data) {
						$scope.request = data.request;
						$scope.votes = data.votes;
						$scope.movieData = data.movieData;
					})
					.catch(function (error) {
						$scope.notFoundMessage = error.data;
					});
			};

			$scope.upload = function (request) {
				$state.go('upload', {requestId: request.id, requestName: request.request});
			};

			$scope.vote = function (request) {
				RequestsResource.Votes.save({
					id: request.id
				}, function (){
					getRequestData();
				});
			};

			$scope.delete = function () {
				var dialog = DeleteDialog('Radera request', 'Vill du radera requesten \''+$scope.request.request+'\'?', true);

				dialog.then(function (reason) {
					RequestsResource.Requests.delete({ id: $stateParams.id, reason: reason }).$promise
						.then(function () {
							$state.go('requests.requests');
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