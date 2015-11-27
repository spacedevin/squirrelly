![Beer Squirrel](https://raw.githubusercontent.com/arzynik/beersquirrel/master/www/icon.png)

## Beer Squirrel

Beer Squirrel is an Image and text sharing tool


#### Requirements

- [PHP](http://php.net/) >= 5.4.0
- [Tipsy](http://tipsy.la/) >= 0.10.0

#### Installation

1. Install Tipsy `$ composer require arzynik/Tipsy`
2. Import the database schema in **db/schema.sql**
3. Create a directory called **data** and make sure it has write permissions.


#### Deploying on Heroku

The **Procfile** contains what you need to switch between Apache or Nginx. Nginx is set by default.

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)


#### Deploying on your own environment

You will need MySQL and Apache/Nginx installed.

1. Edit your **config/db.ini** file with your user, host, and database.
2. Open **install/db.sql** and load that data into your database.
3. Copy this directory somewhere so **web** is readable by apache/nginx.
4. Open the **web** directory in your browser.
5. If using Apache, the **web/.htaccess** file should handle what you need.
6. If using Nginx, you should use the **config/nginx.conf** file.


See [Tipsy Documentation](https://github.com/arzynik/Tipsy/wiki) for more information.
