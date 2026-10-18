-- Banco de Questões LESIC - migração v0.4.1 -> v0.5
-- Execute uma única vez no phpMyAdmin, com o banco banco_questoes_lesic selecionado.

ALTER TABLE accessibility_guidelines
    ADD COLUMN category VARCHAR(100) NULL AFTER framework,
    ADD COLUMN level VARCHAR(50) NULL AFTER category,
    MODIFY COLUMN name VARCHAR(255) NOT NULL,
    ADD COLUMN description TEXT NULL AFTER name;

-- Registros GAG iniciais levantados a partir do TCC de referência.
-- Novas recomendações podem ser adicionadas depois da revisão/classificação.
INSERT IGNORE INTO accessibility_guidelines
(framework, category, level, code, name, description) VALUES
('GAG','Cognitiva','Básico',NULL,'Utilizar linguagem simples e clara',NULL),
('GAG','Cognitiva','Básico',NULL,'Utilizar fonte simples e clara',NULL),
('GAG','Cognitiva','Básico',NULL,'Utilizar tamanho de fonte fácil de ler',NULL),
('GAG','Cognitiva','Básico',NULL,'Incluir tutoriais interativos',NULL),
('GAG','Cognitiva','Intermediário',NULL,'Incluir ajuda contextual',NULL),
('GAG','Cognitiva','Intermediário',NULL,'Permitir lembretes de objetivos',NULL),
('GAG','Motora','Básico',NULL,'Garantir controles simples',NULL),
('GAG','Motora','Básico',NULL,'Utilizar elementos interativos grandes e bem espaçados',NULL),
('GAG','Geral','Básico',NULL,'Oferecer diferentes níveis de dificuldade',NULL),
('GAG','Auditiva','Básico',NULL,'Não depender exclusivamente do som para transmitir informação essencial',NULL);

