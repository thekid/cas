CAS Server
==========

[![Build status on GitHub](https://github.com/thekid/cas/workflows/Tests/badge.svg)](https://github.com/thekid/cas/actions)
[![Uses XP Framework](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
![Less than 1000 lines](https://raw.githubusercontent.com/xp-framework/web/master/static/less-than-1000LOC.png)

Minimalistic [CAS](https://apereo.github.io/cas/) Server in PHP

![image](https://user-images.githubusercontent.com/696742/96371316-6a6d9b00-1161-11eb-8662-0d96e23610f7.png)

Setup
-----
Create a database (*the following uses MySQL syntax, adopt if necessary!*):

```sql
create database IDENTITIES
use IDENTITIES

create table user (
  user_id int(11) primary key auto_increment,
  username varchar(100) unique,
  hash varchar(255)
)

create table token (
  token_id int(11) primary key auto_increment,
  user_id int(11),
  name varchar(255),
  secret varchar(255)
)

create table ticket (
  ticket_id int(11) primary key auto_increment,
  value tinytext,
  created datetime
)

grant all on IDENTITIES.* to 'cas'@'%' identified by '...'
```

Run composer:

```sh
$ composer install
# ...
```

Export environment:

```sh
$ export CAS_DB_PASS=... # The one you used when creating the database user above
$ export CRYPTO_KEY=...  # Must have 32 characters
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
$ xp serve -c src/main/etc/sql
```

*Now open http://localhost:8080/login in your browser.*

User management
---------------

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

