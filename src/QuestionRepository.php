<?php
require_once __DIR__ . '/Database.php';
class QuestionRepository {
 private PDO $pdo;
 public function __construct(){ $this->pdo=Database::connect(); }
 public function all(string $search='',?int $categoryId=null,?int $gameId=null,?string $type=null):array{
  $sql="SELECT q.*,c.name category_name,(SELECT GROUP_CONCAT(DISTINCT gx.name ORDER BY gx.name SEPARATOR ' · ') FROM games gx WHERE gx.id=q.game_id OR EXISTS (SELECT 1 FROM question_games qgx WHERE qgx.question_id=q.id AND qgx.game_id=gx.id)) game_name FROM questions q LEFT JOIN categories c ON c.id=q.category_id LEFT JOIN games g ON g.id=q.game_id WHERE 1=1";$params=[];
  if($search!==''){ $sql.=" AND q.question_text LIKE :search";$params['search']='%'.$search.'%'; }
  if($categoryId){$sql.=" AND q.category_id=:category_id";$params['category_id']=$categoryId;}
  if($gameId){$sql.=" AND (q.game_id=:game_id OR EXISTS (SELECT 1 FROM question_games qgf WHERE qgf.question_id=q.id AND qgf.game_id=:linked_game_id))";$params['game_id']=$gameId;$params['linked_game_id']=$gameId;}
  if($type){$sql.=" AND q.question_type=:question_type";$params['question_type']=$type;}
  $sql.=" ORDER BY q.id DESC";$st=$this->pdo->prepare($sql);$st->execute($params);return $st->fetchAll();
 }
 public function find(int $id):?array{
  $st=$this->pdo->prepare("SELECT q.*,c.name category_name,(SELECT GROUP_CONCAT(DISTINCT gx.name ORDER BY gx.name SEPARATOR ' · ') FROM games gx WHERE gx.id=q.game_id OR EXISTS (SELECT 1 FROM question_games qgx WHERE qgx.question_id=q.id AND qgx.game_id=gx.id)) game_name FROM questions q LEFT JOIN categories c ON c.id=q.category_id LEFT JOIN games g ON g.id=q.game_id WHERE q.id=:id");$st->execute(['id'=>$id]);$q=$st->fetch();if(!$q)return null;
  $st=$this->pdo->prepare("SELECT * FROM question_options WHERE question_id=:id ORDER BY position,id");$st->execute(['id'=>$id]);$q['options']=$st->fetchAll();
  $st=$this->pdo->prepare("SELECT ag.* FROM accessibility_guidelines ag JOIN question_guidelines qg ON qg.guideline_id=ag.id WHERE qg.question_id=:id ORDER BY ag.framework,ag.category,ag.level,ag.code,ag.name");$st->execute(['id'=>$id]);$q['guidelines']=$st->fetchAll();$st=$this->pdo->prepare('SELECT id FROM games WHERE id=:legacy OR id IN (SELECT game_id FROM question_games WHERE question_id=:id) ORDER BY name');$st->execute(['legacy'=>$q['game_id'],'id'=>$id]);$q['game_ids']=array_map('intval',$st->fetchAll(PDO::FETCH_COLUMN));return $q;
 }
 public function categories():array{return $this->pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();}
 public function games():array{return $this->pdo->query("SELECT * FROM games ORDER BY name")->fetchAll();}
 public function guidelines():array{return $this->pdo->query("SELECT * FROM accessibility_guidelines ORDER BY framework,category,level,code,name")->fetchAll();}
 public function create(array $d):int{$this->pdo->beginTransaction();try{
  $st=$this->pdo->prepare("INSERT INTO questions(question_text,author,fonte,category_id,game_id,question_type,has_image,image_path,notes) VALUES(:question_text,:author,:fonte,:category_id,:game_id,:question_type,0,NULL,:notes)");
  $st->execute(['question_text'=>$d['question_text'],'author'=>$d['author'],'fonte'=>trim($d['fonte']??'') !== '' ? trim($d['fonte']) : null,'category_id'=>$d['category_id']?:null,'game_id'=>null,'question_type'=>$d['question_type'],'notes'=>$d['notes']?:null]);$id=(int)$this->pdo->lastInsertId();$this->saveOptions($id,$d['options']??[]);$this->saveGuidelines($id,$d['guideline_ids']??[]);$this->saveGames($id,$d);$this->pdo->commit();return $id;
 }catch(Throwable $e){$this->pdo->rollBack();throw $e;}}
 public function update(int $id,array $d):void{$this->pdo->beginTransaction();try{
  $st=$this->pdo->prepare("UPDATE questions SET question_text=:question_text,author=:author,fonte=:fonte,category_id=:category_id,game_id=:game_id,question_type=:question_type,notes=:notes WHERE id=:id");$st->execute(['id'=>$id,'question_text'=>$d['question_text'],'author'=>$d['author'],'fonte'=>trim($d['fonte']??'') !== '' ? trim($d['fonte']) : null,'category_id'=>$d['category_id']?:null,'game_id'=>null,'question_type'=>$d['question_type'],'notes'=>$d['notes']?:null]);
  $this->pdo->prepare("DELETE FROM question_guidelines WHERE question_id=:id")->execute(['id'=>$id]);$this->updateOptions($id,$d['options']??[]);$this->saveGuidelines($id,$d['guideline_ids']??[]);$this->saveGames($id,$d);$this->pdo->commit();
 }catch(Throwable $e){$this->pdo->rollBack();throw $e;}}
 public function delete(int $id):void{$this->pdo->prepare("DELETE FROM questions WHERE id=:id")->execute(['id'=>$id]);}
 private function saveOptions(int $id,array $opts):void{$st=$this->pdo->prepare("INSERT INTO question_options(question_id,option_text,is_correct,position) VALUES(:question_id,:option_text,:is_correct,:position)");$pos=1;foreach($opts as $o){$t=trim($o['text']??'');if($t==='')continue;$st->execute(['question_id'=>$id,'option_text'=>$t,'is_correct'=>!empty($o['is_correct'])?1:0,'position'=>$pos++]);}}
 private function updateOptions(int $id,array $opts):void {
  $st=$this->pdo->prepare('SELECT * FROM question_options WHERE question_id=:id FOR UPDATE');
  $st->execute(['id'=>$id]);$existing=[];
  foreach($st->fetchAll() as $row)$existing[(int)$row['id']]=$row;
  // Older forms cannot silently discard illustrations when they omit option IDs.
  if(array_filter($existing,fn($o)=>!empty($o['image_path'])) && !array_filter($opts,fn($o)=>!empty($o['id'])))
   throw new InvalidArgumentException('Reabra a edição para preservar as imagens.');
  $seen=[];$pos=1;
  $update=$this->pdo->prepare('UPDATE question_options SET option_text=:text,is_correct=:correct,position=:position WHERE id=:id AND question_id=:question_id');
  $insert=$this->pdo->prepare('INSERT INTO question_options(question_id,option_text,is_correct,position) VALUES(:question_id,:text,:correct,:position)');
  foreach($opts as $o){
   $oid=(int)($o['id']??0);$text=trim($o['text']??'');
   if($oid && (!isset($existing[$oid])||isset($seen[$oid])))throw new InvalidArgumentException('Alternativa inválida.');
   if($oid)$seen[$oid]=true;
   if($text==='' && (!$oid || empty($existing[$oid]['image_path'])))continue;
   $args=['question_id'=>$id,'text'=>$text,'correct'=>!empty($o['is_correct'])?1:0,'position'=>$pos++];
   if($oid){$args['id']=$oid;$update->execute($args);unset($existing[$oid]);}
   else $insert->execute($args);
  }
  $delete=$this->pdo->prepare('DELETE FROM question_options WHERE id=:id AND question_id=:question_id');
  foreach($existing as $oid=>$row){
   if(!empty($row['image_path']))throw new InvalidArgumentException('Reabra a edição para preservar todas as imagens.');
   $delete->execute(['id'=>$oid,'question_id'=>$id]);
  }
 }
 private function saveGames(int $id,array $d):void {
  $raw=$d['game_ids']??(isset($d['game_id'])?[$d['game_id']]:[]);
  if(!is_array($raw))throw new InvalidArgumentException('Jogos inválidos');
  $ids=array_values(array_unique(array_filter(array_map('intval',$raw),fn($v)=>$v>0)));sort($ids);
  $this->pdo->prepare('DELETE FROM question_games WHERE question_id=?')->execute([$id]);
  $st=$this->pdo->prepare('INSERT INTO question_games(question_id,game_id) VALUES(?,?)');
  foreach($ids as $gid)$st->execute([$id,$gid]);
  // Keep one legacy association for old import scripts; all associations live in question_games.
  $this->pdo->prepare('UPDATE questions SET game_id=? WHERE id=?')->execute([$ids[0]??null,$id]);
 }
 private function saveGuidelines(int $id,array $ids):void{if(!$ids)return;$st=$this->pdo->prepare("INSERT IGNORE INTO question_guidelines(question_id,guideline_id) VALUES(:question_id,:guideline_id)");foreach(array_unique(array_map('intval',$ids)) as $gid){if($gid>0)$st->execute(['question_id'=>$id,'guideline_id'=>$gid]);}}
}

