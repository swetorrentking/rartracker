(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('CheatlogController', function ($scope, $stateParams, AdminResource) {
			$scope.itemsPerPage = 25;
			var userid = $stateParams.userid || 0;

			var getLogs = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.CheatLogs.query({
					'limit': $scope.itemsPerPage,
					'index': index,
					'userid': userid
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.logs = data;
				});
			};

			$scope.pageChanged = function () {
				getLogs();
			};

			$scope.searchUser = function (id) {
				userid = id;
				getLogs();
			};

			getLogs();

		});
})();