(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('fileModel', function ()
		{
			return {
				scope: {
					fileModel: '='
				},
				link: function (scope, element) {
					element.bind('change', function (changeEvent) {
						scope.$apply(function () {
							scope.fileModel = changeEvent.target.files[0];
						});
					});
				}
			};

		});
})();