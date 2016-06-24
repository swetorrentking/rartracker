(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('movieInfo', {
			bindings: {
				movieData: '<',
				trailer: '@',
			},
			templateUrl: '../app/components/movie-info.component.template.html',
			controller: MovieInfoController,
			controllerAs: 'vm'
		});

	function MovieInfoController(MovieDataResource, $state, authService, $sce) {

		this.refreshMovieData = function () {
			this.updatingMovieData = true;
			MovieDataResource.Refresh.get({id: this.movieData.id}).$promise
				.then(() => {
					$state.reload();
				});
		};

		this.currentUser = authService.getUser();

		this.saveYoutubeTrailer = function () {
			let youtubeId = this.getYoutubeIdFromUrl(this.youtubeUrl);
			if (!youtubeId) {
				return;
			}

			MovieDataResource.Youtube.update({id: this.movieData.id, youtube_id: youtubeId}).$promise
				.then(() => {
					this.movieData.youtube_id = youtubeId;
					this.showTrailerUpload = false;
				});
		};

		this.removeTrailer = function () {
			MovieDataResource.Youtube.update({id: this.movieData.id, youtube_id: ''}).$promise
				.then(() => {
					this.movieData.youtube_id = '';
					this.showTrailer = false;
				});
		};

		this.getIFrameSrc = function () {
			return $sce.trustAsResourceUrl('https://www.youtube.com/embed/' + this.movieData.youtube_id + '?autoplay=1');
		};

		this.getYoutubeIdFromUrl = function (url) {
			var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
			var match = url.match(regExp);
			return (match && match[7].length==11) ? match[7] : false;
		};

	}

})();
