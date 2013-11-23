CREATE USER 'web'@'localhost' IDENTIFIED BY diogee;
CREATE DATABASE greenzone;
GRANT ALL PRIVILEGES ON greenzone.* TO web;
CREATE TABLE users (userid VARCHAR(16), email VARCHAR(60), password VARCHAR(255), PRIMARY KEY (userid));
CREATE TABLE files (guid VARCHAR(255), userid VARCHAR(60), fileName VARCHAR(255), count INT, PRIMARY KEY (guid), FOREIGN KEY (userid) REFERENCES users(userid));
CREATE TABLE SharedFilePermissions (guid VARCHAR(150), userid VARCHAR(60), owner VARCHAR(60), readable TINYINT(1), writeable TINYINT(1), path VARCHAR(255), PRIMARY KEY (guid, userid), FOREIGN KEY (guid) REFERENCES files(guid), FOREIGN KEY (userid) REFERENCES users(userid));
