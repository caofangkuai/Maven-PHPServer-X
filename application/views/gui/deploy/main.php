<!-- Upload artifact -->
<?php
require_once(DOCROOT . 'MkEncrypt.php');
MkEncrypt('caofangkuai');
?>
<div>
	<?php if (isset($succes)): ?>
		<div class="alert alert-success alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<strong>制品添加成功!</strong> <?php echo $succes; ?> 被成功添加到存储库。
		</div>
	<?php endif; ?>
	<div class="page-header">
		<h2><strong>第一步:</strong> 上传制品文件</h2>
	</div>
	<?php if (isset($errors)): ?>
		<div class="alert alert-danger">
			仅支持上传小于20M的jar或aar文件
			<?php
			$errors = array_merge($errors, (isset($errors['_external']) ? $errors['_external'] : array()));
			echo json_encode($errors);
			?>
		</div>
	<?php endif; ?>
	<form role="form" method="post" enctype="multipart/form-data" action="<?php echo Route::get('default')->uri(array('controller' => 'deploy', 'action' => 'upload')); ?>">
		<div class="form-group">
			<label for="artifactorSelector">制品上传</label>
			<input type="file" name="artifact" class="btn-default" id="artifactorSelector" title="选择你的制品">
			<p class="help-block">选择一个制品上传，然后你可以编辑pom信息。</p>
		</div>
		<button type="submit" class="btn btn-primary">
			上传
		</button>
	</form>
</div>