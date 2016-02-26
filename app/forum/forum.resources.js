(function(){
	'use strict';

	angular
		.module('app.forums')
		.factory('ForumResource', ForumResource);

	function ForumResource(resourceExtension) {
		return {
			Forums:				resourceExtension('forums/:id', { id: '@id' }),
			Topics:				resourceExtension('forums/:forumid/topics/:id', { forumid: '@forumid' }),
			Posts:				resourceExtension('forums/:forumid/topics/:topicid/posts/:id', { topicid: '@topicid', forumid: '@forumid', id: '@id' }),
			Online:				resourceExtension('forums/users-online'),
			UnreadTopics:		resourceExtension('forums/unread-topics'),
			Search:				resourceExtension('forums/search'),
			AllPosts:			resourceExtension('forums/posts'),
			MarkTopicsAsRead:	resourceExtension('forums/mark-all-topics-as-read'),
		};
	}

})();