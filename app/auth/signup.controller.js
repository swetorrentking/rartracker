(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SignupController', function ($scope, $http, $location, $state, $stateParams, AuthService, AuthResource) {
			
			$scope.credentials = {
				gender: 0,
				format: 1,
			};

			$scope.signup = function () {
				AuthResource.save({
					username: $scope.credentials.username,
					email: $scope.credentials.email,
					gender: $scope.credentials.gender,
					age: $scope.credentials.age,
					format: $scope.credentials.format,
					password: $scope.credentials.password,
					passwordAgain: $scope.credentials.passwordAgain,
					inviteKey: $stateParams.id
				}, function () {
					AuthResource.get({
						username: $scope.credentials.username,
						password: $scope.credentials.password
					}, function (data) {
						AuthService.setUser(data.user);
						$state.go('start');
					});
				}, function (error) {
					if (error.data) {
						$scope.addAlert({ type: 'danger', msg: error.data });
					} else {
						$scope.addAlert({ type: 'danger', msg: 'Ett fel inträffade.' });
					}
				});
			};

			$scope.addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};
			
		});
})();