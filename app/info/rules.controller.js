(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('RulesController', function ($scope, RulesResource, $uibModal, ErrorDialog, ConfirmDialog) {

			RulesResource.query({}, function (data) {
				$scope.rules = data;
			});

			$scope.Delete = function (rule) {
				var dialog = ConfirmDialog('Radera regel', 'Vill du radera den valda regeln?');

				dialog.then(function () {
					RulesResource.delete(rule, function () {
						var index = $scope.rules.indexOf(rule);
						$scope.rules.splice(index, 1);
					});
				});
			};

			$scope.Edit = function (rule) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/rules-admin-dialog.html',
					controller: 'RulesAdminDialogController',
					size: 'lg',
					resolve: {
						rule: function () {
							return rule;
						}
					}
				});
				modal.result
					.then(function (rule) {
						RulesResource.update(rule);
					});
			};

			$scope.Create = function () {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/rules-admin-dialog.html',
					controller: 'RulesAdminDialogController',
					size: 'lg',
					resolve: {
						rule: function () {
							return {
								flag: 1,
								type: 'categ',
								categ: 0,
								order: 0,
								question: '',
								answer: ''
							};
						}
					}
				});
				modal.result
					.then(function (rule) {
						RulesResource.save(rule);
						$scope.rules.push(rule);
					});
			};

		});
})();