(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('AdminSearchController', function ($scope, AdminResource, $stateParams) {
			$scope.itemsPerPage = 15;

			$scope.search = {};
			$scope.search.ip = $stateParams.ip;

			var fetchUsers = function () {
				$scope.users = null;
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.Search.get({
					'limit': $scope.itemsPerPage,
					'index': index,
					'username': $scope.search.username,
					'ip': $scope.search.ip,
					'email': $scope.search.email,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.users = data.users;
					$scope.loginAttempts = data.loginAttempts;
					$scope.iplog = data.iplog;
					$scope.recoveryLog = data.recoveryLog;
				});
			};

			$scope.pageChanged = function () {
				fetchUsers();
			};

			$scope.doSearch = function (){
				fetchUsers();
			};

			fetchUsers();

		});
})();