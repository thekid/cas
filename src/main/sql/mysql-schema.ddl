create database IDENTITIES;
use IDENTITIES;

create table user (
  user_id int(11) primary key auto_increment,
  username varchar(100) unique,
  hash varchar(255)
);

create table token (
  token_id int(11) primary key auto_increment,
  user_id int(11),
  name varchar(255),
  secret varchar(255)
);

create table ticket (
  ticket_id int(11) primary key auto_increment,
  value tinytext,
  created datetime
);