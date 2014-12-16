-- ----------------------------------------------------------------------------------------------
-- update-to-1.1.sql
-- Файл таблиц баз данных плагина gc
--
-- @author      Андрей Воронов <andreyv@gladcode.ru>
-- @copyrights  Copyright © 2014, Андрей Воронов
--              Является частью плагина gc
-- @version     0.0.1.1 от 16.12.2014 23:55
-- ----------------------------------------------------------------------------------------------

ALTER TABLE `prefix_comment`
ADD `comment_image` VARCHAR(254) NULL DEFAULT NULL,
ADD `comment_social_id` VARCHAR(50) NULL DEFAULT NULL,
ADD `comment_social` VARCHAR(10) NULL DEFAULT NULL;

ALTER TABLE `prefix_comment_token`
ADD `token_image` VARCHAR(254) NULL DEFAULT NULL;

ALTER TABLE prefix_mresource
MODIFY COLUMN user_id INT UNSIGNED NULL;