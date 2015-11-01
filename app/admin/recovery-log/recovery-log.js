(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RecoveryLogsController', function ($scope, AdminResource) {

			$scope.itemsPerPage = 25;

			var getLogs = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.RecoveryLogs.query({
					'limit': $scope.itemsPerPage,
					'index': index
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.recoveryLogs = data;
				});
			};

			$scope.pageChanged = function () {
				getLogs();
			};

			getLogs();

		});
})();