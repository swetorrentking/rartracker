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

	function SuggestionLabelController($scope) {

		this.render = function () {
			switch(this.status) {
				case 1:
					this.labelClass = 'label-success';
					this.text = 'FÄRDIGT';
						break;
				case 2:
					this.labelClass = 'label-warning';
					this.text = 'GODKÄNT';
						break;
				case 3:
					this.labelClass = 'label-danger';
					this.text = 'NEKAT';
						break;
				case 4:
					this.labelClass = 'label-default';
					this.text = 'INGEN ÅTGÄRD';
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
