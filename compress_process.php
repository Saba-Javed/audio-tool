<?php
/**
 * compress_process.php
 * POST: file, bitrate (64k|128k|192k)
 */
header('Content-Type: application/json');

$uploadsDir    = __DIR__.'/uploads/';
$outputDir     = __DIR__.'/output/';
$outputWeb     = 'output/';
$ffmpeg        = 'ffmpeg';
$validBitrates = ['64k','128k','192k'];

function je($m){echo json_encode(['success'=>false,'error'=>$m]);exit;}
function fmtSz($b){if($b<1024)return $b.' B';if($b<1048576)return round($b/1024,1).' KB';return round($b/1048576,2).' MB';}

if($_SERVER['REQUEST_METHOD']!=='POST') je('Method not allowed.');

$filename = isset($_POST['file'])    ? basename($_POST['file'])    : ''; if(!$filename) je('No file.');
$bitrate  = isset($_POST['bitrate']) ? $_POST['bitrate']           : '128k';
if(!in_array($bitrate,$validBitrates,true)) je('Invalid bitrate.');

$inputPath = $uploadsDir.$filename;
if(!file_exists($inputPath)) je('Source file not found.');
if(!is_dir($outputDir)&&!mkdir($outputDir,0755,true)) je('Cannot create output dir.');

$ext     = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
$outExt  = in_array($ext,['wav','flac','aiff']) ? 'mp3' : $ext;
$base    = preg_replace('/_[a-f0-9]+\.\d+$/','',pathinfo($filename,PATHINFO_FILENAME)) ?: 'audio';
$outName = $base.'_compressed_'.uniqid().'.'.$outExt;
$outPath = $outputDir.$outName;
$dlName  = $base.'_compressed.'.$outExt;

$codec = ($outExt==='mp3') ? '-codec:a libmp3lame' : '';
$cmd=sprintf('%s -y -i %s %s -b:a %s -vn %s 2>&1',
  escapeshellcmd($ffmpeg),
  escapeshellarg($inputPath),
  $codec,
  escapeshellarg($bitrate),
  escapeshellarg($outPath)
);
exec($cmd,$out,$code);

if($code!==0||!file_exists($outPath)||filesize($outPath)<100){
  error_log("compress_process.php failed: ".implode("\n",$out));
  je('FFmpeg compression failed. Check server error log.');
}

$outSize = filesize($outPath);
echo json_encode([
  'success'           =>true,
  'download_url'      =>$outputWeb.rawurlencode($outName),
  'output_name'       =>$dlName,
  'output_size'       =>fmtSz($outSize),
  'output_size_bytes' =>$outSize,
]);
