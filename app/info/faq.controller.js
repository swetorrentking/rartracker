(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('FaqController', function ($scope, $uibModal, FaqResource, ErrorDialog, ConfirmDialog) {
			$scope.adminmode = false;

			FaqResource.query({}, function (data) {
				$scope.faq = data;
			});

			$scope.filterByCategory = function (categoryId) {
				return function (faa) {
					return faa.categ === categoryId;
				};
			};

			$scope.DeleteFaq = function (faq) {
				if (faq.type === 'categ') {
					if ($scope.faq.some(function (f) { return f.categ === faq.id;})) {
						ErrorDialog.display('Du kan inte radera en Faq-huvudkategori som har underkategorier.');
						return;
					}
				}

				var dialog = ConfirmDialog('Radera faq', 'Vill du radera den valda FAQ-punkt?');

				dialog.then(function () {
					FaqResource.delete(faq, function () {
						var index = $scope.faq.indexOf(faq);
						$scope.faq.splice(index, 1);
					});
				});
			};

			$scope.EditFaq = function (faq) {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/faq-admin-dialog.html',
					controller: 'FaqAdminDialogController',
					size: 'lg',
					resolve: {
						faq: function () {
							return faq;
						},
						faqList: function () {
							return $scope.faq;
						}
					}
				});
				modal.result
					.then(function (faq) {
						FaqResource.update(faq);
					});
			};

			$scope.CreateFaq = function () {
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/admin/dialogs/faq-admin-dialog.html',
					controller: 'FaqAdminDialogController',
					size: 'lg',
					resolve: {
						faq: function () {
							return {
								flag: 1,
								type: 'categ',
								categ: 0,
								order: 0,
								question: '',
								answer: ''
							};
						},
						faqList: function () {
							return $scope.faq;
						}
					}
				});
				modal.result
					.then(function (faq) {
						FaqResource.save(faq);
						$scope.faq.push(faq);
					});
			};

		});
})();