<?php


if ($handle = opendir('files'))
{
	while (false !== ($file = readdir($handle))) 
	{
		if ($file[0] != "." && strpos($file, ".html") === false)
		{
			$full_file = "files/$file";
			$files[$full_file] = true;
			$modif = date("F d Y H:i:s.", filemtime($full_file));
			$unit = "MB";
			$size = intval((filesize($full_file)/1024.0)/1024.0);
			if($size == 0)
			{
				$size = intval(filesize($full_file)/1024.0);
				$unit = "KB";
				if($size == 0)
				{
					$size = filesize($full_file);
					$unit = "B";
				}
			}
			$sum = md5_file($full_file);

			if(isset($_GET['list']))
			{
				printf("<tr><td valign=\"top\"></td><td><a href=\"download.php?file=$file\">$file</a>&emsp;</td><td align=\"right\"> $modif &emsp;</td><td align=\"right\">$size $unit &emsp;</td><td>$sum</td></tr>");
			}
		}
	}
}
closedir($handle);	

if(isset($_GET['file']))
{

	function incrementData($filename)
	{
		$num = -1;
		if(!file_exists($filename)){
			$file = fopen($filename, "wb+");
			fwrite($file, "0");
			fclose($file);
		}
		$file = fopen($filename, "rb");
		if($file != NULL){
			$string = fgets($file);
			$num = intval($string);
			$num = $num+1;
			fclose($file);
			$file = fopen($filename, "wb+");
			if($file != NULL)
			{
				fprintf($file, "%d", $num);
				fclose($file);
			}
			else
			{
				echo "error 2";
			}
		}
		else
		{
			echo "error 1";
		}
		
		return($num);
	}
	
	function disable_gzip() {
		@ini_set('zlib.output_compression', 'Off');
		@ini_set('output_buffering', 'Off');
		@ini_set('output_handler', '');
		@apache_setenv('no-gzip', 1);	
	}
		
	function Download($path, $speed = null)
	{
		if (is_file($path) === true)
		{
			$file = @fopen($path, 'rb');
			$speed = (isset($speed) === true) ? round($speed * 1024) : 524288;

			if (is_resource($file) === true)
			{
				set_time_limit(0);
				ignore_user_abort(false);

				while (ob_get_level() > 0)
				{
					ob_end_clean();
				}

				header('Expires: 0');
				header('Pragma: public');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Type: application/octet-stream');
				header('Content-Length: ' . filesize($path));
				header('Content-Disposition: attachment; filename="' . basename($path) . '"');
				header('Content-Transfer-Encoding: binary');
				

				while (feof($file) !== true)
				{
					echo fread($file, $speed);

					while (ob_get_level() > 0)
					{
						ob_end_flush();
					}

					flush();
					sleep(1);
				}

				fclose($file);
			}

			exit();
		}

		return false;
	}

	$file_to_download = "files/".$_GET['file'];
	if(file_exists($file_to_download) && $files[$file_to_download])
	{
		incrementData("stats/".$_GET['file']);
		Download($file_to_download);
	}
	else
	{
		echo "Error, file not found!";
	}


}
	
?>
