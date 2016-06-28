(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('InfoController', InfoController);

	function InfoController($translate, $timeout, ErrorDialog, AdminResource, user) {

		this.initform = function () {
			this.message = {
				sender: user.id,
				subject: '',
				body: '',
				fromprivate: 0
			};
			this.dialogStatus = 0;
		};

		this.send = function () {
			this.dialogStatus = 1;
			AdminResource.MailboxAdmin.save(this.message).$promise
				.then(() => {
					this.dialogStatus = 2;
					$timeout(() => {
						this.initform();
						this.addAlert({ type: 'success', msg: $translate.instant('INFO.MESSAGE_SENT') });
					}, 800);
				})
				.catch((error) => {
					this.dialogStatus = 0;
					ErrorDialog.display(error.data);
				});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

		this.initform();
	}

})();
