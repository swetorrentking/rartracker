(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('NewTopicController', NewTopicController);

	function NewTopicController($scope, $translate, ForumResource, $state, $stateParams, user, authService) {
		$scope.$parent.vm.activateTopicsView();
		this.postStatus = 0;

		var isoDate = new Date(authService.getServerTime()).toISOString();
		var currentDatetime = isoDate.replace(/T/g, ' ').replace(/Z/g, '');

		this.post = {
			id: '?',
			added: currentDatetime,
			editedat: '0000-00-00 00:00:00',
			user: user
		};

		this.createTopic = function () {
			this.closeAlert();
			this.postStatus = 1;
			ForumResource.Topics.save({
				forumid: $stateParams.id,
				subject: this.post.subject,
				sub: this.post.sub,
				body: this.post.body,
			}, (topic) => {
				$state.go('forum.topic', {forumid: $stateParams.id, id: topic.id, slug: topic.slug});
			}, (error) => {
				this.postStatus = 0;
				if (error.data) {
					this.addAlert({ type: 'danger', msg: error.data });
				} else {
					this.addAlert({ type: 'danger', msg: $translate.instant('GENERAL.ERROR_OCCURED') });
				}
			});
		};

		this.addAlert = function (obj) {
			this.alert = obj;
		};

		this.closeAlert = function() {
			this.alert = null;
		};

	}
})();
