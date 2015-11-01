(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('DeleteDialogController', function ($scope, $uibModalInstance, settings) {
			$scope.settings = settings;

			$scope.delete = function () {
				$uibModalInstance.close($scope.settings.reason);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();