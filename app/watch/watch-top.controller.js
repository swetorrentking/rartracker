(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('WatchTopController', WatchTopController);

	function WatchTopController(UsersResource, WatchDialog, user) {

		this.getToplists = function () {
			UsersResource.WatchTop.get({id: user.id}).$promise
				.then((watchTop) => {
					this.movies = watchTop.movies;
					this.tvseries = watchTop.tvseries;
				});
		};

		this.addWatch = function (movie) {
			if (!movie.myBevakId) {
				WatchDialog(movie)
					.then((watchObject) => {
						var index;
						if (watchObject.typ === 0) {
							index = this.movies.indexOf(movie);
							this.movies[index].myBevakId = 1;
						} else {
							index = this.tvseries.indexOf(movie);
							this.tvseries[index].myBevakId = 1;
						}
					});
			} else {
				UsersResource.Watching.remove({id: user.id, watchId: movie.myBevakId});
				var index;
				if (movie.typ === 0) {
					index = this.movies.indexOf(movie);
					this.movies[index].myBevakId = null;
				} else {
					index = this.tvseries.indexOf(movie);
					this.tvseries[index].myBevakId = null;
				}
			}

		};

		this.getToplists();

	}

})();