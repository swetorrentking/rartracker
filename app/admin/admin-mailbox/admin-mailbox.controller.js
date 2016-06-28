(function(){
	'use strict';

	angular
		.module('app.admin')
		.controller('AdminMailboxController', AdminMailboxController);

	function AdminMailboxController($state, $translate, $stateParams, $filter, user, InfoDialog, ErrorDialog, AdminResource, SendMessageDialog) {

		this.currentUser = user;
		this.itemsPerPage = 20;
		this.currentPage = $stateParams.page;

		this.getMessages = function () {
			$state.go('.', { page: this.currentPage }, { notify: false });
			var index = this.currentPage * this.itemsPerPage - this.itemsPerPage || 0;
			AdminResource.MailboxAdmin.query({
				'limit': this.itemsPerPage,
				'index': index,
			}, (data, responseHeaders) => {
				var headers = responseHeaders();
				this.totalItems = headers['x-total-count'];
				this.mailbox = data;
				if (!this.hasLoaded) {
					this.currentPage = $stateParams.page;
					this.hasLoaded = true;
				}
			});
		};

		this.replyMessage = function (message) {
			message.answeredBy = {id: user.id, username: user.username};
			message.answered = 1;
			AdminResource.MailboxAdmin.update({ id: message.id }, message).$promise
				.then(() => {
					return new SendMessageDialog({
						user: message.user,
						body: message.body,
						subject: message.subject
					});
				})
				.then((answer) => {
					var d = new Date();
					message.answer = answer.body;
					message.answeredAt = $filter('date')(d, 'yyyy-MM-dd HH:mm:ss');
					return AdminResource.MailboxAdmin.update({id:message.id}, message);
				})
				.catch((error) => {
					if (error) {
						ErrorDialog.display(error.data);
					}
				});
		};

		this.viewAnswer = function (message) {
			InfoDialog($translate.instant('ADMIN.VIEW_ADMIN_PM'), message.answer);
		};

		this.getMessages();
	}
})();
