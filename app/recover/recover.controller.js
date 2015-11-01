(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RecoverController', function ($scope, $state, ConfirmDialog, ErrorDialog, RecoverResource, $stateParams) {

			if ($stateParams.secret.length > 0) {
				$scope.gotSecret = true;
				RecoverResource.get({
					id: 'by-email',
					secret: $stateParams.secret
				}).$promise
					.then(function(res) {
						addAlert({ type: 'success', msg: 'Ditt lösenord har blivit återställt. Nya uppgifter:\n\nAnvändarnamn: [b]' + res.username + '[/b]\nLösenord: [b]' + res.newPassword + '[/b]' });
					})
					.catch(function (error) {
						addAlert({ type: 'danger', msg: error.data });
					});
			} else {
				$scope.gotSecret = false;
			}

			$scope.resetByEmail = function () {
				RecoverResource.save({
					id: 'by-email',
					email: $scope.credentials.email
				}).$promise
					.then(function() {
						var dialog = ConfirmDialog('Email skickat!', 'Ett email ska nu vara skickat till e-mailadressen. Kolla spam-lådan. Om mailet inte anlänt inom några minuter kan det ha blockats och du måste pröva metod 2 eller kontakta vår #rartracker-supportkanal.');
						dialog.then(function (){
							$state.go('login');
						});
					})
					.catch(function (error) {
						ErrorDialog.display(error.data);
					});
				
			};

			$scope.resetByPasskey = function () {
				RecoverResource.save({
					id: 'by-passkey',
					email: $scope.credentials.email,
					passkey: $scope.credentials.passkey
				}).$promise
					.then(function(data) {
						var dialog = ConfirmDialog('Lösenord bytt', 'Lösenordet på ditt konto [b]'+data.username+'[/b] är nu satt till [b]' + data.newPassword + '[/b].\n\n Tryck bekräfta för att gå till login-sidan, logga in och byt lösenord.');
						dialog.then(function (){
							$state.go('login');
						});
					})
					.catch(function (error) {
						ErrorDialog.display(error.data);
					});
				
			};

			var addAlert = function (obj) {
				$scope.alert = obj;
			};

		});
})();