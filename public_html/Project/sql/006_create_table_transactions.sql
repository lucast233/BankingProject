CREATE TABLE `Transactions`
(
    `id` int auto_increment,
    `account_src` int not null,
    `account_dest` int null,
    `balance_change` decimal(12, 2),
    `transaction_type` varchar(10),
    `memo` TEXT default null,
    `expected_total` decimal(12, 2),
    `created` datetime default current_timestamp,
    
    primary key (`id`),
    foreign key (`account_src`) references `Accounts` (`id`),
    foreign key (`account_dest`) references `Accounts` (`id`)
)