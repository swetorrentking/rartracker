(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('LoginAttemptsController', function ($scope, AdminResource) {

			$scope.itemsPerPage = 25;

			var getLoginAttempts = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.LoginAttempts.query({
					'limit': $scope.itemsPerPage,
					'index': index
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.loginAttempts = data;
				});
			};

			$scope.delete = function (attempt) {
				AdminResource.LoginAttempts.delete({ id: attempt.id }, function () {
					var index = $scope.loginAttempts.indexOf(attempt);
					$scope.loginAttempts.splice(index, 1);
				});
			};

			$scope.pageChanged = function () {
				getLoginAttempts();
			};

			getLoginAttempts();

		});
})();