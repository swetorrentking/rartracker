(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('ConfirmUserPickerDialogController', ConfirmUserPickerDialogController);

	function ConfirmUserPickerDialogController($uibModalInstance, UsersResource, settings) {
		this.settings = settings;
		this.asyncSelected = null;

		this.confirm = function () {
			$uibModalInstance.close(this.settings);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.onSelected = function (item) {
			this.settings.user = item;
		};

		this.getUsers = function (val) {
			return UsersResource.Users.query({search: val}).$promise
				.then(users => users);
		};

	}

})();
