(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('SignupController', SignupController);

	function SignupController($state, $stateParams, authService, AuthResource, InviteValidityResource) {

		this.credentials = {
			gender: 0,
			format: 1,
		};

		this.valid = 0;

		this.checkValidity = function () {
			InviteValidityResource.get({ secret: $stateParams.id }).$promise
				.then(() => {
					this.valid = 1;
				})
				.catch((error) => {
					if (error.status === 404) {
						this.valid = 2;
					}
				});
		};

		this.signup = function () {
			AuthResource.save({
				username: this.credentials.username,
				email: this.credentials.email,
				gender: this.credentials.gender,
				age: this.credentials.age,
				format: this.credentials.format,
				password: this.credentials.password,
				passwordAgain: this.credentials.passwordAgain,
				inviteKey: $stateParams.id
			}).$promise
			.then(() => {
				return AuthResource.get({
					username: this.credentials.username,
					password: this.credentials.password
				}).$promise;
			})
			.then((data) => {
				authService.setUser(data.user);
				$state.go('start');
			})
			.catch((error) => {
				if (error.data) {
					this.addAlert({ type: 'danger', msg: error.data });
				} else {
					this.addAlert({ type: 'danger', msg: 'Ett fel inträffade.' });
				}
			});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.checkValidity();

	}

})();
