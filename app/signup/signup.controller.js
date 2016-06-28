(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('SignupController', SignupController);

	function SignupController($state, $translate, $stateParams, authService, AuthResource, InviteValidityResource, languageSupport, configs) {

		this.languageSupport = languageSupport;
		this.credentials = {
			gender: 0,
			format: 1,
			inviteKey: $stateParams.id,
			language: configs.DEFAULT_LANGUAGE
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

		this.languageChanged = function () {
			$translate.use(this.credentials.language);
		};

		this.signup = function () {
			AuthResource.save(this.credentials).$promise
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
					this.addAlert({ type: 'danger', msg: $translate.instant('GENERAL.ERROR_OCCURED') });
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
