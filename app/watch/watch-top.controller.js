(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('WatchTopController', function ($scope, UsersResource, WatchDialog, user) {

			UsersResource.WatchTop.get({id: user.id}).$promise
				.then(function (watchTop) {
					$scope.movies = watchTop.movies;
					$scope.tvseries = watchTop.tvseries;
				});

			$scope.open = function (movie) {
				if (!movie.myBevakId) {
					var watchDialog = WatchDialog(movie);

					watchDialog.then(function (watchObject) {
						var index;
						if (watchObject.typ === 0) {
							index = $scope.movies.indexOf(movie);
							$scope.movies[index].myBevakId = 1;
						} else {
							index = $scope.tvseries.indexOf(movie);
							$scope.tvseries[index].myBevakId = 1;
						}
					});

				} else {

					UsersResource.Watching.remove({id: user.id, watchId: movie.myBevakId});
					var index;
					if (movie.typ === 0) {
						index = $scope.movies.indexOf(movie);
						$scope.movies[index].myBevakId = null;
					} else {
						index = $scope.tvseries.indexOf(movie);
						$scope.tvseries[index].myBevakId = null;
					}
				}

			};
		});
})();