(function(){
	'use strict';

	angular
		.module('app.torrentLists')
		.component('torrentLists', {
			bindings: {
				torrentLists: '<',
				onDelete: '&',
				onSort: '&',
				colBookmark: '@',
				colDelete: '@',
			},
			templateUrl: '../app/torrent-lists/torrent-lists.component.html',
			controller: TorrentListsComponent,
			controllerAs: 'vm'
		});

	function TorrentListsComponent(TorrentListsResource, authService) {
		this.currentUser = authService.getUser();
		this.vote = function (torrentList) {
			TorrentListsResource.Votes.save({
				id: torrentList.id
			}, (response) => {
				torrentList.votes = response.votes;
			});
		};
	}

})();
