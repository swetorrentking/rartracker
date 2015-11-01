(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopTorrentsController', function ($scope, TorrentsResource) {

			TorrentsResource.Torrents.get({id: 'toplists'}, function (toplists) {
				$scope.torrentsActive = toplists.active;
				$scope.torrentsData = toplists.data;
				$scope.torrentsDownloaded = toplists.downloaded;
				$scope.torrentsSeeded = toplists.seeded;
			});

		});
})();