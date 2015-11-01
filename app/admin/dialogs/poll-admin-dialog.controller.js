(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('PollAdminDialogController', function ($scope, $uibModalInstance, poll) {
			$scope.poll = poll;

			$scope.create = function () {
				$uibModalInstance.close($scope.poll);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();