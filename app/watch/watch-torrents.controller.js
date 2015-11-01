(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('WatchTorrentsController', function ($scope, TorrentsResource, user) {

			$scope.itemsPerPage = user['torrentsperpage'] > 0 ? user['torrentsperpage'] : 20;
			$scope.hideOld = user['visagammalt'] === 0;
			$scope.lastBrowseDate = user['last_bevakabrowse'];

			var getReleases = function () {
				$scope.torrents = null;
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				TorrentsResource.Torrents.query({
					'index': index,
					'p2p': false,
					'limit': $scope.itemsPerPage,
					'watchview': true,
					'page': 'last_bevakabrowse',
				}, function (torrents, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.torrents = torrents;
				});
			};

			$scope.pageChanged = function () {
				getReleases();
			};

			getReleases();
		});
})();