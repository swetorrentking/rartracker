(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ConfirmUserPickerDialogController', function ($scope, $uibModalInstance, UsersResource, settings) {
			$scope.settings = settings;
			$scope.asyncSelected = null;

			$scope.confirm = function () {
				$uibModalInstance.close($scope.settings);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

			$scope.onSelected = function (item) {
				$scope.settings.user = item;
			};

			$scope.getUsers = function (val) {
				return UsersResource.Users.query({search: val}).$promise.then(function (users) {
					return users;
				});
			};

		});
})();