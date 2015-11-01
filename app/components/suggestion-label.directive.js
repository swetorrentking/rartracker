(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('suggestionLabel', function ()
		{
			return {
				scope: {
					status: '='
				},
				controller: function ($scope, $element) {
					var labelClass = '';
					var text = '';

					function render() {
						switch($scope.status) {
							case 1:
								labelClass = 'label-success';
								text = 'FÄRDIGT';
								break;
							case 2:
								labelClass = 'label-warning';
								text = 'GODKÄNT';
								break;
							case 3:
								labelClass = 'label-danger';
								text = 'NEKAT';
								break;
							case 4:
								labelClass = 'label-default';
								text = 'INGEN ÅTGÄRD';
								break;
							default:
						}
						var html = '<span class="label '+labelClass+'">'+text+'</span>';
						$element.html(html);
					}

					$scope.$watch('status', function () {
						render();
					});

				}
			};

		});
})();