(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('FaqAdminDialogController', FaqAdminDialogController);

	function FaqAdminDialogController($uibModalInstance, $translate, faq, faqList) {
		this.faq = faq;
		this.hasChildren = faqList.some(faq => this.faq.id === faq.categ);
		this.faqList = faqList.filter(faq => faq.categ === 0);

		this.typeOptions = [
			{ id: 'item', name: $translate.instant('FAQ.SUB_CATEGORY') },
			{ id: 'categ', name: $translate.instant('FAQ.MAIN_CATEGORY') }
		];

		this.options = {
			1: $translate.instant('FAQ.STATUS_NO_STATUS'),
			2: $translate.instant('FAQ.STATUS_UPDATED'),
			3: $translate.instant('FAQ.STATUS_NEW')
		};

		this.create = function () {
			$uibModalInstance.close(faq);
		};

		this.toInt = function (val) {
			return parseInt(val, 10);
		};

		this.cancel = function () {
			$uibModalInstance.dismiss();
		};

		this.typeChanged = function () {
			if (this.faq.type === 'categ') {
				this.faq.categ = 0;
			} else {
				this.faq.categ = faqList[0].id;
			}
		};
	}

})();
