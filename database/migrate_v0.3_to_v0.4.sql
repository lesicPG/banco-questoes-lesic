USE banco_questoes_lesic;

ALTER TABLE questions DROP COLUMN subcategory;

CREATE TABLE IF NOT EXISTS accessibility_guidelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    framework ENUM('WCAG','GAG') NOT NULL,
    code VARCHAR(30) NULL,
    name VARCHAR(150) NOT NULL,
    UNIQUE KEY uq_guideline (framework, code, name)
);

CREATE TABLE IF NOT EXISTS question_guidelines (
    question_id INT NOT NULL,
    guideline_id INT NOT NULL,
    PRIMARY KEY (question_id, guideline_id),
    CONSTRAINT fk_qg_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_qg_guideline FOREIGN KEY (guideline_id) REFERENCES accessibility_guidelines(id) ON DELETE CASCADE
);

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

