<?php namespace Devmeta;

class File extends \DateTime {

	static function file_extension($filename){	$dot= substr(strrchr($filename, "."), 1);$str= explode("?",$dot);return $str[0];}

	//static function nice_size($fs){if ($fs >= 1073741824) $fs = round(($fs / 1073741824 * 100) / 100).' Gb'; elseif ($fs >= 1048576) $fs = round(($fs / 1048576 * 100) / 100).' Mb'; elseif ($fs >= 1024) $fs = round(($fs / 1024 * 100) / 100).' Kb';else $fs = $fs .' b';return $fs;}

	static function dir_content($directory, $filter = "", $exclude = "", $limit = 0 ) {

		if(substr($directory,-1) != '/') 
			$directory .=  "/";

		$excludeAlways = array(".", "..", "index.php","Thumbs.db");
		$arrFilter = array();
		$arrExclude = array();	
		$results = array();	

		if (strlen($filter))
			$arrFilter = explode(' ', $filter);

		if (strlen($exclude))
			$arrExclude = explode(' ', $exclude);

		$handler = opendir($directory);

		while ($filename = readdir($handler)) {	

			if($limit && $limit == count($results))
				break;

			if($arrFilter)
				if (!in_array(file_extension($filename), $arrFilter))	
					continue;

			if($arrExclude)
				if (in_array($filename, $arrExclude)) 
					continue;			

			$filenamepath = $directory.$filename;

			if (!in_array($filename, $excludeAlways)){	
				if(is_dir( $directory . $filename . '/')){	
				} else {
					$results[] = $filename;
				}
			}
		}

		closedir($handler);	

		return $results;
	}


	static function list_content($directory, $filter = "", $exclude = "" ) {
		if(substr($directory,-1) != '/') $directory .=  "/";
		$excludeAlways = array(".", "..", "index.php","Thumbs.db");
		$arrFilter = array();
		$arrExclude = array();	
		$results = array();	
		if (strlen($filter)){
			$arrFilter = explode(' ', $filter);
		}
		if (strlen($exclude))
			$arrExclude = explode(' ', $exclude);
		$handler = opendir($directory);
		while ($filename = readdir($handler)) {	
			if($arrFilter)
				if (!in_array(file_extension($filename), $arrFilter))	
					continue;
			if($arrExclude)
				if (in_array(file_extension($filename), $arrExclude)) 
					continue;			
			$filenamepath = $directory.$filename;
			if (!in_array($filename, $excludeAlways)){	
				if(is_dir( $directory . $filename . '/')){	
					$results[0][] = $filename;
				} else {
					$filename_stats = stat($filenamepath);
					$results[1][] = array($filename,nice_size($filename_stats[7]),date('l, F dS 20y - H:i:s',$filename_stats[8]),date('l, F dS 20y - H:i:s', $filename_stats[9]),$filename_stats);
				}
			}
		}
		closedir($handler);	
		return $results;
	}

	static function nice_size($fs){if ($fs >= 1073741824) $fs = round(($fs / 1073741824 * 100) / 100).' Gb'; elseif ($fs >= 1048576) $fs = round(($fs / 1048576 * 100) / 100).' Mb'; elseif ($fs >= 1024) $fs = round(($fs / 1024 * 100) / 100).' Kb';else $fs = $fs .' b';return $fs;}

	static function destroy($filename,$type){

		$filepath = SP . "public/upload/{$type}/{$filename}";

		if( is_file( $filepath ) ){
			unlink($filepath);
			return true;
		}

		return false;
	}

	static function writeable($path,$chmod = 0777){
		if( ! is_writable($path)){
			if( $chmod ){
				chmod($path,$chmod);
			}
			return true;
		}
		return false;
	}

   	static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
}