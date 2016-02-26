(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('MyWatchController', MyWatchController);

	function MyWatchController(UsersResource, MovieDataResource, WatchDialog, user) {

		this.asyncSelected = null;

		this.loadWatchings = function () {
			UsersResource.Watching.query({id: user.id}).$promise
				.then((watching) => {
					this.watching = watching;
				});
		};

		this.updateWatch = function (w) {
			UsersResource.Watching.update({id: user.id, watchId: w.id,}, w);
		};

		this.deleteWatch = function (w) {
			UsersResource.Watching.remove({id: user.id, watchId: w.id}, w);
			var index = this.watching.indexOf(w);
			this.watching.splice(index, 1);
		};

		this.getMovieData = function (val) {
			return MovieDataResource.Search.query({search: val}).$promise
				.then(movies => movies);
		};

		this.onSelected = function (movie) {
			 WatchDialog(movie)
				.then(() => {
					this.loadWatchings();
					this.asyncSelected = '';
				});
		};

		this.loadWatchings();
	}

})();