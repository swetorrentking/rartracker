(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RequestsController', function ($scope, $state, $uibModal, $stateParams, RequestsResource) {
			var dataLoaded = false;
			$scope.itemsPerPage = 25;
			$scope.currentPage = $stateParams.page || 1;

			var getReleases = function () {
				$scope.requests = null;
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				RequestsResource.Requests.query({
					'index': index,
					'limit': $scope.itemsPerPage,
					'sort': $scope.sort,
					'order': $scope.order
				}, function (requests, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.requests = requests;
					if (!dataLoaded) {
						$scope.currentPage = $stateParams.page || 1;
						dataLoaded = true;
					}
				});
			};

			$scope.pageChanged = function () {
				if (!dataLoaded) return;
				$state.transitionTo('requests.requests', { page: $scope.currentPage }, { notify: false });
				getReleases();
			};

			$scope.upload = function (request) {
				$state.go('upload', {requestId: request.id, requestName: request.request});
			};

			$scope.vote = function (request) {
				RequestsResource.Votes.save({
					id: request.id
				}, function (response){
					request.reward = response.reward;
					request.votes = response.votes;
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

				modalInstance.result.then(function (result) {
					request.reward = result.reward;
					request.votes = result.votes;
				});
			};

			$scope.sortRequests = function (sort) {
				if ($scope.sort == sort) {
					if ($scope.order === 'asc'){
						$scope.order = 'desc';
					} else {
						$scope.order = 'asc';
					}
				} else {
					$scope.sort = sort;
					if (sort == 'n') {
						$scope.order = 'asc';
					} else {
						$scope.order = 'desc';
					}
				}
				getReleases();
			};

			getReleases();
		});
})();