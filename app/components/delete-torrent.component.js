(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('deleteTorrent', {
			bindings: {
				torrent: '=',
				relatedTorrents: '=',
				model: '=',
				showBan: '@',
				showPmuploader: '@'
			},
			templateUrl: '../app/components/delete-torrent.component.html',
			controller: DeleteTorrentDirectiveController,
			controllerAs: 'vm'
		});

	function DeleteTorrentDirectiveController($translate, authService) {
		this.betterVersionExistsString = $translate.instant('TORRENTS.BETTER_VERSION_EXISTS');
		this.$onInit = () => {
			if (!this.model){
				this.model = {};
			}
			this.model.pmUploader = 0;
			this.model.pmPeers = 1;
			this.model.restoreRequest = 1;
			this.model.banRelease = 0;
			this.model.attachTorrentId = 0;
			this.model.reason = this.model.reason || '';

			this.currentUser = authService.getUser();
		};
	}

})();
