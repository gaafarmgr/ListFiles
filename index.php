<?php

if((include 'config.php') != TRUE)
{
	$title = "TEST";
	$show_downloads = false;
	$directory = "files";
}


$files["."] = false;

function createTable($folder)
{
	global $show_downloads;
	global $files;
	if ($handle = opendir($folder))
	{
		while (false !== ($file = readdir($handle))) 
		{
			if ($file[0] != "." && strpos($file, ".html") === false)
			{
				$full_file = "$folder/$file";
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

				printf("<tr><td valign=\"top\"></td><td><a href=\"index.php?file=$file\">$file</a>&emsp;</td><td align=\"right\"> $modif &emsp;</td><td align=\"right\">$size $unit &emsp;</td><td>$sum</td>");
				if($show_downloads == TRUE)
				{
					$statsFile = @fopen("stats/$file", "rb");
					$count = 0;
					if($statsFile != NULL)
					{
						$string = fgets($statsFile);
						$count = intval($string);
						fclose($statsFile);
					}
					
					printf("<td align=\"right\">$count</td>");
				}
				printf("</tr>");
			}
		}
	}
	closedir($handle);
}

?>

<html>

	<head>
		<title>
			<?php echo $title; ?>
		</title>
	</head>
	
	<body>
		<h1>
			<?php echo $title; ?>
		</h1>
		<table>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Last modified</th>
				<th>Size</th>
				<th>MD5</th>
				<?php
					if($show_downloads)
						echo "<th>Downloads</th>";
				?>
			</tr>
			<tr>
				<th colspan="6">
					<hr>
				</th>
			</tr>
			<?php	
				$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
				$url .= $_SERVER['SERVER_NAME'];
				$url .= $_SERVER['REQUEST_URI'];
				createTable($directory);
			?>
			<tr>
				<th colspan="6"><hr></th>
			</tr>
		</table>
	</body>

</html>

<?php

if(isset($_GET['file']))
{

	function incrementData($filename)
	{
		$num = -1;
		if(!file_exists($filename))
		{
			$file = fopen($filename, "wb+");
			fwrite($file, "0");
			fclose($file);
		}
		$file = fopen($filename, "rb");
		if($file != NULL)
		{
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
	
	function disable_gzip()
	{
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

	$file_to_download = "$directory/".$_GET['file'];
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
