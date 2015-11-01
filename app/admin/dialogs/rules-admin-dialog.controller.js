(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RulesAdminDialogController', function ($scope, $uibModalInstance, rule) {
			$scope.rule = rule;

			$scope.create = function () {
				$uibModalInstance.close(rule);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();