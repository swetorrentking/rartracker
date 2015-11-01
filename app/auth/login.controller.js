(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('LoginController', function ($scope, $http, $state, AuthService, AuthResource) {
			$scope.login = function () {
				$scope.doLogin(
						$scope.credentials.username,
						$scope.credentials.password
					);
			};

			$scope.doLogin = function (username, password) {
				AuthResource.get({username: username, password: password}, function (data) {
					AuthService.serverResponse(data);
					$state.go('start');
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