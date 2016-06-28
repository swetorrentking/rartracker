(function(){
	'use strict';

	angular
		.module('app.shared')
		.filter('prettysize', function ($translate) {
			return function (bytes) {
				if (angular.isUndefined(bytes) || bytes === null) {
					 return;
				}
				if (bytes < 1000 * 1024) {
					return (bytes / 1024).toFixed(0) + ' ' + $translate.instant('GENERAL.UNITS.KB');
				} else if (bytes < 1000 * 1048576) {
					return (bytes / 1048576).toFixed(0) + ' ' + $translate.instant('GENERAL.UNITS.MB');
				} else if (bytes < 1000 * 1073741824) {
					return (bytes / 1073741824).toFixed(2) + ' ' + $translate.instant('GENERAL.UNITS.GB');
				} else {
					return (bytes / 1099511627776).toFixed(2) + ' ' + $translate.instant('GENERAL.UNITS.TB');
				}
			};
		})
		.filter('ratio', function ($sce) {
			var getRatioColor = function (ratio) {
				if (ratio < 0.1) return '#ff0000';
				if (ratio < 0.2) return '#ee0000';
				if (ratio < 0.3) return '#dd0000';
				if (ratio < 0.4) return '#cc0000';
				if (ratio < 0.5) return '#bb0000';
				if (ratio < 0.6) return '#aa0000';
				if (ratio < 0.7) return '#990000';
				if (ratio < 0.8) return '#880000';
				if (ratio < 0.9) return '#770000';
				if (ratio < 1) return '#660000';
				return '#000000';
			};
			return function (ratio) {
				if (isNaN(ratio)) {
					return '∞';
				} else if (ratio > 100) {
					return '100+';
				} else {
					return $sce.trustAsHtml('<span style="color: '+getRatioColor(ratio)+'">' + ratio.toFixed(2) + '</span>');
				}
			};
		})
		.filter('connectable', function ($sce, $translate) {
			return function (connectable) {
				if (connectable) {
					return $sce.trustAsHtml('<span style="color:#008000;">'+$translate.instant('GENERAL.OPEN')+'</span>');
				} else {
					return $sce.trustAsHtml('<span style="color:#800000;">'+$translate.instant('GENERAL.CLOSED')+'</span>');
				}
			};
		})
		.filter('prettyAgent', function () {
			return function (agent) {
				return agent.match(/(.+?)(\(|\;|$)/)[1];
			};
		})
		.filter('round', function () {
			return function (value) {
				return Math.round(value);
			};
		})
		.filter('splitSpace', function () {
			return function (str) {
				return str.split(' ')[0];
			};
		})
		.filter('peerCompleted', function () {
			return function (toGo, torrentSize) {
				if (toGo === 0) {
					return 100;
				}

				return Math.round(100 * (1 - (toGo/torrentSize)));
			};
		})
		.filter('userClass', function (userClasses) {
			return function (id) {
				if (id !== null && id !== undefined) {
					for (var c in userClasses) {
						if (userClasses[c].id === id) {
							return userClasses[c].name;
						}
					}
					return '?';
				}
			};
		})
		.filter('dateStringSplit', function () {
			return function (date) {
				if (date) {
					var text = date.split(' ');
					date = text[0] + '<br />' + text[1];
				}
				return date;
			};
		})
		.filter('dateToUnix', function () {
			return function (date) {
				if (date) {
					var d = new Date(date.replace(/-/g, '/'));
					return d.getTime() / 1000;
				}
			};
		})
		.filter('dateDiff', function (authService) {
			return function (startDate, endDate) {
				var date1 = new Date(typeof startDate == 'string' ? startDate.replace(/ /, 'T') + 'Z' : startDate);
				var date2 = endDate !== undefined ? new Date(typeof endDate == 'string' ? endDate.replace(/ /, 'T') + 'Z' : endDate) : new Date(authService.getServerTime());
				var timeDiff = date1.getTime() - date2.getTime();
				return timeDiff;
			};
		})
		.filter('dateDifference', function (authService, $translate) {
			return function (startDate, endDate) {
				if (startDate !== undefined) {
					var date1 = new Date(typeof startDate == 'string' ? startDate.replace(/ /, 'T') + 'Z' : startDate);
					var date2 = endDate !== undefined ? new Date(typeof endDate == 'string' ? endDate.replace(/ /, 'T') + 'Z' : endDate) : new Date(authService.getServerTime());
					var timeDiff = Math.abs(date2.getTime() - date1.getTime());
					timeDiff = timeDiff / 1000;
					var minutes = Math.floor(timeDiff / 60);
					var hours = Math.floor(minutes / 60);
					minutes -= hours * 60;
					var days = Math.floor(hours / 24);
					hours -= days * 24;
					var weeks = Math.floor(days/7);
					days -= weeks * 7;
					var years = Math.floor(weeks/52);
					weeks -= years * 52;

					if (years > 0) {
						return years + ' ' + (years > 1 ? $translate.instant('DATE.YEARS') : $translate.instant('DATE.YEAR'));
					}

					if (weeks === 1) {
						return weeks + ' ' + $translate.instant('DATE.WEEK');
					} else if (weeks > 1) {
						return weeks + ' ' + $translate.instant('DATE.WEEKS');
					}

					if (days > 0) {
						return days + ' ' + (days > 1 ? $translate.instant('DATE.DAYS') : $translate.instant('DATE.DAY'));
					}

					if (hours > 0) {
						return hours + ' ' + (hours > 1 ? $translate.instant('DATE.HOURS') : $translate.instant('DATE.HOUR'));
					}

					if (minutes > 0) {
						return minutes + ' ' + $translate.instant('DATE.MINUTES_SHORT');
					}

					return '<' + ' 1 ' + $translate.instant('DATE.MINUTES_SHORT');
				}
			};
		})
		.filter('genderIcon', function ($sce, $translate) {
			return function (gender) {
				if (gender == 1) {
					return $sce.trustAsHtml('<img src="/img/icons/man.gif" alt="'+ $translate.instant('SIGNUP.GENDER_MAN') +'" />');
				} else if (gender == 2) {
					return $sce.trustAsHtml('<img src="/img/icons/woman.gif" alt="'+ $translate.instant('SIGNUP.GENDER_WOMAN') +'" />');
				}
			};
		})
		.filter('split', function () {
			return function(input, delimiter) {
				if(!input) {
					return;
				}
				var del = delimiter || ', ';
				return input.split(del);
			};
		})
		.filter('cheatColor', function () {
			return function(input) {
				if(!input) {
					return;
				}
				if (input.rate > 15728640 && input.time > 1) {
					return '#ffcccc';
				}
				if (input.rate > 314571 && input.mbitupp < 2 && input.mbitupp > 0) {
					return '#f7772a';
				}
				if (input.uploaded == input.downloaded) {
					return '#99ccff';
				}
				if (input.adsl == 1 && input.rate > 307200) {
					return '#ec83d7';
				}
				return '';
			};
		})
		.filter('highlight', function($sce) {
			return function (text, phrase, customClass) {
				text = $sce.getTrusted($sce.HTML, text);
				if (phrase && phrase.length > 1) {
					phrase = phrase.replace(/[^\w+\s\.\-]/gi, '');
					phrase = phrase.split(' ');

					phrase.sort(function (a, b) {
						return a.length < b.length;
					});

					text = text.replace(new RegExp('('+phrase.join('|')+')', 'gi'),
					'<span class="' + (customClass ? customClass : 'highlighted') + '">$1</span>');
				}

				return $sce.trustAsHtml(text);
			};
		})
		.filter('reportType', function ($translate) {
			return function (type) {
				switch (type) {
					case 'torrent':		return $translate.instant('TORRENTS.TORRENT');
					case 'post':		return $translate.instant('ADMIN.FORUM_POSTS');
					case 'request':		return $translate.instant('REQUESTS.TITLE_SINGLE');
					case 'pm':			return $translate.instant('MAILBOX.MESSAGE');
					case 'comment':		return $translate.instant('REQUESTS.COMMENT');
					case 'subtitle':	return $translate.instant('TORRENTS.SWE_SUBTITLES');
					case 'user':		return $translate.instant('TORRENTS.USER');
				}
			};
		})
		.filter('nltobr', function () {
			return function (str) {
				if (str) {
					return str.replace(/(?:\r\n|\r|\n)/g, '<br />');
				}
			};
		})
		.filter('bbCode', function ($sce, $translate) {

			var smilies = {
				':-)': 'smile1.gif',
				':smile:': 'smile2.gif',
				':-D': 'grin.gif',
				':lol:': 'laugh.gif',
				':w00t:': 'w00t.gif',
				':-P': 'tongue.gif',
				';-)': 'wink.gif',
				':-|': 'noexpression.gif',
				':-/': 'confused.gif',
				':-(': 'sad.gif',
				':\'-(': 'cry.gif',
				':weep:': 'weep.gif',
				':-O': 'ohmy.gif',
				':o)': 'clown.gif',
				'8-)': 'cool1.gif',
				'|-)': 'sleeping.gif',
				':innocent:': 'innocent.gif',
				':whistle:': 'whistle.gif',
				':unsure:': 'unsure.gif',
				':closedeyes:': 'closedeyes.gif',
				':cool:': 'cool2.gif',
				':fun:': 'fun.gif',
				':thumbsup:': 'thumbsup.gif',
				':thumbsdown:': 'thumbsdown.gif',
				':blush:': 'blush.gif',
				':yes:': 'yes.gif',
				':no:': 'no.gif',
				':love:': 'love.gif',
				':?:': 'question.gif',
				':!:': 'excl.gif',
				':idea:': 'idea.gif',
				':arrow:': 'arrow.gif',
				':arrow2:': 'arrow2.gif',
				':hmm:': 'hmm.gif',
				':hmmm:': 'hmmm.gif',
				':huh:': 'huh.gif',
				':geek:': 'geek.gif',
				':look:': 'look.gif',
				':rolleyes:': 'rolleyes.gif',
				':kiss:': 'kiss.gif',
				':shifty:': 'shifty.gif',
				':blink:': 'blink.gif',
				':smartass:': 'smartass.gif',
				':sick:': 'sick.gif',
				':crazy:': 'crazy.gif',
				':wacko:': 'wacko.gif',
				':alien:': 'alien.gif',
				':wizard:': 'wizard.gif',
				':wave:': 'wave.gif',
				':wavecry:': 'wavecry.gif',
				':baby:': 'baby.gif',
				':angry:': 'angry.gif',
				':ras:': 'ras.gif',
				':sly:': 'sly.gif',
				':devil:': 'devil.gif',
				':evil:': 'evil.gif',
				':evilmad:': 'evilmad.gif',
				':sneaky:': 'sneaky.gif',
				':axe:': 'axe.gif',
				':slap:': 'slap.gif',
				':wall:': 'wall.gif',
				':rant:': 'rant.gif',
				':jump:': 'jump.gif',
				':yucky:': 'yucky.gif',
				':nugget:': 'nugget.gif',
				':smart:': 'smart.gif',
				':shutup:': 'shutup.gif',
				':shutup2:': 'shutup2.gif',
				':crockett:': 'crockett.gif',
				':zorro:': 'zorro.gif',
				':snap:': 'snap.gif',
				':beer:': 'beer.gif',
				':beer2:': 'beer2.gif',
				':drunk:': 'drunk.gif',
				':strongbench:': 'strongbench.gif',
				':weakbench:': 'weakbench.gif',
				':dumbells:': 'dumbells.gif',
				':music:': 'music.gif',
				':stupid:': 'stupid.gif',
				':dots:': 'dots.gif',
				':offtopic:': 'offtopic.gif',
				':spam:': 'spam.gif',
				':oops:': 'oops.gif',
				':lttd:': 'lttd.gif',
				':please:': 'please.gif',
				':sorry:': 'sorry.gif',
				':hi:': 'hi.gif',
				':yay:': 'yay.gif',
				':cake:': 'cake.gif',
				':hbd:': 'hbd.gif',
				':band:': 'band.gif',
				':punk:': 'punk.gif',
				':rofl:': 'rofl.gif',
				':bounce:': 'bounce.gif',
				':mbounce:': 'mbounce.gif',
				':thankyou:': 'thankyou.gif',
				':gathering:': 'gathering.gif',
				':hang:': 'hang.gif',
				':chop:': 'chop.gif',
				':rip:': 'rip.gif',
				':whip:': 'whip.gif',
				':judge:': 'judge.gif',
				':chair:': 'chair.gif',
				':tease:': 'tease.gif',
				':box:': 'box.gif',
				':boxing:': 'boxing.gif',
				':guns:': 'guns.gif',
				':shoot:': 'shoot.gif',
				':shoot2:': 'shoot2.gif',
				':flowers:': 'flowers.gif',
				':wub:': 'wub.gif',
				 ':swub:': 'swub.gif',
				':lovers:': 'lovers.gif',
				':kissing:': 'kissing.gif',
				':kissing2:': 'kissing2.gif',
				':console:': 'console.gif',
				':group:': 'group.gif',
				':hump:': 'hump.gif',
				':hooray:': 'hooray.gif',
				':happy2:': 'happy2.gif',
				':clap:': 'clap.gif',
				':clap2:': 'clap2.gif',
				':weirdo:': 'weirdo.gif',
				':yawn:': 'yawn.gif',
				':bow:': 'bow.gif',
				':dawgie:': 'dawgie.gif',
				':cylon:': 'cylon.gif',
				':book:': 'book.gif',
				':fish:': 'fish.gif',
				':mama:': 'mama.gif',
				':pepsi:': 'pepsi.gif',
				':medieval:': 'medieval.gif',
				':rambo:': 'rambo.gif',
				':ninja:': 'ninja.gif',
				':hannibal:': 'hannibal.gif',
				':party:': 'party.gif',
				':snorkle:': 'snorkle.gif',
				':evo:': 'evo.gif',
				':king:': 'king.gif',
				':chef:': 'chef.gif',
				':mario:': 'mario.gif',
				':pope:': 'pope.gif',
				':fez:': 'fez.gif',
				':cap:': 'cap.gif',
				':cowboy:': 'cowboy.gif',
				':pirate:': 'pirate.gif',
				':pirate2:': 'pirate2.gif',
				':rock:': 'rock.gif',
				':cigar:': 'cigar.gif',
				':icecream:': 'icecream.gif',
				':oldtimer:': 'oldtimer.gif',
				':trampoline:': 'trampoline.gif',
				':banana:': 'bananadance.gif',
				':smurf:': 'smurf.gif',
				':yikes:': 'yikes.gif',
				':osama:': 'osama.gif',
				':saddam:': 'saddam.gif',
				':santa:': 'santa.gif',
				':indian:': 'indian.gif',
				':pimp:': 'pimp.gif',
				':nuke:': 'nuke.gif',
				':jacko:': 'jacko.gif',
				':ike:': 'ike.gif',
				':greedy:': 'greedy.gif',
				':super:': 'super.gif',
				':wolverine:': 'wolverine.gif',
				':spidey:': 'spidey.gif',
				':spider:': 'spider.gif',
				':bandana:': 'bandana.gif',
				':construction:': 'construction.gif',
				':sheep:': 'sheep.gif',
				':police:': 'police.gif',
				':detective:': 'detective.gif',
				':bike:': 'bike.gif',
				':fishing:': 'fishing.gif',
				':clover:': 'clover.gif',
				':horse:': 'horse.gif',
				':shit:': 'shit.gif',
				':soldiers:': 'soldiers.gif',
				';)': 'wink.gif',
				':wink:': 'wink.gif',
				':D': 'grin.gif',
				':P': 'tongue.gif',
				':(': 'sad.gif',
				':\'(': 'cry.gif',
				':|': 'noexpression.gif',
				'8)': 'cool1.gif',
				':Boozer:': 'alcoholic.gif',
				':deadhorse:': 'deadhorse.gif',
				':spank:': 'spank.gif',
				':yoji:': 'yoji.gif',
				':locked:': 'locked.gif',
				':grrr:': 'angry.gif',
				'O:-': 'innocent.gif',
				':sleeping:': 'sleeping.gif',
				':clown:': 'clown.gif',
				':mml:': 'mml.gif',
				':rtf:': 'rtf.gif',
				':morepics:': 'morepics.gif',
				':rb:': 'rb.gif',
				':rblocked:': 'rblocked.gif',
				':maxlocked:': 'maxlocked.gif',
				':hslocked:': 'hslocked.gif'
			};

			var escapeRegExp = function (str) {
				return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
			};

			return function (text) {
				if (!text) {
					return;
				}

				text = text.toString();

				text = String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

				// all local http links
				text = text.replace(/(^|[^=\]'\"a-zA-Z0-9])((http|https):\/\/(www.|)rartracker.org\/([^()<>\s]+))/g,'$1[url=/$5]$2[/url]');
				// all http links
				text = text.replace(/(^|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/g,'$1<a href="http://anonym.to/?$2" target="_blank">$2</a>');

				// [b] Bold [/b]
				text = text.replace(/\[b\]([\S\s]+?)\[\/b\]/g,'<b>$1</b>');

				// [i] Italic [/i]
				text = text.replace(/\[i\]([\S\s]+?)\[\/i\]/g,'<i>$1</i>');

				// [u] Underline [/u]
				text = text.replace(/\[u\]([\S\s]+?)\[\/u\]/g,'<u>$1</u>');

				// [c] Center [/c]
				text = text.replace(/\[c\]([\s\S]+?)\[\/c\]/g,'<div class="text-center">$1</div>');

				// [color=MyColor] Colored text [/color]
				text = text.replace(/\[color\=([a-z]+|#[0-9abcdef]+)\](.*?)\[\/color\]/ig,'<span style="color: $1">$2</span>');

				// [url] Web link local to site [/url]
				text = text.replace(/\[url\=\/(.+?)\](.*?)\[\/url\]/gi,'<a href="$1">$2</a>');

				// [url] Web link [/url]
				text = text.replace(/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/gi,'<a href="$1" target="_blank">$2</a>');

				// [img] Images [/img]
				text = text.replace(/\[img\](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/img\]/gi,'<img border="0" src="$1" />');

				// [img= Images ]
				text = text.replace(/\[img=([^\s'\"<>]+?)\]/gi,'<img border="0" src="$1" />');

				// [imgw] Images resized [/imgw]
				text = text.replace(/\[imgw\](http:\/\/[^\s'\"<>]+(\.(jpg|gif|png)))\[\/imgw\]/gi,'<img width="700" border="0" src="$1" /><br><font class=small>' + $translate.instant('FORUM.IMAGE_RESIZED_CLICK') + '</font>');

				// [imgw= Images resized ]
				text = text.replace(/\[imgw=([^\s'\"<>]+?)\]/gi,'<img border="0" width="700" src="$1" /><br><font class=small>' + $translate.instant('FORUM.IMAGE_RESIZED_CLICK') + '</font>');

				// [quote] Quoted text [/quote]
				text = text.replace(/\[quote\]([\S\s]+?)\[\/quote\]/g,'<b>' + $translate.instant('FORUM.QUOTED') + ':</b><div class="quoted">$1</div>');

				// [quote=Name] Quoted text by Name [/quote]
				text = text.replace(/\[quote=(.*?)\]([\S\s]+?)\[\/quote\]/g,'<b>$1 ' + $translate.instant('MAILBOX.WROTE') + '</b><div class="quoted">$2</div>');

				// [spoiler] Quoted text [/spoiler]
				text = text.replace(/\[spoiler\]([\S\s]+?)\[\/spoiler\]/g,'<b>' + $translate.instant('FORUM.SPOILER_TAG') + '</b><div class="quoted" style="color: #FFF;">$1</div>');

				// [video] Underline [/video]
				text = text.replace(/\[video=(?:https:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(.+)\]/g,'<iframe width="600" height="400" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>');

				// [*] List
				text = text.replace(/\[\*\](.*?)(\n|$)/g,'<li>$1</li>');

				var s, reg;
				for (s in smilies) {
					reg = new RegExp(escapeRegExp(s), 'g');
					text = text.replace(reg, '<img border=0 src=\"/img/smilies/'+smilies[s]+'\" />');
				}

				// Break new lines
				text = text.replace(/(?:\r\n|\r|\n)/g, '<br />');

				return $sce.trustAsHtml(text);
			};
		});
})();
