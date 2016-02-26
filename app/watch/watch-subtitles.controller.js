(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('WatchingSubtitlesController', WatchingSubtitlesController);

	function WatchingSubtitlesController($uibModal, ErrorDialog, WatchingSubtitlesResource) {

		WatchingSubtitlesResource.query({}).$promise
			.then((response) => {
				this.watchingSubtitles = response;
			});

		this.delete = function (torrent) {
			WatchingSubtitlesResource.delete({id: torrent.bevakaSubsId}).$promise
				.then(() => {
					var index = this.watchingSubtitles.indexOf(torrent);
					this.watchingSubtitles.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

	}

})();