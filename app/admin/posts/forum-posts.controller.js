(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('ForumPostsController', ForumPostsController);

	function ForumPostsController($state, $stateParams, ForumResource) {
		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;

		this.loadPosts = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			ForumResource.AllPosts.query({
				id: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (posts, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.posts = posts;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.loadPosts();
	}

})();
