# MySociety Voting App
This is a sample voting app. It is built using Symfony 2 and Twitter Bootstrap 2.

## Requirements
* PHP >= 5.3 and a web server
* A database server (MySQL is used in the examples of how to set it up)
* Redis server

## Setup
* Once you have checked out the code, `cd` into `symfony/` and run `php bin/vendors install` which will install all the dependencies from Git
* Copy `symfony/app/config/parameters.ini.dist` to `symfony/app/config/parameters.ini` and tweak as necessary
* Run `php app/console doctrine:database:create` to create the database, and `php app/console doctrine:schema:update --force` to create the database schema
* Check that the snc\_redis settings in `app/config/config.yml` are suitable for your environment
* Make sure `symfony/app/cache` and `symfony/app/logs` are writable by the webserver (see 'Setting up Permissions' in the [Symfony docs](http://symfony.com/doc/current/book/installation.html))
* Issue the following SQL commands against your database:

```sql
LOAD DATA INFILE '/path/to/root/symfony/parties.csv' INTO TABLE parties FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' (name);
LOAD DATA INFILE '/path/to/root/symfony/constituencies.csv' INTO TABLE constituencies FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';
```

## Server setup
Below is a sample nginx configuration file for the site which uses php-cgi:

```
server {
    listen 80;
    server_name vote.domain.tld;

    root /data/vote.domain.tld;

    access_log /var/log/nginx/vote.domain.tld.access.log;

    keepalive_timeout 5;

    location / {
        index app.php;
        try_files $uri /app.php?$args;
    }

    location ~ ^/(app|app_dev)\.php(/|$) {
        location ~ \..*/.*\.php$ {return 404;}
        fastcgi_pass        127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME   $document_root$fastcgi_script_name;
        fastcgi_param HTTPS             off;
    }
}
```

## Todo
Improvements that could be made:
* The method of determining who has voted is rudimentary (a combination of IP, user agent and request time, the latter for testing purposes) and should be hashed and set as a cookie
* The user experience isn't brilliant:
	* Need something more on the index page to entice people to vote (probably a pie chart of overall voting trends, explanatory text etc)
	* Selection of constituency on the results page isn't user-friendly - search by map/postcode
	* Same on voting page: should be able to enter a postcode rather than choose the constituency
	* Look and feel should be customised to make it less 'bootstrap-y'
	* Needs more graphics (graphs) to make the information easier to digest
* The code needs commenting and automated tests built for it
