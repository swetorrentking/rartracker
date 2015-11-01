(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('AdminMailboxController', function ($scope, $filter, $uibModal, user, InfoDialog, ErrorDialog, AdminMailboxResource, SendMessageDialog) {
			$scope.itemsPerPage = 20;

			var getMessages = function () {
				var index = $scope.currentPage * $scope.itemsPerPage - $scope.itemsPerPage || 0;
				AdminMailboxResource.query({
					'limit': $scope.itemsPerPage,
					'index': index,
				}, function (data, responseHeaders) {
					var headers = responseHeaders();
					$scope.totalItems = headers['x-total-count'];
					$scope.mailbox = data;
				});
			};

			$scope.pageChanged = function () {
				getMessages();
			};

			$scope.replyMessage = function (message) {
				message.answeredBy = {id: user.id, username: user.username};
				message.answered = 1;
				AdminMailboxResource.update({id:message.id}, message, function () {
					var dialog = new SendMessageDialog({
						user: message.user,
						body: message.body,
						subject: message.subject
					});

					dialog.then(function (answer) {
						var d = new Date();
						message.answer = answer.body;
						message.answeredAt = $filter('date')(d, 'yyyy-MM-dd HH:mm:ss');
						AdminMailboxResource.update({id:message.id}, message);
					});
				}, function(error) {
					ErrorDialog.display(error.data);
				});
			};

			$scope.viewAnswer = function (message) {
				InfoDialog('Visa StaffPM', message.answer);
			};

			getMessages();
		});
})();