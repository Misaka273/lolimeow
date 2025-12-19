<?php
/**
 * Template Name: 注册页面
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}
//如果用户已经登陆那么跳转到首页
if (is_user_logged_in()){
    wp_safe_redirect( get_option('home') );
    exit;
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
        /* 🥳 注册页样式重构 - 玻璃拟态 */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: #f0f2f5;
        }
        .login-page-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url(<?php echo get_boxmoe('boxmoe_user_login_bg')? get_boxmoe('boxmoe_user_login_bg') :'https://api.boxmoe.com/random.php'; ?>);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }
        .login-page-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2); /* ⬅️ 背景遮罩，提升文字可读性 */
            backdrop-filter: blur(8px); /* ⬅️ 全局背景模糊 */
            -webkit-backdrop-filter: blur(8px);
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative; /* ⬅️ 确保在粒子层之上 */
            z-index: 1;
        }
        /* ✨ 玻璃拟态卡片 */
        .glass-card {
            background: radial-gradient(circle at top left, rgba(255, 192, 203, 0.75), rgba(173, 216, 230, 0.75)); /* ⬅️ 浅粉色到浅蓝色圆形扩散渐变 */
            backdrop-filter: blur(20px); /* ⬅️ 局部高斯模糊 */
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; /* ⬅️ 圆角风格 */
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 480px; /* ⬅️ 注册表单稍宽一点 */
            padding: 2.5rem 2rem;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.2);
        }
        /* 🌙 暗色模式适配 */
        [data-bs-theme="dark"] .glass-card {
            background: rgba(30, 30, 35, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            color: #e0e0e0;
        }
        [data-bs-theme="dark"] .text-body-tertiary {
            color: #adb5bd !important;
        }
        
        /* 🏷️ 浮动标签与动态文本 */
        .floating-label-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .floating-label-group .form-control {
            height: 3.5rem;
            padding: 1.25rem 1rem 0.75rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3); /* ⬅️ 增加边框线，配合浮动标签 */
            border-radius: 12px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .floating-label-group .form-control {
            background: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .floating-label-group .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
            border-color: var(--bs-primary);
            transform: translateY(-1px);
        }
        [data-bs-theme="dark"] .floating-label-group .form-control:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--bs-primary);
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
            top: 0; /* ⬅️ 移动到顶部边框线上 */
            left: 0.8rem;
            font-size: 0.75rem;
            transform: translateY(-50%); /* ⬅️ 垂直居中于边框 */
            color: var(--bs-primary);
            background: rgba(255, 255, 255, 0.8); /* ⬅️ 添加背景遮挡边框线，保持玻璃感 */
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        [data-bs-theme="dark"] .floating-label-group .form-control:focus ~ label,
        [data-bs-theme="dark"] .floating-label-group .form-control:not(:placeholder-shown) ~ label {
            background: rgba(45, 45, 50, 0.8);
            color: var(--bs-primary);
        }
        .floating-label-group .form-control:focus ~ label::after,
        .floating-label-group .form-control:not(:placeholder-shown) ~ label::after {
            content: attr(data-active);
        }

        .password-field {
            position: relative;
        }
        .passwordToggler {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            color: #6c757d;
            padding: 5px;
        }
        .btn-primary {
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            box-shadow: 0 4px 6px rgba(var(--bs-primary-rgb), 0.3);
            transition: all 0.3s ease;
            position: relative; /* ⬅️ 为扫光动画定位 */
            overflow: hidden;   /* ⬅️ 隐藏溢出的扫光 */
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(var(--bs-primary-rgb), 0.4);
        }
        /* ✨ 按钮扫光动画 */
        .btn-primary::after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.6),
                transparent
            );
            transition: all 0.6s;
        }
        .btn-primary:hover::after {
            left: 100%;
        }
        /* 💕 底部工具栏 */
        .theme-toggle-fixed {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
        }
    </style>
</head>

