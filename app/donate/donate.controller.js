(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('DonateController', DonateController);

	function DonateController($uibModal, SendMessageDialog) {

		this.haveDonated = function () {
			$uibModal.open({
				animation:		true,
				templateUrl:	'../app/donate/have-donated-dialog.template.html',
				controller:		'HaveDonatedController',
				controllerAs:	'vm',
				size:			'md',
			});
		};

		this.sendMessage = function () {
			new SendMessageDialog({user: {id:3, username: 'Akilles'}});
		};

	}

})();