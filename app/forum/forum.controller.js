(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ForumController', function ($scope, $state, ForumResource) {
			$scope.currentState = $state.current.name;
			var loadingTopic = false;
			var loadingForum = false;

			var fetchForumData = function (forumId) {
				loadingForum = true;
				ForumResource.Forums.get({id: forumId}, function (forum) {
					$scope.forum = forum;
					loadingForum = false;
				});
			};

			var fetchTopic = function (topicId) {
				loadingTopic = true;
				ForumResource.Topics.get({id: topicId, forumid: $state.params.forumid}, function (topic) {
					$scope.topic = topic;
					fetchForumData(topic.forumid);
					loadingTopic = false;
				});
			};

			$scope.updateTopic = function () {
				ForumResource.Topics.update({id: $scope.topic.id, forumid: $state.params.forumid}, $scope.topic, function () {
					
				});
			};

			/* If page is reloaded we must fetch forum + topic data */
			switch ($state.current.name) {
				case 'forum.posts':
					fetchTopic($state.params.id);
				break;

				case 'forum.topics':
				case 'forum.new-topic':
					fetchForumData($state.params.id);
				break;
			}

			$scope.activatePostView = function () {
				if (!$scope.topic && !loadingTopic) {
					fetchTopic($state.params.id);
				}
				$scope.currentState = 'forum.posts';
			};

			$scope.activateTopicsView = function () {
				if (!$scope.forum && !loadingForum) {
					fetchForumData($state.params.id);
				}
				$scope.currentState = 'forum.topics';
			};

			$scope.activateForumsView = function () {
				$scope.currentState = 'forum.forums';
			};

		});
})();