(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('AdminLogsController', function ($scope, AdminLogResource) {
			$scope.itemsPerPage = 25;
			$scope.searchText = '';

			var getLogs = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminLogResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
					'searchText': $scope.searchText,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.logs = data;
				});
			};

			$scope.pageChanged = function () {
				getLogs();
			};

			$scope.doSearch = function (){
				getLogs();
			};

			getLogs();

		});
})();