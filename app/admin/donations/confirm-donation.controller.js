(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('ConfirmDonationController', function ($scope, $uibModalInstance, $timeout, donation, MailboxResource, UsersResource, ErrorDialog, DonationsResource) {
			
			donation.sum = parseInt(donation.sum, 10);

			$scope.donation = donation;

			$scope.donation.gb = donation.sum/2;
			$scope.donation.bonus = donation.sum;
			
			$scope.dialogStatus = 0;
			$scope.message = {
				systemMessage: true,
				receiver: donation.user.id,
				body: 'Tack så mycket [b]'+$scope.donation.user.username+'[/b] för din donation på [b]' + $scope.donation.sum + ' SEK![/b].\nDu har belönats med [b]' + $scope.donation.gb +' GB[/b] uppladdat och [b]' + $scope.donation.bonus + ' bonuspoäng[/b].\n\n:thankyou:',
				subject: 'Bekräftelse på donation'
			};

			$scope.updateMessageBody = function () {
				$scope.message.body = 'Tack så mycket [b]'+$scope.donation.user.username+'[/b] för din donation på [b]' + $scope.donation.sum + ' SEK![/b].\nDu har belönats med [b]' + $scope.donation.gb +' GB[/b] uppladdat och [b]' + $scope.donation.bonus + ' bonuspoäng[/b].\n\n:thankyou:';
			};

			$scope.send = function () {
				$scope.dialogStatus = 1;

				UsersResource.Users.get({id: $scope.donation.user.id}).$promise
					.then(function(userObj) {
						userObj.uploaded += $scope.donation.gb*1073741824;
						userObj.bonuspoang = parseInt(userObj.bonuspoang, 10) + parseInt($scope.donation.bonus, 10);
						if ($scope.donation.nostar == 1) {
							userObj.donor = 'no';
						} else {
							userObj.donor = 'yes';
						}
						return UsersResource.Users.update({id: userObj.id }, userObj).$promise;
					})
					.then(function(){
						return MailboxResource.save($scope.message).$promise;
					})
					.then(function () {
						$scope.donation.status = 1;
						DonationsResource.update({id: $scope.donation.id}, $scope.donation);
					})
					.then(function () {
						$scope.dialogStatus = 2;
						$timeout(function () {
							$uibModalInstance.close($scope.model);
						}, 800);
					})
					.catch(function (error) {
						$scope.dialogStatus = 0;
						ErrorDialog.display(error.data);
					});
				
			};
			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

		});
})();