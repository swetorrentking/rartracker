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
								<h2 class="modal-title" translate="USER.EMAIL_TEST"></h2>
							</div>
							<div class="modal-body" style="	text-align: left;">
								{{ 'USER.EMAIL_TEST_INFORMATION' | translate }}
								<br /><br />
								<input type="email" class="form-control" style="width: 250px; margin: auto;" ng-model="vm.email" required />
								<br />
								<uib-alert type="success" ng-show="vm.found == 1">{{ 'USER.MATCHING' | translate }}</uib-alert>
								<uib-alert type="danger" ng-show="vm.found == 2">{{ 'USER.NOT_MATCHING' | translate }}</uib-alert>
							</div>
							<div class="modal-footer">
								<div class="button-group text-center">
									<button type="submit" class="btn btn-success" ng-hide="vm.loading" translate="USER.TEST"></button>
									<button type="button" class="btn btn-success" ng-show="vm.loading" disabled="disabled" translate="USER.TESTING"></button>
									<button type="button" class="btn btn-default" ng-click="vm.cancel()" translate="BUTTONS.ABORT"></button>
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
