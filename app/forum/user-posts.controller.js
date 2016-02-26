(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('UserForumPostsController', UserForumPostsController);

	function UserForumPostsController($state, $stateParams, UsersResource) {
		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;

		this.loadPosts = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			UsersResource.ForumPosts.query({
				id: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (posts, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.posts = posts;
				if (!this.hasLoadedFirsTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirsTime = true;
				}
			});
		};

		this.loadPosts();
	}
})();
