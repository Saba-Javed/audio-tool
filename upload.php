<?php
// upload.php
header('Content-Type: application/json');
$uploadDir='__DIR__/uploads/';
$maxBytes=100*1024*1024;
$allowed=['mp3','wav','ogg','m4a','flac','aac','wma','opus','aiff'];
function je($m){echo json_encode(['success'=>false,'error'=>$m]);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST') je('Method not allowed.');
if(empty($_FILES['audio'])) je('No file received.');
$f=$_FILES['audio'];
if($f['error']!==UPLOAD_ERR_OK) je('Upload error #'.$f['error']);
$orig=basename($f['name']); $ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION));
if($f['size']>$maxBytes) je('File too large (max 100MB).');
if(!in_array($ext,$allowed,true)) je("Unsupported format: .$ext");
$dir=__DIR__.'/uploads/';
if(!is_dir($dir)&&!mkdir($dir,0755,true)) je('Cannot create uploads dir.');
$base=preg_replace('/[^a-zA-Z0-9_\-]/','_',pathinfo($orig,PATHINFO_FILENAME));
$base=substr($base,0,40);
$unique=uniqid($base.'_',true).'.'.$ext;
if(!move_uploaded_file($f['tmp_name'],$dir.$unique)) je('Failed to save.');
echo json_encode(['success'=>true,'filename'=>$unique,'original_name'=>$orig]);