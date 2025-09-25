-- Add MySQL users to ProxySQL
INSERT INTO mysql_users (username, password, default_hostgroup, active)
VALUES ('root', 'rooPasswrd', 0, 1);

INSERT INTO mysql_users (username, password, default_hostgroup, active)
VALUES ('user', 'uspasswrd', 0, 1);

-- Load runtime and save to disk
LOAD MYSQL USERS TO RUNTIME;
SAVE MYSQL USERS TO DISK;

-- Add MySQL backend host
INSERT INTO mysql_servers(hostgroup_id, hostname, port)
VALUES (0, 'db', 3306);

LOAD MYSQL SERVERS TO RUNTIME;
SAVE MYSQL SERVERS TO DISK;
