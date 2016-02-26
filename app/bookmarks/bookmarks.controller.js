(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('BookmarksController', BookmarksController);

	function BookmarksController($uibModal, ErrorDialog, BookmarksResource) {
		
		this.loadBookmarks = function () {
			this.bookmarks = BookmarksResource.query({}).$promise
				.then((response) => {
					this.bookmarks = response;
				});
		};

		this.delete = function (torrent) {
			BookmarksResource.delete({id: torrent.bookmarkId}).$promise
				.then(() => {
					var index = this.bookmarks.indexOf(torrent);
					this.bookmarks.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.loadBookmarks();

	}

})();