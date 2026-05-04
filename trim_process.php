<?php
/**
 * trim_process.php
 * POST: file, mode (center|sides), start, end
 *
 * mode=center → keep [start..end], cut the rest
 * mode=sides  → remove [start..end], keep both sides and join them with concat
 */
header('Content-Type: application/json');

$uploadsDir = __DIR__.'/uploads/';
$outputDir  = __DIR__.'/output/';
$outputWeb  = 'output/';
$ffmpeg     = 'ffmpeg'; // change to full path if needed

function je($m){echo json_encode(['success'=>false,'error'=>$m]);exit;}
function ffTime($s){return sprintf('%02d:%02d:%06.3f',floor($s/3600),floor(($s%3600)/60),fmod($s,60));}
function fmtSz($b){if($b<1024)return $b.' B';if($b<1048576)return round($b/1024,1).' KB';return round($b/1048576,2).' MB';}

if($_SERVER['REQUEST_METHOD']!=='POST') je('Method not allowed.');

$filename = isset($_POST['file'])  ? basename($_POST['file'])  : ''; if(!$filename) je('No file.');
$mode     = isset($_POST['mode'])  ? $_POST['mode']            : 'center';
$start    = (float)($_POST['start']??0);
$end      = (float)($_POST['end']??0);

if(!in_array($mode,['center','sides'],true)) je('Invalid mode.');
if($start<0||$end<=$start) je('Invalid start/end times.');

$inputPath = $uploadsDir.$filename;
if(!file_exists($inputPath)) je('Source file not found.');

if(!is_dir($outputDir)&&!mkdir($outputDir,0755,true)) je('Cannot create output dir.');

$ext      = strtolower(pathinfo($filename,PATHINFO_EXTENSION));
$base     = preg_replace('/_[a-f0-9]+\.\d+$/', '', pathinfo($filename,PATHINFO_FILENAME)) ?: 'audio';
$suffix   = $mode==='center' ? 'trimmed' : 'sides_kept';
$outName  = $base.'_'.$suffix.'_'.uniqid().'.'.$ext;
$outPath  = $outputDir.$outName;
$dlName   = $base.'_'.$suffix.'.'.$ext;

if($mode==='center'){
  // Simple: keep [start..end]
  $cmd=sprintf('%s -y -ss %s -to %s -i %s -c copy -avoid_negative_ts make_zero %s 2>&1',
    escapeshellcmd($ffmpeg),
    escapeshellarg(ffTime($start)),
    escapeshellarg(ffTime($end)),
    escapeshellarg($inputPath),
    escapeshellarg($outPath)
  );
  exec($cmd,$out,$code);

} else {
  // Keep sides: concat left [0..start] and right [end..EOF]
  // We need a temp file for each segment, then concat
  $tmp1 = $outputDir.'_tmp1_'.uniqid().'.'.$ext;
  $tmp2 = $outputDir.'_tmp2_'.uniqid().'.'.$ext;
  $list = $outputDir.'_list_'.uniqid().'.txt';

  // Left segment
  $c1=sprintf('%s -y -ss 00:00:00.000 -to %s -i %s -c copy %s 2>&1',
    escapeshellcmd($ffmpeg),escapeshellarg(ffTime($start)),escapeshellarg($inputPath),escapeshellarg($tmp1));
  exec($c1,$o1,$r1);

  // Right segment
  $c2=sprintf('%s -y -ss %s -i %s -c copy %s 2>&1',
    escapeshellcmd($ffmpeg),escapeshellarg(ffTime($end)),escapeshellarg($inputPath),escapeshellarg($tmp2));
  exec($c2,$o2,$r2);

  // Create concat list
  file_put_contents($list,"file '".addslashes($tmp1)."'\nfile '".addslashes($tmp2)."'\n");

  // Concat
  $c3=sprintf('%s -y -f concat -safe 0 -i %s -c copy %s 2>&1',
    escapeshellcmd($ffmpeg),escapeshellarg($list),escapeshellarg($outPath));
  exec($c3,$out,$code);

  // Cleanup temps
  @unlink($tmp1); @unlink($tmp2); @unlink($list);
}

if($code!==0||!file_exists($outPath)||filesize($outPath)<100){
  error_log("trim_process.php failed. CMD output: ".implode("\n",$out));
  je('FFmpeg processing failed. Check server error log.');
}

echo json_encode([
  'success'      =>true,
  'download_url' =>$outputWeb.rawurlencode($outName),
  'output_name'  =>$dlName,
  'size'         =>fmtSz(filesize($outPath)),
]);
