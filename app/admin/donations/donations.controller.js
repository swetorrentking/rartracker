(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('DonationsController', DonationsController);

	function DonationsController($state, $translate, $stateParams, $uibModal, ConfirmDialog, DonationsResource, configs) {

		this.currency = configs.DONATIONS_CURRENCY;
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
			ConfirmDialog($translate.instant('DONATE.DELETE'), $translate.instant('DONATE.DELETE_CONFIRM'))
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
