-- Selecione o banco existente no phpMyAdmin antes de executar.
-- Reexecutável em MySQL/MariaDB; não altera questões nem relações existentes.
SET @fonte_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'questions' AND COLUMN_NAME = 'fonte');
SET @fonte_sql = IF(@fonte_exists = 0,
 'ALTER TABLE questions ADD COLUMN fonte TEXT NULL AFTER author',
 'SELECT 1 AS fonte_ja_existe');
PREPARE fonte_migration FROM @fonte_sql;
EXECUTE fonte_migration;
DEALLOCATE PREPARE fonte_migration;

