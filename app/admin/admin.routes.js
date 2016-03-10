(function(){
	'use strict';

	angular
		.module('app.admin')
		.config(AdminRoutes);

	function AdminRoutes($stateProvider) {

		$stateProvider
			.state('admin-login-attempts', {
				parent		: 'header',
				url			: '/admin-login-attempts?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/login-attempts/login-attempts.template.html',
						controller  : 'LoginAttemptsController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-signups', {
				parent		: 'header',
				url			: '/admin-signups?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/signups/signups.template.html',
						controller  : 'SignupsController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-ipchanges', {
				parent		: 'header',
				url			: '/admin-ipchanges?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/ipchanges/ipchanges.template.html',
						controller  : 'IpChangesController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-reports', {
				parent		: 'header',
				url			: '/admin-reports?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/reports/reports.template.html',
						controller  : 'ReportsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-logs', {
				parent		: 'header',
				url			: '/admin-logs?page&search',
				views			: {
					'content@': {
						templateUrl : '../app/admin/adminlog/adminlogs.template.html',
						controller  : 'AdminLogsController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true },
					search: { value: '', squash: true }
				}
			})
			.state('recovery-logs', {
				parent		: 'header',
				url			: '/recovery-logs?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/recovery-log/recovery-log.template.html',
						controller  : 'RecoveryLogsController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-sqlerrors', {
				parent		: 'header',
				url			: '/admin-sqlerrors?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/sqlerrors/sqlerrors.template.html',
						controller  : 'SqlErrorsController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-mailbox', {
				parent		: 'header',
				url			: '/admin-mailbox?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/admin-mailbox/admin-mailbox.template.html',
						controller  : 'AdminMailboxController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-donations', {
				parent		: 'header',
				url			: '/admin-donations?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/donations/donations.template.html',
						controller  : 'DonationsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('admin-search', {
				parent		: 'header',
				url			: '/admin-search?ip&name&email&page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/admin-search/admin-search.template.html',
						controller  : 'AdminSearchController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true },
					name: { value: '', squash: true },
					ip: { value: '', squash: true },
					email: { value: '', squash: true }
				}
			})
			.state('cheatlog', {
				parent		: 'header',
				url			: '/cheatlog?page&userid&sort&order',
				views			: {
					'content@': {
						templateUrl : '../app/admin/cheatlogs/cheatlog.template.html',
						controller  : 'CheatlogController as vm',
					}
				},
				params: {
					page: { value: '1', squash: true },
					sort: { value: 'date', squash: true },
					order: { value: 'desc', squash: true }
				}
			})
			.state('admin-banned', {
				parent		: 'header',
				url			: '/banned',
				views			: {
					'content@': {
						templateUrl : '../app/admin/banned/banned.template.html',
						controller  : 'BannedController as vm',
					}
				}
			})
			.state('forum-posts', {
				parent		: 'header',
				url			: '/forum-posts?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/posts/forum-posts.template.html',
						controller  : 'ForumPostsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			})
			.state('torrent-comments', {
				parent		: 'header',
				url			: '/torrent-comments?page',
				views			: {
					'content@': {
						templateUrl : '../app/admin/torrent-comments/all-torrent-comments.template.html',
						controller  : 'AllTorrentCommentsController as vm',
						resolve		: { user: authService => authService.getPromise() }
					}
				},
				params: {
					page: { value: '1', squash: true }
				}
			});

	}

}());
