CREATE DATABASE IF NOT EXISTS `database`;
USE `database`;

CREATE TABLE users (
    `user_id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL ,
    `email` VARCHAR(255) DEFAULT NULL,
    `block` INT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO users VALUES
    (NULL, 'Jack', NULL, 1),
    (NULL, 'John', 'jj@gmail.com', 0);
