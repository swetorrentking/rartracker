(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('BonusController', BonusController);

	function BonusController($uibModal, $translate, authService, ErrorDialog, ConfirmDialog, BonusShopResource, user) {

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
						title: $translate.instant('USER.BUY_CROWN'),
						body: $translate.instant('USER.BUY_CROWN_CONFIRM', {price: item.price})
					};
					break;
				case 6: // invite
					settingsObj = {
						title: $translate.instant('USER.BUY_INVITE'),
						body: $translate.instant('USER.BUY_INVITE_CONFIRM', {price: item.price})
					};
					break;
				case 2: // request slot
					settingsObj = {
						title: $translate.instant('USER.BUY_REQUEST_SLOT'),
						body: $translate.instant('USER.BUY_REQUEST_SLOT_CONFIRM', {price: item.price})
					};
					break;
				case 8: // custom title
					settingsObj = {
						title: $translate.instant('USER.BUY_CUSTOM_TITLE'),
						body: $translate.instant('USER.BUY_CUSTOM_TITLE_CONFIRM', {price: item.price}),
						wantReason: true,
						reasonText: $translate.instant('USER.CUSTOM_TITLE') + ':',
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
							title: $translate.instant('USER.BUY_HEART'),
							body: $translate.instant('USER.BUY_HEART_SHOP_BODY', {price: item.price}),
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
						title: $translate.instant('USER.BUY_GB_TITLE'),
						body: $translate.instant('USER.BUY_GB_BODY', {price: item.price}),
						gigabyte: 10,
						user: {
							id: 0
						},
						price: item.price,
					};
					break;
				case 4: // minus 10gb on friend
					settingsObj = {
						title: $translate.instant('USER.BUY_GB_FRIEND_TITLE'),
						body: $translate.instant('USER.BUY_GB_FRIEND_BODY', {price: item.price}),
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
