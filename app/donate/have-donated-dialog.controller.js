(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('HaveDonatedController', function ($scope, $uibModalInstance, DonationsResource) {
			$scope.submitDisabled = false;
			$scope.settings = {
				type: 2,
				goldstar: 1,
				comment: ''
			};

			$scope.confirm = function () {
				$scope.submitDisabled = true;
				DonationsResource.save($scope.settings, function () {
					$uibModalInstance.close();
				}, function (error) {
					window.alert(error.data);
					$scope.submitDisabled = false;
				});

			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();