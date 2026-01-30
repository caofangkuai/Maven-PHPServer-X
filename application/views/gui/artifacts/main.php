<?php
// 获取所有制品信息
function getAllArtifacts()
{
    $artifacts = [];
    $repoPath = REPOPATH;

    // 递归遍历目录查找maven-metadata.xml文件
    $directoryIterator = new RecursiveDirectoryIterator($repoPath, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directoryIterator);

    foreach ($iterator as $file) {
        if ($file->getFilename() === 'maven-metadata.xml') {
            $filePath = $file->getPathname();
            $artifactData = parseMetadataFile($filePath);
            if ($artifactData) {
                $artifacts[] = $artifactData;
            }
        }
    }

    // 按名称排序
    usort($artifacts, function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    return $artifacts;
}

// 解析maven-metadata.xml文件
function parseMetadataFile($filePath)
{
    if (!file_exists($filePath)) {
        return null;
    }

    $xml = simplexml_load_file($filePath);
    if (!$xml) {
        return null;
    }

    // 从文件路径提取信息
    $relativePath = str_replace(REPOPATH, '', $filePath);
    $pathParts = explode('/', $relativePath);

    // 获取groupId和artifactId
    $groupIdParts = [];
    $artifactId = '';
    $versioning = $xml->versioning;

    // 解析路径以确定groupId和artifactId
    // 假设路径结构为: 存储库名称/group路径/artifactId/maven-metadata.xml
    $repoName = $pathParts[0];

    // 从文件路径向上查找artifactId（maven-metadata.xml所在目录）
    $dirPath = dirname($filePath);
    $artifactId = basename($dirPath);

    // 获取groupId（从存储库名称后的路径到artifactId之前的部分）
    $repoPathLength = strlen(REPOPATH . $repoName . '/');
    $groupPath = substr(dirname($filePath), $repoPathLength);
    $groupPath = dirname($groupPath);

    // 确保$groupPath不以'/'结尾
    if ($groupPath === '.') {
        $groupId = $artifactId;
    } else {
        $groupId = str_replace('/', '.', $groupPath);
    }

    // 获取最新版本
    $latestVersion = (string)$versioning->latest;
    if (empty($latestVersion) && isset($versioning->versions->version[0])) {
        $latestVersion = (string)$versioning->versions->version[0];
    }

    // 获取所有版本
    $versions = [];
    if ($versioning && $versioning->versions && $versioning->versions->version) {
        foreach ($versioning->versions->version as $version) {
            $versions[] = (string)$version;
        }
        // 版本排序（最新的在前）
        usort($versions, 'version_compare');
        $versions = array_reverse($versions);
    }

    // 制品显示名称
    $displayName = $groupId . ':' . $artifactId;
    $shortName = $artifactId;

    return [
        'id' => md5($groupId . ':' . $artifactId),
        'name' => $displayName,
        'shortName' => $shortName,
        'groupId' => $groupId,
        'artifactId' => $artifactId,
        'latestVersion' => $latestVersion,
        'versions' => $versions,
        'repoName' => $repoName,
        'filePath' => $filePath
    ];
}

// 生成依赖代码
function generateDependencyCode($groupId, $artifactId, $version, $type = 'maven')
{
    if ($type === 'maven') {
        return <<<XML
<dependency>
    <groupId>$groupId</groupId>
    <artifactId>$artifactId</artifactId>
    <version>$version</version>
</dependency>
XML;
    } else if ($type === 'gradle') {
        return "implementation '$groupId:$artifactId:$version'";
    }

    return '';
}

// 获取所有制品
$artifacts = getAllArtifacts();

// 处理制品选择
$selectedArtifactId = $_GET['artifact'] ?? '';
$selectedArtifact = null;
if ($selectedArtifactId) {
    foreach ($artifacts as $artifact) {
        if ($artifact['id'] === $selectedArtifactId) {
            $selectedArtifact = $artifact;
            break;
        }
    }
}

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        min-height: 100vh;
        padding: 20px;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    h1 {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 2.5rem;
    }

    .subtitle {
        color: #7f8c8d;
        font-size: 1.1rem;
    }

    .artifacts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .artifact-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        border-left: 5px solid #3498db;
    }

    .artifact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }

    .artifact-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .artifact-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        word-break: break-all;
    }

    .artifact-short-name {
        font-size: 1rem;
        color: #3498db;
        font-weight: 500;
    }

    .artifact-details {
        padding: 15px 20px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .detail-label {
        font-weight: 600;
        color: #7f8c8d;
    }

    .detail-value {
        color: #2c3e50;
        font-weight: 500;
    }

    .artifact-actions {
        padding: 15px 20px;
        background: #f8f9fa;
        text-align: right;
    }

    .view-btn {
        background: linear-gradient(to right, #3498db, #2980b9);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
    }

    .view-btn:hover {
        background: linear-gradient(to right, #2980b9, #1f639b);
        transform: scale(1.05);
    }

    /* 抽屉样式 */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .drawer-overlay.active {
        display: block;
    }

    .drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 85%;
        max-width: 800px;
        height: 100%;
        background: white;
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        overflow-y: auto;
        box-shadow: -5px 0 25px rgba(0, 0, 0, 0.15);
    }

    .drawer.active {
        transform: translateX(0);
    }

    .drawer-header {
        padding: 25px 30px;
        background: linear-gradient(135deg, #2c3e50, #4a6491);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .drawer-title {
        font-size: 1.8rem;
        font-weight: 600;
    }

    .close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }

    .drawer-content {
        padding: 30px;
    }

    .section-title {
        font-size: 1.4rem;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .versions-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 30px;
    }

    .version-item {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .version-item:hover {
        background: #e9ecef;
        transform: translateY(-3px);
    }

    .version-item.active {
        background: #3498db;
        color: white;
        border-color: #2980b9;
    }

    .version-item.latest {
        border-color: #2ecc71;
    }

    .code-block {
        background: #2c3e50;
        color: #ecf0f1;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 25px;
    }

    .code-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        background: #34495e;
    }

    .code-title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .copy-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 6px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .copy-btn:hover {
        background: #2980b9;
    }

    .copy-btn.copied {
        background: #2ecc71;
    }

    .code-content {
        padding: 20px;
        font-family: 'Consolas', 'Monaco', monospace;
        white-space: pre-wrap;
        line-height: 1.5;
        overflow-x: auto;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #7f8c8d;
    }

    .empty-icon {
        font-size: 4rem;
        color: #bdc3c7;
        margin-bottom: 20px;
    }

    .empty-text {
        font-size: 1.2rem;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .drawer {
            width: 95%;
        }

        .artifacts-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        h1 {
            font-size: 2rem;
        }
    }

    .repo-badge {
        display: inline-block;
        background: #e74c3c;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 5px;
    }
</style>
</head>

<body>
    <div class="container">
        <!-- <header>
            <h1><i class="fas fa-box-open"></i> Maven制品浏览器</h1>
            <p class="subtitle">浏览并管理您的Maven制品库，轻松获取依赖代码</p>
            <p class="subtitle">共发现 <strong><?php echo count($artifacts); ?></strong> 个制品</p>
        </header> -->

        <?php if (count($artifacts) > 0): ?>
            <div class="artifacts-grid">
                <?php foreach ($artifacts as $artifact): ?>
                    <div class="artifact-card">
                        <div class="artifact-header">
                            <div class="artifact-name"><?php echo htmlspecialchars($artifact['name']); ?></div>
                            <div class="artifact-short-name"><?php echo htmlspecialchars($artifact['shortName']); ?></div>
                            <div class="repo-badge"><?php echo htmlspecialchars($artifact['repoName']); ?></div>
                        </div>
                        <div class="artifact-details">
                            <div class="detail-row">
                                <span class="detail-label">Group ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($artifact['groupId']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Artifact ID:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($artifact['artifactId']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">最新版本:</span>
                                <span class="detail-value" style="color: #27ae60; font-weight: bold;"><?php echo htmlspecialchars($artifact['latestVersion']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">版本数量:</span>
                                <span class="detail-value"><?php echo count($artifact['versions']); ?></span>
                            </div>
                        </div>
                        <div class="artifact-actions">
                            <button class="view-btn" onclick="openDrawer('<?php echo $artifact['id']; ?>')">
                                <i class="fas fa-code"></i> 查看依赖
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="empty-text">
                    <h3>未找到制品</h3>
                    <p>在 <?php echo REPOPATH; ?> 中未找到任何maven-metadata.xml文件</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 抽屉 -->
    <div class="drawer-overlay <?php echo $selectedArtifact ? 'active' : ''; ?>" id="drawerOverlay" onclick="closeDrawer()"></div>
    <div class="drawer <?php echo $selectedArtifact ? 'active' : ''; ?>" id="drawer">
        <?php if ($selectedArtifact): ?>
            <div class="drawer-header">
                <div class="drawer-title">
                    <i class="fas fa-cube"></i> <?php echo htmlspecialchars($selectedArtifact['name']); ?>
                </div>
                <button class="close-btn" onclick="closeDrawer()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="drawer-content">
                <div class="section-title">
                    <i class="fas fa-list-ol"></i> 可用版本
                </div>
                <div class="versions-list" id="versionsList">
                    <?php foreach ($selectedArtifact['versions'] as $version): ?>
                        <div class="version-item <?php echo $version === $selectedArtifact['latestVersion'] ? 'latest' : ''; ?>"
                            onclick="selectVersion('<?php echo $version; ?>')"
                            data-version="<?php echo $version; ?>">
                            <?php echo htmlspecialchars($version); ?>
                            <?php if ($version === $selectedArtifact['latestVersion']): ?>
                                <div style="font-size: 0.7rem; margin-top: 5px;">最新</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="section-title">
                    <i class="fas fa-code"></i> 依赖代码
                </div>

                <div class="code-block">
                    <div class="code-header">
                        <div class="code-title">
                            <i class="fab fa-java"></i> Maven
                        </div>
                        <button class="copy-btn" onclick="copyCode('mavenCode')">
                            <i class="far fa-copy"></i> 复制
                        </button>
                    </div>
                    <div class="code-content" id="mavenCode">
                        <?php echo htmlspecialchars(generateDependencyCode(
                            $selectedArtifact['groupId'],
                            $selectedArtifact['artifactId'],
                            $selectedArtifact['latestVersion'],
                            'maven'
                        )); ?>
                    </div>
                </div>

                <div class="code-block">
                    <div class="code-header">
                        <div class="code-title">
                            <i class="fab fa-android"></i> Gradle (Groovy DSL)
                        </div>
                        <button class="copy-btn" onclick="copyCode('gradleCode')">
                            <i class="far fa-copy"></i> 复制
                        </button>
                    </div>
                    <div class="code-content" id="gradleCode">
                        <?php echo htmlspecialchars(generateDependencyCode(
                            $selectedArtifact['groupId'],
                            $selectedArtifact['artifactId'],
                            $selectedArtifact['latestVersion'],
                            'gradle'
                        )); ?>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-info-circle"></i> 制品信息
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <div class="detail-row">
                        <span class="detail-label">存储库:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedArtifact['repoName']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Group ID:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedArtifact['groupId']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Artifact ID:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedArtifact['artifactId']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">元数据文件:</span>
                        <span class="detail-value"><?php echo htmlspecialchars(str_replace(REPOPATH, '', $selectedArtifact['filePath'])); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // 打开抽屉
        function openDrawer(artifactId) {
            // 通过URL参数传递选择的制品ID，刷新页面打开抽屉
            window.location.href = window.location.pathname + '?artifact=' + artifactId;
        }

        // 关闭抽屉
        function closeDrawer() {
            // 移除URL参数，刷新页面关闭抽屉
            window.location.href = window.location.pathname;
        }

        // 选择版本
        function selectVersion(version) {
            if (!version) return;

            // 更新代码块中的版本
            const mavenCode = document.getElementById('mavenCode');
            const gradleCode = document.getElementById('gradleCode');
            const artifactId = '<?php echo $selectedArtifact ? $selectedArtifact['id'] : ''; ?>';
            const groupId = '<?php echo $selectedArtifact ? $selectedArtifact['groupId'] : ''; ?>';
            const artifact = '<?php echo $selectedArtifact ? $selectedArtifact['artifactId'] : ''; ?>';

            // 更新Maven代码
            mavenCode.textContent = `<dependency>
    <groupId>${groupId}</groupId>
    <artifactId>${artifact}</artifactId>
    <version>${version}</version>
</dependency>`;

            // 更新Gradle代码
            gradleCode.textContent = `implementation '${groupId}:${artifact}:${version}'`;

            // 更新版本选择状态
            document.querySelectorAll('.version-item').forEach(item => {
                item.classList.remove('active');
                if (item.dataset.version === version) {
                    item.classList.add('active');
                }
            });
        }

        // 复制代码
        function copyCode(elementId) {
            const codeElement = document.getElementById(elementId);
            const text = codeElement.textContent || codeElement.innerText;

            navigator.clipboard.writeText(text).then(() => {
                const copyBtn = codeElement.parentElement.querySelector('.copy-btn');
                const originalText = copyBtn.innerHTML;

                copyBtn.innerHTML = '<i class="fas fa-check"></i> 已复制';
                copyBtn.classList.add('copied');

                setTimeout(() => {
                    copyBtn.innerHTML = originalText;
                    copyBtn.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('复制失败: ', err);
                alert('复制失败，请手动选择并复制文本');
            });
        }

        // 初始选择最新版本
        document.addEventListener('DOMContentLoaded', function() {
            const latestVersion = '<?php echo $selectedArtifact ? $selectedArtifact['latestVersion'] : ''; ?>';
            if (latestVersion) {
                selectVersion(latestVersion);
            }
        });

        // 按ESC键关闭抽屉
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDrawer();
            }
        });
    </script>