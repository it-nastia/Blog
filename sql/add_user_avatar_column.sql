-- SQL скрипт для добавления колонки avatar в таблицу users
-- Выполните этот скрипт в phpMyAdmin

USE `filmblog`;

-- Добавляем колонку avatar для хранения URL изображения пользователя
ALTER TABLE `users` 
ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `email`;

-- Комментарий к колонке
ALTER TABLE `users` 
MODIFY COLUMN `avatar` VARCHAR(255) NULL COMMENT 'URL to user avatar image';

