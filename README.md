![Squirrelly](http://squirrelly.io/icon.svg)

## Squirrelly

Squirrelly is an Image and text sharing tool that allows you to paste straight from your clipboard and share with an anonymous url.


#### Deploying on Heroku

Deploying on Heroku will use Nginx and Postgres.

[![Deploy to Heroku](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)


#### Deploying on your own environment

You will need MySQL/Postgres and Apache/Nginx installed.

1. Setup your database config
  - Either add an **config/db.ini** file with your user, host, and database
  - Or add a `DATABASE_URL`
2. Edit your **config/config.ini** for the type of storage type you want (local, sql)
3. Open either **install/mysql.sql** or **install/pgsql.sql** and load it into your database.
4. If using local storage, make sure the **data** path is writeable.


See [Tipsy Documentation](https://github.com/tipsyphp/tipsy/wiki) for more information.
