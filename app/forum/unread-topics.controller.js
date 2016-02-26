(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('UnreadTopicsController', UnreadTopicsController);

	function UnreadTopicsController($state, $stateParams, ForumResource, user) {

		this.itemsPerPage = user['topicsperpage'] === 0 ? 15 : user['topicsperpage'];
		this.postsPerPage = user['postsperpage'] === 0 ? 15 : user['postsperpage'];
		this.currentPage = $stateParams.page;

		this.fetchTopics = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			this.topics = null;
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			ForumResource.UnreadTopics.query({
				limit: this.itemsPerPage,
				index: index,
			}, (topics, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.topics = topics;
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.ceil = function (postCount, itemsPerPage) {
			return Math.ceil(postCount/itemsPerPage);
		};

		this.fetchTopics();
	}

})();