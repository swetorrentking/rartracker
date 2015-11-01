(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('torrentCell', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/torrent.directive.html',
				scope: {
					torrent: '=',
					lastBrowseDate: '@',
					searchText: '@',
					viewingTorrent: '@'
				},
				controller: function ($scope) {
					$scope.hasSpecialLeech = function (torrent) {
						if (torrent && torrent.reqid === 0) {
							var date1 = new Date(torrent.added.replace(/-/g, '/'));
							var date2 = new Date();
							var timeDiff = Math.abs(date2.getTime() - date1.getTime());
							if (timeDiff < 86400000) {
								return true;
							}
						}
						return false;
					};

					$scope.getSpecialLeech = function (added) {
						var date1 = new Date(added.replace(/-/g, '/'));
						var date2 = new Date();
						var timeDiff = 86400000 - Math.abs(date2.getTime() - date1.getTime());
						timeDiff = timeDiff / 1000;
						var minutes = Math.floor(timeDiff / 60);
						var hours = Math.floor(minutes / 60);
						minutes -= hours * 60;

						if (hours > 0) {
							return hours + ' h';
						}

						if (minutes > 0) {
							return minutes + ' min';
						}

						return '< 1 min';
					};
				}
			};

		});
})();