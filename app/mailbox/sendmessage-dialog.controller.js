(function(){
	'use strict';

	angular
		.module('app.mailbox')
		.controller('SendmessageController', SendmessageController);

	function SendmessageController($scope, $uibModalInstance, $timeout, message, MailboxResource) {
		if (message.subject) {
			message.subject = message.subject.toString();
		}
		if (message.body) {
			message.body = message.body.toString();
		}

		this.user = message.user;
		this.dialogStatus = 0;
		this.message = {
			receiver: message.user.id,
			replyTo: message.id,
			body: message.body ? '\n\n-------- ' + message.user.username + ' skrev: --------\n' + message.body : '',
			subject: message.subject ? message.subject.substring(0, 5) === 'Svar:' ? message.subject : 'Svar: ' + message.subject : ''
		};

		this.send = function () {
			this.dialogStatus = 1;
			this.closeAlert();
			MailboxResource.save({}, this.message).$promise
				.then(() => {
					this.dialogStatus = 2;
					$timeout(() => {
						$uibModalInstance.close(this.message);
					}, 800);
				}, (error) => {
					this.dialogStatus = 0;
					this.addAlert({ type: 'danger', msg: error.data });
				});
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

	}

})();