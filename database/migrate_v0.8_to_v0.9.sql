CREATE TABLE IF NOT EXISTS question_games (
 question_id INT NOT NULL,
 game_id INT NOT NULL,
 PRIMARY KEY (question_id, game_id),
 CONSTRAINT fk_question_games_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
 CONSTRAINT fk_question_games_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);
INSERT IGNORE INTO question_games(question_id,game_id)
 SELECT id,game_id FROM questions WHERE game_id IS NOT NULL;

