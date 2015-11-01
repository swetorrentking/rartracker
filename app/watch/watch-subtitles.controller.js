(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('WatchingSubtitlesController', function ($scope, $uibModal, ErrorDialog, WatchingSubtitlesResource) {
			
			WatchingSubtitlesResource.query({}).$promise
				.then(function (response) {
					$scope.watchingSubtitles = response;
				});


			$scope.delete = function (torrent) {
				WatchingSubtitlesResource.delete({id: torrent.bevakaSubsId}).$promise
					.then(function () {
						var index = $scope.watchingSubtitles.indexOf(torrent);
						$scope.watchingSubtitles.splice(index, 1);
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

		});
})();