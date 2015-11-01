(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopicsController', function ($scope, ForumResource, $stateParams, user, $state) {
			var dataLoaded = false;
			$scope.$parent.topic = null;

			$scope.itemsPerPage = user['topicsperpage'] === 0 ? 15 : user['topicsperpage'];
			$scope.postsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];

			var fetchTopics = function () {
				$scope.topics = null;
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				ForumResource.Topics.query({
					forumid: $stateParams.id,
					limit: $scope.itemsPerPage,
					index: index,
				}, function (topics, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.topics = topics;
					if (!dataLoaded) {
						$scope.currentPage = $stateParams.page || 1;
						dataLoaded = true;
					}
				});
			};

			$scope.setTopic = function (topic) {
				$scope.$parent.topic = topic;
			};

			$scope.pageChanged = function () {
				if (!dataLoaded) return;
				$state.transitionTo('forum.topics', { page: $scope.currentPage, id: $stateParams.id }, { notify: false });
				fetchTopics();
			};

			$scope.$parent.activateTopicsView();

			$scope.openTopic = function (topicId, forumId, page) {
				$state.go('forum.posts', {id: topicId, forumid: forumId, page: page});
			};

			$scope.ceil = function (postCount, itemsPerPage) {
				return Math.ceil(postCount/itemsPerPage);
			};

			fetchTopics();
		});
})();