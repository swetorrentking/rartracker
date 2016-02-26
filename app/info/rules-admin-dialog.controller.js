(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('RulesAdminDialogController', RulesAdminDialogController);

	function RulesAdminDialogController($uibModalInstance, rule) {
		this.rule = rule;

		this.create = function () {
			$uibModalInstance.close(rule);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

	}

})();