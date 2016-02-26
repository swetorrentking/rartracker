(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('RecoverController', RecoverController);

	function RecoverController($state, ConfirmDialog, ErrorDialog, RecoverResource, $stateParams) {

		this.resetByEmail = function () {
			RecoverResource.save({
				id: 'by-email',
				email: this.credentials.email
			}).$promise
			.then(() => {
				var dialog = ConfirmDialog('Email skickat!', 'Ett email ska nu vara skickat till e-mailadressen. Kolla spam-lådan. Om mailet inte anlänt inom några minuter kan det ha blockats och du måste pröva metod 2 eller kontakta vår #rartracker-supportkanal.');
				dialog.then(() => {
					$state.go('login');
				});
			})
			.catch((error) => {
				ErrorDialog.display(error.data);
			});
			
		};

		this.resetByPasskey = function () {
			RecoverResource.save({
				id: 'by-passkey',
				email: this.credentials.email,
				passkey: this.credentials.passkey
			}).$promise
			.then((data) => {
				var dialog = ConfirmDialog('Lösenord bytt', 'Lösenordet på ditt konto [b]'+data.username+'[/b] är nu satt till [b]' + data.newPassword + '[/b].\n\n Tryck bekräfta för att gå till login-sidan, logga in och byt lösenord.');
				dialog.then(function (){
					$state.go('login');
				});
			})
			.catch((error) => {
				ErrorDialog.display(error.data);
			});
			
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		if ($stateParams.secret.length > 0) {
			this.gotSecret = true;
			RecoverResource.get({
				id: 'by-email',
				secret: $stateParams.secret
			}).$promise
			.then((res) => {
				this.addAlert({ type: 'success', msg: 'Ditt lösenord har blivit återställt. Nya uppgifter:\n\nAnvändarnamn: [b]' + res.username + '[/b]\nLösenord: [b]' + res.newPassword + '[/b]' });
			})
			.catch((error) => {
				this.addAlert({ type: 'danger', msg: error.data });
			});
		} else {
			this.gotSecret = false;
		}


	}

})();