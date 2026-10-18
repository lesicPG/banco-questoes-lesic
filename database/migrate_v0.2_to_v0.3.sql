USE banco_questoes_lesic;

ALTER TABLE questions
ADD COLUMN author VARCHAR(150) NOT NULL DEFAULT 'Autor não identificado' AFTER question_text;

