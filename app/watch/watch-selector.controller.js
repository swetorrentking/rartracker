(function(){
	'use strict';

	angular
		.module('app.watcher')
		.controller('WatchSelectorController', WatchSelectorController);

	function WatchSelectorController($timeout, $uibModalInstance, ErrorDialog, user, UsersResource, movie, categories) {
		this.movie = movie;
		this.dialogStatus = 0;
		this.model = {
			swesub: false,
			formats: {
				hd720: movie.category ? movie.category === categories.MOVIE_720P.id || movie.category === categories.TV_720P.id : true,
				hd1080: movie.category === categories.MOVIE_1080P.id || movie.category === categories.TV_1080P.id,
				dvdrpal: movie.category === categories.DVDR_PAL.id || movie.category === categories.DVDR_TV.id,
				dvdrcustom: movie.category === categories.DVDR_CUSTOM.id,
				bluray: movie.category === categories.BLURAY.id
			},
			imdbinfoid: movie.imdbinfoid || movie.id,
			typ: movie.typ || (movie.seasoncount === 0 ? 0 : 1)
		};

		this.isSomeSelected = function () {
			for (var i in this.model.formats) {
				if (this.model.formats[i]) {
					return true;
				}
			}
		};

		this.ok = function () {
			this.dialogStatus = 1;
			UsersResource.Watching.save({id: user.id}, this.model).$promise
				.then(() => {
					this.dialogStatus = 2;
					$timeout(() => {
						$uibModalInstance.close(this.model);
					}, 800);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
					this.dialogStatus = 0;
				});
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};
	}
})();
