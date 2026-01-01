
<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}

// �️ 直接获取banner背景图
// 定义获取banner背景图的函数
function get_shiroki_banner_image() {
    $src = '';
    
    // 检查是否有自定义的boxmoe函数可用
    if (function_exists('get_boxmoe')) {
        // 优先使用主题的banner设置
        if (get_boxmoe('boxmoe_banner_api_switch')) {
            $src = get_boxmoe('boxmoe_banner_api_url');
        } elseif (get_boxmoe('boxmoe_banner_rand_switch')) {
            // 随机背景图
            $random_images = glob(get_template_directory() . '/assets/images/random/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            if (!empty($random_images)) {
                $random_key = array_rand($random_images);
                $relative_path = str_replace(get_template_directory(), '', $random_images[$random_key]);
                $src = get_template_directory_uri() . $relative_path;
            }
        } elseif (get_boxmoe('boxmoe_banner_url')) {
            $src = get_boxmoe('boxmoe_banner_url');
        }
    }
    
    // 如果没有设置或获取失败，使用默认banner图
    if (empty($src)) {
        $src = get_template_directory_uri() . '/assets/images/banner.jpg';
    }
    
    return $src;
}

// 获取banner背景图
$banner_image = get_shiroki_banner_image();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>您访问了一个错误的页面</title>
    <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/assets/images/favicon.ico" type="image/x-icon">
    <style>
        /* 重置样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* 页面主体 */
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            background-color: #e2effcff; /* ⬅️ 初始背景色，防止加载时有闪烁效果 */
        }
        
        /* 背景容器 - 实现了一些动画效果 */
        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0;
            transform: scale(1.1);
            transition: all 1.2s ease-in-out;
        }
        
        /* 背景图片 */
        .bg-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("<?php echo $banner_image; ?>");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        /* 拟态毛玻璃的高斯模糊遮盖层 */
        .bg-container::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            /* ⬇️ 毛玻璃效果 */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            /* ⬇️ 渐变遮罩，加强层次感 */
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.6) 100%);
            /* ⬇️ 噪点纹理，加强拟态效果 */
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
            z-index: 1;
        }
        
        /* 🌟 背景的粒子容器样式 */
        #particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* 确保粒子在背景和内容之间 */
            pointer-events: none; /* 不影响鼠标交互 */
        }
        
        /* 动画完成状态 */
        .bg-container.animated {
            opacity: 1;
            transform: scale(1);
        }
        
        /* 内容容器 */
        .container {
            text-align: center;
            max-width: 600px;
            width: 100%;
            /* ⬇️ 立即显示内容，不需要延迟动画 */
        }
        
        /* 404错误高斯模糊拟态圆角风卡片 */
        .error-404 {
            /* ⬇️ 高斯模糊背景 */
            background: 
                url("<?php echo get_template_directory_uri(); ?>/assets/images/error/stars.svg") no-repeat center center,
                rgba(0, 60, 255, 0.19); /* 卡片背景色 */
            background-size: cover;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            /* ⬇️ 圆角 */
            border-radius: 30px;
            /* ⬇️ 拟态阴影效果 */
            box-shadow: 
                8px 8px 25px rgba(0, 0, 0, 0.1),
                -8px -8px 25px rgba(0, 4, 255, 0.18),
                inset 2px 2px 5px rgba(255, 255, 255, 0.5),
                inset -2px -2px 5px rgba(0, 0, 0, 0.05);
            /* ⬇️ 加强的边框效果 */
            border: 1px solid rgba(255, 255, 255, 0.3);
            /* ⬇️ 加强内边距，提升视觉效果 */
            padding: 60px 40px;
            /* ⬇️ 过渡效果，加强交互的视觉体验 */
            transition: all 0.3s ease;
            /* ⬇️ 用于定位星星效果的定位 */
            position: relative;
            overflow: hidden;
        }
        
        /* 🌟 右上角的月球SVG */
        .top-right-globe {
            position: absolute;
            top: 5px; /* 上边距向下缩进5px */
            right: 5px; /* 右缩进5px */
            z-index: 10;
            opacity: 0.8;
        }
        
        .top-right-globe img,
        .top-right-globe svg {
            width: 60px;
            height: 60px;
            transition: all 0.3s ease;
            animation: globeRotate 20s linear infinite; /* 循环旋转动画 */
        }
        
        .error-404:hover .top-right-globe img,
        .error-404:hover .top-right-globe svg {
            transform: scale(1.1);
            opacity: 1;
        }
        
        /* 🌟 右上角的月球SVG - 旋转动画 */
        @keyframes globeRotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* 卡片悬停效果 */
        .error-404:hover {
            box-shadow: 
                12px 12px 30px rgba(0, 0, 0, 0.15),
                -12px -12px 30px rgba(255, 255, 255, 0.22),
                inset 3px 3px 6px rgba(255, 255, 255, 0.29),
                inset -3px -3px 6px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .error-404 h1 {
            font-size: 120px;
            font-weight: bold;
            color: #ff9d9dff;
            margin-bottom: 20px;
        }
        
        .error-404 h2 {
            font-size: 24px;
            color: #0026ffff;
            margin-bottom: 15px;
        }
        
        .error-404 p {
            font-size: 16px;
            color: #4b4b4bff;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            /* ⬇️ 加强按钮拟态效果 */
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        /* ✨ 按钮扫光效果 */
        .btn-primary::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-20deg);
            opacity: 0;
            transition: left 0s ease, opacity 0s ease;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }
        
        /* ✨ 悬停时显示扫光效果 */
        .btn-primary:hover::after {
            left: 100%;
            opacity: 1;
            transition: left 0.6s ease, opacity 0s ease 0s;
        }
        
        /* 🟣 返回上一页按钮样式 */
        .btn-secondary {
            display: inline-block;
            padding: 12px 30px;
            background-color: #a78bfa; /* 淡紫色背景 */
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(167, 139, 250, 0.2);
            position: relative;
            overflow: hidden;
            margin-left: 10px; /* 与返回首页按钮保持间距 */
        }
        
        /* 🟣 返回上一页按钮扫光效果 */
        .btn-secondary::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            border-radius: 25px;
            z-index: -1;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-20deg);
            opacity: 0;
            transition: left 0s ease, opacity 0s ease;
        }
        
        .btn-secondary:hover {
            background-color: #8b5cf6; /* 深紫色悬停效果 */
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(167, 139, 250, 0.3);
        }
        
        /* 🟣 返回上一页按钮悬停扫光效果 */
        .btn-secondary:hover::after {
            left: 100%;
            opacity: 1;
            transition: left 0.6s ease, opacity 0s ease 0s;
        }
        
        /* 🌟 宇宙星球SVG容器 */
        .planet-container {
            position: relative;
            width: auto;
            height: auto;
            margin: 20px 0 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 🌟 固定的宇宙星球向下弧形 */
        .planet-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 120px;
            height: 120px;
            /* 保持固定，不随星球旋转 */
        }
        
        /* 🌟 会动的宇宙星球SVG */
        .planet-svg {
            width: 120px;
            height: 120px;
            animation: rotate 20s linear infinite;
        }
        
        /* 🌟 宇宙星球的图片容器 */
        .planet-image-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        
        /* 🌟 宇宙星球的图片样式 - 加强样式 */
        .planet-character {
            max-width: 80px; /* 图片宽度 */
            max-height: 80px; /* 图片高度 */
            animation: floatUpDown 3s ease-in-out infinite;
        }
        
        /* 🌟 宇宙星球的图片 - 添加上下浮动动画 */
        @keyframes floatUpDown {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        /* 🌟 宇宙星球的图片 - 旋转动画设定 */
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        /* 🌟 星星效果d的容器 */
        .stars-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }
        
        /* 🌟 星星的样式 */
        .star {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            animation: twinkle 3s ease-in-out infinite;
        }
        
        /* 🌟 星星的闪烁动画 */
        @keyframes twinkle {
            0%, 100% {
                opacity: 0;
                transform: scale(0.5);
            }
            50% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* 🌟 为星星设置不同的动画延迟、大小和位置 */
        .star:nth-child(1) { width: 2px; height: 2px; top: 10%; left: 15%; animation-delay: 0s; }
        .star:nth-child(2) { width: 3px; height: 3px; top: 60%; left: 85%; animation-delay: 0.2s; }
        .star:nth-child(3) { width: 1px; height: 1px; top: 30%; left: 65%; animation-delay: 0.4s; }
        .star:nth-child(4) { width: 2px; height: 2px; top: 85%; left: 35%; animation-delay: 0.6s; }
        .star:nth-child(5) { width: 3px; height: 3px; top: 25%; left: 75%; animation-delay: 0.8s; }
        .star:nth-child(6) { width: 2px; height: 2px; top: 75%; left: 20%; animation-delay: 1s; }
        .star:nth-child(7) { width: 1px; height: 1px; top: 45%; left: 10%; animation-delay: 1.2s; }
        .star:nth-child(8) { width: 3px; height: 3px; top: 20%; left: 90%; animation-delay: 1.4s; }
        .star:nth-child(9) { width: 2px; height: 2px; top: 80%; left: 60%; animation-delay: 1.6s; }
        .star:nth-child(10) { width: 2px; height: 2px; top: 40%; left: 30%; animation-delay: 1.8s; }
        .star:nth-child(11) { width: 1px; height: 1px; top: 15%; left: 45%; animation-delay: 2s; }
        .star:nth-child(12) { width: 3px; height: 3px; top: 70%; left: 55%; animation-delay: 2.2s; }
        .star:nth-child(13) { width: 2px; height: 2px; top: 35%; left: 80%; animation-delay: 2.4s; }
        .star:nth-child(14) { width: 2px; height: 2px; top: 55%; left: 25%; animation-delay: 2.6s; }
        .star:nth-child(15) { width: 1px; height: 1px; top: 50%; left: 50%; animation-delay: 2.8s; }
        .star:nth-child(16) { width: 3px; height: 3px; top: 10%; left: 70%; animation-delay: 0.3s; }
        .star:nth-child(17) { width: 2px; height: 2px; top: 90%; left: 50%; animation-delay: 0.7s; }
        .star:nth-child(18) { width: 2px; height: 2px; top: 65%; left: 10%; animation-delay: 1.1s; }
        .star:nth-child(19) { width: 1px; height: 1px; top: 25%; left: 35%; animation-delay: 1.5s; }
        .star:nth-child(20) { width: 3px; height: 3px; top: 55%; left: 75%; animation-delay: 1.9s; }
        .star:nth-child(21) { width: 2px; height: 2px; top: 30%; left: 15%; animation-delay: 2.3s; }
        .star:nth-child(22) { width: 2px; height: 2px; top: 75%; left: 85%; animation-delay: 2.7s; }
        
        /* 🌟 网站logo和名称样式 */
        .site-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .site-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .site-name {
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
            text-decoration: none;
        }
        
        .site-info a {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .site-info a:hover {
            transform: translateY(-2px);
        }
        
        /* 🌟 404内容布局 */
        .error-content {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        /* 🌟 星球容器调整 */
        .planet-container {
            margin-top: 20px;
        }
        
        .error-text {
            flex: 1;
            min-width: 250px;
        }
        
        /* 🌟 文本内容居中显示 */
        .error-text {
            text-align: center;
        }
        
        /* 🌟 响应式调整 */
        @media (max-width: 768px) {
            .error-content {
                flex-direction: column;
                gap: 20px;
                align-items: center;
            }
            
            .planet-container {
                width: 100px;
                height: 100px;
            }
            
            /* 确保所有内容在移动端居中显示 */
            .error-text {
                text-align: center;
                width: 100%;
            }
            
            .site-info {
                justify-content: center;
            }
            
            /* 保持右上角月球SVG位置不变 */
            .top-right-globe {
                position: absolute;
                top: 5px;
                right: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- 背景容器 -->
    <div class="bg-container" id="bgContainer"></div>
    
    <!-- 🌟 粒子画布 -->
    <canvas id="particles-container"></canvas>
    
    <!-- 内容容器 -->
    <div class="container">
        <div class="error-404">
            <!-- 🌟 右上角月球SVG -->
            <div class="top-right-globe">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/error/globe.svg" alt="Globe" />
            </div>
            
            <!-- 🌟 星星效果容器 -->
            <div class="stars-container">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
            </div>
            
            <!-- 🌟 404内容布局 -->
            <div class="error-content">
                <!-- 🌟 宇宙星球容器 -->
                <div class="planet-container">
                    <!-- 旋转的星球主体 -->
                    <svg class="planet-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <!-- 宇宙星球的主体 -->
                        <circle cx="50" cy="50" r="40" fill="url(#planetGradient)" />
                        <!-- 宇宙星球的纹理 -->
                        <circle cx="35" cy="45" r="8" fill="rgba(255, 255, 255, 0.2)" />
                        <circle cx="65" cy="55" r="10" fill="rgba(255, 255, 255, 0.15)" />
                        <circle cx="50" cy="30" r="6" fill="rgba(255, 255, 255, 0.25)" />
                        <ellipse cx="50" cy="70" rx="12" ry="5" fill="rgba(255, 255, 255, 0.1)" />
                        
                        <!-- 宇宙星球的渐变和滤镜定义 -->
                        <defs>
                            <linearGradient id="planetGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4a90e2" />
                                <stop offset="100%" stop-color="#2c5aa0" />
                            </linearGradient>
                            
                            <!-- 🌟 发光滤镜 -->
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                    </svg>
                    
                    <!-- 固定的向下弧形 -->
                    <svg class="planet-ring" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <!-- 固定的宇宙星球向下弧形-->
                        <path d="M 10 85 C 40 97 60 97 90 85" fill="none" stroke="rgba(255, 255, 255, 0.5)" stroke-width="1.5" />
                    </svg>
                    
                    <!-- 🌟 宇宙星球上的图片 -->
                    <div class="planet-image-container">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/error/МawaЕnnh.png" alt="МawaЕnnh" class="planet-character">
                    </div>
                </div>
                
                <!-- 🌟 404文字内容 -->
                <div class="error-text">
                    <h2>oi~坏惹，居然是404</h2>
                    <p>您好像访问了一个不存在的页面</p>
                    <p>要不您返回首页叭~或者联系站长</p>
                    <h3> Ciallo～(∠・ω< )⌒★</h3>
                    <p></p>
                    <a href="<?php echo home_url(); ?>" class="btn-primary">返回首页</a>
                    <a href="javascript:history.back()" class="btn-secondary">返回上一页</a>
                </div>
            </div>
            
            <!-- 🌟 网站logo和名称 -->
            <div class="site-info">
                <a href="<?php echo home_url(); ?>" title="返回首页">
                    <img src="<?php echo get_site_icon_url(); ?>" alt="<?php echo get_bloginfo('name'); ?>" class="site-logo">
                    <span class="site-name"><?php echo get_bloginfo('name'); ?></span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- 静默预加载脚本 -->
    <script>
        // 🖼️ 静默预加载背景图
        const bgImage = new Image();
        bgImage.src = "<?php echo $banner_image; ?>";
        
        // 加载完成时显示背景图
        bgImage.onload = function() {
            const bgContainer = document.getElementById('bgContainer');
            bgContainer.classList.add('animated');
        };
        
        // 加载失败时的处理
        bgImage.onerror = function() {
            // 即使加载失败，也显示背景容器（使用默认背景色）
            const bgContainer = document.getElementById('bgContainer');
            bgContainer.classList.add('animated');
        };
        
        // 添加一个超时机制，确保背景图总能显示
        setTimeout(function() {
            const bgContainer = document.getElementById('bgContainer');
            if (!bgContainer.classList.contains('animated')) {
                bgContainer.classList.add('animated');
            }
        }, 3000);
        
        // 🎨 为星星添加随机颜色
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            // 定义星星颜色数组，包含多种颜色
            const starColors = [
                '#ffffff', // 白色
                '#ffff00', // 黄色
                '#ff00ff', // 粉色
                '#00ffff', // 青色
                '#ff8800', // 橙色
                '#88ff00', // 绿色
                '#0088ff', // 蓝色
                '#ff0088', // 玫红色
                '#8800ff', // 紫色
                '#00ff88'  // 浅绿色
            ];
            
            // 为每个星星分配随机颜色
            stars.forEach(star => {
                // 随机选择一种颜色
                const randomColor = starColors[Math.floor(Math.random() * starColors.length)];
                // 设置星星背景色
                star.style.backgroundColor = randomColor;
            });
            
            // 🌟 初始化网络粒子效果
            initParticles();
        });
        
        // 🚀 网络粒子效果实现
        function initParticles() {
            // 获取画布元素
            const canvas = document.getElementById('particles-container');
            const ctx = canvas.getContext('2d');
            
            // 设置画布尺寸
            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);
            
            // 粒子类定义
            class Particle {
                constructor() {
                    // 随机位置
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    
                    // 随机速度 - 极度缓慢
                    this.vx = (Math.random() - 0.5) * 0.1;
                    this.vy = (Math.random() - 0.5) * 0.1;
                    
                    // 随机大小
                    this.size = Math.random() * 3 + 1;
                    
                    // 随机颜色
                    this.color = this.getRandomColor();
                    
                    // 透明度
                    this.opacity = Math.random() * 0.6 + 0.2; // 初始透明度范围
                    this.opacityDirection = Math.random() > 0.5 ? 1 : -1; // 透明度变化方向，1为增加，-1为减少
                    this.opacitySpeed = Math.random() * 0.003 + 0.002; // 透明度变化速度
                }
                
                // 获取随机颜色
                getRandomColor() {
                    const colors = [
                        '#ffffff', '#000000ff', '#ff00ff', '#00ffff', '#ff8800',
                        '#91ffe7ff', '#0088ff', '#ff0088', '#8800ff', '#00ff88'
                    ];
                    return colors[Math.floor(Math.random() * colors.length)];
                }
                
                // 更新粒子位置和透明度
                update(mouseX, mouseY) {
                    // 边界反弹
                    if (this.x + this.size > canvas.width || this.x - this.size < 0) {
                        this.vx *= -1;
                    }
                    if (this.y + this.size > canvas.height || this.y - this.size < 0) {
                        this.vy *= -1;
                    }
                    
                    // 鼠标排斥反应
                    const dx = mouseX - this.x;
                    const dy = mouseY - this.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    
                    // 排斥力半径
                    const repelRadius = 150;
                    if (distance < repelRadius && distance > 0) {
                        const force = (repelRadius - distance) / repelRadius;
                        const angle = Math.atan2(dy, dx);
                        this.vx -= Math.cos(angle) * force * 0.5;
                        this.vy -= Math.sin(angle) * force * 0.5;
                    }
                    
                    // 更新位置
                    this.x += this.vx;
                    this.y += this.vy;
                    
                    // 速度限制 - 更加缓慢
                    const maxSpeed = 0.3;
                    this.vx = Math.max(-maxSpeed, Math.min(maxSpeed, this.vx));
                    this.vy = Math.max(-maxSpeed, Math.min(maxSpeed, this.vy));
                    
                    // 更新透明度 - 渐隐渐显效果
                    this.opacity += this.opacityDirection * this.opacitySpeed;
                    
                    // 当透明度达到边界值时，反转方向
                    if (this.opacity > 0.8) {
                        this.opacity = 0.8;
                        this.opacityDirection = -1;
                    } else if (this.opacity < 0.2) {
                        this.opacity = 0.2;
                        this.opacityDirection = 1;
                    }
                }
                
                // 绘制粒子
                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fillStyle = this.color;
                    ctx.globalAlpha = this.opacity;
                    ctx.fill();
                    ctx.closePath();
                    ctx.globalAlpha = 1;
                }
            }
            
            // 粒子数组
            const particles = [];
            const particleCount = 50;
            
            // 初始化粒子
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
            
            // 鼠标位置
            let mouseX = canvas.width / 2;
            let mouseY = canvas.height / 2;
            
            // 监听鼠标移动
            window.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });
            
            // 动画循环
            function animate() {
                // 清空画布
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // 更新和绘制粒子
                particles.forEach(particle => {
                    particle.update(mouseX, mouseY);
                    particle.draw();
                });
                
                // 绘制连线
                drawConnections();
                
                // 继续动画
                requestAnimationFrame(animate);
            }
            
            // 绘制粒子间的连线
            function drawConnections() {
                const connectionRadius = 120;
                
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const distance = Math.sqrt(dx * dx + dy * dy);
                        
                        if (distance < connectionRadius) {
                            // 连线透明度根据距离动态变化
                            const opacity = (connectionRadius - distance) / connectionRadius * 0.3;
                            
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.strokeStyle = `rgba(255, 255, 255, ${opacity})`;
                            ctx.lineWidth = 0.5;
                            ctx.stroke();
                            ctx.closePath();
                        }
                    }
                }
            }
            
            // 开始动画
            animate();
        }
    </script>
</body>
</html>