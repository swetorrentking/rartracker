(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('ConfirmDialogController', ConfirmDialogController);

	function ConfirmDialogController($uibModalInstance, settings) {
		this.settings = settings;

		this.confirm = function () {
			$uibModalInstance.close(this.settings.reason);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

	}

})();
