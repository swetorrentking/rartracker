(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('movieInfo', {
			bindings: {
				movieData: '<',
			},
			templateUrl: '../app/components/movie-info.component.template.html',
			controller: MovieInfoController,
			controllerAs: 'vm'
		});

	function MovieInfoController(MovieDataResource, $state, authService) {

		this.refreshMovieData = function () {
			this.updatingMovieData = true;
			MovieDataResource.Refresh.get({id: this.movieData.id}).$promise
				.then(() => {
					$state.reload();
				});
		};

		this.currentUser = authService.getUser();

	}

})();
