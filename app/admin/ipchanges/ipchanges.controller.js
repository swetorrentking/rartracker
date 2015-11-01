(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('IpChangesController', function ($scope, AdminResource) {

			$scope.itemsPerPage = 25;

			var getIpChanges = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.IpChanges.query({
					'limit': $scope.itemsPerPage,
					'index': index
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.ipchanges = data;
				});
			};

			$scope.pageChanged = function () {
				getIpChanges();
			};

			getIpChanges();

		});
})();