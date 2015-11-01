(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('MyWatchController', function ($scope, user, UsersResource, MovieDataResource, WatchDialog) {
			$scope.asyncSelected = null;

			var loadWatchings = function () {
				UsersResource.Watching.query({id: user.id}).$promise
					.then(function (watching) {
						$scope.watching = watching;
					});
			};

			$scope.updateWatch = function (w) {
				UsersResource.Watching.update({id: user.id, watchId: w.id,}, w);
			};

			$scope.deleteWatch = function (w) {
				UsersResource.Watching.remove({id: user.id, watchId: w.id}, w);
				var index = $scope.watching.indexOf(w);
				$scope.watching.splice(index, 1);
			};

			$scope.getMovieData = function (val) {
				return MovieDataResource.Search.query({search: val}).$promise
					.then(function (movies) {
						return movies;
					});
			};

			$scope.onSelected = function (movie) {
				var watchDialog = WatchDialog(movie);

				watchDialog.then(function () {
					loadWatchings();
					$scope.asyncSelected = '';
				});
			};

			loadWatchings();
		});
})();