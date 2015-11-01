(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopMoviesController', function ($scope, MovieDataResource) {

			MovieDataResource.Data.query({id: 'toplist'}, function (data) {
				$scope.moviedata = data;
			});

		});
})();