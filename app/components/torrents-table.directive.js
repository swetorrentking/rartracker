(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('torrentsTable', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/torrents-table.directive.html',
				scope: {
					torrents: '=',
					lastBrowseDate: '=',
					searchText: '=',
					onDelete: '&',
					onSort: '&',
					onFilterCategory: '&',
					checkMode: '=',
				},
				link: function (scope, element, attrs){
					scope.header = scope.$eval(attrs.header) === false ? false : true;
					scope.columnDownload = attrs.columnDownload !== undefined || false;
					scope.columnBookmark = attrs.columnBookmark !== undefined || false;
					scope.columnComments = attrs.columnComments !== undefined || false;
					scope.columnDate = attrs.columnDate !== undefined || false;
					scope.columnSize = attrs.columnSize !== undefined || false;
					scope.columnTimesCompleted = attrs.columnTimesCompleted !== undefined || false;
					scope.columnSeeders = attrs.columnSeeders !== undefined || false;
					scope.columnLeechers = attrs.columnLeechers !== undefined || false;
					scope.columnData = attrs.columnData !== undefined || false;
					scope.columnIndex = attrs.columnIndex !== undefined || false;
					scope.columnDelete = attrs.columnDelete !== undefined || false;
					scope.columnCheck = attrs.columnCheck !== undefined || false;
				},
				controller: function ($scope, BookmarksResource) {
					$scope.bookmark = function (torrent) {
						BookmarksResource.save({torrentid: torrent.id});
						torrent.bookmarked = true;
					};

					$scope.checkAll = function () {
						var torrents = Array.prototype.slice.call($scope.torrents);
						if (torrents.length === 0) {
							return;
						}

						if (!torrents[0].selected || torrents[0].selected === 'no') {
							torrents.forEach(function (torrent) {
								torrent.selected = 'yes';
							});
						} else {
							torrents.forEach(function (torrent) {
								torrent.selected = 'no';
							});
						}
					};
				}
			};

		});
	})();