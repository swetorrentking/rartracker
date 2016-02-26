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
			template: `<button ng-click="vm.reportDialog()" class="btn btn-xs btn-default"><i class="fa fa-exclamation-triangle"></i><span ng-show="::vm.small !== 'true'"> Rapportera</span></button>`,
			controller: ReportButtonController,
			controllerAs: 'vm',
		});

	function ReportButtonController($uibModal) {

		this.reportDialog = function () {
			var title;
			switch (this.type) {
				case 'torrent':		title = 'Rapportera torrent';			break;
				case 'post':			title = 'Rapportera foruminlägg';	break;
				case 'request':		title = 'Rapportera request';			break;
				case 'pm':				title = 'Rapportera meddelande';		break;
				case 'comment':		title = 'Rapportera kommentar';		break;
				case 'subtitle':		title = 'Rapportera undertext';		break;
				case 'user':			title = 'Rapportera användare';		break;
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
