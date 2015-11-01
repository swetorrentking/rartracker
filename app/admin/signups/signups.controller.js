(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SignupsController', function ($scope, AdminResource) {

			$scope.itemsPerPage = 25;

			var getSignups = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminResource.Signups.query({
					'limit': $scope.itemsPerPage,
					'index': index
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.signups = data;
				});
			};

			$scope.pageChanged = function () {
				getSignups();
			};

			getSignups();

		});
})();