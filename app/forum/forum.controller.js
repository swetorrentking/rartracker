(function(){
	'use strict';

	angular
		.module('app.forums')
		.controller('ForumController', ForumController);

	function ForumController($state, ForumResource) {

		this.currentState = $state.current.name;
		var loadingTopic = false;
		var loadingForum = false;

		this.fetchForumData = function (forumId) {
			loadingForum = true;
			ForumResource.Forums.get({id: forumId}, (forum) => {
				this.forum = forum;
				loadingForum = false;
			});
		};

		this.fetchTopic = function (topicId) {
			loadingTopic = true;
			ForumResource.Topics.get({id: topicId, forumid: $state.params.forumid}, (topic) => {
				this.topic = topic;
				this.fetchForumData(topic.forumid);
				loadingTopic = false;
			});
		};

		/* If page is reloaded we must fetch forum + topic data */
		switch ($state.current.name) {
			case 'forum.posts':
				this.fetchTopic($state.params.id);
				break;
			case 'forum.topics':
			case 'forum.new-topic':
				this.fetchForumData($state.params.id);
				break;
		}

		this.activatePostView = function () {
			if (!this.topic && !loadingTopic) {
				this.fetchTopic($state.params.id);
			}
			this.currentState = 'forum.posts';
		};

		this.activateTopicsView = function () {
			if (!this.forum && !loadingForum) {
				this.fetchForumData($state.params.id);
			}
			this.currentState = 'forum.topics';
		};

		this.activateForumsView = function () {
			this.currentState = 'forum.forums';
		};

	}
})();
