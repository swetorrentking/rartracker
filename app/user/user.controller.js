(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('UserController', UserController);

	function UserController($stateParams, $translate, $state, ConfirmDialog, SendMessageDialog, FriendsResource, BlocksResource, BonusShopResource, ErrorDialog, UsersResource, authService, user) {

		this.currentUser = user;

		this.loadUser = function () {
			UsersResource.Users.get({id: $stateParams.id}, (user) => {
				this.user = user;
			}, (error) => {
				this.notFoundMessage = error.data;
			});
		};

		this.togglePeers = function () {
			this.showPeers = !this.showPeers;
			if (!this.seeding) {
				UsersResource.Peers.get({id: $stateParams.id}, (peers) => {
					this.seeding = peers.seeding;
					this.leeching = peers.leeching;
				});
			}
		};

		this.toggleInvites = function () {
			this.showInvites = !this.showInvites;
			if (!this.invites) {
				UsersResource.Invitees.query({id: $stateParams.id}, (invitees) => {
					this.invitees = invitees;
				});
			}
		};

		this.toggleBonusLog = function (loadmore) {
			var limit = 0;
			if (!loadmore) {
				this.showBonuslog = !this.showBonuslog;
			} else {
				limit = 9999;
			}

			if (!this.bonuslog || limit) {
				UsersResource.Bonuslog.query({id: $stateParams.id, limit: limit}, (bonuslog) => {
					this.bonuslog = bonuslog;
				});
			}
		};

		this.toggleIpLog = function (loadmore) {
			var limit = 0;
			if (!loadmore) {
				this.showIpLog = !this.showIpLog;
			} else {
				limit = 9999;
			}

			if (!this.iplog || limit) {
				UsersResource.Iplog.query({id: $stateParams.id, limit: limit}, (iplog) => {
					this.iplog = iplog;
				});
			}
		};

		this.toggleTorrents = function () {
			this.showTorrents = !this.showTorrents;
			if (!this.torrents) {
				UsersResource.Torrents.query({id: $stateParams.id}, (torrents) => {
					this.torrents = torrents;
				});
			}
		};

		this.toggleSnatchLog = function () {
			this.showSnatchLog = !this.showSnatchLog;
			if (!this.snatchLog) {
				UsersResource.Snatchlog.query({id: $stateParams.id}, (snatchLog) => {
					this.snatchLog = snatchLog;
				});
			}
		};

		this.toggleRequests = function () {
			this.showRequests = !this.showRequests;
			if (!this.requests) {
				UsersResource.Torrents.query({id: $stateParams.id, requests: 1}, (requests) => {
					this.requests = requests;
				});
			}
		};

		this.sendMessage = function () {
			new SendMessageDialog({user: this.user});
		};

		this.buyHeart = function () {
			ConfirmDialog($translate.instant('USER.BUY_HEART'), $translate.instant('USER.BUY_HEART_BODY', {username: this.user.username}), true, $translate.instant('TORRENTS.REASON'))
			.then((reason) => {
				BonusShopResource.save({ id: 1, userId: this.user.id, motivation: reason }).$promise
					.then(() => {
						authService.statusCheck();
						this.loadUser();
					})
					.catch((error) => {
						ErrorDialog.display(error.data);
					});
			});
		};

		this.addFriend = function () {
			ConfirmDialog($translate.instant('USER.ADD_FRIEND'), $translate.instant('USER.ADD_FRIEND_BODY', {username: this.user.username}), true, $translate.instant('USER.OPTIONAL_COMMENT'))
			.then((reason) => {
				FriendsResource.save({friendid: this.user.id, comment: reason}).$promise
					.then(() => {
						$state.go('friends');
					})
					.catch((error) => {
						ErrorDialog.display(error.data);
					});
			});
		};

		this.blockUser = function () {
			var dialog = ConfirmDialog($translate.instant('USER.BLOCK_USER'), $translate.instant('USER.BLOCK_USER_BODY', {username: this.user.username}), true, $translate.instant('USER.OPTIONAL_COMMENT'));

			dialog.then((reason) => {
				BlocksResource.save({blockid: this.user.id, comment: reason}).$promise
					.then(() => {
						$state.go('friends');
					})
					.catch((error) => {
						ErrorDialog.display(error.data);
					});
			});
		};

		this.deleteIPLog = function (iplog) {
			UsersResource.Iplog.delete({id: $stateParams.id, iplogId: iplog.id}, () => {
				var index = this.iplog.indexOf(iplog);
				this.iplog.splice(index, 1);
			});
		};

		this.loadUser();

	}

})();
