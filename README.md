CAS Server
==========

[![Build Status on TravisCI](https://secure.travis-ci.org/thekid/cas.svg)](http://travis-ci.org/thekid/cas)
[![Uses XP Framework](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
![Less than 1000 lines](https://raw.githubusercontent.com/xp-framework/web/master/static/less-than-1000LOC.png)

Minimalistic CAS Server in PHP

![Login Screen](https://user-images.githubusercontent.com/696742/63304345-f298ac00-c2e2-11e9-8dcf-94b8566e1785.png)

Setup
-----
Create a database (*the following uses MySQL syntax, adopt if necessary!*):

```sql
create database IDENTITIES
use IDENTITIES

create table user (
  user_id int(11) primary key auto_increment,
  username varchar(255),
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

Running
-------
Start the server:

```sh
$ export CAS_DB_PASS=... # The one you used when creating the database user above
$ export CRYPTO_KEY=...  # Must have 32 characters
$ xp -supervise web -c src/main/etc/local de.thekid.cas.App
```

Creating a user
---------------
```sh
# Create a new user
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.NewUser <user> [--password=<password>]
```

Setting up MFA
--------------

```sh
# Create a new token
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.NewToken <user> [--name=<name>]

# List existing tokens
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.ListTokens <user>

# Remove existing token
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.RemoveToken <user> <name>
```

