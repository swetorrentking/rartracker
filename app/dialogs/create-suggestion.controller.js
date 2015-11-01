(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('CreateSuggestionController', function ($scope, $uibModalInstance, SuggestionsResource) {
			$scope.create = function () {
				$scope.closeAlert();
				SuggestionsResource.Suggest.save({}, $scope.suggestion).$promise
					.then(function (result) {
						$uibModalInstance.close(result);
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