(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('fileModel', fileModel);

	function fileModel() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				fileModel: '=',
				fileChanged: '&'
			},
			controller: FileModelController,
			controllerAs: 'vm'
		};
	}

	function FileModelController($scope, $element, $timeout) {
		$element.bind('change', (changeEvent) => {
			this.fileModel = changeEvent.target.files[0];
			if (this.fileModel) {
				$timeout(() => {
					this.fileChanged();
				});
				$scope.$apply();
			}
		});

		$scope.$on('$destroy', () => {
			$element.unbind('change');
		});
	}

})();
