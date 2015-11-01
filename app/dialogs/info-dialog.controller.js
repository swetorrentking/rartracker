(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('InfoDialogController', function ($scope, $uibModalInstance, settings) {
			$scope.settings = settings;

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();