<body>
   <main>
      <!-- 🖼️ 全屏背景容器 -->
      <div class="login-page-bg"></div>

      <div class="login-container">
         <div class="glass-card">
            <!-- Logo区域 -->
            <div class="text-center mb-4">
               <a href="<?php echo get_option('home'); ?>" class="d-inline-block transition-hover">
                   <?php boxmoe_logo(); ?>
               </a>
               <h3 class="mt-3 mb-1 fw-bold">欢迎加入</h3>
               <p class="text-muted small mb-0">
                  如果你已经注册了账号，可以点击
                  <a href="<?php echo boxmoe_sign_in_link_page(); ?>" class="text-primary fw-bold text-decoration-none">登录</a>
               </p>
            </div>

            <!-- 注册表单 -->
            <form class="needs-validation mb-3" id="signupform" novalidate="">
                <!-- 用户名 -->
               <div class="mb-3 floating-label-group">
                  <input type="text" class="form-control" name="username" id="signupFullnameInput" required="" placeholder=" ">
                  <label for="signupFullnameInput" data-default="设置一个用户名" data-active="用户名"></label>
                  <div class="invalid-feedback">请输入用户名。</div>
               </div>
               
               <!-- 邮箱 -->
               <div class="mb-3 floating-label-group">
                  <input type="email" class="form-control" name="email" id="signupEmailInput" required="" placeholder=" ">
                  <label for="signupEmailInput" data-default="设置邮箱，用于接收验证码" data-active="邮箱"></label>
                  <div class="invalid-feedback">请输入邮箱。</div>
               </div>

               <!-- 验证码 -->
               <div class="mb-3">
                  <div class="d-flex gap-2">
                     <div class="flex-grow-1 floating-label-group mb-0 position-relative">
                         <input type="text" class="form-control" name="verificationcode" id="signupVerificationCode" required="" placeholder=" ">
                         <label for="signupVerificationCode" data-default="输入验证码" data-active="验证码"></label>
                         <div class="invalid-feedback">请输入验证码。</div>
                     </div>
                     <button type="button" class="btn btn-primary text-nowrap" id="sendVerificationCode" style="min-width: 110px; height: 3.5rem;">获取验证码</button>
                  </div>
               </div>

               <!-- 密码 -->
               <div class="mb-3 position-relative floating-label-group">
                  <div class="password-field">
                     <input type="password" class="form-control fakePassword" name="password" id="formSignUpPassword" required="" placeholder=" ">
                     <label for="formSignUpPassword" data-default="设置密码" data-active="密码"></label>
                     <i class="bi bi-eye-slash passwordToggler"></i>
                  </div>
                  <div class="invalid-feedback">请输入密码。</div>
               </div>

               <!-- 确认密码 -->
               <div class="mb-3 position-relative floating-label-group">
                  <div class="password-field">
                     <input type="password" class="form-control fakePassword" name="confirmpassword" id="formSignUpConfirmPassword" required="" placeholder=" ">
                     <label for="formSignUpConfirmPassword" data-default="再次输入密码" data-active="确认密码"></label>
                     <i class="bi bi-eye-slash passwordToggler"></i>
                  </div>
                  <div class="invalid-feedback">请确认密码。</div>
               </div>

               <!-- 安全验证 -->
               <input type="hidden" name="signup_nonce" value="<?php echo wp_create_nonce('user_signup'); ?>">

               <!-- 注册按钮 -->
               <div class="d-grid mt-4">
                  <button class="btn btn-primary" type="submit" name="signup_submit">
                     <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                     <span class="btn-text">立即注册</span>
                  </button>
               </div>
               <div id="signup-message"></div>
            </form>

            <!-- 底部版权 -->
            <div class="text-center mt-4 pt-3 border-top border-light">
               <div class="small text-body-tertiary">
                  Copyright © <?php echo date('Y'); ?> 
                  <span class="text-primary"><a href="<?php echo get_option('home'); ?>" class="text-reset text-decoration-none fw-bold"><?php echo get_bloginfo('name'); ?></a></span>
                  <br> Theme by
                  <span class="text-primary"><a href="https://www.boxmoe.com" class="text-reset text-decoration-none fw-bold">Boxmoe</a></span> powered by WordPress
               </div>
            </div>
         </div>
      </div>

      <!-- 🛠️ 主题切换按钮 -->
      <div class="position-absolute start-0 bottom-0 m-4">
         <div class="dropdown">
            <button
                    class="float-btn bd-theme btn btn-light btn-icon rounded-circle d-flex align-items-center shadow-sm"
                    type="button"
                    aria-expanded="false"
                    data-bs-toggle="dropdown"
                    aria-label="Toggle theme (auto)">
                    <i class="fa fa-adjust"></i>
                    <span class="visually-hidden bs-theme-text">主题颜色切换</span>
            </button>
            <ul class="bs-theme dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                        <i class="fa fa-sun-o"></i>
                        <span class="ms-2">亮色</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                        <i class="fa fa-moon-o"></i>
                        <span class="ms-2">暗色</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                        <i class="fa fa-adjust"></i>
                        <span class="ms-2">跟随系统</span>
                    </button>
                </li>
            </ul>
         </div>
      </div>
   </main>
   <?php 
    ob_start();
    wp_footer();
    $wp_footer_output = ob_get_clean();
    echo preg_replace('/\n/', "\n    ", trim($wp_footer_output))."\n    ";
    ?>
    <script>
      // 🔗 注册相关JS功能
      document.addEventListener('DOMContentLoaded', function() {
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
              var spinner = btn.querySelector('.spinner-border');
              var btnText = btn.querySelector('.btn-text');
              
              btn.disabled = true;
              spinner.classList.remove('d-none');
              btnText.textContent = '注册中...';
              
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
                      spinner.classList.add('d-none');
                      btnText.textContent = '立即注册';
                  }
              })
              .catch(err => {
                  document.getElementById('signup-message').innerHTML = '<div class="alert alert-danger mt-3">网络错误，请重试</div>';
                  btn.disabled = false;
                  spinner.classList.add('d-none');
                  btnText.textContent = '立即注册';
              });
          });
      });
    </script>
    <!-- 🌌 引入粒子效果脚本 -->
    <script src="<?php echo get_template_directory_uri(); ?>/assets/js/login-particles.js"></script>
</body></html>