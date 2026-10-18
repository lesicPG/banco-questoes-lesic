CREATE DATABASE IF NOT EXISTS banco_questoes_lesic
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE banco_questoes_lesic;

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    author VARCHAR(150) NOT NULL,
    fonte TEXT NULL,
    category_id INT NULL,
    game_id INT NULL,
    question_type ENUM('multiple_choice','yes_no','scale','open_text','rating') NOT NULL,
    has_image BOOLEAN DEFAULT FALSE,
    image_path VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_questions_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255),
    image_path VARCHAR(255),
    is_correct BOOLEAN DEFAULT FALSE,
    position INT DEFAULT 0,
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS accessibility_guidelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    framework ENUM('WCAG','GAG') NOT NULL,
    category VARCHAR(100) NULL,
    level VARCHAR(50) NULL,
    code VARCHAR(30) NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    UNIQUE KEY uq_guideline (framework, code, name)
);

CREATE TABLE IF NOT EXISTS question_guidelines (
    question_id INT NOT NULL,
    guideline_id INT NOT NULL,
    PRIMARY KEY (question_id, guideline_id),
    CONSTRAINT fk_qg_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_qg_guideline FOREIGN KEY (guideline_id) REFERENCES accessibility_guidelines(id) ON DELETE CASCADE
);

INSERT IGNORE INTO categories (name) VALUES
('Conhecimento'),('Usabilidade'),('Acessibilidade'),('Experiência'),('Satisfação'),('Aprendizagem');

INSERT IGNORE INTO games (name) VALUES
('Dental Game'),('Mordida Selvagem 2.0'),('Querida Floresta 2.0'),('Energia em Ação'),('Memoletrando');

-- Diretrizes WCAG utilizadas no TCC de referência. Diretrizes GAG podem ser cadastradas
-- na mesma tabela conforme forem identificadas e validadas durante a classificação.
INSERT IGNORE INTO accessibility_guidelines (framework, code, name) VALUES
('WCAG','1.2','Mídias com Base em Tempo'),
('WCAG','1.3','Adaptável'),
('WCAG','1.4','Discernível'),
('WCAG','2.2','Tempo Suficiente'),
('WCAG','2.4','Navegável'),
('WCAG','2.5','Modalidades de Entrada'),
('WCAG','3.1','Legível'),
('WCAG','3.2','Previsível'),
('WCAG','3.3','Assistência de Entrada');

CREATE TABLE IF NOT EXISTS question_games (
 question_id INT NOT NULL,
 game_id INT NOT NULL,
 PRIMARY KEY (question_id, game_id),
 CONSTRAINT fk_question_games_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
 CONSTRAINT fk_question_games_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);
INSERT IGNORE INTO question_games(question_id,game_id)
 SELECT id,game_id FROM questions WHERE game_id IS NOT NULL;

