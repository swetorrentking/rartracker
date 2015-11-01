(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('MailboxController', function ($scope, $uibModal, MailboxResource, $state, SendMessageDialog, AdminMailboxResource, ReportDialog, AuthService) {
			$scope.itemsPerPage = 20;
			$scope.currentView = 0;

			var getMessages = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				MailboxResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
					'location': $scope.currentView
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.mailbox = data;
				});
			};

			$scope.pageChanged = function () {
				getMessages();
			};

			$scope.readMessage = function (message) {
				if (message.unread == 'yes') {
					message.unread = 'no';
					MailboxResource.update({id:message.id}, message, function () {
						AuthService.readUnreadMessage();
					});
				}
			};

			$scope.saveMessage = function (message) {
				message.saved = message.saved == 1 ? 0 : 1;
				MailboxResource.update({id:message.id}, message);
			};

			$scope.switchView = function () {
				getMessages();
			};

			$scope.replyMessage = function (message) {
				var dialog = new SendMessageDialog(message);

				dialog.then(function () {
					message.svarad = 1;
				});
			};

			$scope.report = function (mail) {
				new ReportDialog('pm', mail.id, mail.subject);
			};

			$scope.adminMessage = function (message) {
				message.fromprivate = 'yes';
				AdminMailboxResource.save(message).$promise
					.then(function () {
						return MailboxResource.delete({id: message.id}).$promise;
					})
					.then(function() {
						$state.go('admin-mailbox');
					});
			};

			$scope.delete = function (message) {
				MailboxResource.delete({id: message.id}).$promise
					.then(function () {
						var index = $scope.mailbox.indexOf(message);
						$scope.mailbox.splice(index, 1);
					});
			};

			getMessages();
		});
})();