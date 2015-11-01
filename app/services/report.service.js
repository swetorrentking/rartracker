(function(){
	'use strict';

	angular.module('tracker.services')
		.service('ReportDialog', function ($uibModal) {

			return function (type, targetid, body) {
				var title;
				switch (type) {
					case 'torrent':		title = 'Rapportera torrent';		break;
					case 'post':		title = 'Rapportera foruminlägg';	break;
					case 'request':		title = 'Rapportera request';		break;
					case 'pm':			title = 'Rapportera meddelande';	break;
					case 'comment':		title = 'Rapportera kommentar';		break;
					case 'subtitle':	title = 'Rapportera undertext';		break;
					case 'user':		title = 'Rapportera användare';		break;
				}
				var modal = $uibModal.open({
					animation: true,
					templateUrl: '../app/dialogs/report-dialog.html',
					controller: 'ReportDialogController',
					size: 'md',
					resolve: {
						settings: function () {
							return {
								title: title,
								type: type,
								targetid: targetid,
								body: body,
								reason: ''
							};
						}
					}
				});
				return modal.result;
			};

		});
})();