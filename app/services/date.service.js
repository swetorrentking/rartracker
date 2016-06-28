(function(){
	'use strict';

	angular
		.module('app.shared')
		.service('DateService', function ($translate) {
			this.leadingZeros = function (date) {
				return date < 10 ? '0' + date : date;
			};

			this.getYMD = function (timestamp) {
				var date = new Date(timestamp*1000);
				return date.getFullYear() + '-' + this.leadingZeros((date.getMonth() + 1)) + '-' + this.leadingZeros(date.getDate());
			};

			this.getHI = function (timestamp) {
				var date = new Date(timestamp*1000);
				return this.leadingZeros(date.getHours()) + ':' + this.leadingZeros(date.getMinutes());
			};

			this.getWeekDay = function (timestamp) {
				var date = new Date(timestamp*1000);
				switch(date.getDay()) {
					case 0: return $translate.instant('GENERAL.DAYS.0');
					case 1: return $translate.instant('GENERAL.DAYS.1');
					case 2: return $translate.instant('GENERAL.DAYS.2');
					case 3: return $translate.instant('GENERAL.DAYS.3');
					case 4: return $translate.instant('GENERAL.DAYS.4');
					case 5: return $translate.instant('GENERAL.DAYS.5');
					case 6: return $translate.instant('GENERAL.DAYS.6');
					default: return '-';
				}
			};
		});
})();
