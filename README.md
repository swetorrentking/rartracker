# rartracker
A modern front and backend bittorrent tracker written in PHP and AngularJS.
## Version
0.1.0
## Author
the swedish torrent king
## Feature highlights
* Super responsive GUI thanks to the SPA-application nature of AngularJS
* Advanced admin-features for monitoring site activity, handling reports and multi-deleting torrents.
* Many features suitable for scene only trackers
* Awesome features like bonus-system, leech bonus, seed-time, request system and rss-system.
* Highly skinnable and mobile friendly design with Bootstrap CSS

# Installation
## Install nodejs and npm

**Ubuntu/Debian:**
```sh
$ sudo apt-get install --yes nodejs
```
**Windows:**
* https://nodejs.org/en/download/
### Download dependencies
Use npm and then bower to install all build script packages and all 3rd party dependencies
```sh
$ npm install
$ bower install
```
## Import database (database.sql)
## Configurations

### 1. Config
* Database config in api/secrets.php
* Some site settings in api/Helper.php
### 3. Generate new unique password/cookie salt hashes (optional for security)
* Note that updating the salts will make current registered accounts unusable, you'd want to log in and create invite codes (step 4) before changing salts and finally registering new accounts.
* $passwordSalt and $cookieSalt in **User.php** should be updated with new random hashes.
### 4. Create admin accounts
Use built in account named "**System**" password: "**password**" to create invites and register new admin-accounts
The "System" account **must remain** as a parked account and have Staff rights because it's used as deleter of torrents, sender of PM and creation of forum topics etc. Change System password.


## Server settings
### Recommended packages
``php5 apache2 mariadb-server libapache2-mod-php5 php5-xcache php5-mysql php5-memcache``
### Enable rewrite module for .htaccess
``a2enmod rewrite``
### Permissions
The following folders needs write permission:

* torrents/
* subs/
* img/imdb/
### Recommended MariaDB settings
This is for making the fulltext search work
```sh
[mysqld]
ft_min_word_len=1
ft_stopword_file='stopword_file.txt'
tmp_table_size=2G
max_heap_table_size=2G
```

## Crontab settings (crontab -e)
````
12      *       *       *       *       wget -O /dev/null http://127.0.0.1/api/v1/run-leechbonus
*/20    *       *       *       *       wget -O /dev/null http://127.0.0.1/api/v1/run-cleanup
0       0       *       *       *       wget -O /dev/null http://127.0.0.1/api/v1/fetch-tvdata
0       0       *       *       *       wget -O /dev/null http://127.0.0.1/api/v1/fetch-moviedata
0       0       *       *       5       wget -O /dev/null http://127.0.0.1/api/v1/run-bonus
0       20      *       *       *       wget -O /dev/null http://127.0.0.1/api/v1/run-statistics
````

## Developing and deploying
### Developing
In the project folder run ``grunt dev``. This will lauch a watcher that continuously build the code into the dist/.
### Deploying
In the project folder run ``grunt dist`` and the code will be minified and scrambled, a JsHint check will also be made.

The **app/** folder should never be exposed in live production, since its html/js is beeing packaged into **dist/**

# License
[WTFPL]

[//]: # 
[WTFPL]: <http://www.wtfpl.net/>
