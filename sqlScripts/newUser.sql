INSERT INTO users (username,gecos,homedir,password)
    VALUES ('cinergi', 'Ben Goodwin', '/home/cinergi', ENCRYPT('cinergi'));
INSERT INTO groups (name)
    VALUES ('foobaz');
INSERT INTO grouplist (gid,username)
    VALUES (5000,'cinergi');
