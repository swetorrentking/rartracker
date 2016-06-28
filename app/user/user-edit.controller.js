(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('UserEditController', UserEditController);

	function UserEditController($stateParams, $state, $translate, DeleteDialog, UsersResource, userClasses, cssDesigns, authService, languageSupport, user) {

		this.currentUser = user;
		this.userClasses = userClasses;
		this.cssDesigns = cssDesigns;
		this.languageSupport = languageSupport;

		this.getUser = function () {
			UsersResource.Users.get({id: $stateParams.id}, (user) => {
				this.user = user;
			}, (error) => {
				this.notFoundMessage = error.data;
			});
		};

		this.getMaskedClasses = function () {
			if (!this.user) return [];

			var maskClasses = [];
			for (var c in this.userClasses) {
				if (this.userClasses[c].id <= this.user['class']) {
					maskClasses.push(userClasses[c]);
				}
			}
			return maskClasses;
		};

		this.generatePasskey = function () {
			var chars = '0123456789abcdef';
			var randomstring = '';
			for (var i=0; i < 32; i++) {
				var rnum = Math.floor(Math.random() * chars.length);
				randomstring += chars.substring(rnum,rnum+1);
			}
			this.user.passkey = randomstring;
		};

		this.deleteUser = function () {
			DeleteDialog($translate.instant('USER.DELETE'), $translate.instant('USER.DELETE_CONFIRM', {'username': this.user.username}))
				.then(() => {
					return UsersResource.Users.delete({id: $stateParams.id}).$promise;
				}).then(() => {
					$state.go('user', {id: this.user.id, username: this.user.username});
				});
		};

		this.saveEditProfile = function () {
			this.editButtonDisabled = true;

			UsersResource.Users.update({id: this.user.id}, this.user, () => {
				this.addAlert({ type: 'success', msg: $translate.instant('USER.SETTINGS_SAVED') });
				this.editButtonDisabled = false;
				if (this.user.id == user.id) {
					authService.statusCheck();
				} else {
					this.getUser();
				}
			}, (error) => {
				this.addAlert({ type: 'danger', msg: error.data });
				this.editButtonDisabled = false;
			});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.getUser();
	}

})();
