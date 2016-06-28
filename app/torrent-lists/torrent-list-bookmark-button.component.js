(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('torrentListBookmarkButton', {
			bindings: {
				torrentList: '=',
				small: '@'
			},
			template: `<button ng-click="vm.create()" ng-disabled="vm.currentUser.id === vm.torrentList.user.id" class="btn btn-default btn-sm" ng-class="{'active': vm.torrentList.bookmarked}"><i class="fa fa-bookmark"></i><span ng-show="vm.small != 'true'"> {{ 'TORRENTS.BOOKMARK' | translate }}</span></button>`,
			controller: TorrentListBookmarkButtonController,
			controllerAs: 'vm'
		});

	function TorrentListBookmarkButtonController(TorrentListsResource, authService) {
		this.currentUser = authService.getUser();
		this.create = function () {
			TorrentListsResource.Bookmarks.save({torrentList: this.torrentList.id}).$promise
				.then((result) => {
					this.torrentList.bookmarked = result['bookmarked'];
				});
		};

	}

})();
