(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('InfoDialogController', InfoDialogController);

	function InfoDialogController($uibModalInstance, settings) {
		this.settings = settings;

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};
	}

})();
