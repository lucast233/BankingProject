ALTER TABLE `Users`
ADD COLUMN username varchar(60) default NULL,
ADD UNIQUE (username)