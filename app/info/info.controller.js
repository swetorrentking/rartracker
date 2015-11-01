(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('InfoController', function ($scope, AdminMailboxResource, user, $timeout, ErrorDialog) {

			var initform = function () {
				$scope.message = {
					sender: user.id,
					subject: '',
					body: '',
					fromprivate: 0
				};
				$scope.dialogStatus = 0;
			};

			$scope.send = function () {
				$scope.dialogStatus = 1;
				AdminMailboxResource.save($scope.message).$promise
					.then(function () {
						$scope.dialogStatus = 2;
						$timeout(function () {
							initform();
							addAlert({ type: 'success', msg: 'Meddelande skickat' });
						}, 800);
					})
					.catch(function(error) {
						$scope.dialogStatus = 0;
						ErrorDialog.display(error.data);
					});
			};

			var addAlert = function (obj) {
				$scope.alert = obj;
			};

			$scope.closeAlert = function() {
				$scope.alert = null;
			};


			initform();
		});
})();