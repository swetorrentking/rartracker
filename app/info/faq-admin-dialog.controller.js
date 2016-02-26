(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('FaqAdminDialogController', FaqAdminDialogController);

	function FaqAdminDialogController($uibModalInstance, faq, faqList) {
		this.faq = faq;
		this.hasChildren = faqList.some(faq => this.faq.id === faq.categ);
		this.faqList = faqList.filter(faq => faq.categ === 0);

		this.typeOptions = [
			{ id: 'item', name: 'Underkategori' },
			{ id: 'categ', name: 'Huvudkategori' }
		];

		this.options = {
			1: '(Ingen status)',
			2: 'Uppdaterad',
			3: 'Ny'
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