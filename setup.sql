CREATE USER 'web'@'localhost' IDENTIFIED BY diogee;
CREATE DATABASE greenzone;
GRANT ALL PRIVILEGES ON greenzone.* TO web;
CREATE TABLE users (userid VARCHAR(16), email VARCHAR(60), password VARCHAR(255), PRIMARY KEY (userid));
CREATE TABLE files (guid VARCHAR(255), userid VARCHAR(60), fileName VARCHAR(255), count INT, PRIMARY KEY (guid), FOREIGN KEY (userid) REFERENCES users(userid));