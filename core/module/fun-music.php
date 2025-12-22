<?php
/**
 * @link gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器功能模块
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 🎵 音乐播放器功能模块 - 由初叶🍂提供，白木🥰集成
function boxmoe_music_player_init() {
    // 获取主题设置
    $music_switch = get_boxmoe('boxmoe_music_player_switch', false);
    
    // 如果音乐播放器未启用，则返回
    if (!$music_switch) {
        return;
    }
    
    // 注册音乐播放器脚本（移除默认CSS，使用用户自定义CSS）
    wp_register_script('boxmoe-music-player-js', get_template_directory_uri() . '/assets/js/music-player/APlayer.min.js', array('jquery'), THEME_VERSION, true);
    wp_register_script('boxmoe-music-meting-js', get_template_directory_uri() . '/assets/js/music-player/Meting.min.js', array('boxmoe-music-player-js'), THEME_VERSION, true);
    wp_register_script('boxmoe-music-lyrics-fix', get_template_directory_uri() . '/assets/js/music-player/aplayer-lyrics-fix.js', array('boxmoe-music-player-js'), THEME_VERSION, true);
    
    // 仅加载脚本，不加载默认CSS
    wp_enqueue_script('boxmoe-music-player-js');
    wp_enqueue_script('boxmoe-music-meting-js');
    wp_enqueue_script('boxmoe-music-lyrics-fix');
}

// 加载音乐播放器资源
add_action('wp_enqueue_scripts', 'boxmoe_music_player_init');

// 🎵 输出音乐播放器HTML
function boxmoe_music_player_html() {
    // 获取主题设置
    $music_switch = get_boxmoe('boxmoe_music_player_switch', false);
    
    // 如果音乐播放器未启用，则返回
    if (!$music_switch) {
        return;
    }
    
    // 获取音乐播放器设置
    $server = get_boxmoe('boxmoe_music_player_server', 'netease');
    $type = get_boxmoe('boxmoe_music_player_type', 'playlist');
    $id = get_boxmoe('boxmoe_music_player_id', '6814606449'); // 默认网易云音乐歌单ID
    $autoplay = get_boxmoe('boxmoe_music_player_autoplay', false) ? 'true' : 'false';
    $loop = get_boxmoe('boxmoe_music_player_loop', true) ? 'all' : 'none';
    $volume = get_boxmoe('boxmoe_music_player_volume', '50');
    $position = get_boxmoe('boxmoe_music_player_position', 'bottom-right');
    $size = get_boxmoe('boxmoe_music_player_size', 'medium');
    $theme_color = get_boxmoe('boxmoe_music_player_theme_color', '#8b3dff');
    $custom_css = get_boxmoe('boxmoe_music_player_custom_css', '');
    
    
    // 获取API设置
    $custom_api = get_boxmoe('boxmoe_music_player_api', '');
    $default_api = get_boxmoe('boxmoe_music_player_default_api', 'default');
    
    // 确定使用的API地址
    $api_url = '';
    $use_custom_api = !empty($custom_api); // 标记是否使用自定义API
    
    if ($use_custom_api) {
        // 优先使用自定义API，忽略默认API设置
        $api_url = $custom_api;
    } else {
        // 只有在没有设置自定义API时，才使用预设API
        switch ($default_api) {
            case 'tencent_vip':
                $api_url = 'https://musicapi.chuyel.top/meting/api';
                break;
            default:
                // 默认API不设置，使用Meting.js内置的API
                $api_url = '';
                break;
        }
    }
    
    // 生成播放器大小样式
    $size_class = '';
    switch ($size) {
        case 'small':
            $size_class = 'music-player-small';
            break;
        case 'large':
            $size_class = 'music-player-large';
            break;
        default:
            $size_class = 'music-player-medium';
            break;
    }
    
    // 只加载脚本，不加载默认CSS，使用用户自定义CSS
    $html = '<script src="' . get_template_directory_uri() . '/assets/js/music-player/APlayer.min.js" type="text/javascript"></script>';
    $html .= '<script src="' . get_template_directory_uri() . '/assets/js/music-player/Meting.min.js" type="text/javascript"></script>';
    
    // 如果有自定义CSS，直接加载
    if (!empty($custom_css)) {
        $html .= '<style type="text/css">';
        $html .= '/* 🎵 用户自定义播放器样式 */\n';
        $html .= $custom_css;
        $html .= '</style>';
    } else {
        // 如果没有自定义CSS，加载默认样式作为 fallback
        $html .= '<link rel="stylesheet" href="' . get_template_directory_uri() . '/assets/css/music-player/APlayer.min.css" type="text/css">';
    }
    
    // 输出播放器HTML - 添加custom-music-player id以便增强CSS选择器
    $html .= '<div id="custom-music-player" class="music-player-wrapper ' . $position . ' ' . $size_class . '">';
    $html .= '<button class="music-player-toggle-btn" onclick="toggleMusicPlayer()" title="切换播放器显示/隐藏">';
    $html .= '<span class="close-btn">❌</span>';
    $html .= '</button>';
    $html .= '<div class="music-player-content" id="musicPlayerContent">';
    $html .= '<meting-js server="' . $server . '" type="' . $type . '" id="' . $id . '" ';
    $html .= 'autoplay="' . $autoplay . '" loop="' . $loop . '" volume="' . $volume . '" ';
    $html .= 'theme="' . $theme_color . '" listfolded="true" mutex="true" lrc="true" ';
    // 添加API属性，如果有自定义API地址
    if (!empty($api_url)) {
        $html .= 'api="' . $api_url . '" ';
    }
    $html .= '></meting-js>';
    $html .= '</div>';
    $html .= '</div>';
    
    // 输出切换播放器的JavaScript
    $html .= <<<EOT
