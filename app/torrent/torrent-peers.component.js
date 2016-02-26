(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('torrentPeers', {
			bindings: {
				peers: '<',
				torrentSize: '<',
				myUserId: '@',
			},
			templateUrl: '../app/torrent/torrent-peers.component.template.html',
			controller: TorrentPeersController,
			controllerAs: 'vm'
		});

	function TorrentPeersController() {
		var now = Math.floor(Date.now() / 1000);

		this.calcUploadSpeed = function (peer) {
			var seconds = Math.max(1, (now - peer.st) - (now - peer.la));
			return (peer.uploaded - peer.uploadoffset) / seconds;
		};

		this.calcDownloadSpeed = function (peer) {
			if (peer.seeder == 'yes') {
				return (peer.downloaded - peer.downloadoffset) / Math.max(1, peer.finishedat - peer.st);
			} else {
				var seconds = Math.max(1, (now - peer.st) - (now - peer.la));
				return (peer.downloaded - peer.downloadoffset) / seconds;
			}
		};
	}

})();
