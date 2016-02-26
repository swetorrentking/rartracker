(function(){
	'use strict';

	angular
		.module('app.shared')
		.directive('goldstars', goldstars);

	function goldstars() {
		return {
			scope: {
				stars: '='
			},
			link: link
		};
	}

	function link(scope, element) {

		function generateStars(numstars) {
			var numberOfStars = Math.round(numstars);
			var starsHtml = '<span class="goldstars"> ';
			var i = 0;
			for (; i < numberOfStars; i++) {
				starsHtml += '<i class="fa fa-star"></i> ';
			}
			for (; i < 10; i++) {
				starsHtml += '<i class="fa fa-star-o"></i> ';
			}
			starsHtml += '</span> <b style="top: 3px; position: relative; font-size: 14px;">'+numstars+'/10</b>';
			element.html(starsHtml);
		}

		scope.$watch('stars', function(newValue) {
			if (newValue) {
				generateStars(newValue);
			}
		}, true);

	}

})();
