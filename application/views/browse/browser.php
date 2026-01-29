<?php
function startsWith($haystack, $needle)
{
	return $needle === "" || strpos($haystack, $needle) === 0;
}
function human_filesize($bytes, $decimals = 2) {
	$sz = 'BKMGTP';
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html>
<head>
	<base href="<?php echo $currenturl; ?>">
	<title>Index of <?php echo basename($currentpath); ?></title>
</head>
<body>
<h1>Index of <?php echo basename($currentpath); ?></h1>
<pre>Name                                     Last modified          Size</pre>
<hr/>
<pre><?php
	foreach ($dircontent as $key => $value):
		if (startsWith($value, '.') && $isRoot):
			
		else:
			$file = realpath($currentpath . $value);
			$isdir = is_dir($file);
			$filelastedit = filemtime($file);
			if ($isdir) {
				$value = $value . '/';
			}
			
			$space = substr('                                         ', strlen($value));
?>
<a href="<?php echo $value; ?>"><?php echo $value; ?></a><?php echo $space; ?><?php echo str_pad(date("d-M-Y H:i:s", $filelastedit), 23); ?><?php echo  $isdir ? '-' : human_filesize(filesize($file)); ?>

<?php
		endif;
	endforeach;
?>
</pre>
<hr/>
<address style="font-size:small;"><?php echo SERVER_NAME; ?>/<?php echo SERVER_VERSION; ?> Server at <?php echo $_SERVER['SERVER_NAME']; ?> Port <?php echo $_SERVER['SERVER_PORT']; ?></address>
</body>
</html>