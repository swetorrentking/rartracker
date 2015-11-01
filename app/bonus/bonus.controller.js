(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('BonusController', function ($scope, $uibModal, AuthService, ErrorDialog, ConfirmDialog, BonusShopResource) {
			
			BonusShopResource.query({}).$promise
				.then(function (response) {
					$scope.bonusShop = response;
				});

			var simpleConfirmDialog = function (item) {

				var settingsObj = {};

				switch (item.id) {
					case 10: // crown
						settingsObj = {
							title: 'Köp krona',
							body: 'Vill du spendera '+item.price+'p på en krona-ikon?',
						};
						break;
					case 6: // invite
						settingsObj = {
							title: 'Köp invite',
							body: 'Vill du spendera '+item.price+'p på en invite?',
						};
						break;
					case 2: // request slot
						settingsObj = {
							title: 'Köp request-slot',
							body: 'Vill du spendera '+item.price+'p på en extra request-slot?',
						};
						break;
					case 8: // custom title
						settingsObj = {
							title: 'Köp custom title',
							body: 'Vill du spendera '+item.price+'p på en custom title?',
							wantReason: true,
							reasonText: 'Custom title:',
						};
						break;
				}

				var dialog = ConfirmDialog(settingsObj.title, settingsObj.body, settingsObj.wantReason, settingsObj.reasonText);

				dialog.then(function (input) {
					BonusShopResource.save({ id: item.id, input: input }).$promise
						.then(function () {
							AuthService.statusCheck();
						})
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			var confirmWithUserPickerDialog = function (item) {
				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/confirm-user-picker-dialog.html',
					controller: 'ConfirmUserPickerDialogController',
					size: 'md',
					resolve: {
						settings: function () {
							return  {
								title: 'Köp hjärta till vän',
								body: 'Vill du spendera '+item.price+'p på ett hjärta till en vän?',
								motivation: '',
								user: {
									id: 0
								}
							};
						}
					}
				});

				modalInstance.result.then(function (settings) {
					BonusShopResource.save({ id: item.id, userId: settings.user.id, motivation: settings.motivation }).$promise
						.then(function () {
							AuthService.statusCheck();
						})
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			var confirmGigabyteDialog = function (item) {
				var settingsObj = {};

				switch (item.id) {
					case 3: // minus 10gb
						settingsObj = {
							title: 'Köp bort nerladdad GB',
							body: 'Vill du köpa bort GB från din nerladdat för minst '+item.price+'p?',
							gigabyte: 10,
							user: {
								id: 0
							},
							price: item.price,
						};
						break;
					case 4: // minus 10gb on friend
						settingsObj = {
							title: 'Köp bort nerladdad GB på vän',
							body: 'Vill du köpa bort GB nerladdat på en vän för minst '+item.price+'p?',
							gigabyte: 10,
							user: {
								id: 0
							},
							showUserPicker: true,
							price: item.price,
						};
						break;
				}

				var modalInstance = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/gigabyte-dialog.html',
					controller: 'BonusGigabyteDialogController',
					size: 'md',
					resolve: {
						settings: function () {
							return settingsObj;
						},
						user: function () {
							return AuthService.getPromise();
						}
					}
				});

				modalInstance.result.then(function (settings) {
					BonusShopResource.save({ id: item.id, userId: settings.user.id, amount: settings.gigabyte }).$promise
						.then(function () {
							AuthService.statusCheck();
						})
						.catch(function (error) {
							ErrorDialog.display(error.data);
						});
				});
			};

			$scope.buyItem = function (item) {
				switch (item.id) {
					case 10:	// crown
					case 6:		// invite
					case 2:		// request slot
					case 8:		// custom title
						simpleConfirmDialog(item);
						break;
					case 1:		// heart
						confirmWithUserPickerDialog(item);
						break;
					case 3:		// minus 10gb
						confirmGigabyteDialog(item);
						break;
					case 4:		// minus 10gb friend
						confirmGigabyteDialog(item);
						break;
					default:
						return;
				}
			};

		});
})();