(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('AllTorrentCommentsController', function ($scope, CommentsResource) {
			$scope.itemsPerPage = 10;
			
			var loadComments = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				CommentsResource.query({
					limit: $scope.itemsPerPage,
					index: index,
				}, function (comments, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.numberOfPages = Math.ceil($scope.totalItems/$scope.itemsPerPage);
					$scope.comments = comments;
				});
			};

			$scope.pageChanged = function () {
				loadComments();
			};

			loadComments();
		});
})();