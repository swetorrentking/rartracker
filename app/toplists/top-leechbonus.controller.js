(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('TopLeechbonusController', function ($scope, UsersResource) {

			UsersResource.Users.query({id: 'leechbonustop'}, function (data) {
				$scope.users = data;
			}, function () {
				$scope.error = true;
			});

		});
})();