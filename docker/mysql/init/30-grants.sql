-- The default `warcry` user (created via MYSQL_USER) only owns `warcry`.
-- Grant access to the `auth` DB too so the CMS can reach both.
GRANT ALL PRIVILEGES ON `warcry`.* TO 'warcry'@'%';
GRANT ALL PRIVILEGES ON `auth`.*   TO 'warcry'@'%';
FLUSH PRIVILEGES;
