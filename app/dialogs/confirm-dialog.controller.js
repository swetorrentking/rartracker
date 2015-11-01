(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ConfirmDialogController', function ($scope, $uibModalInstance, settings) {
			$scope.settings = settings;

			$scope.confirm = function () {
				$uibModalInstance.close($scope.settings.reason);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();