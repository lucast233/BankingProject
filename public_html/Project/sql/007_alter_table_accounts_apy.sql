ALTER TABLE Accounts ADD COLUMN APY decimal(6, 4) default 0.0000;
ALTER TABLE Accounts ADD COLUMN active tinyint(1) default 1;
