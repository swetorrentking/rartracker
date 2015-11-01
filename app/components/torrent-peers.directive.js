(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('torrentPeers', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/torrent-peers.directive.html',
				scope: {
					peers: '=',
					torrentSize: '=',
					myUserId: '@',
				},
				controller: function ($scope) {
					var now = Math.floor(Date.now() / 1000);

					$scope.uploadSpeed = function (peer) {
						var seconds = Math.max(1, (now - peer.st) - (now - peer.la));
						return (peer.uploaded - peer.uploadoffset) / seconds;
					};

					$scope.downloadSpeed = function (peer) {
						if (peer.seeder == 'yes') {
							return (peer.downloaded - peer.downloadoffset) / Math.max(1, peer.finishedat - peer.st);
						} else {
							var seconds = Math.max(1, (now - peer.st) - (now - peer.la));
							return (peer.downloaded - peer.downloadoffset) / seconds;
						}
					};
				}
			};

		});
})();