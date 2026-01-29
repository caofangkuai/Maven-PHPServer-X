<!-- Upload artifact -->

<div>
	<div class="page-header">
		<h2><strong>第二(2)步:</strong> 添加依赖库</h2>
	</div>
	<?php if (isset($errors)): ?>
		<div class="alert alert-danger">
			Group id, artifact id和版本信息是必填项。
		</div>
	<?php endif; ?>
	<form role="form" method="post">
		<div class="form-group">
			<label for="groupId">Group id</label>
			<input type="text" class="form-control" name="groupId" id="groupId" value="<?php if (isset($dependency['groupId'])) echo $dependency['groupId']; ?>">
		</div>
		<div class="form-group">
			<label for="artifactId">Artifact id</label>
			<input type="text" class="form-control" name="artifactId" id="artifactId" value="<?php if (isset($dependency['artifactId'])) echo $dependency['artifactId']; ?>">
		</div>
		<div class="form-group">
			<label for="version">版本</label>
			<input type="text" class="form-control" name="version" id="version" value="<?php if (isset($dependency['version'])) echo $dependency['version']; ?>">
		</div>
		<div class="form-group">
			<label for="classifier">Classifier</label>
			<input type="text" class="form-control" name="classifier" id="classifier" value="<?php if (isset($dependency['classifier'])) echo $dependency['classifier']; ?>">
		</div>
		<div class="form-group">
			<label for="type">类型</label>
			<input type="text" class="form-control" name="type" id="type" value="<?php if (isset($dependency['type'])) echo $dependency['type']; ?>">
		</div>
		<button type="submit" class="btn btn-primary">
			添加依赖库
		</button>
		<div class="form-group">
			<label for="pom">Pom</label>
			<textarea name="pom" id="pom" class="form-control" rows="30" readonly="readonly"><?php echo htmlspecialchars($pom); ?></textarea>
		</div>
	</form>
</div>