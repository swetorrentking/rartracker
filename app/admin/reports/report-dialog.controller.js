(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('ReportDialogController', ReportDialogController);

	function ReportDialogController($uibModalInstance, $timeout, ReportsResource, settings) {
		this.settings = settings;
		this.reportStatus = 0;

		this.send = function () {
			this.reportStatus = 1;
			this.closeAlert();
			ReportsResource.save(this.settings).$promise
				.then(() => {
					this.reportStatus = 2;
					$timeout(() => {
						$uibModalInstance.close();
					}, 800);
				}, (error) => {
					this.reportStatus = 0;
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

	}

})();
