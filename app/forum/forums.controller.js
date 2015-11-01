(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ForumsController', function ($scope, $state, ForumResource, user) {
			$scope.postsPerPage = user['postsperpage'] || 15;
			$scope.$parent.forum = null;
			$scope.$parent.topic = null;

			ForumResource.Forums.query({}, function (forums) {
				$scope.forums = forums;
			});

			ForumResource.Online.query({}, function (onlineUsers) {
				$scope.onlineUsers = onlineUsers;
			});

			$scope.setForum = function (forum) {
				$scope.$parent.forum = forum;
			};

			$scope.$parent.activateForumsView();

			$scope.ceil = function (item) {
				return Math.ceil(item);
			};

			$scope.markAllTopicsAsRead = function () {
				ForumResource.MarkTopicsAsRead.get({}, function () {
					$state.reload();
				});
			};
		});
})();