<script type="text/javascript">
function toggleMusicPlayer() {
		var playerContent = document.getElementById("musicPlayerContent");
		var toggleBtn = document.querySelector(".music-player-toggle-btn");
		var aplayerPic = document.querySelector(".aplayer-pic");
		var coverUrl = "";
		if (aplayerPic) {
			coverUrl = aplayerPic.style.backgroundImage;
			// 修复：移除可能的多层url()包装和引号
			coverUrl = coverUrl.replace(/^url\(['"]?(.*?)['"]?\)$/, "$1");
			// 再次处理，防止多层嵌套
			coverUrl = coverUrl.replace(/^url\(['"]?(.*?)['"]?\)$/, "$1");
			// 移除HTML实体编码
			coverUrl = coverUrl.replace(/&quot;/g, '"');
			coverUrl = coverUrl.replace(/&amp;/g, '&');
		}
		
		// 修复点击两次才打开的问题：使用更可靠的状态检测
		// 检查元素是否实际可见
		var isVisible = playerContent.offsetWidth > 0 || playerContent.offsetHeight > 0;
		
		if (!isVisible || playerContent.style.display === "none" || playerContent.style.display === "") {
			// 打开播放器
			playerContent.style.display = "block";
			toggleBtn.innerHTML = "<span class=\"close-btn\">❌</span>";
			toggleBtn.classList.remove("open-btn");
		} else {
			// 关闭播放器
			playerContent.style.display = "none";
			toggleBtn.innerHTML = coverUrl ? "<div class=\"cover-btn\" style=\"background-image: url('" + coverUrl + "'); background-size: cover; background-position: center; background-repeat: no-repeat;\"><div class=\"play-btn-overlay\"><svg class=\"play-icon\" width=\"32\" height=\"32\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><circle cx=\"12\" cy=\"12\" r=\"10\" class=\"play-icon-circle\"/><path d=\"M9.5 7.5L16.5 12L9.5 16.5V7.5Z\" fill=\"white\"/></svg></div></div>" : "<span class=\"open-indicator\">🎵</span>";
			toggleBtn.classList.add("open-btn");
		}
	}
// 页面加载完成后，初始化播放器和按钮状态
document.addEventListener("DOMContentLoaded", function() {
    var playerContent = document.getElementById("musicPlayerContent");
    var toggleBtn = document.querySelector(".music-player-toggle-btn");
    
    // 🚀 预加载音乐资源 - 避免点击打开时等待
    // 1. 首先，让播放器在不可见状态下初始化，预加载资源
    playerContent.style.visibility = "hidden";
    playerContent.style.position = "absolute";
    playerContent.style.display = "block";
    
    // 2. 设置按钮初始状态为打开状态（显示🎵）
    toggleBtn.innerHTML = "<span class=\"open-indicator\">🎵</span>";
    toggleBtn.classList.add("open-btn");
    
    // 3. 等待Meting.js和APlayer完全初始化并加载资源
    setTimeout(function() {
        // 🔊 修复音量滑块点击静音问题 - 终极修复方案
        document.querySelectorAll('.aplayer').forEach(function(aplayerElement) {
            // 获取音量控制相关元素
            const volumeWrap = aplayerElement.querySelector('.aplayer-volume-wrap');
            const volumeIcon = aplayerElement.querySelector('.aplayer-volume-wrap .aplayer-icon');
            const volumeBarWrap = aplayerElement.querySelector('.aplayer-volume-bar-wrap');
            const volumeBar = aplayerElement.querySelector('.aplayer-volume-bar');
            
            if (!volumeWrap || !volumeIcon || !volumeBarWrap || !volumeBar) {
                return;
            }
            
            // 🔧 移除所有现有的事件监听器
            const newVolumeWrap = volumeWrap.cloneNode(true);
            const newVolumeIcon = newVolumeWrap.querySelector('.aplayer-icon');
            const newVolumeBarWrap = newVolumeWrap.querySelector('.aplayer-volume-bar-wrap');
            const newVolumeBar = newVolumeWrap.querySelector('.aplayer-volume-bar');
            
            // 替换原始元素
            volumeWrap.parentNode.replaceChild(newVolumeWrap, volumeWrap);
            
            // 🔧 重新绑定事件
            // 1. 静音按钮事件
            newVolumeIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                if (aplayerElement.aplayer) {
                    aplayerElement.aplayer.toggleMute();
                }
            });
            
            // 2. 音量条包装器事件
            newVolumeBarWrap.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                
                if (!aplayerElement.aplayer) {
                    return;
                }
                
                // 计算点击位置和音量值
                const rect = newVolumeBar.getBoundingClientRect();
                const y = e.clientY - rect.top;
                const height = rect.height;
                const volume = Math.max(0, Math.min(1, 1 - (y / height)));
                
                // 设置音量
                if (aplayerElement.aplayer.muted) {
                    aplayerElement.aplayer.toggleMute();
                }
                aplayerElement.aplayer.volume(volume, true);
            });
            
            // 3. 音量条事件
            newVolumeBar.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
            });
        });
        
        // 4. 资源预加载完成后，恢复播放器的隐藏状态
        setTimeout(function() {
            playerContent.style.display = "none";
            playerContent.style.visibility = "visible";
            playerContent.style.position = "static";
        }, 800); // 减少等待时间，优化性能
    }, 2000); // 更长延迟，确保Meting.js完全初始化APlayer
});
</script>
EOT;
    
    // 输出初始隐藏播放器的CSS
    $html .= '<style type="text/css">';
    /* 定义呼吸灯和放大缩小动画 */
    $html .= '@keyframes breathe {';
    $html .= '    0%, 100% {';
    $html .= '        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25), 0 0 0 0 rgba(139, 61, 255, 0.3);';
    $html .= '    }';
    $html .= '    50% {';
    $html .= '        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), 0 0 20px 0 rgba(139, 61, 255, 0.6);';
    $html .= '    }';
    $html .= '};';
    $html .= '@keyframes pulse {';
    $html .= '    0%, 100% {';
    $html .= '        transform: scale(1);';
    $html .= '    }';
    $html .= '    50% {';
    $html .= '        transform: scale(1.05);';
    $html .= '    }';
    $html .= '}';
    $html .= '.music-player-toggle-btn {';
    $html .= '    position: fixed;';
    $html .= '    width: 60px;';
    $html .= '    height: 60px;';
    $html .= '    border: 2px solid rgba(255, 255, 255, 0.9);';
    $html .= '    background: #8b3dff;';
    $html .= '    backdrop-filter: blur(10px);';
    $html .= '    -webkit-backdrop-filter: blur(10px);';
    $html .= '    border-radius: 16px;';
    $html .= '    cursor: pointer;';
    $html .= '    opacity: 0.95;';
    $html .= '    transition: all 0.3s ease;';
    $html .= '    z-index: 100001;';
    $html .= '    display: flex;';
    $html .= '    align-items: center;';
    $html .= '    justify-content: center;';
    $html .= '    overflow: hidden;';
    $html .= '    padding: 0;';
    $html .= '    margin: 0;';
    $html .= '    visibility: visible !important;';
    $html .= '    animation: breathe 3s ease-in-out infinite, pulse 2s ease-in-out infinite;';
    $html .= '}';
    $html .= '.music-player-toggle-btn:hover {';
    $html .= '    opacity: 1;';
    $html .= '    transform: scale(1.1);';
    $html .= '    box-shadow: var(--aplayer-shadow-hover), 0 0 30px 0 rgba(139, 61, 255, 0.8);';
    $html .= '    animation-play-state: paused;';
    $html .= '}';
    $html .= '.music-player-toggle-btn.open-btn {';
    $html .= '    border-radius: 16px !important;';
    $html .= '    width: 60px !important;';
    $html .= '    height: 60px !important;';
    $html .= '    visibility: visible !important;';
    $html .= '    opacity: 0.95 !important;';
    $html .= '    animation: breathe 3s ease-in-out infinite, pulse 2s ease-in-out infinite !important;';
    $html .= '}';
    $html .= '.music-player-toggle-btn.open-btn:hover {';
    $html .= '    opacity: 1 !important;';
    $html .= '    transform: scale(1.1) !important;';
    $html .= '    box-shadow: var(--aplayer-shadow-hover), 0 0 30px 0 rgba(139, 61, 255, 0.8) !important;';
    $html .= '    animation-play-state: paused !important;';
    $html .= '}';
    $html .= '.music-player-toggle-btn .close-btn {';
    $html .= '    font-size: 24px;';
    $html .= '    font-weight: bold;';
    $html .= '    color: var(--aplayer-text-primary);';
    $html .= '    line-height: 1;';
    $html .= '}';
    $html .= '.music-player-toggle-btn .open-indicator {';
    $html .= '    font-size: 20px;';
    $html .= '    color: var(--aplayer-text-primary);';
    $html .= '    line-height: 1;';
    $html .= '}';
    $html .= '.music-player-toggle-btn .cover-btn {';
    $html .= '    width: 100%;';
    $html .= '    height: 100%;';
    $html .= '    border-radius: 14px;';
    $html .= '    background-size: cover;';
    $html .= '    background-position: center;';
    $html .= '    background-repeat: no-repeat;';
    $html .= '    border: none;';
    $html .= '    box-shadow: none;';
    $html .= '    margin: 0;';
    $html .= '    padding: 0;';
    $html .= '    display: flex;';
    $html .= '    align-items: center;';
    $html .= '    justify-content: center;';
    $html .= '    position: relative;';
    $html .= '}';
    $html .= '.play-btn-overlay {';
    $html .= '    position: absolute;';
    $html .= '    top: 0;';
    $html .= '    left: 0;';
    $html .= '    width: 100%;';
    $html .= '    height: 100%;';
    $html .= '    display: flex;';
    $html .= '    align-items: center;';
    $html .= '    justify-content: center;';
    $html .= '    background: rgba(0, 0, 0, 0.3);';
    $html .= '    border-radius: 14px;';
    $html .= '    opacity: 0.9;';
    $html .= '    transition: opacity 0.3s ease;';
    $html .= '}';
    $html .= '.play-icon {';
    $html .= '    width: 32px;';
    $html .= '    height: 32px;';
    $html .= '    margin-left: 2px;';
    $html .= '}';
    $html .= '.play-icon-circle {';
    $html .= '    stroke: url(#playGradient);';
    $html .= '    stroke-width: 2;';
    $html .= '    fill: url(#playGradient);';
    $html .= '    animation: playBtnGradient 3s ease-in-out infinite;';
    $html .= '}';
    $html .= '@keyframes playBtnGradient {';
    $html .= '    0% {';
    $html .= '        filter: hue-rotate(0deg);';
    $html .= '    }';
    $html .= '    50% {';
    $html .= '        filter: hue-rotate(180deg);';
    $html .= '    }';
    $html .= '    100% {';
    $html .= '        filter: hue-rotate(360deg);';
    $html .= '    }';
    $html .= '}';
    $html .= '.music-player-toggle-btn:has(.cover-btn),';
    $html .= '.music-player-toggle-btn.open-btn:has(.cover-btn) {';
    $html .= '    background: transparent !important;';
    $html .= '    border-color: rgba(255, 255, 255, 0.9) !important;';
    $html .= '}';
    $html .= '.music-player-content {';
    $html .= '    display: none;';
    $html .= '    transition: var(--aplayer-transition);';
    $html .= '    margin-top: 10px;';
    $html .= '}';
    $html .= '.music-player-wrapper {';
    $html .= '    display: flex;';
    $html .= '    flex-direction: column;';
    $html .= '    align-items: flex-end;';
    $html .= '    min-height: 60px;';
    $html .= '}';
    $html .= '</style>';
    // 添加渐变定义
    $html .= '<svg width="0" height="0" style="position: absolute;">';
    $html .= '<defs>';
    $html .= '<linearGradient id="playGradient" x1="0%" y1="0%" x2="100%" y2="100%">';
    $html .= '<stop offset="0%" stop-color="#8b3dff"/>';
    $html .= '<stop offset="50%" stop-color="#ff6b6b"/>';
    $html .= '<stop offset="100%" stop-color="#45b7d1"/>';
    $html .= '</linearGradient>';
    $html .= '</defs>';
    $html .= '</svg>';
    
    // 确保切换按钮始终可见，不受其他样式影响
    $html .= '<style type="text/css">';
    $html .= '.music-player-toggle-btn {';
    $html .= '    visibility: visible !important;';
    $html .= '    opacity: 1 !important;';
    $html .= '    display: flex !important;';
    $html .= '    z-index: 100001 !important;';
    $html .= '    background: linear-gradient(135deg, #8b3dff 0%, #ff6b6b 100%) !important;';
    $html .= '    border: 3px solid rgba(255, 255, 255, 0.9) !important;';
    $html .= '    box-shadow: 0 8px 24px rgba(139, 61, 255, 0.4) !important;';
    $html .= '}';
    $html .= '.music-player-toggle-btn:hover {';
    $html .= '    transform: scale(1.15) !important;';
    $html .= '    box-shadow: 0 12px 32px rgba(139, 61, 255, 0.6) !important;';
    $html .= '}';
    $html .= '</style>';
    

    
    // 输出播放器位置和大小样式
    $html .= '<style type="text/css">';
    $html .= '.music-player-wrapper {';
    $html .= '    position: fixed;';
    $html .= '    z-index: 99999;';
    $html .= '    margin: 10px;';
    $html .= '}';
    $html .= '.music-player-wrapper.top-left {';
    $html .= '    top: 0;';
    $html .= '    left: 0;';
    $html .= '}';
    $html .= '.music-player-wrapper.top-right {';
    $html .= '    top: 0;';
    $html .= '    right: 0;';
    $html .= '}';
    $html .= '.music-player-wrapper.bottom-left {';
    $html .= '    bottom: 0;';
    $html .= '    left: 0;';
    $html .= '}';
    $html .= '.music-player-wrapper.bottom-right {';
    $html .= '    bottom: 0;';
    $html .= '    right: 0;';
    $html .= '}';
    // 切换按钮位置与播放器同步
    $html .= '.music-player-wrapper.top-left .music-player-toggle-btn {';
    $html .= '    top: 10px;';
    $html .= '    left: 10px;';
    $html .= '}';
    $html .= '.music-player-wrapper.top-right .music-player-toggle-btn {';
    $html .= '    top: 10px;';
    $html .= '    right: 10px;';
    $html .= '}';
    $html .= '.music-player-wrapper.bottom-left .music-player-toggle-btn {';
    $html .= '    bottom: 10px;';
    $html .= '    left: 10px;';
    $html .= '}';
    $html .= '.music-player-wrapper.bottom-right .music-player-toggle-btn {';
    $html .= '    bottom: 10px;';
    $html .= '    right: 10px;';
    $html .= '}';
    $html .= '.music-player-small .aplayer {';
    $html .= '    width: 320px;';
    $html .= '    min-width: 320px;';
    $html .= '    max-height: none;';
    $html .= '}';
    $html .= '.music-player-medium .aplayer {';
    $html .= '    width: 360px;';
    $html .= '    min-width: 360px;';
    $html .= '    max-height: none;';
    $html .= '}';
    $html .= '.music-player-large .aplayer {';
    $html .= '    width: 420px;';
    $html .= '    min-width: 420px;';
    $html .= '    max-height: none;';
    $html .= '}';
    $html .= '.aplayer {';
    $html .= '    overflow: hidden;';
    $html .= '    display: flex;';
    $html .= '    flex-direction: column;';
    $html .= '    min-width: 320px;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    max-height: none !important;';
    $html .= '    height: auto !important;';
    $html .= '    min-height: auto !important;';
    $html .= '}';
    $html .= '.aplayer-withlrc .aplayer-body, ';
    $html .= '.aplayer-withlist .aplayer-body, ';
    $html .= '.aplayer-withlrc.aplayer-withlist .aplayer-body {';
    $html .= '    min-height: auto !important;';
    $html .= '    height: auto !important;';
    $html .= '}';
    // 修复左下角播放器不显示问题：覆盖aplayer-fixed类的样式
    $html .= '.music-player-wrapper .aplayer.aplayer-fixed {';
    $html .= '    position: relative !important;';
    $html .= '    width: 100% !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    border-radius: var(--aplayer-radius) !important;';
    $html .= '    box-shadow: var(--aplayer-shadow) !important;';
    $html .= '    transform: none !important;';
    $html .= '}';
    // 确保播放器容器正确显示
    $html .= '.music-player-wrapper.bottom-left .aplayer {';
    $html .= '    display: block !important;';
    $html .= '    opacity: 1 !important;';
    $html .= '    visibility: visible !important;';
    $html .= '}';
    $html .= '.aplayer-controller {';
    $html .= '    display: flex !important;';
    $html .= '    flex-direction: row !important;';
    $html .= '    align-items: center !important;';
    $html .= '    gap: 4px !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    width: 100% !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    min-height: 36px !important;';
    $html .= '    height: 36px !important;';
    $html .= '    justify-content: space-between !important;';
    $html .= '}';
    $html .= '.aplayer-bar-wrap {';
    $html .= '    flex: 1 !important;';
    $html .= '    margin: 0 4px !important;';
    $html .= '    min-width: 0 !important;';
    $html .= '    padding: 4px 0 !important;';
    $html .= '    position: relative !important;';
    $html .= '    cursor: pointer !important;';
    $html .= '    height: 36px !important;';
    $html .= '    display: flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '}';
    $html .= '.aplayer-bar {';
    $html .= '    position: relative !important;';
    $html .= '    height: 8px !important;';
    $html .= '    width: 100% !important;';
    $html .= '    background: rgba(255, 255, 255, 0.8) !important;';
    $html .= '    border-radius: 4px !important;';
    $html .= '    overflow: hidden !important;';
    $html .= '    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.05) !important;';
    $html .= '    transition: var(--aplayer-transition) !important;';
    $html .= '}';
    $html .= '.aplayer-bar-wrap:hover .aplayer-bar {';
    $html .= '    transform: scaleY(1.15) !important;';
    $html .= '}';
    $html .= '.aplayer-loaded {';
    $html .= '    position: absolute !important;';
    $html .= '    left: 0 !important;';
    $html .= '    top: 0 !important;';
    $html .= '    bottom: 0 !important;';
    $html .= '    background: linear-gradient(90deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2)) !important;';
    $html .= '    height: 100% !important;';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '}';
    $html .= '.aplayer-played {';
    $html .= '    position: absolute !important;';
    $html .= '    left: 0 !important;';
    $html .= '    top: 0 !important;';
    $html .= '    bottom: 0 !important;';
    $html .= '    height: 100% !important;';
    $html .= '    background: var(--aplayer-primary-gradient) !important;';
    $html .= '    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    overflow: hidden !important;';
    $html .= '    border-radius: var(--aplayer-bar-radius) !important;';
    $html .= '    will-change: width !important;';
    $html .= '}';
    $html .= '.aplayer-played::after {';
    $html .= '    content: "" !important;';
    $html .= '    position: absolute !important;';
    $html .= '    top: 0 !important;';
    $html .= '    left: 0 !important;';
    $html .= '    right: 0 !important;';
    $html .= '    bottom: 0 !important;';
    $html .= '    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent) !important;';
    $html .= '    animation: aplayer-shine 2s infinite linear !important;';
    $html .= '    border-radius: var(--aplayer-bar-radius) !important;';
    $html .= '}';
    $html .= '.aplayer-thumb {';
    $html .= '    position: absolute !important;';
    $html .= '    top: 50% !important;';
    $html .= '    right: 0 !important;';
    $html .= '    transform: translateY(-50%) !important;';
    $html .= '    margin-right: -9px !important;';
    $html .= '    width: 18px !important;';
    $html .= '    height: 18px !important;';
    $html .= '    border-radius: 50% !important;';
    $html .= '    background: var(--aplayer-primary-gradient) !important;';
    $html .= '    cursor: pointer !important;';
    $html .= '    transition: var(--aplayer-transition) !important;';
    $html .= '    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.5) !important;';
    $html .= '    border: 3px solid rgba(255, 255, 255, 0.9) !important;';
    $html .= '    will-change: transform, box-shadow !important;';
    $html .= '}';
    $html .= '.aplayer-bar-wrap:hover .aplayer-thumb {';
    $html .= '    transform: translateY(-50%) scale(1.25) !important;';
    $html .= '    box-shadow: 0 6px 24px rgba(102, 126, 234, 0.7) !important;';
    $html .= '}';
    $html .= '@keyframes aplayer-shine {';
    $html .= '    0% { transform: translateX(-100%); }';
    $html .= '    100% { transform: translateX(100%); }';
    $html .= '}';
    $html .= '.aplayer-time {';
    $html .= '    display: block !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    gap: 6px !important;';
    $html .= '    margin: 0 auto !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    width: 100% !important;';
    $html .= '    text-align: center !important;';
    $html .= '}';
    $html .= '.aplayer-time-inner {';
    $html .= '    order: initial !important;';
    $html .= '    min-width: 70px !important;';
    $html .= '    text-align: right !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    display: inline-block !important;';
    $html .= '    width: auto !important;';
    $html .= '    font-size: 32px !important;';
    $html .= '    font-weight: 600 !important;';
    $html .= '    color: #333 !important;';
    $html .= '    line-height: 1 !important;';
    $html .= '    padding: 4px 0 !important;';
    $html .= '}';
    $html .= '.aplayer-icons {';
    $html .= '    display: flex !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    align-items: center !important;';
    $html .= '    gap: 4px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    width: auto !important;';
    $html .= '    flex-shrink: 0 !important;';
    $html .= '    height: 100% !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '}';
    $html .= '.aplayer-time .aplayer-icon {';
    $html .= '    width: 20px !important;';
    $html .= '    height: 20px !important;';
    $html .= '    display: inline-flex !important;';
    $html .= '    order: initial !important;';
    $html .= '    flex: none !important;';
    $html .= '    background-color: transparent !important;';
    $html .= '    border: none !important;';
    $html .= '    cursor: pointer !important;';
    $html .= '    margin: 0 3px !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    font-size: 14px !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '}';
    $html .= '.aplayer-time .aplayer-icon svg {';
    $html .= '    width: 20px !important;';
    $html .= '    height: 20px !important;';
    $html .= '    fill: currentColor !important;';
    $html .= '}';
    $html .= '.aplayer-time > span.aplayer-icon {';
    $html .= '    display: inline-flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    margin: 0 3px !important;';
    $html .= '    width: 20px !important;';
    $html .= '    height: 20px !important;';
    $html .= '    flex: none !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '}';
    $html .= '.aplayer-time > div.aplayer-volume-wrap {';
    $html .= '    display: inline-flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    margin: 0 3px !important;';
    $html .= '    /* 修复音量控制：移除固定宽高限制，允许音量滑块正常显示 */';
    $html .= '    width: auto !important;';
    $html .= '    height: 100% !important;';
    $html .= '    flex: none !important;';
    $html .= '    position: relative !important;';
    $html .= '}';
    $html .= '.aplayer-volume-wrap {';
    $html .= '    display: inline-flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    height: 100% !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '}';
    $html .= '.aplayer-lrc {';
    $html .= '    width: 100% !important;';
    $html .= '    overflow-y: auto !important;';
    $html .= '    overflow-x: hidden !important;';
    $html .= '    margin: 10px 0 !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    height: 140px !important;';
    $html .= '    max-height: 140px !important;';
    $html .= '    min-height: 140px !important;';
    $html .= '    background: transparent !important;';
    $html .= '    border: none !important;';
    $html .= '    box-shadow: none !important;';
    $html .= '    border-radius: 0 !important;';
    $html .= '    display: block !important;';
    $html .= '    position: relative !important;';
    $html .= '    text-align: center !important;';
    $html .= '}';
    $html .= '.aplayer-lrc-contents {';
    $html .= '    text-align: center !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    display: block !important;';
    $html .= '    overflow: visible !important;';
    $html .= '    position: relative !important;';
    $html .= '    top: 0 !important;';
    $html .= '    left: 0 !important;';
    $html .= '    right: 0 !important;';
    $html .= '    width: 100% !important;';
    $html .= '    height: auto !important;';
    $html .= '    margin: 0 auto !important;';
    $html .= '    transition: transform 0.1s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    will-change: transform !important;';
    $html .= '    transform-origin: top center !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p {';
    $html .= '    font-size: 11px !important;';
    $html .= '    line-height: 22px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 8px !important;';
    $html .= '    text-align: center !important;';
    $html .= '    width: 100% !important;';
    $html .= '    display: block !important;';
    $html .= '    color: rgba(0, 0, 0, 0.6) !important;';
    $html .= '    opacity: 1 !important;';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '    word-wrap: break-word !important;';
    $html .= '    word-break: break-word !important;';
    $html .= '    min-height: 22px !important;';
    $html .= '    height: auto !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-current {';
    $html .= '    font-size: 12px !important;';
    $html .= '    line-height: 24px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 8px !important;';
    $html .= '    font-weight: 600 !important;';
    $html .= '    color: #8b3dff !important;';
    $html .= '    opacity: 1 !important;';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '    background: var(--aplayer-primary-gradient);';
    $html .= '    -webkit-background-clip: text;';
    $html .= '    -webkit-text-fill-color: transparent;';
    $html .= '    background-clip: text;';
    $html .= '    min-height: 24px !important;';
    $html .= '    height: auto !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-current-prev {';
    $html .= '    opacity: 0.4 !important;';
    $html .= '    color: rgba(0, 0, 0, 0.4) !important;';
    $html .= '    line-height: 22px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 8px !important;';
    $html .= '    transform: translateY(-10px) scale(0.95) !important;';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '    min-height: 22px !important;';
    $html .= '    height: auto !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-current-next {';
    $html .= '    opacity: 0.4 !important;';
    $html .= '    color: rgba(0, 0, 0, 0.4) !important;';
    $html .= '    line-height: 22px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 8px !important;';
    $html .= '    transform: translateY(0) scale(0.95) !important;';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '    min-height: 22px !important;';
    $html .= '    height: auto !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.lyric-push-up {';
    $html .= '    animation: lyricPushUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;';
    $html .= '}';
    $html .= '@keyframes lyricPushUp {';
    $html .= '    0% {';
    $html .= '        transform: translateY(0) scale(1) !important;';
    $html .= '        opacity: 1 !important;';
    $html .= '    }';
    $html .= '    100% {';
    $html .= '        transform: translateY(-20px) scale(0.9) !important;';
    $html .= '        opacity: 0.2 !important;';
    $html .= '    }';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-line {';
    $html .= '    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '    transform-origin: center !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p + p {';
    $html .= '    margin-top: 4px !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-current + p.aplayer-lrc-current-next {';
    $html .= '    margin-top: 6px !important;';
    $html .= '}';
    $html .= '.aplayer-lrc p.aplayer-lrc-current-prev + p.aplayer-lrc-current {';
    $html .= '    margin-top: 6px !important;';
    $html .= '}';
    $html .= '.aplayer-lrc::-webkit-scrollbar {';
    $html .= '    width: 6px !important;';
    $html .= '    background: rgba(0, 0, 0, 0.05) !important;';
    $html .= '}';
    $html .= '.aplayer-lrc::-webkit-scrollbar-thumb {';
    $html .= '    background: var(--aplayer-primary-gradient) !important;';
    $html .= '    border-radius: 3px !important;';
    $html .= '    transition: all 0.3s ease !important;';
    $html .= '}';
    $html .= '.aplayer-lrc::-webkit-scrollbar-thumb:hover {';
    $html .= '    background: var(--aplayer-secondary-gradient) !important;';
    $html .= '    transform: scaleX(1.2) !important;';
    $html .= '}';
    $html .= '.aplayer-body {';
    $html .= '    padding: 4px 16px !important;';
    $html .= '    gap: 6px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '}';
    // 🔊 音量滑块优化 - 增大尺寸和可点击区域
    $html .= '.aplayer-volume-wrap {';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    margin-left: 8px !important;';
    $html .= '    display: inline-flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    position: relative !important;';
    $html .= '    z-index: 99999 !important;';
    $html .= '    cursor: default !important;';
    $html .= '}';
    $html .= '.aplayer-volume-wrap .aplayer-icon {';
    $html .= '    cursor: pointer !important;';
    $html .= '    z-index: 100001 !important;';
    $html .= '    position: relative !important;';
    $html .= '}';
    $html .= '.aplayer-volume-bar-wrap {';
    $html .= '    cursor: pointer !important;';
    $html .= '    pointer-events: auto !important;';
    $html .= '}';
    $html .= '.aplayer-volume-bar {';
    $html .= '    cursor: pointer !important;';
    $html .= '    pointer-events: auto !important;';
    $html .= '}';
    $html .= '.aplayer-volume-bar-wrap {';
    $html .= '    position: absolute !important;';
    $html .= '    width: 32px !important;';
    $html .= '    height: 80px !important;';
    $html .= '    right: -14px !important;';
    $html .= '    bottom: 32px !important;';
    $html .= '    border-radius: var(--aplayer-small-radius) !important;';
    $html .= '    padding: 8px !important;';
    $html .= '    display: flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    background: rgba(139, 61, 255, 0.1) !important;';
    $html .= '    z-index: 99999 !important;';
    $html .= '    box-shadow: var(--aplayer-shadow-hover) !important;';
    $html .= '    border: 1px solid rgba(139, 61, 255, 0.2) !important;';
    $html .= '    overflow: visible !important;';
    $html .= '}';
    $html .= '.aplayer-volume-wrap .aplayer-volume-bar-wrap {';
    $html .= '    display: block !important;';
    $html .= '    opacity: 0 !important;';
    $html .= '    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;';
    $html .= '}';
    $html .= '.aplayer-volume-bar {';
    $html .= '    width: 8px !important;';
    $html .= '    height: 60px !important;';
    $html .= '    right: 12px !important;';
    $html .= '    border-radius: 4px !important;';
    $html .= '    background: rgba(139, 61, 255, 0.2) !important;';
    $html .= '    box-shadow: var(--aplayer-inset-shadow) !important;';
    $html .= '    transition: var(--aplayer-transition) !important;';
    $html .= '}';
    $html .= '.aplayer-volume-bar:hover {';
    $html .= '    transform: scaleX(1.2) !important;';
    $html .= '    background: rgba(139, 61, 255, 0.3) !important;';
    $html .= '}';
    $html .= '.aplayer-volume {';
    $html .= '    width: 8px !important;';
    $html .= '    border-radius: 4px !important;';
    $html .= '    background: linear-gradient(to top, #8b3dff, #a855f7) !important;';
    $html .= '    transition: var(--aplayer-transition-fast) !important;';
    $html .= '    box-shadow: 0 2px 8px rgba(139, 61, 255, 0.4) !important;';
    $html .= '}';
    $html .= '.aplayer-volume-wrap:hover .aplayer-volume-bar-wrap {';
    $html .= '    display: flex !important;';
    $html .= '    opacity: 1 !important;';
    $html .= '    height: 80px !important;';
    $html .= '    z-index: 100000 !important;';
    $html .= '    position: absolute !important;';
    $html .= '    transform: none !important;';
    $html .= '    right: -14px !important;';
    $html .= '    bottom: 32px !important;';
    $html .= '}';
    // 🔊 修复音量滑块被遮挡问题 - 确保父容器不裁剪内容
    $html .= '.aplayer-time {';
    $html .= '    overflow: visible !important;';
    $html .= '    position: relative !important;';
    $html .= '    z-index: 100000 !important;';
    $html .= '}';
    $html .= '.aplayer-time > div {';
    $html .= '    overflow: visible !important;';
    $html .= '    position: relative !important;';
    $html .= '    z-index: 100000 !important;';
    $html .= '}';
    $html .= '.aplayer-controller {';
    $html .= '    overflow: visible !important;';
    $html .= '    position: relative !important;';
    $html .= '    z-index: 100000 !important;';
    $html .= '}';
    $html .= '.aplayer-body {';
    $html .= '    overflow: visible !important;';
    $html .= '    position: relative !important;';
    $html .= '    z-index: 100000 !important;';
    $html .= '}';
    $html .= '.aplayer {';
    $html .= '    overflow: visible !important;';
    $html .= '    z-index: 99999 !important;';
    $html .= '}';
    // 🔊 音量控制图标优化 - 统一尺寸，与其他图标对齐
    $html .= '.aplayer-volume-wrap .aplayer-icon {';
    $html .= '    width: 20px !important;';
    $html .= '    height: 20px !important;';
    $html .= '    margin: 0 !important;';
    $html .= '    padding: 0 !important;';
    $html .= '    font-size: 16px !important;';
    $html .= '    display: inline-flex !important;';
    $html .= '    align-items: center !important;';
    $html .= '    justify-content: center !important;';
    $html .= '    box-sizing: border-box !important;';
    $html .= '}';
    $html .= '.aplayer-volume-wrap .aplayer-icon svg {';
    $html .= '    width: 20px !important;';
    $html .= '    height: 20px !important;';
    $html .= '    fill: currentColor !important;';
    $html .= '}';
    $html .= '</style>';
    
    // 🎵 隐藏播放器的miniswitcher按钮 - 只有在没有自定义CSS时才应用
    if (empty($custom_css)) {
        $html .= '<style type="text/css">';
        $html .= '.aplayer-miniswitcher { display: none !important; }';
        $html .= '</style>';
    }
    
    echo $html;
}

// 在页面底部输出音乐播放器
add_action('wp_footer', 'boxmoe_music_player_html');