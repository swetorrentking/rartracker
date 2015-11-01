(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RequestRewardController', function ($scope, $uibModalInstance, RequestsResource, request) {
			$scope.reward = 20;
			$scope.create = function () {
				$scope.closeAlert();
				RequestsResource.Votes.save({
					id: request.id,
					reward: $scope.reward
				}, function (response){
					$uibModalInstance.close(response);
				}, function (error) {
					addAlert({ type: 'danger', msg: error.data });
				});
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

			var addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

		});
})();