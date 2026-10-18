<?php
final class OptionImage {
 public static function url(?string $path): ?string {
  if(!$path || !preg_match('~^images/(?:[A-Za-z0-9_-]+/)*[A-Za-z0-9_-]+\.(?:png|jpe?g|gif|webp)$~iD',$path))return null;
  $root=realpath(__DIR__.'/../public/images');$file=realpath(__DIR__.'/../public/'.$path);
  if(!$root||!$file||strncmp($file,$root.DIRECTORY_SEPARATOR,strlen($root)+1)!==0||!is_file($file))return null;
  $info=@getimagesize($file);
  if(!$info||!in_array($info['mime'],['image/png','image/jpeg','image/gif','image/webp'],true))return null;
  return $path;
 }
 public static function render(array $option): string {
  $path=$option['image_path']??null;if(!$path)return '';
  $url=self::url($path);
  if(!$url)return '<span class="option-image-missing">Imagem indisponível</span>';
  $label=trim($option['option_text']??'');
  $alt=$label!==''?'Ilustração da alternativa: '.$label:'Ilustração da alternativa';
  return '<img class="option-image" src="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'" alt="'.htmlspecialchars($alt,ENT_QUOTES,'UTF-8').'" loading="lazy">';
 }
}

