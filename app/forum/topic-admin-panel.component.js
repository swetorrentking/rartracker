(function(){
	'use strict';

	angular
		.module('app.forums')
		.component('topicAdminPanel', {
			bindings: {
				topic: '=',
			},
			templateUrl: '../app/forum/topic-admin-panel.component.template.html',
			controller: TopicAdminPanelController,
			controllerAs: 'vm'
		});

	function TopicAdminPanelController($state, $translate, $stateParams, ForumResource, DeleteDialog) {

		this.$onInit = function () {
			ForumResource.Forums.query({}, (forums) => {
				forums = Array.prototype.slice.call(forums);
				var subforums = [];
				forums.forEach((forums) => {
					forums.forums = forums.forums.map((forum) => {
						forum.forumhead = forums.name;
						return forum;
					});
					subforums = subforums.concat(forums.forums);
				});
				this.forums = subforums;
			});
		};

		this.updateTopic = function () {
			ForumResource.Topics.update({id: this.topic.id, forumid: $state.params.forumid}, this.topic);
			this.settingsSaved = true;
		};

		this.deleteTopic = function (){
			DeleteDialog($translate.instant('FORUM.DELETE_THREAD'), $translate.instant('FORUM.DELETE_THREAD_CONFIRM'), false)
				.then(() => {
					return ForumResource.Topics.delete({
						forumid: $stateParams.forumid,
						id: this.topic.id,
					}).$promise;
				})
				.then(() => {
					$state.go('forum.topics', {id: $stateParams.forumid});
				});
		};

	}

})();
