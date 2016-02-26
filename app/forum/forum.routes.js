(function(){
	'use strict';

	angular
		.module('app.forums')
		.config(ForumRoutes);

	function ForumRoutes($stateProvider) {

		$stateProvider
			.state('forum', {
				parent		: 'header',
				url			: '/forum',
				views		: {
					'content@': {
						templateUrl : '../app/forum/forum.template.html',
						controller  : 'ForumController as vm',
					}
				},
				redirectTo	: 'forum.forums'
			})
			.state('forum.forums', {
				url			: '/',
				templateUrl : '../app/forum/forums.template.html',
				controller  : 'ForumsController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('forum.topics', {
				url			: '/:id?page',
				templateUrl : '../app/forum/topics.template.html',
				controller  : 'TopicsController as vm',
				params		: { page: { value: '1', squash: true } },
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('forum.topic', {
				url			: '/:forumid/topic/:id/:slug?page',
				templateUrl : '../app/forum/topic.template.html',
				controller  : 'TopicController as vm',
				params		: {page: { value: '1', squash: true }, lastpost: null},
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('forum.newTopic', {
				url			: '/:id/new-topic/',
				templateUrl : '../app/forum/new-topic.template.html',
				controller  : 'NewTopicController as vm',
				resolve		: { user: authService => authService.getPromise() }
			})
			.state('forum-user-posts', {
				parent		: 'header',
				url			: '/user/:id/:username/posts?page',
				views		: {
					'content@': {
						templateUrl : '../app/forum/user-posts.template.html',
						controller  : 'UserForumPostsController as vm',
					}
				},
				params		: { page: { value: '1', squash: true } },
			})
			.state('forum-unread-topics', {
				parent		: 'header',
				url			: '/forums/unread-topics?page',
				views		: {
					'content@': {
						templateUrl : '../app/forum/unread-topics.template.html',
						controller  : 'UnreadTopicsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: { page: { value: '1', squash: true } },
			})
			.state('forum-search', {
				parent		: 'header',
				url			: '/forums/search?page&search&table',
				views		: {
					'content@': {
						templateUrl : '../app/forum/forum-search.template.html',
						controller  : 'ForumSearchController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params		: {
					page: {
						value: '1',
						squash: true
					},
					search: {
						value: '',
						squash: true
					},
					table: {
						value: 'topics',
						squash: true
					}
				}
			});
	}

}());
