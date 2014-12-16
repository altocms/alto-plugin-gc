-- ----------------------------------------------------------------------------------------------
-- install.sql
-- Файл таблиц баз данных плагина Gc
--
-- @author      Андрей Г. Воронов <andreyv@gladcode.ru>
-- @copyrights  Copyright © 2014, Андрей Г. Воронов
--              Является частью плагина Gc
-- @version     0.0.1 от 03.09.2014 10:02
-- ----------------------------------------------------------------------------------------------

INSERT INTO
  `prefix_user`
  (user_id, user_login, user_password, user_activate, user_mail, user_date_register, user_ip_register)
VALUES
  (0, '0B8uAORUAmrIObE9QZ1U5QW8xakE', '0B8uAORUAmrIObE9QZ1U5QW8xakE', 0, 'test@gladcode.ru', '2014-01-01 00:01',
   '127.0.0.1');

ALTER TABLE
`prefix_comment`
ADD `comment_guest_login` VARCHAR(100) NULL DEFAULT NULL,
ADD `comment_guest_mail` VARCHAR(100) NULL DEFAULT NULL,
ADD `comment_token_id` INT(10) UNSIGNED NULL DEFAULT NULL;

-- ХРАНЕНИЕ ТОКЕНОВ ПРОВАЙДЕРОВ
CREATE TABLE IF NOT EXISTS `prefix_comment_token` (
  `token_id`               INT(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `token_data`             VARCHAR(250) NOT NULL UNIQUE,
  `token_data_secret`      VARCHAR(250) NOT NULL,
  `token_provider_name`    VARCHAR(50)  NOT NULL,
  `token_provider_user_id` VARCHAR(50)  NOT NULL,
  `token_user_email`       VARCHAR(100) NULL DEFAULT '',
  `token_user_login`       VARCHAR(100) NULL DEFAULT '',
  `token_image`            VARCHAR(254) NULL DEFAULT NULL,
  `token_expire`           INT(10) UNSIGNED
)
  ENGINE =MyISAM
  DEFAULT CHARSET =utf8
  AUTO_INCREMENT =1;


