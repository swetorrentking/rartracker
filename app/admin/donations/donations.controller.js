(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('DonationsController', function ($scope, $uibModal, ConfirmDialog, DonationsResource) {
			$scope.itemsPerPage = 25;

			var getDonations = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				DonationsResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.donations = data;
				});
			};

			$scope.pageChanged = function () {
				getDonations();
			};

			$scope.confirmDonation = function (donation) {
				$uibModal.open({
					animation: true,
					templateUrl: '../app/admin/donations/confirm-donation-dialog.html',
					controller: 'ConfirmDonationController',
					size: 'md',
					resolve: {
						donation: function () {
							return donation;
						}
					}
				});
			};

			$scope.delete = function (donation) {
				var dialog = ConfirmDialog('Radera donation', 'Vill du verkligen radera donationen?');

				dialog.then(function () {
					DonationsResource.delete({ id: donation.id }, function () {
						var index = $scope.donations.indexOf(donation);
						$scope.donations.splice(index, 1);
					});
				});
			};

			$scope.edit = function (donation) {
				$uibModal.open({
					animation: true,
					templateUrl: '../app/admin/donations/edit-donation-dialog.html',
					controller: 'EditDonationController',
					size: 'md',
					resolve: {
						donation: function () {
							return donation;
						}
					}
				});
			};

			getDonations();

		});
})();