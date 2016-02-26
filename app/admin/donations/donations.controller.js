(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('DonationsController', DonationsController);

	function DonationsController($state, $stateParams, $uibModal, ConfirmDialog, DonationsResource) {
		this.itemsPerPage = 25;
		this.currentPage = $stateParams.page;

		this.getDonations = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			DonationsResource.query({
				'limit': this.itemsPerPage,
				'index': index,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.donations = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.confirmDonation = function (donation) {
			$uibModal.open({
				animation: true,
				templateUrl: '../app/admin/donations/confirm-donation-dialog.template.html',
				controller: 'ConfirmDonationController as vm',
				size: 'md',
				resolve: {
					donation: () => donation
				}
			});
		};

		this.delete = function (donation) {
			ConfirmDialog('Radera donation', 'Vill du verkligen radera donationen?')
				.then(() => {
					return DonationsResource.delete({ id: donation.id }, () => {
						var index = this.donations.indexOf(donation);
						this.donations.splice(index, 1);
					});
				});
		};

		this.edit = function (donation) {
			$uibModal.open({
				animation: true,
				templateUrl: '../app/admin/donations/edit-donation-dialog.template.html',
				controller: 'EditDonationController as vm',
				size: 'md',
				resolve: {
					donation: () => donation
				}
			});
		};

		this.getDonations();

	}

})();
