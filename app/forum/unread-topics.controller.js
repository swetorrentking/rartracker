(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('UnreadTopicsController', function ($scope, ForumResource, $state, user) {

			$scope.itemsPerPage = user['topicsperpage'] === 0 ? 15 : user['topicsperpage'];
			$scope.postsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];

			var fetchTopics = function () {
				$scope.topics = null;
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				ForumResource.UnreadTopics.query({
					limit: $scope.itemsPerPage,
					index: index,
				}, function (topics, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.topics = topics;
				});
			};

			$scope.pageChanged = function () {
				fetchTopics();
			};

			$scope.openTopic = function (topicId, forumId, page) {
				$state.go('forum.posts', {id: topicId, forumid: forumId, page: page});
			};

			$scope.ceil = function (postCount, itemsPerPage) {
				return Math.ceil(postCount/itemsPerPage);
			};

			fetchTopics();
		});
})();