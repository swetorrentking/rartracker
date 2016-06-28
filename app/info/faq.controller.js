(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('FaqController', FaqController);

	function FaqController($uibModal, $translate, FaqResource, ErrorDialog, ConfirmDialog, user) {

		this.currentUser = user;
		this.adminMode = false;

		FaqResource.query({}, (data) => {
			this.faq = data;
		});

		this.filterByCategory = function (categoryId) {
			return faa => faa.categ === categoryId;
		};

		this.deleteFaq = function (faq) {
			if (faq.type === 'categ') {
				if (this.faq.some(f => f.categ === faq.id)) {
					ErrorDialog.display($translate.instant('FAQ.DELETE_FAQ_ERROR'));
					return;
				}
			}

			ConfirmDialog($translate.instant('FAQ.DELETE'), $translate.instant('FAQ.DELETE_CONFIRM'))
				.then(() => {
					FaqResource.delete(faq, () => {
						var index = this.faq.indexOf(faq);
						this.faq.splice(index, 1);
					});
				});
		};

		this.editFaq = function (faq) {
			var modal = $uibModal.open({
				animation: true,
				templateUrl: '../app/info/faq-admin-dialog.template.html',
				controller: 'FaqAdminDialogController',
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'lg',
				resolve: {
					faq: () => faq,
					faqList: () => this.faq
				}
			});
			modal.result
				.then((faq) => {
					FaqResource.update(faq);
				});
		};

		this.createFaq = function () {
			var modal = $uibModal.open({
				animation: true,
				templateUrl: '../app/info/faq-admin-dialog.template.html',
				controller: 'FaqAdminDialogController',
				controllerAs: 'vm',
				backdrop: 'static',
				size: 'lg',
				resolve: {
					faq: () => {
						return {
							flag: 1,
							type: 'categ',
							categ: 0,
							order: 0,
							question: '',
							answer: ''
						};
					},
					faqList: () => this.faq
				}
			});
			modal.result
				.then((faq) => {
					FaqResource.save(faq);
					this.faq.push(faq);
				});
		};

	}

})();
