<?php namespace bootie;

class str extends \DateTime {

	static function words($str,$words=30,$del='...'){
		return str_word_count($str) < $words ? $str : implode(' ',array_slice(explode(' ',$str),0,$words)) . ' ' . $del;
	}

	static function cwords($words){

		$wcount =  str_word_count($words);

		$ret = '';
		$words_pmin = 200;

		$estread = ceil($wcount / $words_pmin);
		$ret.= "" . $wcount . " palabras &mdash; \n";
		$ret.= "" . $estread . " minuto";
		$ret.= $estread > 1 ? "s":"";

		return $ret;
	}

	static public function slugify($text){ 
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

	  // trim
	  $text = trim($text, '-');

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // lowercase
	  $text = strtolower($text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  if (empty($text))
	  {
	    return 'n-a';
	  }

	  return $text;
	}

    static function sanitize($name,$folder = '')
    {

    	if($folder==''){
    		return false;
    	}

        $safe = $name;
        $safe = str_replace("#", "Nro", $safe);
        $safe = str_replace("$", "Dollar", $safe);
        $safe = str_replace("%", "Percent", $safe);
        $safe = str_replace("^", "", $safe);
        $safe = str_replace("&", "and", $safe);
        $safe = str_replace("*", "", $safe);
        $safe = str_replace("?", "", $safe);
        $safe = str_replace("(", "", $safe);
        $safe = str_replace(")", "", $safe);
        $safe = str_replace("á", "a", $safe);
        $safe = str_replace("é", "e", $safe);
        $safe = str_replace("í", "i", $safe);
        $safe = str_replace("ó", "o", $safe);
        $safe = str_replace("ú", "u", $safe);

		$files = \bootie\file::dir_content($folder);
        sort($files);
        $j=1;

        while(in_array($safe,$files))
        {
            $safe= preg_replace("/\((.*?)\)/", "",$safe);
            $parts= explode(".",$safe);
            $parts2= $parts;
            unset($parts2[count($parts2)-1]);
            $safe= implode(".",$parts2) . "($j)." .  $parts[count($parts)-1];
            $j++;
        }

        $safelast = str_replace(' ','-',strtolower($safe));

        return \bootie\str::make_unique_filename($safelast,$folder); 
    }

    static function make_unique_filename($filename, $destination)
    {
        $i = 0;
        $path_parts = pathinfo($filename);
        $path_parts['filename'] = str::slugify($path_parts['filename']);
        $filename = $path_parts['filename'];

        while (file_exists($destination.$filename.'-th.'.$path_parts['extension'])) {
            $filename = $path_parts['filename'].'-'.$i;
            $i++;
        }

        return $filename.'.'.$path_parts['extension'];
    }

    
	static function nice_size($fs){if ($fs >= 1073741824) $fs = round(($fs / 1073741824 * 100) / 100).' Gb'; elseif ($fs >= 1048576) $fs = round(($fs / 1048576 * 100) / 100).' Mb'; elseif ($fs >= 1024) $fs = round(($fs / 1024 * 100) / 100).' Kb';else $fs = $fs .' b';return $fs;}


	static function bold($str, $keywords = '')
	{
		$keywords = preg_replace('/\s\s+/', ' ', strip_tags(trim($keywords))); // filter

		$style = 'bold';
		$style_i = 'bold-strong';

		/* Apply Style */

		$var = '';

		foreach(explode(' ', $keywords) as $keyword)
		{
			$replacement = "<span class='".$style."'>".$keyword."</span>";
			$var .= $replacement." ";

			$str = str_ireplace($keyword, $replacement, $str);
		}

		/* Apply Important Style */

		$str = str_ireplace(rtrim($var), "<span class='".$style_i."'>".$keywords."</span>", $str);

		return $str;
	}

  	static function getCColor( $credits  ){
		$color = 'grey';

		if($credits > 0 && $credits < 100) {
			$color = 'blue';
		} else if($credits > 0 && $credits < 500) {
			$color = 'green';
		} else if($credits > 0 && $credits < 1000) {
			$color = 'magenta';
		}

		return $color;

	}
}