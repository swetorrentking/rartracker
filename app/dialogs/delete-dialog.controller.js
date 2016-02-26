(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('DeleteDialogController', DeleteDialogController);

	function DeleteDialogController($uibModalInstance, settings) {
		this.settings = settings;

		this.delete = function () {
			$uibModalInstance.close(this.settings.reason);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};
	}

})();
