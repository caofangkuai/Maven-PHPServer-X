<?php
	if ($sub_menu != FALSE):
?>
<ul class="nav nav-tabs">
<?php

	foreach ($sub_menu as $sub_page => $urltext):
?>
		<li <?php echo ($sub_page == $current_sub_page) ? 'class="active"' : ''; ?>>
			<a href="<?php echo Route::get('sub_menu')->uri(array('controller' => Request::current()->controller(), 'action' => $sub_page)); ?>" >
				<?php echo $urltext; ?>
			</a>
		</li>
<?php	
	endforeach;
?>
</ul>	
<?php
	endif;