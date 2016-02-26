(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('BonusController', BonusController);

	function BonusController($uibModal, authService, ErrorDialog, ConfirmDialog, BonusShopResource, user) {

		this.currentUser = user;

		this.loadData = function () {
			BonusShopResource.query({}).$promise
				.then((response) => {
					this.bonusShop = response;
				});
		};

		this.buyItem = function (item) {
			switch (item.id) {
				case 10:	// crown
				case 6:		// invite
				case 2:		// request slot
				case 8:		// custom title
					this.simpleConfirmDialog(item);
					break;
				case 1:		// heart
					this.confirmWithUserPickerDialog(item);
					break;
				case 3:		// minus 10gb
					this.confirmGigabyteDialog(item);
					break;
				case 4:		// minus 10gb friend
					this.confirmGigabyteDialog(item);
					break;
				default:
					return;
			}
		};

		this.simpleConfirmDialog = function (item) {
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

			ConfirmDialog(settingsObj.title, settingsObj.body, settingsObj.wantReason, settingsObj.reasonText)
				.then((input) => {
					return BonusShopResource.save({ id: item.id, input: input }).$promise;
				})
				.then(() => {
					authService.statusCheck();
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.confirmWithUserPickerDialog = function (item) {
			var modalInstance = $uibModal.open({
				templateUrl: '../app/dialogs/confirm-user-picker-dialog.template.html',
				controller: 'ConfirmUserPickerDialogController as vm',
				backdrop: 'static',
				size: 'md',
				resolve: {
					settings: () => {
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

			modalInstance.result
				.then((settings) => {
					return BonusShopResource.save({ id: item.id, userId: settings.user.id, motivation: settings.motivation }).$promise;
				})
				.then(() => {
					authService.statusCheck();
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.confirmGigabyteDialog = function (item) {
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
				templateUrl: '../app/bonus/gigabyte-dialog.template.html',
				controller: 'BonusGigabyteDialogController as vm',
				backdrop: 'static',
				size: 'md',
				resolve: {
					settings: () => settingsObj,
					user: () => authService.getPromise()
				}
			});

			modalInstance.result
				.then((settings) => {
					return BonusShopResource.save({ id: item.id, userId: settings.user.id, amount: settings.gigabyte }).$promise;
				})
				.then(function () {
					authService.statusCheck();
				})
				.catch(function (error) {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.loadData();

	}

})();
