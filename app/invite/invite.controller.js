(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('InviteController', InviteController);

	function InviteController($timeout, ErrorDialog, InvitesResource, configs, ConfirmDialog, user) {

		this.currentUser = user;
		this.configs = configs;
		this.timeLeft = 10;
		this.createButtonText = 'Läs reglerna (10)';

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
			ConfirmDialog('Radera invite', 'Vill du verkligen radera invitelänken. Den kommer bli okbrukbar!')
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
					this.createButtonText = 'Läs reglerna! (' + this.timeLeft + ')';
					this.countDownTick();
				} else {
					this.createButtonText = 'Skapa ny invite-länk';
				}
			}, 1000);
		};

		this.fetchInvites();
		this.countDownTick();
	}

})();