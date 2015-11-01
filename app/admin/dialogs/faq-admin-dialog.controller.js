(function(){
	'use strict';

	angular.module('tracker.controllers')
		.controller('FaqAdminDialogController', function ($scope, $uibModalInstance, faq, faqList) {
			$scope.faq = faq;

			$scope.hasChildren = faqList.some(function (faq) {
				return $scope.faq.id === faq.categ;
			});

			$scope.faqList = faqList.filter(function (faq) {
				return faq.categ === 0;
			});

			$scope.create = function () {
				$uibModalInstance.close(faq);
			};

			$scope.typeOptions = [
				{ id: 'item', name: 'Underkategori' },
				{ id: 'categ', name: 'Huvudkategori' }
			];

			$scope.options = {
				1: '(Ingen status)',
				2: 'Uppdaterad',
				3: 'Ny'
			};

			$scope.toInt = function (val) {
				return parseInt(val, 10);
			};

			$scope.cancel = function () {
				$uibModalInstance.dismiss();
			};

			$scope.TypeChanged = function () {
				if (faq.type === 'categ') {
					faq.categ = 0;
				} else {
					faq.categ = faqList[0].id;
				}
			};
		});
})();