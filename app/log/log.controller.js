(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('LogController', function ($scope, LogsResource, $stateParams, $state) {
			var dataLoaded = false;
			$scope.itemsPerPage = 25;
			$scope.searchText = '';
			$scope.currentPage = $stateParams.page || 1;

			var getLogs = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				LogsResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
					'search': $scope.searchText,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.logs = data;
					if (!dataLoaded) {
						$scope.currentPage = $stateParams.page || 1;
						dataLoaded = true;
					}
				});
			};

			$scope.pageChanged = function () {
				if (!dataLoaded) return;
				$state.transitionTo('log', { page: $scope.currentPage }, { notify: false });
				getLogs();
			};

			$scope.doSearch = function (){
				getLogs();
			};

			getLogs();

		});
})();