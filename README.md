CAS Server
==========

[![Uses XP Framework](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
![Less than 1000 lines](https://raw.githubusercontent.com/xp-framework/web/master/static/less-than-1000LOC.png)

Minimalistic CAS Server in PHP

Setup
-----
Create a database:

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

```sql
insert into user (username, hash) values ("username", "sha256-hash")
select @@identity
```

Setting up MFA
--------------

```sh
$ xp de.thekid.cas.cmd.NewToken [username] $CRYPTO_KEY
```

```sql
insert into token (user_id, secret) values (1, "secret")
```