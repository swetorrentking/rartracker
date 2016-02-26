(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('onPasteText', onPasteText);

	function onPasteText() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				ngModel: '=',
				onPasteText: '&'
			},
			require: 'ngModel',
			controller: OnPasteTextController,
			controllerAs: 'vm'
		};
	}

	function OnPasteTextController($scope) {
		$scope.$watch(() => this.ngModel, (newVal, oldVal) => {
			if (newVal === undefined || oldVal === undefined) {
				return;
			}
			if (newVal.length > oldVal.length + 10) {
				this.onPasteText();
			}
		});
	}

})();
