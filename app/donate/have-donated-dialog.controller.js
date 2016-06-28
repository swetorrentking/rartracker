(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('HaveDonatedController', HaveDonatedController);

	function HaveDonatedController($uibModalInstance, DonationsResource, configs) {

		this.currency = configs.DONATIONS_CURRENCY;
		this.submitDisabled = false;
		this.settings = {
			type: 2,
			goldstar: 1,
			comment: ''
		};

		this.confirm = function () {
			this.submitDisabled = true;
			DonationsResource.save(this.settings, function () {
				$uibModalInstance.close();
			}, (error) => {
				window.alert(error.data);
				this.submitDisabled = false;
			});

		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

	}

})();
