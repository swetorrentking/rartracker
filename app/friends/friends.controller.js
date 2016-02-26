(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('FriendsController', FriendsController);

	function FriendsController($uibModal, ErrorDialog, FriendsResource, BlocksResource, user) {

		this.currentUser = user;

		FriendsResource.query({}).$promise
			.then((response) => {
				this.friends = response;
			});

		BlocksResource.query({}).$promise
			.then((response) => {
				this.blocked = response;
			});

		this.deleteFriend = function (friend) {
			FriendsResource.delete({id: friend.id}).$promise
				.then(() => {
					var index = this.friends.indexOf(friend);
					this.friends.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.deleteBlock = function (enemy) {
			BlocksResource.delete({id: enemy.id}).$promise
				.then(() => {
					var index = this.blocked.indexOf(enemy);
					this.blocked.splice(index, 1);
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

	}

})();
