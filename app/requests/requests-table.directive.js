(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('requestsTable', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/requests/requests-table.directive.html',
				scope: {
					requests: '=',
					giveReward: '&',
					delete: '&',
					vote: '&',
					report: '&',
					onSort: '&'
				},
				link: function (scope, element, attrs){
					scope.colEdit = attrs.colEdit !== undefined || false;
					scope.colVote = attrs.colVote !== undefined || false;
					scope.colReward = attrs.colReward !== undefined || false;
					scope.colUser = attrs.colUser !== undefined || false;
				},
				controller: function ($scope, ReportDialog) {
					$scope.report = function (request) {
						new ReportDialog('request', request.id, request.request);
					};
				}
			};

		});
	})();