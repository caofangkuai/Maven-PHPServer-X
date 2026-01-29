<div style="position: relative; width: 100%; height: 50%;">
    <!-- 小箭头图标按钮 -->
    <button id="open-new-window"
            title="在新窗口打开"
            style="
                position: absolute; top: 8px; right: 8px; z-index: 10;
                width: 36px; height: 36px; border: none; border-radius: 50%;
                background-color: #007bff; cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                padding: 0;
                transition: background-color 0.3s, transform 0.3s;
            ">
        <!-- SVG 箭头 -->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 16 16" fill="white"
             style="transition: transform 0.3s;">
            <path fill-rule="evenodd" d="M6.5 3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 .5.5v5a.5.5 0 0 1-1 0V4.707L3.854 11.354a.5.5 0 0 1-.708-.708L10.293 4H6.5z"/>
        </svg>
    </button>

    <iframe id="my-iframe"
            src="<?php echo URL::base(TRUE, TRUE) . 'browse/'; ?>"
            style="border:none;width:100%;height:100%;">
    </iframe>
</div>

<script>
    const iframe = document.getElementById('my-iframe');
    const btn = document.getElementById('open-new-window');

    btn.addEventListener('click', () => {
        try {
            // 同源情况下获取当前 iframe URL
            const currentUrl = iframe.contentWindow.location.href;
            window.open(currentUrl, '_blank');
        } catch (e) {
            // 跨域回退到初始 src
            window.open(iframe.src, '_blank');
        }
    });

    // 悬停动画
    btn.addEventListener('mouseenter', () => {
        btn.style.backgroundColor = '#0056b3';       // 背景变深
        btn.querySelector('svg').style.transform = 'scale(1.2) rotate(10deg)'; // 箭头放大+微旋转
    });
    btn.addEventListener('mouseleave', () => {
        btn.style.backgroundColor = '#007bff';       // 背景恢复
        btn.querySelector('svg').style.transform = 'scale(1) rotate(0deg)';   // 箭头恢复
    });
</script>
