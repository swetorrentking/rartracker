(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('EditDonationController', function ($scope, $uibModalInstance, $timeout, donation, MailboxResource, UsersResource, ErrorDialog, DonationsResource) {
			$scope.donation = donation;

			$scope.donation.gb = donation.sum/2;
			$scope.donation.bonus = donation.sum;
			
			$scope.dialogStatus = 0;

			$scope.send = function () {
				$scope.dialogStatus = 1;

				DonationsResource.update({id: $scope.donation.id}, $scope.donation).$promise
					.then(function () {
						$scope.dialogStatus = 2;
						$timeout(function () {
							$uibModalInstance.close($scope.model);
						}, 500);
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