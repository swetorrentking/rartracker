(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('ErrorDialogController', ErrorDialogController);

	function ErrorDialogController($uibModalInstance, body) {
		this.body = body;

		this.ok = function () {
			$uibModalInstance.dismiss();
		};
	}

})();
