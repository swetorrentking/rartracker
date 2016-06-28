(function(){
	'use strict';

	angular
		.module('app.admin')
		.component('reportButton', {
			bindings: {
				small: '@',
				type: '@',
				id: '<',
				body: '<'
			},
			template: `<button ng-click="vm.reportDialog()" class="btn btn-xs btn-default"><i class="fa fa-exclamation-triangle"></i><span ng-show="::vm.small !== 'true'"> {{ 'GENERAL.REPORT' | translate }}</span></button>`,
			controller: ReportButtonController,
			controllerAs: 'vm',
		});

	function ReportButtonController($uibModal, $translate) {

		this.reportDialog = function () {
			var title;
			switch (this.type) {
				case 'torrent':		title = $translate.instant('GENERAL.REPORT_TORRENT');		break;
				case 'post':		title = $translate.instant('GENERAL.REPORT_POST');			break;
				case 'request':		title = $translate.instant('GENERAL.REPORT_REQUEST');		break;
				case 'pm':			title = $translate.instant('GENERAL.REPORT_MESSAGE');		break;
				case 'comment':		title = $translate.instant('GENERAL.REPORT_COMMENT');		break;
				case 'subtitle':	title = $translate.instant('GENERAL.REPORT_SUBTITLE');		break;
				case 'user':		title = $translate.instant('GENERAL.REPORT_USER');			break;
			}
			var modal = $uibModal.open({
				templateUrl: '../app/admin/reports/report-dialog.template.html',
				controller: 'ReportDialogController as vm',
				backdrop: 'static',
				size: 'md',
				resolve: {
					settings: () => {
						return {
							title: title,
							type: this.type,
							targetid: this.id,
							body: this.body,
							reason: ''
						};
					}
				}
			});
			return modal.result;
		};

	}

})();
