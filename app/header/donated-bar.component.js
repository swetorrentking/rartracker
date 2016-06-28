(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('donatedBar', {
			template: `
				<div ui-sref="donate" class="hidden-xs" style="position: absolute; top: 10px; right: 20px; cursor:pointer; color: #FFF;">
					{{ 'GENERAL.PETTY_CASH' | translate }}:
					<div style="width: 102px; height: 20px; border: 1px solid; color: #FFF;">
						<div style="background-color: {{ $ctrl.donatedProgressColor }}; width: {{ $ctrl.donatedProgress }}px; height: 18px;">
							<div style="width: 100px; height: 20px; line-height: 19px; text-align: center;">
								<b>&nbsp;{{ $ctrl.amount }}</b> {{ $ctrl.currency }}
							</div>
						</div>
					</div>
				</div>
			`,
			controller: DonatedBarController
		});

	function DonatedBarController($scope, authService, configs) {

		this.currency = configs.DONATIONS_CURRENCY;

		this.setDonatedProgress = function (amount) {
			var savedBuffer = 1300;
			var percent = amount/savedBuffer;
			percent = Math.round(percent * 100);
			if (percent >= 100) {
				percent = 100;
				this.donatedProgressColor = '#6bc75c';
			} else if (percent >= 50) {
				this.donatedProgressColor = '#c7c65c';
			} else {
				this.donatedProgressColor = '#c75c5c';
			}
			this.donatedProgress = percent;
			this.amount = amount;
		};

		$scope.$watch(() => authService.getSettings(), (settings) => {
			this.setDonatedProgress(settings['donatedAmount']);
		});

	}

})();
