(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('UserPickerController', function ($scope, $uibModalInstance, UsersResource) {

		$scope.asyncSelected = null;

		$scope.onSelected = function (item) {
			$uibModalInstance.close(item);
		};

		$scope.getUsers = function (val) {
			return UsersResource.Users.query({search: val}).$promise.then(function (users) {
				return users;
			});
		};
	});
})();