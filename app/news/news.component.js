(function(){
	'use strict';

	angular
		.module('app.shared')
		.component('news', {
			bindings: {
				limit: '@',
				markAsRead: '@'
			},
			templateUrl: '../app/news/news.component.template.html',
			controller: NewsDirectiveController,
			controllerAs: 'vm'
		});

	function NewsDirectiveController(NewsResource, authService, ConfirmDialog, ErrorDialog, $state, $uibModal) {

		this.$onInit = function () {
			this.currentUser = authService.getUser();

			NewsResource.query({
				limit: this.limit ? this.limit : 9999,
				markAsRead: this.markAsRead
			}, (data) => {
				this.news = data;
				if (this.markAsRead === 'true') {
					authService.readUnreadNews();
				}
			});
		};

		this.edit = function (news) {
			var modalInstance = $uibModal.open({
				animation: true,
				templateUrl: '../app/admin/dialogs/news-admin-dialog.template.html',
				controller: 'NewsAdminController as vm',
				size: 'md',
				resolve: {
					news: () => news
				}
			});

			modalInstance.result
				.then(() => {
					$state.reload();
				});
		};

		this.delete = function (news) {
			var dialog = ConfirmDialog('Radera nyhet', 'Vill du radera nyheten?');
			dialog
				.then(() => {
					return NewsResource.delete({id: news.id}).$promise;
				})
				.then(() => {
					$state.reload();
				})
				.catch((error) => {
					ErrorDialog.display(error.data);
				});
		};

	}

})();
