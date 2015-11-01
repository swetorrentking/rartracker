(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('UserTorrentComments', function ($scope, $stateParams, UsersResource) {
			$scope.itemsPerPage = 10;
			
			var loadPosts = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				UsersResource.Comments.query({
					id: $stateParams.id,
					limit: $scope.itemsPerPage,
					index: index,
				}, function (posts, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.numberOfPages = Math.ceil($scope.totalItems/$scope.itemsPerPage);
					$scope.posts = posts;
				});
			};

			$scope.pageChanged = function () {
				loadPosts();
			};

			loadPosts();
		});
})();