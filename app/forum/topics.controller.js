(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('TopicsController', TopicsController);

	function TopicsController($scope, ForumResource, $stateParams, user, $state) {
		var dataLoaded = false;
		$scope.$parent.vm.topic = null;

		this.currentPage = $stateParams.page;
		this.forumId = $stateParams.id;
		this.currentUser = user;
		this.itemsPerPage = user['topicsperpage'] === 0 ? 15 : user['topicsperpage'];
		this.postsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];

		this.fetchTopics = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			this.topics = null;
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			ForumResource.Topics.query({
				forumid: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (topics, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.topics = topics;
				if (!dataLoaded) {
					this.currentPage = $stateParams.page || 1;
					dataLoaded = true;
				}
			});
		};

		this.ceil = function (postCount, itemsPerPage) {
			return Math.ceil(postCount/itemsPerPage);
		};

		this.fetchTopics();
		$scope.$parent.vm.activateTopicsView();
	}
})();
