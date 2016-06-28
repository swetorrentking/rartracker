(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('userPicker', UserPicker);

	function UserPicker() {
		return {
			restrict: 'A',
			scope: {},
			controller: UserPickerDirectiveController,
			controllerAs: 'vm',
		};
	}

	function UserPickerDirectiveController($state, $element, $uibModal, $scope) {

		this.openUserPicker = function () {
			var modalInstance = $uibModal.open({
				template: `
					<div class="container-fluid">
						<h4><i class="fa fa-user fa-fw"></i> {{ 'USER.SEARCH' | translate }}</h4>
						<input type="text" ng-model="vm.asyncSelected" placeholder="{{ 'USER.NAME' | translate }}" uib-typeahead="user.username for user in vm.getUsers($viewValue)" typeahead-loading="vm.loadingUsers" class="form-control" typeahead-on-select="vm.onSelected($item)" auto-focus />
						<br />
					</div>
				`,
				controller: UserPickerController,
				controllerAs: 'vm',
				size: 'sm',
				resolve: {
					items: () => this.items
				}
			});

			modalInstance.result
				.then((user) => {
					$state.go('user', {id: user.id, username: user.username});
				});

		};

		$element.bind('click', () => {
			this.openUserPicker();
		});

		$scope.$on('$destroy', () => {
			$element.unbind('click');
		});

	}

	function UserPickerController($uibModalInstance, UsersResource) {

		this.asyncSelected = null;

		this.onSelected = function (item) {
			$uibModalInstance.close(item);
		};

		this.getUsers = function (val) {
			return UsersResource.Users.query({search: val}).$promise
				.then(users => users);
		};
	}

})();
