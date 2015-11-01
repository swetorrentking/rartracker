(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ErrorDialogController', function ($scope, $uibModalInstance, body) {
			$scope.body = body;

			$scope.ok = function () {
				$uibModalInstance.dismiss();
			};

		});
})();