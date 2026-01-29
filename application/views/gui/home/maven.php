<?php

$base_url = URL::base(TRUE, TRUE) . 'browse/';

// Maven settings.xml 模板
$maven_template = <<<MAVEN
<?xml version="1.0" encoding="UTF-8"?>
<settings xsi:schemaLocation="http://maven.apache.org/SETTINGS/1.1.0 http://maven.apache.org/xsd/settings-1.1.0.xsd" xmlns="http://maven.apache.org/SETTINGS/1.1.0"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <profiles>
    <profile>
      <repositories>
        <repository>
          <snapshots>
            <enabled>false</enabled>
          </snapshots>
          <id>central</id>
          <name>libs-release-local</name>
          <url>{$base_url}libs-release-local</url>
        </repository>
        <repository>
          <snapshots />
          <id>snapshots</id>
          <name>libs-snapshots-local</name>
          <url>{$base_url}libs-snapshots-local</url>
        </repository>
      </repositories>
      <pluginRepositories>
        <pluginRepository>
          <snapshots>
            <enabled>false</enabled>
          </snapshots>
          <id>central</id>
          <name>plugins-releases-local</name>
          <url>{$base_url}plugins-releases-local</url>
        </pluginRepository>
        <pluginRepository>
          <snapshots />
          <id>snapshots</id>
          <name>plugins-snapshots-local</name>
          <url>{$base_url}plugins-snapshots-local</url>
        </pluginRepository>
      </pluginRepositories>
      <id>phpmaven</id>
    </profile>
  </profiles>
  <activeProfiles>
    <activeProfile>phpmaven</activeProfile>
  </activeProfiles>
</settings>
MAVEN;

// Gradle 仓库配置模板
$gradle_template = <<<GRADLE
repositories {
    maven {
        url "{$base_url}libs-release-local"
        mavenContent {
            releasesOnly()
        }
    }
    maven {
        url "{$base_url}libs-snapshots-local"
        mavenContent {
            snapshotsOnly()
        }
    }
}

pluginManagement {
    repositories {
        maven {
            url "{$base_url}plugins-releases-local"
            mavenContent {
                releasesOnly()
            }
        }
        maven {
            url "{$base_url}plugins-snapshots-local"
            mavenContent {
                snapshotsOnly()
            }
        }
    }
}
GRADLE;
?>

<style>
.textarea-container {
    position: relative;
    margin-bottom: 20px;
}

.copy-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    padding: 3px 8px;
    font-size: 12px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 3px;
}

.copy-btn:hover {
    background-color: #0056b3;
}
</style>

<div>
    <h3>Maven settings.xml</h3>
    <div class="textarea-container">
        <button class="copy-btn" onclick="copyToClipboard('mavenTextarea')">复制</button>
        <textarea id="mavenTextarea" class="form-control" rows="20" readonly="readonly"><?php echo htmlspecialchars($maven_template); ?></textarea>
    </div>

    <h3>Gradle 仓库配置</h3>
    <div class="textarea-container">
        <button class="copy-btn" onclick="copyToClipboard('gradleTextarea')">复制</button>
        <textarea id="gradleTextarea" class="form-control" rows="20" readonly="readonly"><?php echo htmlspecialchars($gradle_template); ?></textarea>
    </div>
</div>

<script>
function copyToClipboard(textareaId) {
    const textarea = document.getElementById(textareaId);
    textarea.select();
    textarea.setSelectionRange(0, 99999); // 兼容移动端
    try {
        const successful = document.execCommand('copy');
        if(successful){
            alert('复制成功!');
        } else {
            alert('复制失败，请手动复制');
        }
    } catch (err) {
        alert('浏览器不支持自动复制，请手动复制');
    }
}
</script>
