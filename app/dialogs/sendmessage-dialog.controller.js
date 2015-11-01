(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('SendmessageController', function ($scope, $uibModalInstance, $timeout, message, MailboxResource) {
			
			if (message.subject) {
				message.subject = message.subject.toString();
			}
			if (message.body) {
				message.body = message.body.toString();
			}

			$scope.user = message.user;
			$scope.dialogStatus = 0;
			$scope.message = {
				receiver: message.user.id,
				replyTo: message.id,
				body: message.body ? '\n\n-------- ' + message.user.username + ' skrev: --------\n' + message.body : '',
				subject: message.subject ? message.subject.substring(0, 5) === 'Svar:' ? message.subject : 'Svar: ' + message.subject : ''
			};

			$scope.send = function () {
				$scope.dialogStatus = 1;
				$scope.closeAlert();
				MailboxResource.save({}, $scope.message).$promise
					.then(function () {
						$scope.dialogStatus = 2;
						$timeout(function () {
							$uibModalInstance.close($scope.message);
						}, 800);
					}, function (error) {
						$scope.dialogStatus = 0;
						addAlert({ type: 'danger', msg: error.data });
					});
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

			var addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};

		});
})();