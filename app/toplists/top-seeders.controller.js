(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopSeedersController', function ($scope, UsersResource) {

			UsersResource.Users.get({id: 'topseeders'}, function (data) {
				$scope.newSeeders = data.new;
				$scope.archiveSeeders = data.archive;
			}, function () {
				$scope.error = true;
			});

		});
})();