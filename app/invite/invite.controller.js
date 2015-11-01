(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('InviteController', function ($scope, $http, $uibModal, $timeout, ErrorDialog, AuthService, InvitesResource, configs) {
			$scope.configs = configs;
			$scope.timeLeft = 10;
			$scope.createButtonText = 'Läs reglerna (10)';

			var fetchInvites = function () {
				InvitesResource.query({}).$promise
					.then(function (response) {
						$scope.invites = response;
					});
			};

			$scope.createInvite = function () {
				InvitesResource.save({}).$promise
					.then(function () {
						fetchInvites();
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

			$scope.deleteInvite = function (invite) {
				InvitesResource.delete({id: invite.id}).$promise
					.then(function () {
						var index = $scope.invites.indexOf(invite);
						$scope.invites.splice(index, 1);
					})
					.catch(function(error) {
						ErrorDialog.display(error.data);
					});
			};

			var countDownTick = function () {
				$timeout(function () {
					$scope.timeLeft -= 1;
					if ($scope.timeLeft > 0) {
						$scope.createButtonText = 'Läs reglerna! (' + $scope.timeLeft + ')';
						countDownTick();
					} else {
						$scope.createButtonText = 'Skapa ny invite-länk';
					}
				}, 1000);
			};

			fetchInvites();
			countDownTick();
		});
})();