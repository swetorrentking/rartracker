(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('bookmarkButton', {
			bindings: {
				torrent: '=',
				small: '@'
			},
			template: `<button ng-click="vm.create()" class="btn btn-default btn-xs" ng-class="{'disabled':vm.torrent.bookmarked}"><i class="fa fa-bookmark"></i><span ng-show="vm.small != 'true'"> Bokmärk</span></button>`,
			controller: BookmarkButtonController,
			controllerAs: 'vm'
		});

	function BookmarkButtonController(BookmarksResource) {

		this.create = function () {
			BookmarksResource.save({torrentid: this.torrent.id});
			this.torrent.bookmarked = true;
		};

	}

})();
