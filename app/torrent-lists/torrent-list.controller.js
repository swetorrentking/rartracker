(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.controller('TorrentListController', TorrentListController);

	function TorrentListController($stateParams, $translate, $state, $uibModal, ErrorDialog, TorrentListsResource, authService, DeleteDialog) {

		this.currentUser = authService.getUser();

		this.loadList = function () {
			TorrentListsResource.Lists.get({ id: $stateParams.id }).$promise
				.then((torrentList) => {
					this.torrentList = torrentList;
				});
		};

		this.delete = function () {
			DeleteDialog($translate.instant('LIST.DELETE'), $translate.instant('LIST.CONFIRM_DELETE'))
				.then(() => {
					return TorrentListsResource.Lists.delete({id: $stateParams.id}).$promise;
				})
				.then(() => {
					$state.go('torrent-lists.torrent-lists');
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.vote = function () {
			TorrentListsResource.Votes.save({
				id: this.torrentList.id
			}, (response) => {
				this.torrentList.votes = response.votes;
			});
		};

		this.loadList();

	}

})();
