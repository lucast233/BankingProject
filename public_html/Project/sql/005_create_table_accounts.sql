CREATE TABLE IF NOT EXISTS  `Accounts`
(
    `id`         int auto_increment,
    `account_number`    VARCHAR(12) NOT NULL,
    `user_id`  INT,
    `balance` DECIMAL(12,2) default 0.00,
    `account_type` VARCHAR(20),
    `created` TIMESTAMP default CURRENT_TIMESTAMP,
    `modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) 
)