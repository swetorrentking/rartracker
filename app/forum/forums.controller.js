(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('ForumsController', ForumsController);

	function ForumsController($scope, $state, ForumResource, user) {
		this.currentUser = user;
		this.postsPerPage = user['postsperpage'] || 15;
		$scope.$parent.vm.forum = null;
		$scope.$parent.vm.topic = null;

		ForumResource.Forums.query({}, (forums) => {
			this.forums = forums;
		});

		ForumResource.Online.query({}, (onlineUsers) => {
			this.onlineUsers = onlineUsers;
		});

		this.setForum = function (forum) {
			$scope.$parent.vm.forum = forum;
		};

		$scope.$parent.vm.activateForumsView();

		this.ceil = function (item) {
			return Math.ceil(item);
		};

		this.markAllTopicsAsRead = function () {
			ForumResource.MarkTopicsAsRead.get({}, () => {
				$state.reload();
			});
		};
	}
})();