(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.controller('TorrentListBookmarksController', TorrentListBookmarksController);

	function TorrentListBookmarksController($uibModal, ErrorDialog, TorrentListsResource, UsersResource, authService) {

		this.currentUser = authService.getUser();

		this.loadBookmarkLists = function () {
			TorrentListsResource.Bookmarks.query({}).$promise
				.then((response) => {
					this.bookmarks = response;
				});

			UsersResource.TorrentLists.query({id: this.currentUser.id}).$promise
				.then((response) => {
					this.torrentLists = response;
				});
		};

		this.delete = function (torrentList) {
			TorrentListsResource.Bookmarks.delete({id: torrentList.bookmarkId}).$promise
				.then(() => {
					var index = this.bookmarks.indexOf(torrentList);
					this.bookmarks.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.loadBookmarkLists();

	}

})();
