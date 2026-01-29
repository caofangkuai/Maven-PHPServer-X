<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<html>
<head>
	<title><?php echo SERVER_NAME; ?> - <?php echo $page_title; ?></title>
	<base href="<?php echo URL::base(TRUE); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Bootstrap -->
	<link href="static/css/bootstrap.min.css" rel="stylesheet">
	
	<link href="static/css/custom.css" rel="stylesheet">
	
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	
	<div class="container">
	
		<!-- Fixed navbar -->
		<div class="header">
			<ul class="nav nav-pills pull-right">
				<?php echo $menu; ?>
			</ul>
			<h3 class="text-muted"><?php echo SERVER_NAME; ?></h3>
		</div>
		
		<?php echo $sub_menu; ?>
		
		<div class="page-header">
			<h1><?php echo $page_title; ?></h1>
		</div>
		
		<div class="alert alert-warning">
		<!-- <div class="alert alert-danger"> -->
			<strong>警告!</strong>
			此应用程序仍在开发中。目前处于pre-alpha阶段。
		</div>
    
		<?php echo $content; ?>
	
		<div class="footer">
			<p>&copy; Creating Future 2013 & caofangkuai 2026</p>
		</div>
		
	</div> <!-- /container -->

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="static/js/jquery-2.min.js"></script>
	<!-- <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script> -->
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="static/js/bootstrap.min.js"></script>
<?php
	if ($scripts != FALSE):
		foreach ($scripts as $script_entry):
?>
	<script src="static/js/<?php echo $script_entry; ?>"></script>
<?php
		endforeach;
	endif;
	if ($script != FALSE):
?>
	<script>
		<?php echo $script; ?>
	</script>
<?php
	endif;
?>	
</body>
</html>