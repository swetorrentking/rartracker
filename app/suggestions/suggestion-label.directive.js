(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('suggestionLabel', {
			bindings: {
				status: '<'
			},
			template: `<span style="font-size: 12px; line-height: 12px; vertical-align: middle;" class="label {{ vm.labelClass }}">{{ vm.text }}</span>`,
			controller: SuggestionLabelController,
			controllerAs: 'vm'
		});

	function SuggestionLabelController($scope, $translate) {

		this.render = function () {
			switch(this.status) {
				case 1:
					this.labelClass = 'label-success';
					this.text = $translate.instant('SUGGEST.STATUS_DONE').toUpperCase();
						break;
				case 2:
					this.labelClass = 'label-warning';
					this.text = $translate.instant('SUGGEST.STATUS_ACCEPTED').toUpperCase();
						break;
				case 3:
					this.labelClass = 'label-danger';
					this.text = $translate.instant('SUGGEST.STATUS_DENIED').toUpperCase();
						break;
				case 4:
					this.labelClass = 'label-default';
					this.text = $translate.instant('SUGGEST.STATUS_NO_ACTION').toUpperCase();
						break;
				default:
					this.text = '';
			}
		};

		$scope.$watch(() => this.status, () => {
			this.render();
		});
	}

})();
