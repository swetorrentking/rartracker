(function(){
	'use strict';

	angular.module('tracker.directives')
		.directive('posts', function () {
			return {
				restrict: 'E',
				templateUrl: '../app/components/posts.directive.html',
				scope: {
					currentUser: '=',
					searchText: '=',
					posts: '=',
					onQuote: '&',
					report: '&',
					deletePost: '&',
					gotoAnchor: '&',
					editPost: '&',
					abortEdit: '&',
					saveEdit: '&',
					editObj: '='
				},
				link: function (scope, element, attrs){
					scope.uploadCommentsView = scope.$eval(attrs.uploadCommentsView) === true ? true : false;
				}
			};

		});
})();