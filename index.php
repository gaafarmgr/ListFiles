<html>

	<head>
		<title>Files</title>
	</head>
	
	<body>
		<h1>Files</h1>
		<table>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Last modified</th>
				<th>Size</th>
				<th>MD5</th>
			</tr>
			<tr>
				<th colspan="5">
					<hr>
				</th>
			</tr>
			<?php	
				$url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
				$url .= $_SERVER['SERVER_NAME'];
				$url .= $_SERVER['REQUEST_URI'];
				echo file_get_contents("$url/download.php?list");	
			?>
			<tr>
				<th colspan="5"><hr></th>
			</tr>
		</table>
	</body>

</html>
