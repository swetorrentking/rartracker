(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('RecoverController', RecoverController);

	function RecoverController($state, $translate, ConfirmDialog, ErrorDialog, RecoverResource, $stateParams) {

		this.resetByEmail = function () {
			RecoverResource.save({
				id: 'by-email',
				email: this.credentials.email
			}).$promise
			.then(() => {
				ConfirmDialog($translate.instant('RECOVER.EMAIL_SENT_SUBJECT'), $translate.instant('RECOVER.EMAIL_SENT_BODY'))
				.then(() => {
					$state.go('login');
				});
			})
			.catch((error) => {
				ErrorDialog.display(error.data);
			});

		};

		this.resetByPasskey = function () {
			RecoverResource.save({
				id: 'by-passkey',
				email: this.credentials.email,
				passkey: this.credentials.passkey
			}).$promise
			.then((data) => {
				ConfirmDialog($translate.instant('RECOVER.PASSWORD_CHANGED_SUBJECT'), $translate.instant('RECOVER.PASSWORD_CHANGED_BODY', {username: data.username, password: data.newPassword}))
				.then(function (){
					$state.go('login');
				});
			})
			.catch((error) => {
				ErrorDialog.display(error.data);
			});

		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		if ($stateParams.secret.length > 0) {
			this.gotSecret = true;
			RecoverResource.get({
				id: 'by-email',
				secret: $stateParams.secret
			}).$promise
			.then((res) => {
				this.addAlert({ type: 'success', msg: $translate.instant('RECOVER.NEW_CREDENTIALS', {username: res.username, password: res.newPassword})});
			})
			.catch((error) => {
				this.addAlert({ type: 'danger', msg: error.data });
			});
		} else {
			this.gotSecret = false;
		}


	}

})();
