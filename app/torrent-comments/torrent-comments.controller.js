(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TorrentCommentsController', function ($scope, AuthService, user, UsersResource) {
			AuthService.readUnreadTorrentComments();
			$scope.itemsPerPage = 10;
			
			var loadComments = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				UsersResource.TorrentComments.query({
					id: user.id,
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