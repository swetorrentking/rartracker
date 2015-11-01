(function(){
	'use strict';

	angular
		.module('tracker.configs', [])
		.constant('configs', {
			STATUS_CHECK_TIMER_LIMIT_MINUTES: 60 * 24,
			SITE_URL: 'https://rartracker.org',
			API_BASE_URL: '/api/v1/',
		})
		.constant('userClasses', {
			STATIST:		{id: 0, name: 'Statist'},
			SKADIS:			{id: 1, name: 'Skådis'},
			FILMSTJARNA:	{id: 2, name: 'Filmstjärna'},
			REGISSAR:		{id: 3, name: 'Regissör'},
			PRODUCENT:		{id: 4, name: 'Producent'},
			UPLOADER:		{id: 6, name: 'Uploader'},
			VIP: 			{id: 7, name: 'VIP'},
			STAFF:			{id: 8, name: 'Staff'},
		})
		.constant('categories', {
			DVDR_PAL:		{id: 1, text: 'DVDR PAL'},
			DVDR_CUSTOM:	{id: 2, text: 'DVDR CUSTOM'},
			DVDR_TV:		{id: 3, text: 'DVDR TV'},
			MOVIE_720P:		{id: 4, text: '720p Film'},
			MOVIE_1080P:	{id: 5, text: '1080p Film'},
			TV_720P: 		{id: 6, text: '720p TV'},
			TV_1080P:		{id: 7, text: '1080p TV'},
			TV_SWE:			{id: 8, text: 'Svensk TV'},
			AUDIOBOOKS:		{id: 9, text: 'Ljudböcker'},
			EBOOKS:			{id: 10, text: 'E-böcker'},
			EPAPERS:		{id: 11, text: 'E-tidningar'},
			MUSIC:			{id: 12, text: 'Musik'},
		})
		.constant('cssDesigns', {
			STANDARD:			{ id: 0, text: 'Standard'},
			BLUE:				{ id: 2, text: 'Standard blå'},
			SWEBITS:			{ id: 3, text: 'SweBits'},
			CUSTOM_EXTERNAL:	{ id: 1, text: 'Anpassad extern CSS'},
		});
})();