(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('AllTorrentCommentsController', AllTorrentCommentsController);

	function AllTorrentCommentsController($state, $stateParams, CommentsResource) {
		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;

		this.loadComments = function () {
			$state.go('.', {page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage;
			CommentsResource.query({
				limit: this.itemsPerPage,
				index: index,
			}, (comments, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.comments = comments;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.loadComments();
	}

})();
