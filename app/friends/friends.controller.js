(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('FriendsController', function ($scope, $uibModal, AuthService, ErrorDialog, SendMessageDialog, FriendsResource, BlocksResource) {
			
			FriendsResource.query({}).$promise
				.then(function (response) {
					$scope.friends = response;
				});

			BlocksResource.query({}).$promise
				.then(function (response) {
					$scope.blocked = response;
				});

			$scope.deleteFriend = function (friend) {
				FriendsResource.delete({id: friend.id}).$promise
					.then(function () {
						var index = $scope.friends.indexOf(friend);
						$scope.friends.splice(index, 1);
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

			$scope.deleteBlock = function (enemy) {
				BlocksResource.delete({id: enemy.id}).$promise
					.then(function () {
						var index = $scope.blocked.indexOf(enemy);
						$scope.blocked.splice(index, 1);
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

			$scope.sendMessage = function (receiver) {
				new SendMessageDialog({user:receiver});
			};

		});
})();