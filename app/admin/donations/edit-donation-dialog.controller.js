(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('EditDonationController', EditDonationController);

	function EditDonationController($uibModalInstance, $timeout, donation, ErrorDialog, DonationsResource) {
		this.donation = donation;

		this.donation.gb = donation.sum/2;
		this.donation.bonus = donation.sum;

		this.dialogStatus = 0;

		this.send = function () {
			this.dialogStatus = 1;

			DonationsResource.update({id: this.donation.id}, this.donation).$promise
				.then(() => {
					this.dialogStatus = 2;
					$timeout(() => {
						$uibModalInstance.close(this.model);
					}, 500);
				})
				.catch((error) => {
					this.dialogStatus = 0;
					ErrorDialog.display(error.data);
				});

		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

	}

})();
