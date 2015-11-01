(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('BookmarksController', function ($scope, $uibModal, ErrorDialog, BookmarksResource) {
			
			BookmarksResource.query({}).$promise
				.then(function (response) {
					$scope.bookmarks = response;
				});


			$scope.delete = function (torrent) {
				BookmarksResource.delete({id: torrent.bookmarkId}).$promise
					.then(function () {
						var index = $scope.bookmarks.indexOf(torrent);
						$scope.bookmarks.splice(index, 1);
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

		});
})();