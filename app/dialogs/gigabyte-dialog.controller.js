(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('BonusGigabyteDialogController', function ($scope, $uibModalInstance, user, UsersResource, settings) {
			$scope.settings = settings;
			$scope.user = user;
			$scope.asyncSelected = null;

			$scope.gbSelect = [];
			for (var i = $scope.settings.price, j = 10; i < $scope.user.bonuspoang; i+=$scope.settings.price, j+=10) {
				$scope.gbSelect.push({value: j,  label: j + ' GB för ' + i + 'p'});
			}
			$scope.settings.gigabyte = $scope.gbSelect[0].value;

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