<?php
require_once('vendor/autoload.php');
use Monolog\Logger;
use Monolog\Handler\NullHandler;
use XPDF\PdfToText;
function flatten(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}
$dir = getcwd().'/data';
$fb = new \Sal\FileBrowser($dir);
$file_selected = false;
if(isset($_REQUEST['file'])){
	$file_selected = $_REQUEST['file'];
}
$has_pdfs = false;
if(isset($fb->files['application/pdf']) && (count($fb->files['application/pdf']) > 0)){
	$has_pdfs = true;
}
$map = [];
$parcels = [];
?>
<pre>
<?php
if($has_pdfs){
	// Create a logger
	$logger = new Logger('MyLogger');
	$logger->pushHandler(new NullHandler());

	// You have to pass a Monolog logger
	// This logger provides some usefull infos about what's happening
	$pdfToText = new PdfToText('C:\Program Files\Git\mingw64\bin\pdftotext.exe',$logger);
	foreach($fb->files['application/pdf'] as $file){
		// echo "\n<a href=\"data/$file\">$file</a>";
		// open PDF
		$pdfToText->open($dir.'/'.$file);

		// PDF text is now in the $text variable
		$text = $pdfToText->getText();
		$text = preg_replace('/-\n/','-',$text);
		$text = preg_replace('/\n|and|,|\([a-z ]*\)/',' ',$text);
		$match = [];
		// echo "\n------CLEAN------\n$text\n-----------\n";
		// preg_match_all('/PID([0-9]{3}-[0-9]{4}-[0-9]{5}(, (and)?)?)*Legal/s',$text,$match);
		preg_match_all('/PID ?(([0-9]{3}-[0-9]{4}-[0-9]{5}( *)){1,})/s',$text,$match);
		// print_r($match);
		$idstring = implode(' ',$match[1]);
		$ids = preg_split('/[ ]*/',$idstring,PREG_SPLIT_NO_EMPTY);
		$ids = explode(' ',$idstring);
		$ids = array_filter($ids,function($id){
			return ($id != '');
		});
		// print_r($ids);
		$pdfToText->close();
		$ids = array_unique($ids);
		foreach($ids as $id){
			if(!isset($parcels[$id])){
				$parcels[$id] = [];
			}
			$parcels[$id][] = $file;
		}
		$map[$file] = $ids;
	}
	// print_r($map);
	// print_r($parcels);
}
echo "PDF,PIDs";
foreach($map as $file=>$pids){
	echo "\n$file,".implode(' ',$pids);
}
// echo "PID,PDFs";
// foreach($parcels as $parcel=>$ids){
	// echo "\n$parcel,".implode(' ',$ids);
// }
?>
</pre>