(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('posts', {
			bindings: {
				searchText: '<',
				posts: '<',
				onQuote: '&',
				deletePost: '&',
				gotoAnchor: '&',
				editPost: '&',
				abortEdit: '&',
				saveEdit: '&',
				editObj: '=',
				uploadCommentsView: '@',
			},
			templateUrl: '../app/components/posts.component.template.html',
			controller: AllPostsController,
			controllerAs: 'vm',
		});

	function AllPostsController(authService) {
		this.currentUser = authService.getUser();
	}

})();
