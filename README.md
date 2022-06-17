CAS Server
==========

[![Build status on GitHub](https://github.com/thekid/cas/workflows/Tests/badge.svg)](https://github.com/thekid/cas/actions)
[![Uses XP Framework](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
![Less than 1000 lines](https://raw.githubusercontent.com/xp-framework/web/master/static/less-than-1000LOC.png)

Minimalistic [CAS](https://apereo.github.io/cas/) Server in PHP supporting MySQL / MariaDB or MongoDB persistence.

![image](https://user-images.githubusercontent.com/696742/96371316-6a6d9b00-1161-11eb-8662-0d96e23610f7.png)

Setup
-----
For use with MySQL / MariaDB, create a database with the following tables (*the following uses MySQL syntax, adopt if necessary!*):

```bash
# Create database and tables
$ cat src/main/sql/mysql-schema.ddl | mysql -u root

# Create user
$ mysql -u root -e "grant all on IDENTITIES.* to 'cas'@'%' identified by '...'"
```

MongoDB collections are created automatically when the first document is inserted - so the only thing necessary is to create the user for the respective database, as shown in the following Mongo CLI commands:

```javascript
mongo> use admin;
mongo> db.createUser({
  user: "cas",
  pwd: "...",
  roles: [ { role: "readWrite", db: "cas" } ]
})
```

Run composer:

```sh
$ composer install
# ...
```

Export environment:

```sh
$ export CAS_DB_PASS=... # The one you used when creating the database user above
$ export CRYPTO_KEY=...  # Must have 32 characters, generate with `openssl rand -base64 24`
```

You can also put these variables into a file named **credentials**, if you wish:

```sh
$ cat > credentials
CAS_DB_PASS=...
CRYPTO_KEY=...
```

Running
-------
Start the server:

```sh
# For MySQL / MariaDB
$ xp serve -c src/main/etc/sql

# For MongoDB
$ xp serve -c src/main/etc/mongo
```

*Now open http://localhost:8080/login in your browser.*

To change the address and port the server runs on, add `-a 0.0.0.0:8443` to the above command line.

User management
---------------
All of the following use the *sql* configuration. For use with MongoDB, use `src/main/etc/mongo` instead!

```sh
# Create a new user; generating a random password if necessary
$ xp cmd -c src/main/etc/sql NewUser <user> [--password=<password>]

# Change a user's password
$ xp cmd -c src/main/etc/sql ChangePassword <user> [--password=<password>]

# Remove an existing user
$ xp cmd -c src/main/etc/sql RemoveUser <user>

# List all users
$ xp cmd -c src/main/etc/sql ListUsers

# Filter users on their username. Use * to match any character
$ xp cmd -c src/main/etc/sql ListUsers 't*'
```

Setting up MFA
--------------

```sh
# Create a new token
$ xp cmd -c src/main/etc/sql NewToken <user> [--name=<name>]

# List existing tokens
$ xp cmd -c src/main/etc/sql ListTokens <user>

# Remove an existing token
$ xp cmd -c src/main/etc/sql RemoveToken <user> <name>
```

