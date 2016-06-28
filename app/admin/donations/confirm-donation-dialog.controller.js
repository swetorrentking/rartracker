(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('ConfirmDonationController', ConfirmDonationController);

	function ConfirmDonationController($uibModalInstance, $translate, $timeout, donation, MailboxResource, UsersResource, ErrorDialog, DonationsResource) {
		donation.sum = parseInt(donation.sum, 10);

		this.donation = donation;

		this.donation.gb = donation.sum/2;
		this.donation.bonus = donation.sum;

		this.dialogStatus = 0;
		this.message = {
			systemMessage: true,
			receiver: donation.user.id,
			body: '',
			subject: $translate.instant('ADMIN.CONFIRM_DONATE_SUBJECT')
		};

		this.updateMessageBody = function () {
			this.message.body = $translate.instant('ADMIN.CONFIRM_DONATE_BODY', {username: this.donation.user.username, sum: this.donation.sum, gb: this.donation.gb, bonus: this.donation.bonus});
		};

		this.send = function () {
			this.dialogStatus = 1;

			UsersResource.Users.get({id: this.donation.user.id}).$promise
				.then((userObj) => {
					userObj.uploaded += this.donation.gb*1073741824;
					userObj.bonuspoang = parseInt(userObj.bonuspoang, 10) + parseInt(this.donation.bonus, 10);
					if (this.donation.nostar == 1) {
						userObj.donor = 'no';
					} else {
						userObj.donor = 'yes';
					}
					return UsersResource.Users.update({id: userObj.id }, userObj).$promise;
				})
				.then(() =>{
					return MailboxResource.save(this.message).$promise;
				})
				.then(() => {
					this.donation.status = 1;
					DonationsResource.update({id: this.donation.id}, this.donation);
				})
				.then(() => {
					this.dialogStatus = 2;
					$timeout(() => {
						$uibModalInstance.close(this.model);
					}, 800);
				})
				.catch((error) => {
					this.dialogStatus = 0;
					ErrorDialog.display(error.data);
				});

		};
		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.updateMessageBody();

	}
})();
