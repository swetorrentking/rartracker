(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('TorrentCommentsController', TorrentCommentsController);

	function TorrentCommentsController($state, $stateParams, authService, user, UsersResource) {

		authService.readUnreadTorrentComments();
		this.itemsPerPage = 10;
		this.currentPage = $stateParams.page;
		
		this.loadComments = function () {
			$state.go($state.current.name, { page: this.currentPage, search: this.searchText }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			UsersResource.TorrentComments.query({
				id: user.id,
				limit: this.itemsPerPage,
				index: index,
			}, (comments, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.numberOfPages = Math.ceil(this.totalItems/this.itemsPerPage);
				this.comments = comments;
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.loadComments();
	}

})();