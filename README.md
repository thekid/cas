CAS Server
==========

[![Build Status on TravisCI](https://secure.travis-ci.org/thekid/cas.svg)](http://travis-ci.org/thekid/cas)
[![Uses XP Framework](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
![Less than 1000 lines](https://raw.githubusercontent.com/xp-framework/web/master/static/less-than-1000LOC.png)

Minimalistic CAS Server in PHP

![Login flow](https://user-images.githubusercontent.com/696742/63349758-5c08d100-c35c-11e9-9f6d-d15f84a1748b.png)


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

*You can also put these variables into a file named **credentials**, if you wish:*


```sh
$ cat > credentials
CAS_DB_PASS=...
CRYPTO_KEY=...
```

Running
-------
Start the server:

```sh
$ xp -supervise web -c src/main/etc/local de.thekid.cas.App
```

*Now open http://localhost:8080/login in your browser.*

User management
---------------

```sh
# Create a new user; generating a random password if necessary
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.NewUser <user> [--password=<password>]

# Change a user's password
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.ChangePassword <user> [--password=<password>]

# Remove an existing new user
$ xp cmd -c src/main/etc/local/ de.thekid.cas.cmd.RemoveUser <user>
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

