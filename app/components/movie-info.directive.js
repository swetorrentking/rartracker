(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('movieInfo', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/movie-info.directive.html',
				scope: {
					movieData: '=',
					myself: '='
				},
				controller: function ($scope, MovieDataResource, $state) {
					$scope.refreshMovieData = function (id) {
						$scope.updatingMovieData = true;
						MovieDataResource.Refresh.get({id: id}).$promise.
							then(function() {
								$state.reload();
							});
					};
				}
			};

		});
})();