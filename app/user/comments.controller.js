(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('UserTorrentComments', UserTorrentComments);

	function UserTorrentComments($state, $stateParams, UsersResource) {

		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;

		this.loadPosts = function () {

			$state.go($state.current.name, { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;

			UsersResource.Comments.query({
				id: $stateParams.id,
				limit: this.itemsPerPage,
				index: index,
			}, (posts, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.posts = posts;
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.loadPosts();
	}

})();