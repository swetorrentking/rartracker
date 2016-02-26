(function(){
	'use strict';

	angular
		.module('app.requests')
		.component('requestsTable', {
			bindings: {
				requests: '<',
				giveReward: '&',
				onSort: '&',
				delete: '&',
				vote: '&',
				colUser: '@',
				colEdit: '@',
				colVote: '@',
				colReward: '@',
			},
			templateUrl: '../app/requests/requests-table.component.template.html',
			controllerAs: 'vm'
		});
})();
