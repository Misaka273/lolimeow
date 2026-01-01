<?php
/**
 * Template Name: 登录页面
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
//如果用户已经登陆那么跳转到首页或重定向页面
if (is_user_logged_in()){
   // 🔗 检查是否有 reauth 参数，如果有则不重定向，允许重新认证
   if (isset($_GET['reauth']) && $_GET['reauth'] == '1') {
       // 如果是重新认证请求，允许继续访问登录页面
       // 这里不需要重定向，直接退出条件判断
   } else {
       // 🔗 检查是否有重定向参数
       if (isset($_GET['redirect_to'])) {
           $redirect_url = urldecode($_GET['redirect_to']);
           // 验证重定向地址的安全性
           if (wp_validate_redirect($redirect_url)) {
               // 避免重定向循环：检查是否已经在目标页面
               if (strpos($_SERVER['REQUEST_URI'], basename(parse_url($redirect_url, PHP_URL_PATH))) === false) {
                   wp_safe_redirect($redirect_url);
                   exit;
               }
           }
       }
       
       // 检查用户是否是管理员，如果是管理员则跳转到后台
       if (current_user_can('manage_options')) {
           // 避免重定向循环：检查是否已经在后台
           if (strpos($_SERVER['REQUEST_URI'], 'wp-admin') === false) {
               wp_safe_redirect( admin_url() );
               exit;
           }
       }
       
       // 普通用户跳转到首页
       // 避免重定向循环：检查是否已经在首页
       $home_url = get_option('home');
       $home_path = parse_url($home_url, PHP_URL_PATH);
       if (empty($home_path)) {
           $home_path = '/';
       }
       
       // 检查当前请求是否已经是首页，避免循环
       if ($_SERVER['REQUEST_URI'] == $home_path || $_SERVER['REQUEST_URI'] == $home_path . '/') {
           exit;
       }
       
       wp_safe_redirect( $home_url );
       exit;
   }
}
?>
<html <?php language_attributes(); ?>>
    <head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title><?php echo boxmoe_theme_title(); ?></title>
   <link rel="icon" href="<?php echo boxmoe_favicon(); ?>" type="image/x-icon">
    <?php boxmoe_keywords(); ?>
    <?php boxmoe_description(); ?>
    <?php ob_start();wp_head();$wp_head_output = ob_get_clean();echo preg_replace('/\n/', "\n    ", trim($wp_head_output))."\n    ";?>
    <style>
        /* 🥳 登录页样式 - 双面板设计 */
        :root {
            --primary: #5995fd;
            --primary-dark: #4d84e2;
            --bg: rgba(176, 208, 255, 0.56);
            --text: #222;
            --white: rgba(202, 214, 255, 0.6);
        }
        
        /* 🌟 背景图设置 */
        body {
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0;
            animation: fadeInScale 1.5s ease-out forwards;
            background-color: #dde9ffff; /* 确保图片加载前有背景色 */
        }
        
        /* 🌟 背景图淡入放大动画 */
        @keyframes fadeInScale {
            0% {
                opacity: 0;
                background-size: 105% auto;
            }
            100% {
                opacity: 1;
                background-size: cover;
            }
        }
        
        /* 🌟 隐藏的预加载图片 */
        #bg-preloader {
            position: absolute;
            top: -9999px;
            left: -9999px;
            width: 0;
            height: 0;
            opacity: 0;
        }
        

        
        /* 🌟 解决无法全屏显示 */
        html, body, main {
            width: 100vw !important;
            height: 100vh !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            position: relative !important;
            left: 0 !important;
            top: 0 !important;
        }
        
        /* 🌟 强制覆盖所有可能的居中样式 */
        body > * {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            max-width: none !important;
            width: 100vw !important;
        }
        
        /* 🌟 确保容器绝对定位 */
        .container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
            overflow: hidden !important;
            background-color: var(--white);
            z-index: 1;
            backdrop-filter: blur(20px); /* 增强高斯模糊效果 */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            overflow: hidden; /* 防止页面滚动 */
        }
        
        main {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
        }
        
        input {
            font-family: "Poppins", sans-serif;
        }
        
        /* 🌟 强制覆盖所有可能的居中样式 */
        body > * {
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            max-width: none !important;
            width: 100vw !important;
        }
        
        /* 🌟 确保body强制全屏 */
        body {
            display: block !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            overflow: hidden !important;
        }
        
        /* 🌟 确保main标签强制全屏 */
        main {
            display: block !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        /* 🌟 粒子效果样式 */
        #particles-js {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1 !important;
            overflow: hidden !important;
        }
        
        .container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            max-width: none !important;
            overflow: hidden !important;
            background-color: var(--white);
            z-index: 2 !important;
            transform: none !important;
            display: block !important;
            box-sizing: border-box !important;
            border: none !important;
            outline: none !important;
        }
        

        
        /* 🌟 防止WordPress添加额外容器 */
        body > *:not(main) {
            display: none !important;
        }
        
        /* 🌟 确保main是唯一显示的容器 */
        body > main {
            display: block !important;
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
        }
        
        .container::before {
            content: "";
            position: absolute;
            height: 2000px;
            width: 2000px;
            top: -10%;
            right: 52%;
            transform: translateY(-50%);
            background-image: linear-gradient(-45deg, #1c5fd1 0%, #1ec3fa 100%);
            transition: 1.8s ease-in-out;
            border-radius: 50%;
            z-index: 6;
        }
        
        .forms-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }
        
        .signin-signup {
            position: absolute;
            top: 50%;
            transform: translate(0, -50%);
            right: 0;
            width: 50%;
            transition: 1s 0.7s ease-in-out;
            display: grid;
            grid-template-columns: 1fr;
            z-index: 5;
        }
        
        form {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 5rem;
            transition: all 0.2s 0.7s;
            overflow: hidden;
            grid-column: 1 / 2;
            grid-row: 1 / 2;
        }
        
        form.sign-up-form {
            opacity: 0;
            z-index: 1;
        }
        
        form.sign-in-form {
            z-index: 2;
        }
        
        .title {
            font-size: 2.2rem;
            color: #444;
            margin-bottom: 10px;
        }
        
        /* 🌟 网站Logo样式 */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 150px;
            height: auto;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        /* 🏷️ 浮动标签与动态文本 - 勋章效果 */
        .floating-label-group {
            position: relative;
            margin-bottom: 1.5rem;
            width: 100%;
            max-width: 380px;
        }
        .floating-label-group .form-control {
            height: 3.5rem;
            padding: 1.25rem 40px 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            /* 隐藏浏览器默认的密码显示按钮 */
            -moz-appearance: none;
            -webkit-appearance: none;
        }
        
        /* 强制隐藏浏览器默认的密码显示按钮 */
        input[type="password"]::-ms-reveal {
            display: none;
        }
        
        input[type="password"]::-webkit-clear-button,
        input[type="password"]::-webkit-inner-spin-button,
        input[type="password"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        /* 确保显示密码按钮始终可见 */
        .toggle-password {
            z-index: 10 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            opacity: 0.7 !important;
        }
        
        .toggle-password:hover {
            opacity: 1 !important;
        }
        
        .toggle-password:focus {
            outline: none !important;
            opacity: 1 !important;
        }
        .floating-label-group .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(89, 149, 253, 0.2);
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        .floating-label-group label {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            transition: 0.2s ease all;
            color: #6c757d;
            padding: 0 5px;
            z-index: 5;
            margin: 0;
            width: auto;
            height: auto;
            font-size: 1rem;
            border-radius: 4px;
        }
        .floating-label-group label::after {
            content: attr(data-default);
            transition: all 0.2s ease;
        }
        /* 激活状态 */
        .floating-label-group .form-control:focus ~ label,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label {
            top: 0;
            left: 0.8rem;
            font-size: 0.75rem;
            transform: translateY(-50%);
            color: var(--primary);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .floating-label-group .form-control:focus ~ label::after,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label::after {
            content: attr(data-active);
        }
        /* 表单验证反馈 */
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        /* 按钮样式优化 - 统一居中样式 */
        .btn {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            box-shadow: 0 4px 6px rgba(89, 149, 253, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(89, 149, 253, 0.4);
        }
        
        .btn::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -100%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                135deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                rgba(255, 255, 255, 0.6),
                rgba(255, 255, 255, 0.4),
                transparent
            );
            transform: rotate(30deg);
            transition: all 0.8s ease;
        }
        
        .btn:hover::after {
            left: 100%;
        }
        
        .input_field {
            max-width: 380px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            margin: 10px 0;
            height: 55px;
            border-radius: 55px;
            display: grid;
            grid-template-columns: 15% 85%;
            padding: 0 0.4rem;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .input_field input {
            background: none;
            outline: none;
            border: none;
            line-height: 1;
            min-width: 270px;
            font-weight: 600;
            font-size: 1.1rem;
            padding-left: 10px;
            color: var(--text);
            font-size: 16px;
        }
        
        .input_field input::placeholder {
            color: rgba(0, 0, 0, 0.4);
            font-weight: 500;
        }
        

        
        .shortMessage,
        .Password_login {
            width: 16px;
            height: 16px;
            display: inline-block;
            text-align: center;
            vertical-align: baseline;
            position: relative;
            border-radius: 50%;
            outline: none;
            -webkit-appearance: none;
            border: 1px solid #fff;
            -webkit-tab-highlight-color: rgba(0, 0, 0, 0);
            color: #fff;
            background: #fff;
        }
        
        .shortMessage::before,
        .Password_login::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            background: #fff;
            width: 100%;
            height: 100%;
            border: 1px solid #999999;
            border-radius: 50%;
            color: #fff;
        }
        
        .shortMessage:checked::before,
        .Password_login:checked::before {
            content: "\2713";
            background-color: #51a7e0;
            border: 1px solid #51a7e0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            color: #fff;
            font-size: 0.52rem;
            border-radius: 50%;
        }
        
        .agree_text {
            padding-left: 4px;
            white-space: normal;
            word-break: break-all;
            font-size: 12px;
            line-height: 21px;
        }
        
        .agree_text a {
            color: #51a7e0;
            text-decoration: none;
        }
        
        .btn {
            width: 200px;
            background-color: #5995fd;
            border: none;
            outline: none;
            height: 49px;
            border-radius: 49px;
            color: #fff;
            text-transform: uppercase;
            font-weight: 600;
            margin: 10px 0;
            cursor: pointer;
            transition: 0.5s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn:hover {
            background-color: #4d84e2;
        }
        
        .panels-container {
            position: absolute;
            height: 100%;
            width: 100%;
            top: 0;
            left: 0;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
        
        .panel {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-around;
            text-align: center;
            z-index: 6;
        }
        
        .left-panel {
            pointer-events: all;
            padding: 3rem 17% 2rem 12%;
        }
        
        .right-panel {
            pointer-events: none;
            padding: 3rem 12% 2rem 17%;
        }
        
        .panel .content {
            color: #fff;
            transition: transform 0.9s ease-in-out;
            transition-delay: 0.6s;
        }
        
        .panel h3 {
            font-weight: 600;
            line-height: 1;
            font-size: 1.5rem;
        }
        
        .panel p {
            font-size: 0.95rem;
            padding: 0.7rem 0;
        }
        
        .btn.transparent {
            margin: 0;
            background: none;
            border: 2px solid #fff;
            width: auto;
            height: auto;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            padding: 10px 50px;
            box-sizing: border-box;
            line-height: 1.2;
            letter-spacing: 0;
            text-indent: 0;
            border-radius: 22px;
            display: inline-block;
        }
        
        .image {
            width: 100%;
            transition: transform 1.1s ease-in-out;
            transition-delay: 0.4s;
        }
        
        .right-panel .image,
        .right-panel .content {
            transform: translateX(800px);
        }
        
        /* 🌙 暗色模式适配 */
        [data-bs-theme="dark"] {
            --primary: #6e9eff;
            --primary-dark: #5a87e3;
            --bg: #1a1a1a;
            --text: #e0e0e0;
            --white: #2d2d2d;
        }
        
        [data-bs-theme="dark"] .title {
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .input_field {
            background-color: #3d3d3d;
        }
        
        [data-bs-theme="dark"] .input_field input {
            color: #e0e0e0;
        }
        
        [data-bs-theme="dark"] .input_field input::placeholder {
            color: #888;
        }
        
        /* ANIMATION */
        
        /* 注册模式样式 */
        .container.sign-up-mode::before {
            transform: translate(100%, -50%);
            right: 52%;
        }
        
        .container.sign-up-mode .signin-signup {
            left: 0;
            right: auto;
        }
        
        .container.sign-up-mode form.sign-up-form {
            opacity: 1;
            z-index: 2;
        }
        
        .container.sign-up-mode form.sign-in-form {
            opacity: 0;
            z-index: 1;
        }
        
        .container.sign-up-mode .left-panel .content,
        .container.sign-up-mode .left-panel .image {
            transform: translateX(-800px);
        }
        
        .container.sign-up-mode .right-panel .content,
        .container.sign-up-mode .right-panel .image {
            transform: translateX(0);
        }
        
        .container.sign-up-mode .left-panel {
            pointer-events: none;
        }
        
        .container.sign-up-mode .right-panel {
            pointer-events: all;
        }
        
        @media (max-width: 870px) {
            .container {
                min-height: 800px;
                height: 100vh;
            }
            
            .signin-signup {
                width: 100%;
                top: 95%;
                transform: translate(-50%, -100%);
                transition: 1s 0.8s ease-in-out;
            }
            
            .signin-signup,
            .container.sign-up-mode .signin-signup {
                left: 50%;
                transform: translate(-50%, -100%);
                right: auto;
            }
            
            .panels-container {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 2fr 1fr;
            }
            
            .panel {
                flex-direction: row;
                justify-content: space-around;
                align-items: center;
                padding: 2.5rem 8%;
                grid-column: 1 / 2;
            }
            
            .right-panel {
                grid-row: 3 / 4;
            }
            
            .left-panel {
                grid-row: 1 / 2;
            }
            
            .image {
                width: 200px;
                transition: transform 0.9s ease-in-out;
                transition-delay: 0.6s;
            }
            
            .panel .content {
                padding-right: 15%;
                transition: transform 0.9s ease-in-out;
                transition-delay: 0.8s;
            }
            
            .panel h3 {
                font-size: 1.2rem;
            }
            
            .panel p {
                font-size: 0.7rem;
                padding: 0.5rem 0;
            }
            
            .btn.transparent {
                width: auto;
                height: auto;
                font-size: 0.7rem;
                text-align: center;
                padding: 8px 17px;
                box-sizing: border-box;
                line-height: 1.2;
                display: inline-block;
            }
            
            .container:before {
                width: 1500px;
                height: 1500px;
                transform: translateX(-50%);
                left: 30%;
                bottom: 68%;
                right: initial;
                top: initial;
                transition: 2s ease-in-out;
            }
            
            .container.sign-up-mode:before {
                transform: translate(-50%, 100%);
                bottom: 32%;
                right: initial;
            }
            
            .container.sign-up-mode .left-panel .image,
            .container.sign-up-mode .left-panel .content {
                transform: translateY(-300px);
            }
            
            .container.sign-up-mode .right-panel .image,
            .container.sign-up-mode .right-panel .content {
                transform: translateY(0px);
            }
            
            .right-panel .image,
            .right-panel .content {
                transform: translateY(300px);
            }
            
            .container.sign-up-mode .signin-signup {
                top: 5%;
                transform: translate(-50%, 0);
            }
        }
        
        @media (max-width: 570px) {
            form {
                padding: 0 1.5rem;
            }
            
            .image {
                display: none;
            }
            
            .panel .content {
                padding: 0.5rem 1rem;
            }
            
            .container {
                padding: 1.5rem;
            }
            
            .container:before {
                bottom: 72%;
                left: 50%;
            }
            
            .container.sign-up-mode:before {
                bottom: 28%;
                left: 50%;
            }
        }

    </style>
</head>

<body>
   <main>
      <!-- 🥳 双面板登录页面 -->
      <div class="container">
         <!-- 表单区 -->
         <div class="forms-container">
            <div class="signin-signup">
               <!-- 登录表单 -->
               <form class="sign-in-form needs-validation" action="" method="post" id="loginform" novalidate>
                  <div class="logo-container">
                     <?php boxmoe_logo(); ?>
                  </div>
                  <h2 class="title">登录</h2>
                  <div class="floating-label-group">
                     <input type="text" name="username" class="form-control" id="username" required placeholder=" " />
                     <label for="username" data-default="请输入用户名" data-active="用户名"></label>
                     <div class="invalid-feedback">请输入有效的用户名。</div>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="password" class="form-control" id="password" required placeholder=" " />
                     <label for="password" data-default="请输入密码" data-active="密码"></label>
                     <div class="invalid-feedback">请输入密码。</div>
                     <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  <p class="social_text">
                     <input class="Password_login" type="checkbox" name="rememberme" id="rememberme">
                     <span class="agree_text">
                        记住账号
                     </span>
                  </p>
                  <?php wp_nonce_field('user_login', 'login_nonce'); ?>
                  <button class="btn" type="submit" name="login_submit">Go</button>
                  <div id="login-message" class="mt-3"></div>
                  <div class="mt-3">
                     <a href="<?php echo boxmoe_reset_password_link_page(); ?>" class="text-primary text-decoration-none">忘记密码?</a>
                  </div>
               </form>

               <!-- 注册表单 -->
               <form class="sign-up-form needs-validation" id="signupform" novalidate>
                  <div class="logo-container">
                     <?php boxmoe_logo(); ?>
                  </div>
                  <h2 class="title">注册</h2>
                  <div class="floating-label-group">
                     <input type="text" name="username" class="form-control" id="signupFullnameInput" required placeholder=" " />
                     <label for="signupFullnameInput" data-default="请输入用户名" data-active="用户名"></label>
                     <div class="invalid-feedback">请输入有效的用户名。</div>
                  </div>
                  <div class="floating-label-group">
                     <input type="email" name="email" class="form-control" id="signupEmailInput" required placeholder=" " />
                     <label for="signupEmailInput" data-default="请输入邮箱" data-active="邮箱"></label>
                     <div class="invalid-feedback">请输入有效的邮箱地址。</div>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="text" name="verificationcode" class="form-control" id="signupVerificationCode" required placeholder=" " style="padding-right: 120px; width: 100%; max-width: 380px;" />
                     <label for="signupVerificationCode" data-default="请输入验证码" data-active="验证码"></label>
                     <div class="invalid-feedback">请输入验证码。</div>
                     <div class="Acquire_box" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);">
                        <span class="Acquire" id="sendVerificationCode" style="display: inline-block; background: var(--primary); color: white; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px;">获取验证码</span>
                     </div>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="password" class="form-control" id="formSignUpPassword" required placeholder=" " />
                     <label for="formSignUpPassword" data-default="请设置密码" data-active="密码"></label>
                     <div class="invalid-feedback">请设置密码。</div>
                     <button type="button" class="toggle-password" data-target="formSignUpPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  <div class="floating-label-group" style="position: relative;">
                     <input type="password" name="confirmpassword" class="form-control" id="formSignUpConfirmPassword" required placeholder=" " />
                     <label for="formSignUpConfirmPassword" data-default="请确认密码" data-active="确认密码"></label>
                     <div class="invalid-feedback">请确认密码。</div>
                     <button type="button" class="toggle-password" data-target="formSignUpConfirmPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: var(--text); opacity: 0.7; transition: opacity 0.3s ease;">
                        <i class="fa fa-eye-slash"></i>
                     </button>
                  </div>
                  <p class="social_text">
                     <input class="shortMessage" type="checkbox" name="agree" required />
                     <span class="agree_text">
                        已阅读并同意<a href="#">《用户协议》</a><a href="#">《隐私政策》</a>
                     </span>
                  </p>
                  <input type="hidden" name="signup_nonce" value="<?php echo wp_create_nonce('user_signup'); ?>">
                  <button class="btn" type="submit" name="signup_submit">Go</button>
                  <div id="signup-message" class="mt-3"></div>
               </form>
            </div>
         </div>

         <!-- 双面板 -->
         <div class="panels-container">
            <div class="panel left-panel">
               <div class="content">
                  <h3>新用户?</h3>
                  <p>
                     注册账号，开始您的旅程，探索更多精彩内容。
                  </p>
                  <button class="btn transparent" id="sign-up-btn">注册</button>
               </div>
               <img class="image" src="<?php echo get_template_directory_uri(); ?>/assets/images/logon/注册.png" alt="注册" />
            </div>

            <div class="panel right-panel">
               <div class="content">
                  <h3>已有账号?</h3>
                  <p>
                     登录您的账号，继续之前的体验。
                  </p>
                  <button class="btn transparent" id="sign-in-btn">登录</button>
               </div>
               <img class="image" src="<?php echo get_template_directory_uri(); ?>/assets/images/logon/登录.png" alt="登录" />
            </div>
         </div>
      </div>

      <!-- 📝 主题版权信息 -->
      <style>
          .theme-copyright {
              position: fixed;
              bottom: 20px;
              left: 50%;
              transform: translateX(-50%);
              text-align: center;
              font-size: 12px;
              color: rgba(0, 0, 0, 0.6);
              z-index: 9999;
              line-height: 1.6;
          }
          
          .theme-copyright a {
              color: #1a5fb4;
              text-decoration: none;
              transition: color 0.3s ease;
          }
          
          .theme-copyright a:hover {
              color: #154360;
          }
      </style>
      
      <div class="theme-copyright">
          <p>主题名称🎉<?php $theme_data = wp_get_theme(); echo $theme_data->get('Name'); ?></p>
          <p>主题版本🛰️<?php $theme_data = wp_get_theme(); echo $theme_data->get('Version'); ?></p>
          <p>本页面由🗼 <a href="https://gl.baimu.live/864" target="_blank">白木</a> 重构</p>
      </div>
   </main>
   
   <!-- 🌟 背景图预加载 -->
   <img id="bg-preloader" src="<?php boxmoe_banner_image(); ?>" alt="Preload Background" />
   
   <?php 
    ob_start();
    wp_footer();
    $wp_footer_output = ob_get_clean();
    echo preg_replace('/\n/', "\n    ", trim($wp_footer_output))."\n    ";
    ?>
    <script>
      // 直接定义ajax_object，避免依赖主题脚本加载
      var ajax_object = {
        ajaxurl: '<?php echo admin_url("admin-ajax.php"); ?>',
        themeurl: '<?php echo boxmoe_theme_url(); ?>'
      };

      // 🌟 背景图预加载功能
      document.addEventListener('DOMContentLoaded', function() {
          // 获取预加载图片元素
          const preloaderImg = document.getElementById('bg-preloader');
          const body = document.body;
          
          // 设置预加载图片的onload事件
          preloaderImg.onload = function() {
              // 图片加载完成后，将图片应用到body背景
              body.style.backgroundImage = `url('${this.src}')`;
              // 触发动画效果
              body.style.opacity = '1';
          };
          
          // 🔗 双面板切换功能
          const sign_in_btn = document.getElementById('sign-in-btn');
          const sign_up_btn = document.getElementById('sign-up-btn');
          const container = document.querySelector('.container');

          sign_up_btn.addEventListener('click', () => {
              container.classList.add('sign-up-mode');
          });

          sign_in_btn.addEventListener('click', () => {
              container.classList.remove('sign-up-mode');
          });
          
          // 🔗 根据URL参数自动切换登录/注册模块
          const urlParams = new URLSearchParams(window.location.search);
          const mode = urlParams.get('mode');
          
          if (mode === 'signup') {
              container.classList.add('sign-up-mode');
          } else {
              container.classList.remove('sign-up-mode');
          }
          
          // 🔗 显示/隐藏密码功能 - 优化实现
          const togglePasswordBtns = document.querySelectorAll('.toggle-password');
          
          // 确保按钮元素存在
          if (togglePasswordBtns.length > 0) {
              togglePasswordBtns.forEach(btn => {
                  // 使用原生事件监听器，确保事件绑定成功
                  btn.addEventListener('click', function(e) {
                      e.preventDefault();
                      
                      const targetId = this.getAttribute('data-target');
                      const passwordInput = document.getElementById(targetId);
                      const icon = this.querySelector('i');
                      
                      // 确保目标元素存在
                      if (passwordInput && icon) {
                          // 切换密码显示状态
                          if (passwordInput.type === 'password') {
                              passwordInput.type = 'text';
                              icon.classList.remove('fa-eye-slash');
                              icon.classList.add('fa-eye');
                          } else {
                              passwordInput.type = 'password';
                              icon.classList.remove('fa-eye');
                              icon.classList.add('fa-eye-slash');
                          }
                      }
                  });
              });
          }

          // 🔗 登录表单提交事件监听
          document.getElementById('loginform').addEventListener('submit', function(e) {
              e.preventDefault();
              
              const loginButton = this.querySelector('button[type="submit"]');
              
              loginButton.disabled = true;
              loginButton.textContent = '登录中...';

              // 🔗 获取 URL 中的 redirect_to 参数
              const urlParams = new URLSearchParams(window.location.search);
              const redirect_to = urlParams.get('redirect_to');

              // 🔄 动态生成新的nonce，避免过期问题
              const newNonce = document.querySelector('input[name="login_nonce"]').value;
              const formData = {
                  username: document.getElementById('username').value,
                  password: document.getElementById('password').value,
                  rememberme: document.getElementById('rememberme').checked,
                  login_nonce: newNonce,
                  redirect_to: redirect_to // ⬅️ 将重定向参数传给后端
              };
              
              // 使用FormData来构建请求体，确保WordPress能正确解析
              const formDataToSend = new FormData();
              formDataToSend.append('action', 'user_login_action');
              formDataToSend.append('formData', JSON.stringify(formData));
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  credentials: 'same-origin',
                  body: formDataToSend
              })
              .then(response => response.json())
              .then(response => {
                  if(response.success) {
                      document.getElementById('login-message').innerHTML = 
                          '<div class="alert alert-success mt-3">' + response.data.message + '，正在跳转...</div>';
                      setTimeout(() => {
                          // 🔗 优先跳转到后端返回的地址，其次尝试 URL 参数，最后回落到 referrer 或首页
                          if (response.data.redirect_url) {
                              window.location.href = response.data.redirect_url;
                          } else if (redirect_to) {
                              window.location.href = redirect_to;
                          } else {
                               window.location.href = '/';
                          }
                      }, 1000);
                  } else {
                      loginButton.disabled = false;
                      loginButton.textContent = 'Go';
                      
                      document.getElementById('login-message').innerHTML = 
                          '<div class="alert alert-danger mt-3">' + response.data.message + '</div>';
                  }
              })
              .catch(error => {
                  loginButton.disabled = false;
                  loginButton.textContent = 'Go';
                  
                  // 显示更详细的错误信息，帮助用户了解登录失败的原因
                  const errorMessage = error.message || '未知错误';
                  document.getElementById('login-message').innerHTML = 
                      '<div class="alert alert-danger mt-3">登录请求失败: ' + errorMessage + '，请稍后重试</div>';
                  
                  // 在控制台打印完整的错误信息，方便开发者调试
                  console.error('登录请求失败:', error);
              });
          });

          // 🔗 注册相关JS功能
          // 发送验证码逻辑
          document.getElementById('sendVerificationCode').addEventListener('click', function() {
              var email = document.getElementById('signupEmailInput').value;
              var btn = this;
              if(!email) {
                  alert('请先填写邮箱地址');
                  return;
              }
              
              btn.disabled = true;
              btn.textContent = '发送中...';
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                  body: 'action=send_verification_code&email=' + encodeURIComponent(email)
              })
              .then(response => response.json())
              .then(data => {
                  if(data.success) {
                      alert(data.data.message);
                      var countdown = 60;
                      var timer = setInterval(function() {
                          btn.textContent = countdown + 's后重试';
                          countdown--;
                          if(countdown < 0) {
                              clearInterval(timer);
                              btn.disabled = false;
                              btn.textContent = '获取验证码';
                          }
                      }, 1000);
                  } else {
                      alert(data.data.message);
                      btn.disabled = false;
                      btn.textContent = '获取验证码';
                  }
              })
              .catch(err => {
                  alert('发送失败，请重试');
                  btn.disabled = false;
                  btn.textContent = '获取验证码';
              });
          });

          // 注册表单提交
          document.getElementById('signupform').addEventListener('submit', function(e) {
              e.preventDefault();
              var btn = this.querySelector('button[type="submit"]');
              
              btn.disabled = true;
              btn.textContent = '注册中...';
              
              // 构建表单数据对象
              var formData = {
                  username: document.getElementById('signupFullnameInput').value,
                  email: document.getElementById('signupEmailInput').value,
                  verificationcode: document.getElementById('signupVerificationCode').value,
                  password: document.getElementById('formSignUpPassword').value,
                  confirmpassword: document.getElementById('formSignUpConfirmPassword').value,
                  signup_nonce: this.querySelector('input[name="signup_nonce"]').value
              };
              
              fetch(ajax_object.ajaxurl, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: 'action=user_signup_action&formData=' + encodeURIComponent(JSON.stringify(formData))
              })
              .then(response => response.json())
              .then(data => {
                  if(data.success) {
                      document.getElementById('signup-message').innerHTML = '<div class="alert alert-success mt-3">'+data.data.message+'</div>';
                      setTimeout(function(){
                          window.location.href = '<?php echo boxmoe_sign_in_link_page(); ?>';
                      }, 2000);
                  } else {
                      document.getElementById('signup-message').innerHTML = '<div class="alert alert-danger mt-3">'+data.data.message+'</div>';
                      btn.disabled = false;
                      btn.textContent = 'Go';
                  }
              })
              .catch(err => {
                  document.getElementById('signup-message').innerHTML = '<div class="alert alert-danger mt-3">网络错误，请重试</div>';
                  btn.disabled = false;
                  btn.textContent = 'Go';
              });
          });
      });
    </script>
    <!-- 🌌 引入粒子效果脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login-particles.js"></script>
</body></html>