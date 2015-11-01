(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('deleteTorrent', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/delete-torrent.directive.html',
				scope: {
					torrent: '=',
					relatedTorrents: '=',
					model: '=',
					myself: '=',
					showBan: '@',
					showPmuploader: '@'
				},
				controller: function ($scope) {
					if (!$scope.model){
						$scope.model = {};
					}
					$scope.model.pmUploader = 0;
					$scope.model.pmPeers = 1;
					$scope.model.restoreRequest = 1;
					$scope.model.banRelease = 0;
					$scope.model.attachTorrentId = 0;
					$scope.model.reason = $scope.model.reason || '';
				}
			};
		});
})();