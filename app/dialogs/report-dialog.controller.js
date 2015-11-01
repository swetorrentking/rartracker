(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ReportDialogController', function ($scope, $uibModalInstance, $timeout, ReportsResource, settings) {
			$scope.settings = settings;
			$scope.reportStatus = 0;

			$scope.send = function () {
				$scope.reportStatus = 1;
				$scope.closeAlert();
				ReportsResource.save($scope.settings).$promise
					.then(function () {
						$scope.reportStatus = 2;
						$timeout(function () {
							$uibModalInstance.close();
						}, 800);
					}, function (error) {
						$scope.reportStatus = 0;
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