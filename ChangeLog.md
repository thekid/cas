CAS Server change log
=====================

## ?.?.? / ????-??-??

## 0.5.0 / 2020-11-22

* Merged PR #12: Support OpenSSL (as well as Sodium) extensions for
  encryption, thereby adding support for PHP 7.0 and PHP 7.1
  (@thekid)
* Rearranged code into `de.thekid.cas.impl` package - @thekid
* Merged PR #10: Use record syntax for User - @thekid
* Merged PR #9: Use enum syntax for ServiceResponse - @thekid

## 0.4.0 / 2020-10-18

* Upgraded bundled Fomantic UI to version 2.8.7 - @thekid
* Added verification on session user existing in user database. If the
  user has been deleted in the meantime, show login screen!
  (@thekid)

## 0.3.0 / 2020-10-18

* Added command to list users, optionally filtered - @thekid
* Made database implementation compatible with SQLite - @thekid
* Upgraded dependencies, making this compatible with PHP 8 - @thekid

## 0.2.0 / 2019-08-21

* Use more compact XML layout for service responses - @thekid
* Added various unittests for login and logout functionality - @thekid

## 0.1.0 / 2019-08-20

* Implemented issue #1: Bundle JS / CSS dependencies (Fomantic UI, jQuery)
  (@thekid)
* Implemented issue #2: Add CLIs for management
  - Merged PR #6: User management
  - Merged PR #5: Tokens management
  (@thekid)
* Hello World! First release - @thekid