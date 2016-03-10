(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('hashedEmailTestButton', {
			bindings: {
				userId: '<'
			},
			transclude: true,
			template: `<button type="button" class="btn btn-default" ng-click="vm.openDialog()"><ng-transclude></ng-transclude></button>`,
			controller: ReportButtonController,
			controllerAs: 'vm',
		});

	function ReportButtonController($uibModal) {

		this.openDialog = function () {
			$uibModal.open({
				template: `
					<div class="text-center">
						<form ng-submit="vm.confirm()">
							<div class="modal-header">
								<h2 class="modal-title">Testa en email</h2>
							</div>
							<div class="modal-body" style="	text-align: left;">
								Eftersom emailadresserna är hashade i databasen och inte går att avläsa så finns ändå möjligheten att "testa" om en email stämmer med hashen genom att först hasha mailadressen du testar med.
								<br /><br />
								Använd för att bekräfta vilken emailadress som är kopplad till ditt konto.
								<br /><br />
								<input type="email" class="form-control" style="width: 250px; margin: auto;" ng-model="vm.email" required />
								<br />
								<uib-alert type="success" ng-show="vm.found == 1">
								 	Matchar.
								</uib-alert>
								<uib-alert type="danger" ng-show="vm.found == 2">
								 	Matchar inte.
								</uib-alert>
							</div>
							<div class="modal-footer">
								<div class="button-group text-center">
									<button type="submit" class="btn btn-success" ng-hide="vm.loading">Testa</button>
									<button type="button" class="btn btn-success" ng-show="vm.loading" disabled="disabled">Testar...</button>
									<button type="button" class="btn btn-default" ng-click="vm.cancel()">Avbryt</button>
								</div>
							</div>
						</form>
					</div>
				`,
				resolve: {
					userId: () => this.userId
				},
				controller: HashedEmailTestDialogController,
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'md',
			});
		};

	}

	function HashedEmailTestDialogController($uibModalInstance, UsersResource, userId) {
		this.email = '';
		this.found = 0;

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.confirm = function () {
			this.loading = true;
			this.found = 0;
			UsersResource.EmailTest.get({ id: userId, email: this.email }).$promise
				.then(() => {
					this.found = 1;
				})
				.catch((error) => {
					if (error.status === 404) {
						this.found = 2;
					}
				})
				.finally(() => {
					this.loading = false;
				});
		};
	}

})();
