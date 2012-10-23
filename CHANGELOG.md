## 1.0.13 (2012-10-17) ##

* Pause and unpause events go into their own log category

## 1.0.12 (2012-10-14) ##

* Check that `$logger` is not null before using

## 1.0.11 (2012-10-01) ##

* Update Composer.json

## 1.0.10 (2012-09-27) ##

* Update Composer.json


## 1.0.9 (2012-09-20) ##

* Delegate all the MonologHandler creation to MonologInit. (requires a composer update).
* Fix stop event that was not logged

## 1.0.8 (2012-09-19) ##

* In start log, add a new fields for recording queues names

## 1.0.7 (2012-09-10) ##

* Fix tests

## 1.0.6 (2012-09-10) ##

* Merge latest commits from php-resque


## 1.0.5 (2012-08-29) ##

* Add custom redis database and namespace support

## 1.0.4 (2012-08-29) ##

* Job creation will be delegated to Resque_Job_Creator class if found
* Use persistent connection to Redis

## 1.0.3 (2012-08-26) ##

* Fix unknown self reference

## 1.0.2 (2012-08-22) ##

* Don't use persistent connection to redis, because of segfault bug

## 1.0.1 (2012-08-21) ##

* Output to STDOUT if no log Handler is defined

## 1.0.0 (2012-08-21) ##

* Initial release