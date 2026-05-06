-- Minimal TrinityCore-shaped auth schema so the CMS bootstraps without a full
-- core dump. Replace with a real auth DB dump when you wire up TrinityCore.
CREATE DATABASE IF NOT EXISTS `auth` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `auth`;

CREATE TABLE IF NOT EXISTS `account` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`       VARCHAR(32) NOT NULL DEFAULT '',
    `salt`           BINARY(32)  NOT NULL DEFAULT '',
    `verifier`       BINARY(32)  NOT NULL DEFAULT '',
    `sha_pass_hash`  VARCHAR(40) NOT NULL DEFAULT '',
    `session_key`    VARCHAR(80) NOT NULL DEFAULT '',
    `email`          VARCHAR(255) NOT NULL DEFAULT '',
    `reg_mail`       VARCHAR(255) NOT NULL DEFAULT '',
    `joindate`       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_ip`        VARCHAR(15) NOT NULL DEFAULT '127.0.0.1',
    `failed_logins`  INT UNSIGNED NOT NULL DEFAULT 0,
    `locked`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `lock_country`   VARCHAR(2)  NOT NULL DEFAULT '00',
    `last_login`     TIMESTAMP   NULL DEFAULT NULL,
    `online`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expansion`      TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `mutetime`       BIGINT      NOT NULL DEFAULT 0,
    `mutereason`     VARCHAR(255) NOT NULL DEFAULT '',
    `muteby`         VARCHAR(50)  NOT NULL DEFAULT '',
    `locale`         TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `os`             VARCHAR(3)  NOT NULL DEFAULT '',
    `recruiter`      INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `account_access` (
    `id`        INT UNSIGNED NOT NULL,
    `gmlevel`   TINYINT NOT NULL DEFAULT 0,
    `RealmID`   INT NOT NULL DEFAULT -1,
    `Comment`   VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`, `RealmID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `realmlist` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`            VARCHAR(32) NOT NULL DEFAULT '',
    `address`         VARCHAR(255) NOT NULL DEFAULT '127.0.0.1',
    `localAddress`    VARCHAR(255) NOT NULL DEFAULT '127.0.0.1',
    `localSubnetMask` VARCHAR(255) NOT NULL DEFAULT '255.255.255.0',
    `port`            SMALLINT UNSIGNED NOT NULL DEFAULT 8085,
    `icon`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `flag`            TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `timezone`        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `allowedSecurityLevel` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `population`      FLOAT UNSIGNED NOT NULL DEFAULT 0,
    `gamebuild`       INT UNSIGNED NOT NULL DEFAULT 12340,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
