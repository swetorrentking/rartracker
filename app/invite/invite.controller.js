(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('InviteController', InviteController);

	function InviteController($timeout, $translate, ErrorDialog, InvitesResource, configs, ConfirmDialog, user) {

		this.currentUser = user;
		this.configs = configs;
		this.timeLeft = 10;
		this.createButtonText = $translate.instant('INVITE.READ_THE_RULES') + ' (10)';

		this.fetchInvites = function () {
			InvitesResource.query({}).$promise
				.then((response) => {
					this.invites = response;
				});
		};

		this.createInvite = function () {
			InvitesResource.save({}).$promise
				.then(() => {
					this.fetchInvites();
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

		this.deleteInvite = function (invite) {
			ConfirmDialog($translate.instant('INVITE.DELETE'), $translate.instant('INVITE.DELETE_CONFIRM'))
				.then(() => {
					return InvitesResource.delete({id: invite.id}).$promise;
				})
				.then(() => {
					var index = this.invites.indexOf(invite);
					this.invites.splice(index, 1);
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.countDownTick = function () {
			$timeout(() => {
				this.timeLeft -= 1;
				if (this.timeLeft > 0) {
					this.createButtonText = $translate.instant('INVITE.READ_THE_RULES') + ' (' + this.timeLeft + ')';
					this.countDownTick();
				} else {
					this.createButtonText = $translate.instant('INVITE.CREATE');
				}
			}, 1000);
		};

		this.fetchInvites();
		this.countDownTick();
	}

})();
