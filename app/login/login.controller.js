(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('LoginController', LoginController);

	function LoginController($state, $translate, authService, AuthResource) {

		this.credentials = {
			username: '',
			password: '',
		};

		this.login = function () {
			AuthResource.get({
				username: this.credentials.username,
				password: this.credentials.password
			}, function (data) {
				authService.serverResponse(data).then(() => $state.go('start'));
			}, (error) => {
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

	}

})();
