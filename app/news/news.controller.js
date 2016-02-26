(function(){
	'use strict';

	angular
		.module('app.shared')
		.controller('NewsController', NewsController);

	function NewsController($uibModal, $state, authService, NewsResource, user) {

		this.currentUser = user;

		this.createNews = function () {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/admin/dialogs/news-admin-dialog.template.html',
				controller: 'NewsAdminController as vm',
				size: 'md',
				resolve: {
					news: null
				}
			});

			modalInstance.result
				.then((topic) => {
					$state.go('forum.topic', {forumid: 1, id: topic.id, slug: topic.slug});
				});
		};

	}
})();
