(function(){
	'use strict';

	angular
		.module('app.mailbox')
		.controller('MailboxController', MailboxController);

	function MailboxController($state, $stateParams, $q, MailboxResource, SendMessageDialog, AdminResource, authService, user) {

		this.currentUser = user;
		this.itemsPerPage = 20;
		this.currentView = parseInt($stateParams.view, 10);
		this.currentPage = $stateParams.page;

		this.getMessages = function () {
			$state.go($state.current.name, { page: this.currentPage, view: this.currentView }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			MailboxResource.query({
				'limit': this.itemsPerPage,
				'index': index,
				'location': this.currentView
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.mailbox = data;
				if (!this.hasLoadedFirstTime) {
					this.currentPage = $stateParams.page;
					this.hasLoadedFirstTime = true;
				}
			});
		};

		this.readMessage = function (message) {
			if (message.unread == 'yes') {
				message.unread = 'no';
				MailboxResource.update({id:message.id}, message, () => {
					authService.readUnreadMessage();
				});
			}
		};

		this.saveMessage = function (message) {
			message.saved = message.saved == 1 ? 0 : 1;
			MailboxResource.update({id:message.id}, message);
		};

		this.replyMessage = function (message) {
			var dialog = new SendMessageDialog(message);

			dialog
				.then(() => {
					message.svarad = 1;
				});
		};

		this.adminMessage = function (message) {
			message.fromprivate = 'yes';
			AdminResource.MailboxAdmin.save(message).$promise
				.then(() => {
					return MailboxResource.delete({id: message.id}).$promise;
				})
				.then(() => {
					$state.go('admin-mailbox');
				});
		};

		this.delete = function (message) {
			MailboxResource.delete({id: message.id}).$promise
				.then(() => {
					var index = this.mailbox.indexOf(message);
					this.mailbox.splice(index, 1);
				});
		};

		this.deleteSelected = function () {
			var promises = [];
			this.mailbox.filter(mail => mail.selected).forEach((mail) => {
				promises.push(MailboxResource.delete({id: mail.id}).$promise);
			});
			$q.all([promises])
				.then(() => {
					this.currentPage = 1;
					this.getMessages();
				});
		};

		this.readSelected = function () {
			var promises = [];
			this.mailbox.filter(mail => mail.selected && mail.unread === 'yes').forEach((mail) => {
				mail.unread = 'no';
				promises.push(MailboxResource.update({id: mail.id}, mail).$promise);
			});
			$q.all([promises])
				.then(() => {
					authService.readUnreadMessage(promises.length);
					if (promises.length > 0) {
						this.currentPage = 1;
						this.getMessages();
					}
					this.mailbox.forEach((mail) => {
						mail.selected = false;
					});
				});
		};

		this.hasSelectedMail = function () {
			return this.mailbox && this.mailbox.filter(mail => mail.selected).length;
		};

		this.checkAll = function () {
			if (this.mailbox.length === 0) {
				return;
			}

			if (!this.mailbox[0].selected || this.mailbox[0].selected !== true) {
				this.mailbox.forEach((mail) => {
					mail.selected = true;
				});
			} else {
				this.mailbox.forEach((mail) => {
					mail.selected = false;
				});
			}
		};

		this.getMessages();
	}

})();
