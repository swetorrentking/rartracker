(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SqlErrorsController', function ($scope, SqlErrorsResource) {
			$scope.itemsPerPage = 25;

			var getLogs = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				SqlErrorsResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.logs = data;
				});
			};

			$scope.pageChanged = function () {
				getLogs();
			};

			getLogs();

		});
})();