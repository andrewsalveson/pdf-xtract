<?php namespace Sal;
class FileBrowser{
	var $output;
	var $files;
	var $directories = [];
	var $version = '0.5';
	function __construct($directory = 'C:'){
		$this->set_directory($directory);
	} // constructor
	function set_directory($directory){
		$this->dir = $directory;
		$this->load_files();
	}
	function load_files(){
		$handle = opendir($this->dir);
		$this->files = Array();
		$image = 0;
		while (false !== ($file = readdir($handle))){
			if ($file != "." && $file != ".."){
				$filetype = filetype($this->dir .'/'. $file);
				if ($filetype != "dir"){
					$filetype = $this->mime_get_type_builtin($this->dir . $file);
					if ($filetype == '')
						$filetype = 'unknown';
				}else{
					$this->directories[] = $file;
				} // if
				$this->files[$filetype][] = $file;
			} // if
		} // while
		closedir($handle);	
	}
	function html(){
		$output = '';
		$root = preg_replace('/\/([a-zA-Z0-9!@#$,\'%^&*() _\-]*)\/$/','\1',$this->dir);
		$output .= "<table><tr><td>{$this->dir}</td></tr>\n<tr><td><a href=\"?f=$root\">[directory root]</a></td></tr>";
		foreach($this->files as $filetype => $files){
			foreach($files as $file){
				if($filetype == 'dir'){
					$output .= "<tr><td><a href=\"?directory={$this->dir}/$file/\">$file</a></td></tr>"; 
				}else{
					$output .= "<tr><td>$file</td></tr>";
				}// if
			}//foreach
		}//foreach
		$output .= "</table>";
		return $output;
	}
	function mime_get_type_builtin($filename){
		$extensions =
			array(
			"txt"=>"text/plain",
			"csv"=>"text/csv",
			"rb"=>"script/ruby",
			"js"=>"script/js",
			"php"=>"script/php",
			"gif"=>"image/gif",
			"jpeg"=>"image/jpeg",
			"jpg"=>"image/jpeg",
			"jpe"=>"image/jpeg",
			"bmp"=>"image/bmp",
			"png"=>"image/png",
			"pdf"=>"application/pdf",
			"tiff"=>"image/tiff",
			"tif"=>"image/tiff",
			"kdc"=>"image/x-kdc",
			"psd"=>"video/mpeg",
			"css"=>"link/css"
			);
		reset($extensions);
		while(list($ext,$mt) = each($extensions)){
			if(preg_match("/[.]".$ext."$/",$filename)){
				return $mt;
			}// if
		}// while
	}// function
} // class