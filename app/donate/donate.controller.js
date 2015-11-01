(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('DonateController', function ($scope, $uibModal, SendMessageDialog) {

			$scope.haveDonated = function () {
				$uibModal.open({
					animation: true,
					templateUrl: '../app/donate/have-donated-dialog.html',
					controller: 'HaveDonatedController',
					size: 'md',
				});
			};

			$scope.sendMessage = function () {
				new SendMessageDialog({user: {id:3, username: 'Akilles'}});
			};

		});
})();