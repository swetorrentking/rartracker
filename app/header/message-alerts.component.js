(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('messageAlerts', {
			template: `
				<div class="alert alert-warning message-dialog" ng-show="vm.currentUser.newMessages > 0 && vm.stateName != 'mailbox'">
					<span><i class="fa fa-envelope-o"></i> <a ui-sref="mailbox" translate="GENERAL.UNREAD_PM" translate-value-unread="{{ vm.currentUser.newMessages }}"></a></span>
				</div>
				<div class="alert alert-warning message-dialog" ng-show="vm.currentUser.unreadFlashNews > 0 && vm.stateName != 'news'">
					<span><i class="fa fa-info-circle"></i> <a ui-sref="news" translate="GENERAL.UNREAD_NEWS" translate-value-unread="{{ vm.currentUser.unreadFlashNews }}"></a></span>
				</div>
			`,
			controller: MessageAlertsController,
			controllerAs: 'vm'
		});

	function MessageAlertsController($scope) {

		$scope.$on('$stateChangeSuccess', (event, newState) => {
			this.stateName = newState.name;
		});

		$scope.$on('userUpdated', (event, user) => {
			this.currentUser = user;
		});

	}

})();
