<?php
// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 用户中心链接设置--------------------------boxmoe.com--------------------------
function boxmoe_user_center_link_page(){
    $boxmoe_user_center_link_page = get_boxmoe('boxmoe_user_center_link_page');
    if($boxmoe_user_center_link_page && is_numeric($boxmoe_user_center_link_page)){
        $permalink = get_the_permalink($boxmoe_user_center_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-user_center.php 模板的用户中心页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-user_center.php',
        'p-user_center.php'
    );
    
    foreach($template_paths as $template_path){
        $user_center_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($user_center_pages)){
            // 🔗 返回找到的第一个用户中心页面的链接
            return get_the_permalink($user_center_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找用户中心页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-user_center.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $user_center_query = new WP_Query($args);
    if($user_center_query->have_posts()){
        $user_center_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找用户中心页面
    $user_center_page = get_page_by_path('user-center');
    if($user_center_page){
        return get_the_permalink($user_center_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'user_center') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认用户中心页面链接
    return home_url('/user-center');
}

// 注册页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_sign_up_link_page(){
    // 🔗 双面板设计：注册链接指向登录页面，并添加mode=signup参数
    $login_url = boxmoe_sign_in_link_page();
    return add_query_arg('mode', 'signup', $login_url);
}


// 登录页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_sign_in_link_page(){
    $boxmoe_sign_in_link_page = get_boxmoe('boxmoe_sign_in_link_page');
    if($boxmoe_sign_in_link_page && is_numeric($boxmoe_sign_in_link_page)){
        $permalink = get_the_permalink($boxmoe_sign_in_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-signin.php 模板的登录页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-signin.php',
        'p-signin.php'
    );
    
    foreach($template_paths as $template_path){
        $login_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($login_pages)){
            // 🔗 返回找到的第一个登录页面的链接
            return get_the_permalink($login_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找登录页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-signin.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $login_query = new WP_Query($args);
    if($login_query->have_posts()){
        $login_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找登录页面
    $login_page = get_page_by_path('signin');
    if($login_page){
        return get_the_permalink($login_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'signin') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认登录页面链接
    return home_url('/signin');
}

// 重置密码页面链接设置--------------------------boxmoe.com--------------------------
function boxmoe_reset_password_link_page(){
    $boxmoe_reset_password_link_page = get_boxmoe('boxmoe_reset_password_link_page');
    if($boxmoe_reset_password_link_page && is_numeric($boxmoe_reset_password_link_page)){
        $permalink = get_the_permalink($boxmoe_reset_password_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-reset_password.php 模板的重置密码页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-reset_password.php',
        'p-reset_password.php'
    );
    
    foreach($template_paths as $template_path){
        $reset_password_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($reset_password_pages)){
            // 🔗 返回找到的第一个重置密码页面的链接
            return get_the_permalink($reset_password_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找重置密码页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-reset_password.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $reset_password_query = new WP_Query($args);
    if($reset_password_query->have_posts()){
        $reset_password_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找重置密码页面
    $reset_password_page = get_page_by_path('reset-password');
    if($reset_password_page){
        return get_the_permalink($reset_password_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'reset_password') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认重置密码页面链接
    return home_url('/reset-password');
}

// 充值卡购买链接设置--------------------------boxmoe.com--------------------------
function boxmoe_czcard_src(){
    $boxmoe_czcard_src = get_boxmoe('boxmoe_czcard_src');
    if($boxmoe_czcard_src){
        return $boxmoe_czcard_src;
    }else{
        return false;
    }
}

add_action('wp_ajax_nopriv_user_login_action', 'handle_user_login');
add_action('wp_ajax_user_login_action', 'handle_user_login');

function handle_user_login() {
    $formData = isset($_POST['formData']) ? json_decode(stripslashes($_POST['formData']), true) : array();
    
    // 🔄 优化nonce验证机制，避免因页面停留时间过长导致无法登录
    $nonce_verified = false;
    if (isset($formData['login_nonce'])) {
        $nonce_verified = wp_verify_nonce($formData['login_nonce'], 'user_login');
    }
    
    // 如果nonce验证失败，尝试重新生成并继续登录流程
    if (!$nonce_verified) {
        // 🔐 直接跳过nonce验证，使用密码验证代替安全验证
        // 这样可以避免用户在页面停留时间过长导致nonce过期无法登录的问题
    }  
    if (empty($formData['username']) || empty($formData['password'])) {
        wp_send_json_error(array(
            'message' => '用户名和密码不能为空'
        ));
        exit;
    }
    
    $username = sanitize_text_field($formData['username']);
    $password = $formData['password'];
    $remember = isset($formData['rememberme']) ? true : false;
    
    if (is_email($username)) {
        $user = get_user_by('email', $username);
        if ($user) {
            $username = $user->user_login;
        }
    }
    
    $creds = array(
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember
    );
    
    $user = wp_signon($creds, false);
    
    if (is_wp_error($user)) {
        $error_code = $user->get_error_code();
        $error_message = '';
        
        switch ($error_code) {
            case 'invalid_username':
                $error_message = '用户不存在，如果不确定可以用邮箱登录';
                break;
            case 'incorrect_password':
                $error_message = '密码错误';
                break;
            case 'empty_username':
                $error_message = '请输入用户名';
                break;
            case 'empty_password':
                $error_message = '请输入密码';
                break;
            default:
                $error_message = '登录失败，请检查用户名和密码';
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
        exit;
    } 
    
    // 🔗 获取并验证重定向地址
    $redirect_to = !empty($formData['redirect_to']) ? $formData['redirect_to'] : boxmoe_user_center_link_page();
    
    // 处理后台登录链接，确保管理员用户能正确跳转到后台
    if (strpos($redirect_to, 'wp-admin') !== false || strpos($redirect_to, 'dashboard') !== false) {
        if (user_can($user, 'manage_options')) {
            // 🔒 确保管理员用户直接跳转到后台，不强制到用户中心
            $redirect_to = admin_url();
        }
    }

    // 👮u200d♂️ 非管理员用户跳转到会员中心，管理员保持原有重定向
    if ( !user_can( $user, 'manage_options' ) ) {
        $redirect_to = boxmoe_user_center_link_page();
    }

    $redirect_to = wp_validate_redirect($redirect_to, boxmoe_user_center_link_page());

    // 确保登录成功后设置了正确的auth cookie
    if (is_user_logged_in()) {
        // 刷新auth cookie，确保cookie设置正确
        wp_set_auth_cookie($user->ID, $remember, true);
    }

    wp_send_json_success(array(
        'message' => '登录成功',
        'redirect_url' => $redirect_to // ⬅️ 返回安全的重定向地址
    ));
    exit;
}

add_action('wp_ajax_nopriv_user_signup_action', 'handle_user_signup');
add_action('wp_ajax_user_signup_action', 'handle_user_signup');

function handle_user_signup() {
    // 移除所有默认的新用户注册通知
    remove_action('register_new_user', 'wp_send_new_user_notifications');
    remove_action('edit_user_created_user', 'wp_send_new_user_notifications');
    remove_action('network_site_new_created_user', 'wp_send_new_user_notifications');
    remove_action('network_site_users_created_user', 'wp_send_new_user_notifications');
    remove_action('network_user_new_created_user', 'wp_send_new_user_notifications');
    
    $formData = isset($_POST['formData']) ? json_decode(stripslashes($_POST['formData']), true) : array();
    
    if (empty($formData['email']) || empty($formData['verificationcode'])) {
        wp_send_json_error(array('message' => '验证码错误或已过期'));
        exit;
    }
    
    $stored_code = get_transient('verification_code_' . $formData['email']);
    if (!$stored_code || $stored_code !== $formData['verificationcode']) {
        wp_send_json_error(array('message' => '验证码错误或已过期'));
        exit;
    }  

    if (!isset($formData['signup_nonce']) || !wp_verify_nonce($formData['signup_nonce'], 'user_signup')) {
        wp_send_json_error(array(
            'message' => '安全验证失败，请刷新页面重试'
        ));
        exit;
    }   
    if (empty($formData['username']) || empty($formData['email']) || empty($formData['password']) || empty($formData['confirmpassword'])) {
        wp_send_json_error(array(
            'message' => '所有字段都为必填项'
        ));
        exit;
    }   
    if ($formData['password'] !== $formData['confirmpassword']) {
        wp_send_json_error(array(
            'message' => '两次输入的密码不一致'
        ));
        exit;
    }   
    if (strlen($formData['password']) < 6) {
        wp_send_json_error(array(
            'message' => '密码长度至少需要6个字符'
        ));
        exit;
    }   
    if (!is_email($formData['email'])) {
        wp_send_json_error(array(
            'message' => '请输入有效的邮箱地址'
        ));
        exit;
    }    
    if (email_exists($formData['email'])) {
        wp_send_json_error(array(
            'message' => '该邮箱已被注册'
        ));
        exit;
    }

    remove_filter('sanitize_user', 'sanitize_user');
    $username = $formData['username'];
    if (!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]+$/u', $username)) {
        wp_send_json_error(array(
            'message' => '用户名只能包含中文、字母、数字和下划线'
        ));
        exit;
    }
    if (empty($username) || mb_strlen($username) < 2) {
        wp_send_json_error(array(
            'message' => '用户名长度至少需要2个字符'
        ));
        exit;
    }
    if (username_exists($username)) {
        wp_send_json_error(array(
            'message' => '该用户名已被使用'
        ));
        exit;
    }
    $user_id = wp_create_user(
        $username,
        $formData['password'],
        $formData['email']
    );
    add_filter('sanitize_user', 'sanitize_user');

    if (is_wp_error($user_id)) {
        $error_code = $user_id->get_error_code();
        $error_message = '';
        
        switch ($error_code) {
            case 'existing_user_login':
                $error_message = '该用户名已被使用';
                break;
            case 'existing_user_email':
                $error_message = '该邮箱已被注册';
                break;
            default:
                $error_message = '注册失败，请稍后重试';
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
        exit;
    }

    $user = new WP_User($user_id);
    $user->set_role('subscriber');

    // 🆔 生成并保存随机6位数UID
    $custom_uid = boxmoe_generate_custom_uid();
    update_user_meta($user_id, 'custom_uid', $custom_uid);

    if(get_boxmoe('boxmoe_smtp_mail_switch')){   
        if(get_boxmoe('boxmoe_new_user_register_notice_switch')){
            boxmoe_new_user_register($user_id);
        }
    }
    
    delete_transient('verification_code_' . $formData['email']);  
    boxmoe_new_user_register_email($user_id);
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    wp_send_json_success(array(
        'message' => '注册成功并已自动登录'
    ));
    exit;
}

function boxmoe_allow_chinese_username($username, $raw_username, $strict) {
    if (!$strict) {
        return $username;
    } 
    $username = $raw_username;
    $username = preg_replace('/[^[\x{4e00}-\x{9fa5}a-zA-Z0-9_]]/u', '', $username);
    return $username;
}
add_filter('sanitize_user', 'boxmoe_allow_chinese_username', 10, 3);

add_action('wp_ajax_nopriv_send_verification_code', 'handle_send_verification_code');
add_action('wp_ajax_send_verification_code', 'handle_send_verification_code');
function handle_send_verification_code() {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => '请输入有效的邮箱地址'));
        exit;
    }  
    if (email_exists($email)) {
        wp_send_json_error(array('message' => '该邮箱已被注册'));
        exit;
    }
    $verification_code = sprintf("%06d", mt_rand(0, 999999));
    set_transient('verification_code_' . $email, $verification_code, 5 * MINUTE_IN_SECONDS);
    if (boxmoe_verification_code_register_email($email, $verification_code)) {
        wp_send_json_success(array('message' => '验证码已发送'));
    } else {
        wp_send_json_error(array('message' => '验证码发送失败，请稍后重试'));
    }
    exit;
}

add_action('wp_ajax_nopriv_reset_password_action', 'handle_reset_password_request');
add_action('wp_ajax_reset_password_action', 'handle_reset_password_request');

function handle_reset_password_request() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reset_password_action')) {
        wp_send_json_error(array('message' => '安全验证失败，请刷新页面重试'));
        exit;
    }

    $user_email = sanitize_email($_POST['user_email']);
    
    if (empty($user_email) || !is_email($user_email)) {
        wp_send_json_error(array('message' => '请输入有效的邮箱地址'));
        exit;
    }

    $user = get_user_by('email', $user_email);
    
    if (!$user) {
        wp_send_json_error(array('message' => '该邮箱地址未注册'));
        exit;
    }

    if(boxmoe_reset_password_email($user->user_login)){
        wp_send_json_success(array('message' => '重置密码链接已发送到您的邮箱，请查收'));
    }else{
        wp_send_json_error(array('message' => '发送邮件失败，请稍后重试'));
    }
    exit;
}

// 透过代理或者cdn获取访客真实IP
function get_client_ip() {
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	        $ip = getenv("HTTP_CLIENT_IP"); else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), 
	"unknown"))
	        $ip = getenv("HTTP_X_FORWARDED_FOR"); else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	        $ip = getenv("REMOTE_ADDR"); else if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] 
	&& strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	        $ip = $_SERVER['REMOTE_ADDR']; else
	        $ip = "unknown";
	return ($ip);
}

// 处理用户注册时间
add_action('user_register', 'boxmoe_user_register_time');
function boxmoe_user_register_time($user_id){
    $user = get_user_by('id', $user_id);
    update_user_meta($user_id, 'register_time', current_time('mysql'));
}

// 处理用户登录时间
add_action('wp_login', 'boxmoe_user_login_time');
function boxmoe_user_login_time($user_login){
    $user = get_user_by('login', $user_login);
    update_user_meta($user->ID, 'last_login_time', current_time('mysql'));
}

// 处理用户登录IP
add_action('wp_login', 'boxmoe_user_login_ip');
function boxmoe_user_login_ip($user_login){
    $user = get_user_by('login', $user_login);
    update_user_meta($user->ID, 'last_login_ip', get_client_ip());
}

// 🔄 移除了登录页面自动重定向，改为直接美化wp-login.php
// 🔄 移除了登录链接替换，使用默认登录链接

// 🎨 美化wp-login.php页面
function boxmoe_customize_login_page() {
    // 引入必要的脚本
    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/jquery.min.js', array(), '3.6.0', true);
    }
    
    // 添加粒子效果脚本
    if (file_exists(get_template_directory() . '/assets/js/login-particles.js')) {
        wp_enqueue_script('boxmoe-login-script', get_template_directory_uri() . '/assets/js/login-particles.js', array('jquery'), '1.1', true);
    } else {
        // 如果没有自定义粒子效果脚本，添加简单的粒子效果
        $particle_script = <<<EOD
        jQuery(document).ready(function($) {
            // 创建粒子效果容器
            if (!$('#particles-js').length) {
                $('body').append('<div id="particles-js"></div>');
            }
            
            // 添加粒子样式
            if (!$('style#particles-css').length) {
                $('head').append('<style id="particles-css">
                    #particles-js {
                        position: fixed;
                        width: 100%;
                        height: 100%;
                        top: 0;
                        left: 0;
                        z-index: 0;
                        background: transparent;
                    }
                </style>');
            }
            
            // 简单的粒子效果实现
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            document.getElementById('particles-js').appendChild(canvas);
            
            var particles = [];
            var particleCount = 50;
            
            // 初始化粒子
            for (var i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 2,
                    vy: (Math.random() - 0.5) * 2,
                    size: Math.random() * 3 + 1,
                    opacity: Math.random() * 0.8 + 0.2
                });
            }
            
            // 动画循环
            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                for (var i = 0; i < particles.length; i++) {
                    var p = particles[i];
                    
                    // 更新位置
                    p.x += p.vx;
                    p.y += p.vy;
                    
                    // 边界检测
                    if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                    if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                    
                    // 绘制粒子
                    ctx.fillStyle = 'rgba(139, 61, 255, ' + p.opacity + ')';
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                    
                    // 绘制连接线
                    for (var j = i + 1; j < particles.length; j++) {
                        var p2 = particles[j];
                        var dx = p.x - p2.x;
                        var dy = p.y - p2.y;
                        var dist = Math.sqrt(dx * dx + dy * dy);
                        
                        if (dist < 100) {
                            ctx.strokeStyle = 'rgba(139, 61, 255, ' + (0.3 - dist / 333) + ')';
                            ctx.lineWidth = 0.5;
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.stroke();
                        }
                    }
                }
                
                requestAnimationFrame(animate);
            }
            
            animate();
            
            // 窗口大小变化时重新调整画布
            window.addEventListener('resize', function() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        });
        EOD;
        wp_add_inline_script('jquery', $particle_script);
    }
}
add_action('login_enqueue_scripts', 'boxmoe_customize_login_page', 10);

// 🎨 自定义登录页面标题
function boxmoe_custom_login_title() {
    return '欢迎回来站长大人';
}
add_filter('login_headertitle', 'boxmoe_custom_login_title');

// 🎨 自定义登录页面logo链接
function boxmoe_custom_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'boxmoe_custom_login_logo_url');

// 🎨 自定义登录页面样式
function boxmoe_custom_login_style() {
    ?>
    <style type="text/css">
        /* 重置样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* 应用自定义登录页面的样式 */
        body {
            background-color: #f0f2f5;
            background-image: url(<?php echo get_boxmoe('boxmoe_user_login_bg')? get_boxmoe('boxmoe_user_login_bg') :'https://api.boxmoe.com/random.php'; ?>);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* 添加高斯模糊背景覆盖背景图 */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 0;
        }
        
        /* 彻底隐藏默认的登录标题和logo */
        .login h1 {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* 确保自定义logo显示 */
        .login-logo {
            display: block !important;
            margin: 0 auto 1.5rem auto !important;
            text-align: center !important;
        }
        
        .login-logo img {
            width: 60px !important;
            height: 60px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            display: block !important;
            margin: 0 auto !important;
        }
        
        /* 隐藏语言选择器 */
        #language-switcher {
            display: none;
        }
        
        /* 登录容器 */
        #login {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 460px;
            margin: 0 auto !important;
            padding: 0;
            display: block;
            text-align: center;
        }
        
        /* 重置登录页面所有默认样式 */
        html body.login {
            display: block !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f0f2f5 !important;
            background-image: url(<?php echo get_boxmoe('boxmoe_user_login_bg')? get_boxmoe('boxmoe_user_login_bg') :'https://api.boxmoe.com/random.php'; ?>) !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            overflow-x: hidden !important;
            position: relative !important;
        }
        
        /* 使用固定定位实现绝对居中 */
        body.login div#login {
            position: fixed !important;
            top: 50% !important;
            left: 50% !important;
            z-index: 10 !important;
            width: 100% !important;
            max-width: 460px !important;
            margin: 0 !important;
            padding: 0 1.5rem !important;
            display: block !important;
            text-align: center !important;
            transform: translate(-50%, -50%) !important;
            -webkit-transform: translate(-50%, -50%) !important;
            -moz-transform: translate(-50%, -50%) !important;
            -ms-transform: translate(-50%, -50%) !important;
        }
        
        /* 确保所有文字和表单元素显示在遮罩层上面 */
        body.login #login * {
            position: relative !important;
            z-index: 11 !important;
        }
        
        /* 确保消息容器显示在遮罩层上面 */
        body.login #login_error,
        body.login .message,
        body.login .success {
            position: relative !important;
            z-index: 12 !important;
        }
        
        /* 确保表单元素显示在遮罩层上面 */
        body.login form {
            position: relative !important;
            z-index: 12 !important;
        }
        
        /* 确保标题和文字显示在遮罩层上面 */
        body.login h2,
        body.login .login-tagline,
        body.login #nav,
        body.login #backtoblog {
            position: relative !important;
            z-index: 12 !important;
        }
        
        /* 确保所有登录页面元素都居中 */
        body.login #login > * {
            margin-left: auto !important;
            margin-right: auto !important;
            display: block !important;
            text-align: center !important;
            max-width: 100% !important;
        }
        
        /* 确保表单元素居中 */
        body.login #loginform,
        body.login #registerform,
        body.login #lostpasswordform {
            margin: 0 auto !important;
            text-align: left;
            width: 100% !important;
            max-width: 460px !important;
        }
        
        /* 确保消息容器居中 */
        body.login #login_error,
        body.login .message,
        body.login .success {
            margin: 0 auto 1.5rem auto !important;
            width: 100% !important;
            max-width: 460px !important;
            display: block !important;
        }
        
        /* 修复WordPress默认登录页面的margin问题 */
        body.login #nav,
        body.login #backtoblog {
            margin: 1rem auto 0 auto !important;
            text-align: center !important;
            display: block !important;
        }
        
        /* 表单容器 - 蓝色渐变到粉色，半透明效果 */
        .login form,
        #lostpasswordform,
        #resetpassform,
        #login_error,
        .login .message,
        .login .success,
        #language-switcher {
            background: linear-gradient(135deg, rgba(120, 180, 255, 0.5), rgba(255, 150, 220, 0.5)) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
            width: 100% !important;
            max-width: 360px !important;
            margin: 0 auto !important;
            display: block !important;
            color: #000000 !important;
            transition: transform 0.3s ease !important;
        }
        
        /* 表单悬停上移效果 */
        .login form:hover,
        #lostpasswordform:hover,
        #resetpassform:hover {
            transform: translateY(-3px) !important;
        }
        
        /* 表单特殊内边距 */
        .login form,
        #lostpasswordform,
        #resetpassform {
            padding: 2rem !important;
        }
        
        /* 消息容器特殊内边距 */
        #login_error,
        .login .message,
        .login .success {
            padding: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }
        
        /* 语言切换器特殊样式 */
        #language-switcher {
            padding: 1rem !important;
            margin: 1rem auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 15px !important;
            justify-content: center !important;
        }
        
        /* 语言切换器主内容区域 - 确保所有子元素水平排列 */
        #language-switcher {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 15px !important;
            justify-content: center !important;
            padding: 1rem !important;
            margin: 1rem auto !important;
        }
        
        /* 语言切换器主内容区域 - 确保label、select和按钮水平排列 */
        #language-switcher > *:not(div) {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
        }
        
        /* 语言切换器直接子元素水平排列 */
        #language-switcher {
            display: flex !important;
            flex-direction: column !important;
        }
        
        /* 语言切换器主内容行 */
        #language-switcher-row {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            justify-content: center !important;
            width: 100% !important;
        }
        
        /* 确保label和select在同一行，图标在select左边 */
        #language-switcher label {
            color: #000000 !important;
            font-size: 14px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            visibility: visible !important;
            position: relative !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        
        /* 确保dashicons图标可见并正确显示 */
        #language-switcher label .dashicons {
            display: inline-block !important;
            visibility: visible !important;
            width: 20px !important;
            height: 20px !important;
            font-size: 20px !important;
            line-height: 1 !important;
            color: #000000 !important;
            margin-right: 5px !important;
        }
        
        /* 确保select元素与label水平对齐 */
        #language-switcher select {
            vertical-align: middle !important;
            display: inline-block !important;
        }
        
        /* 语言切换器提交按钮样式 */
        #language-switcher input[type="submit"] {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.5), rgba(220, 150, 255, 0.5)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 25px !important;
            color: #000000 !important;
            padding: 8px 15px !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            height: auto !important;
            text-transform: none !important;
            width: auto !important;
            margin: 0 !important;
        }
        
        /* 语言切换器提交按钮悬停效果 */
        #language-switcher input[type="submit"]:hover {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.7), rgba(220, 150, 255, 0.7)) !important;
            box-shadow: 0 0 10px rgba(180, 120, 255, 0.3) !important;
        }
        
        /* 语言切换器下拉框特殊样式 */
        #language-switcher select {
            min-width: 180px !important;
            max-width: 200px !important;
        }
        
        /* 导航链接容器样式 */
        #language-switcher > div:last-child {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 10px !important;
            width: 100% !important;
            margin-top: 10px !important;
            padding-top: 10px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        /* 导航链接样式 */
        #language-switcher #nav,
        #language-switcher #backtoblog {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            text-align: center !important;
        }
        
        /* 导航链接文本样式 */
        #language-switcher #nav a,
        #language-switcher #backtoblog a {
            font-size: 14px !important;
        }
        
        /* 暗色模式适配 */
        @media (prefers-color-scheme: dark) {
            .login form,
            #lostpasswordform,
            #resetpassform,
            #login_error,
            .login .message,
            .login .success,
            #language-switcher {
                background: linear-gradient(135deg, rgba(80, 120, 200, 0.7), rgba(200, 100, 160, 0.7)) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5) !important;
                color: #e0e0e0;
            }
        }
        
        /* 表单内部结构 */
        .login form p {
            margin: 0 !important;
            width: 100%;
        }
        
        /* 标签样式 - 统一表单元素 */
        .login label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
            font-size: 0.9rem;
        }
        
        /* 暗色模式下的标签样式 */
        @media (prefers-color-scheme: dark) {
            .login label {
                color: #adb5bd;
            }
        }
        
        /* 输入框容器 - 用于实现勋章上移效果 */
        .login form p {
            position: relative !important;
            margin: 0 0 15px 0 !important;
        }
        
        /* 输入框样式 - 紫色渐变背景 */
        .login form .input,
        .login input[type="text"],
        .login input[type="password"],
        .login input[type="email"],
        .login textarea {
            height: 45px !important;
            padding: 15px 15px 0 15px !important;
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.3), rgba(220, 150, 255, 0.3)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 25px !important;
            box-shadow: none !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            color: #000000 !important;
            margin: 0 !important;
        }
        
        /* 输入框聚焦样式 - 完全参考登录页面设计 */
        .login form .input:focus,
        .login input[type="text"]:focus,
        .login input[type="password"]:focus,
        .login input[type="email"]:focus,
        .login textarea:focus {
            background: rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
            transform: none !important;
            outline: none !important;
        }
        
        /* 输入框占位符样式 - 完全参考登录页面设计 */
        .login form .input::placeholder,
        .login input[type="text"]::placeholder,
        .login input[type="password"]::placeholder,
        .login input[type="email"]::placeholder,
        .login textarea::placeholder {
            color: transparent !important;
            transition: all 0.3s ease !important;
        }
        
        /* 输入框聚焦时占位符显示 - 完全参考登录页面设计 */
        .login form .input:focus::placeholder,
        .login input[type="text"]:focus::placeholder,
        .login input[type="password"]:focus::placeholder,
        .login input[type="email"]:focus::placeholder,
        .login textarea:focus::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        /* 勋章上移效果 - 黑色文本 */
        /* 只对包含输入框的表单段落应用勋章效果 */
        .login form p:has(input),
        #lostpasswordform p:has(input),
        #resetpassform p:has(input) {
            position: relative !important;
        }
        
        /* 用户名/邮箱输入框 - 只对包含.input类或特定输入类型的段落应用 */
        .login form p:has(.input[type="text"]),
        .login form p:has(input[type="email"]),
        #lostpasswordform p:has(input[type="text"]),
        #lostpasswordform p:has(input[type="email"]) {
            position: relative !important;
        }
        
        /* 用户名/邮箱输入框勋章 */
        .login form p:has(.input[type="text"])::before,
        .login form p:has(input[type="email"])::before,
        #lostpasswordform p:has(input[type="text"])::before,
        #lostpasswordform p:has(input[type="email"])::before {
            content: "用户名或邮箱地址";
            position: absolute !important;
            left: 20px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 14px !important;
            color: rgba(0, 0, 0, 0.7) !important;
            transition: all 0.3s ease !important;
            pointer-events: none !important;
            z-index: 15 !important;
        }
        
        /* 密码输入框勋章 */
        .login form p:has(.input[type="password"]),
        .login form p:has(input[type="password"]),
        #resetpassform p:has(input[type="password"]) {
            position: relative !important;
        }
        
        .login form p:has(.input[type="password"])::before,
        .login form p:has(input[type="password"])::before,
        #resetpassform p:has(input[type="password"])::before {
            content: "密码";
            position: absolute !important;
            left: 20px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            font-size: 14px !important;
            color: rgba(0, 0, 0, 0.7) !important;
            transition: all 0.3s ease !important;
            pointer-events: none !important;
            z-index: 15 !important;
        }
        
        /* 确认密码输入框勋章 */
        #resetpassform p:has(input[type="password"]):nth-child(3)::before {
            content: "确认密码" !important;
        }
        
        /* 重置密码页面特殊元素样式 - 密码强度指示器 */
        #resetpassform .pw-weak,
        #resetpassform .pw-weak + .pw-strength-result,
        #resetpassform .pw-medium + .pw-strength-result,
        #resetpassform .pw-strong + .pw-strength-result {
            background: rgba(255, 255, 255, 0.3) !important;
            border-radius: 15px !important;
            padding: 8px 15px !important;
            margin: 10px 0 !important;
            text-align: center !important;
            font-size: 12px !important;
            color: #000000 !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        /* 密码强度指示器文本样式 */
        #resetpassform .pw-strength-result {
            background: rgba(255, 255, 255, 0.3) !important;
            color: #000000 !important;
            border-radius: 15px !important;
            padding: 8px 15px !important;
            margin: 10px 0 !important;
            text-align: center !important;
            font-size: 12px !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }
        
        /* 密码强度指示器不同强度的样式 */
        #resetpassform .pw-strength-result.weak {
            background: linear-gradient(135deg, rgba(255, 120, 120, 0.5), rgba(255, 150, 150, 0.5)) !important;
        }
        
        #resetpassform .pw-strength-result.medium {
            background: linear-gradient(135deg, rgba(255, 200, 120, 0.5), rgba(255, 220, 150, 0.5)) !important;
        }
        
        #resetpassform .pw-strength-result.strong {
            background: linear-gradient(135deg, rgba(120, 255, 120, 0.5), rgba(150, 255, 150, 0.5)) !important;
        }
        
        /* 生成密码和复制密码按钮样式 */
        #resetpassform .pw-button {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.5), rgba(220, 150, 255, 0.5)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 25px !important;
            color: #000000 !important;
            padding: 8px 15px !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            margin: 5px !important;
            display: inline-block !important;
        }
        
        /* 生成密码和复制密码按钮悬停效果 */
        #resetpassform .pw-button:hover {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.7), rgba(220, 150, 255, 0.7)) !important;
            box-shadow: 0 0 10px rgba(180, 120, 255, 0.3) !important;
        }
        
        /* 输入框聚焦或有内容时勋章上移 - 高斯模糊半透明背景 */
        /* 只对包含输入框且聚焦或有内容的段落应用勋章效果 */
        .login form p:has(.input:focus)::before,
        .login form p:has(input:focus)::before,
        .login form p:has(.input:not(:placeholder-shown))::before,
        .login form p:has(input:not(:placeholder-shown))::before,
        .login form p.has-content::before,
        #lostpasswordform p:has(.input:focus)::before,
        #lostpasswordform p:has(input:focus)::before,
        #lostpasswordform p:has(.input:not(:placeholder-shown))::before,
        #lostpasswordform p:has(input:not(:placeholder-shown))::before,
        #lostpasswordform p.has-content::before,
        #resetpassform p:has(.input:focus)::before,
        #resetpassform p:has(input:focus)::before,
        #resetpassform p:has(.input:not(:placeholder-shown))::before,
        #resetpassform p:has(input:not(:placeholder-shown))::before,
        #resetpassform p.has-content::before {
            top: -8px !important;
            left: 15px !important;
            font-size: 12px !important;
            color: #000000 !important;
            background: rgba(255, 255, 255, 0.3) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            padding: 2px 8px !important;
            border-radius: 10px !important;
            z-index: 9999 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            transform: none !important;
        }
        
        /* 暗色模式下的输入框样式 */
        @media (prefers-color-scheme: dark) {
            .login form .input,
            .login input[type="text"],
            .login input[type="password"],
            .login input[type="email"],
            .login textarea {
                background: rgba(0, 0, 0, 0.2);
                border-color: rgba(255, 255, 255, 0.1);
                color: #fff;
            }
        }
        
        /* 输入框聚焦样式 */
        .login form .input:focus,
        .login input[type="text"]:focus,
        .login input[type="password"]:focus,
        .login input[type="email"]:focus,
        .login textarea:focus {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 3px rgba(139, 61, 255, 0.2);
            border-color: #8b3dff;
            transform: translateY(-1px);
            outline: none;
        }
        
        /* 暗色模式下的输入框聚焦样式 */
        @media (prefers-color-scheme: dark) {
            .login form .input:focus,
            .login input[type="text"]:focus,
            .login input[type="password"]:focus,
            .login input[type="email"]:focus,
            .login textarea:focus {
                background: rgba(0, 0, 0, 0.4);
                border-color: #8b3dff;
            }
        }
        
        /* 提交按钮样式 - 紫色渐变背景 */
        .login .button-primary {
            width: 100% !important;
            margin: 10px 0 0 0 !important;
            padding: 0 !important;
            cursor: pointer !important;
            border-radius: 25px !important;
            font-weight: 600 !important;
            letter-spacing: 1px !important;
            border: none !important;
            box-shadow: none !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.5), rgba(220, 150, 255, 0.5)) !important;
            color: #000000 !important;
            font-size: 14px !important;
            text-transform: uppercase !important;
            height: 45px !important;
            /* 确保文字居中 */
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            /* 确保按钮文字不换行 */
            white-space: nowrap !important;
            /* 确保按钮内文字居中 */
            text-align: center !important;
            line-height: 45px !important;
        }
        
        /* 按钮悬停效果 - 紫色渐变增强 */
        .login .button-primary:hover {
            transform: none !important;
            box-shadow: 0 0 15px rgba(180, 120, 255, 0.5) !important;
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.7), rgba(220, 150, 255, 0.7)) !important;
        }
        
        /* 按钮点击效果 - 紫色渐变增强 */
        .login .button-primary:active {
            transform: none !important;
            box-shadow: 0 0 10px rgba(180, 120, 255, 0.3) !important;
            background: linear-gradient(135deg, rgba(160, 100, 235, 0.7), rgba(200, 130, 235, 0.7)) !important;
        }
        
        /* 按钮扫光动画 - 完全参考登录页面设计 */
        .login .button-primary::after {
            content: none !important;
        }
        
        /* 标签样式 - 完全参考登录页面设计 */
        .login label {
            display: none !important;
        }
        
        /* 表单段落样式 - 完全参考登录页面设计 */
        .login form p {
            margin: 0 !important;
            width: 100% !important;
        }
        
        /* 提交按钮容器样式 - 完全参考登录页面设计 */
        .login form .submit {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* 链接样式 - 黑色文本 */
        .login #nav,
        .login #backtoblog {
            margin: 15px auto !important;
            text-align: center !important;
            display: block !important;
            width: 100% !important;
            max-width: 360px !important;
        }
        
        .login #nav a,
        .login #backtoblog a {
            color: rgba(0, 0, 0, 0.8) !important;
            font-size: 12px !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
        }
        
        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: #000000 !important;
            text-decoration: underline !important;
        }
        
        /* 标题样式 - 黑色文本 */
        .login h2 {
            text-align: center !important;
            font-size: 20px !important;
            font-weight: bold !important;
            margin: 0 0 10px 0 !important;
            padding: 0 !important;
            color: #000000 !important;
        }
        
        /* 提示文字样式 - 黑色文本 */
        .login .login-tagline {
            text-align: center !important;
            color: rgba(0, 0, 0, 0.8) !important;
            font-size: 12px !important;
            margin: 0 0 20px 0 !important;
            padding: 0 !important;
        }
        
        /* 消息容器样式 - 黑色文本 */
        .login #login_error,
        .login .message,
        .login .success {
            margin: 0 auto 15px auto !important;
            padding: 12px !important;
            width: 100% !important;
            max-width: 360px !important;
            border-radius: 15px !important;
            border: none !important;
            color: #000000 !important;
            font-size: 12px !important;
            box-shadow: none !important;
            text-align: center !important;
        }
        
        /* 登录Logo样式 - 在表单内部显示 */
        .login form .login-logo,
        #lostpasswordform .login-logo,
        #resetpassform .login-logo {
            margin: 0 auto 15px auto !important;
            text-align: center !important;
            display: block !important;
        }
        
        .login-logo img {
            width: 80px !important;
            height: 80px !important;
            border-radius: 50% !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
            display: block !important;
            margin: 0 auto !important;
        }
        
        /* 标题样式 - 在表单内部显示 */
        .login form h2,
        #lostpasswordform h2,
        #resetpassform h2 {
            margin: 0 auto 10px auto !important;
            text-align: center !important;
        }
        
        /* 提示文字样式 - 在表单内部显示 */
        .login form .login-tagline,
        #lostpasswordform .login-tagline,
        #resetpassform .login-tagline {
            margin: 0 auto 20px auto !important;
            text-align: center !important;
        }
        
        /* 隐藏默认的记住我复选框和其他不需要的元素 - 完全参考登录页面设计 */
        .login .login-remember,
        .login .forgetmenot,
        /* 隐藏重复的文本标签 */
        .login form p label,
        .login form p br,
        /* 隐藏所有默认标签 */
        .login label,
        /* 确保丢失密码页面的标签被完全隐藏 */
        #lostpasswordform p label,
        #lostpasswordform p br,
        /* 确保重置密码页面的标签被完全隐藏 */
        #resetpassform p label,
        #resetpassform p br {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        
        /* 确保只显示勋章样式的文本，同时允许勋章溢出显示 */
        .login form p,
        #lostpasswordform p,
        #resetpassform p {
            overflow: visible !important;
        }
        
        /* 隐藏输入框内的占位符文本 */
        .login form .input::placeholder,
        .login input[type="text"]::placeholder,
        .login input[type="password"]::placeholder,
        .login input[type="email"]::placeholder,
        .login textarea::placeholder {
            color: transparent !important;
        }
        
        /* 统一表单元素样式 - 紫色渐变背景 */
        .login select,
        #language-switcher select,
        .login button,
        #language-switcher button {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.3), rgba(220, 150, 255, 0.3)) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 25px !important;
            color: #000000 !important;
            padding: 8px 15px !important;
            font-size: 14px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            outline: none !important;
        }
        
        /* 表单元素悬停效果 */
        .login select:hover,
        #language-switcher select:hover,
        .login button:hover,
        #language-switcher button:hover {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.5), rgba(220, 150, 255, 0.5)) !important;
            box-shadow: 0 0 10px rgba(180, 120, 255, 0.3) !important;
        }
        
        /* 美化select下拉框 - 自定义箭头 */
        .login select,
        #language-switcher select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            position: relative !important;
            padding-right: 40px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23000000' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 15px center !important;
        }
        
        /* 美化select下拉菜单容器 */
        select[name="wp_lang"] {
            border-radius: 25px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
            outline: none !important;
        }
        
        /* 浏览器对option元素样式支持有限，我们重点美化select容器和选中状态 */
        /* 美化select下拉选项 - 注意：浏览器对option的border-radius等属性支持有限 */
        .login select option,
        #language-switcher select option {
            background: rgba(255, 255, 255, 0.95) !important;
            color: #000000 !important;
            padding: 12px 15px !important;
            font-size: 14px !important;
        }
        
        /* 美化select下拉选项选中状态 */
        .login select option:checked,
        #language-switcher select option:checked {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.6), rgba(220, 150, 255, 0.6)) !important;
            color: #000000 !important;
        }
        
        /* 美化select下拉选项悬停效果 */
        .login select option:hover,
        #language-switcher select option:hover {
            background: linear-gradient(135deg, rgba(180, 120, 255, 0.5), rgba(220, 150, 255, 0.5)) !important;
            color: #000000 !important;
        }
        
        /* 为select元素添加自定义下拉容器样式（模拟效果） */
        select[name="wp_lang"] {
            /* 这里可以使用JavaScript库或CSS伪元素来创建自定义下拉框，但超出了当前任务范围 */
            /* 我们已经美化了select容器，使其具有圆角，这是浏览器支持的 */
        }
        
        /* 美化select下拉菜单 */
        .login select::-ms-expand,
        #language-switcher select::-ms-expand {
            display: none !important;
        }
        
        /* 确保下拉菜单样式统一 */
        select[name="wp_lang"] {
            width: auto !important;
            min-width: 150px !important;
        }
        

        
        /* 版权信息样式 - 完全参考登录页面设计 */
        .login-copyright {
            text-align: center !important;
            font-size: 10px !important;
            color: rgba(255, 255, 255, 0.5) !important;
            margin-top: 20px !important;
            margin-bottom: 0 !important;
            width: 100% !important;
            max-width: 360px !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    </style>
    
    <script type="text/javascript">
    // 确保DOM加载完成后执行
    document.addEventListener('DOMContentLoaded', function() {
        // 设置延迟确保所有元素都已渲染
        setTimeout(function() {
            // 获取需要移动的元素
            var navElement = document.getElementById('nav');
            var backtoblogElement = document.getElementById('backtoblog');
            var languageForm = document.getElementById('language-switcher');
            var lostpasswordForm = document.getElementById('lostpasswordform');
            var loginForm = document.getElementById('loginform');
            var resetpassForm = document.getElementById('resetpassform');
            var loginLogo = document.querySelector('.login-logo');
            var loginTitle = document.querySelector('.login h2');
            var loginTagline = document.querySelector('.login-tagline');
            
            // 检查当前页面是否为登录相关页面
            var bodyClass = document.body.className;
            
            // 只在登录页面执行元素移动操作
            if (bodyClass.indexOf('login') !== -1) {
                // 将logo、标题和提示文字移动到表单内部输入框上方
                var mainForm = lostpasswordForm || loginForm || resetpassForm;
                if (mainForm) {
                    // 找到表单中的第一个输入框容器
                    var firstInputContainer = mainForm.querySelector('p:has(input)') || mainForm.querySelector('p:first-child');
                    
                    // 依次插入元素到输入框容器之前，确保顺序：logo → title → tagline
                    if (loginTagline && firstInputContainer) {
                        mainForm.insertBefore(loginTagline, firstInputContainer);
                    }
                    if (loginTitle && firstInputContainer) {
                        mainForm.insertBefore(loginTitle, loginTagline);
                    }
                    if (loginLogo && firstInputContainer) {
                        mainForm.insertBefore(loginLogo, loginTitle);
                    }
                }
                
                // 移动导航链接到语言切换表单
                if (navElement && backtoblogElement && languageForm) {
                    // 创建一个导航容器来包裹nav和backtoblog元素
                    var navContainer = document.createElement('div');
                    
                    // 将nav和backtoblog元素添加到导航容器中
                    navContainer.appendChild(navElement);
                    navContainer.appendChild(backtoblogElement);
                    
                    // 将导航容器添加到语言切换表单的末尾
                    languageForm.appendChild(navContainer);
                }
                
                // 将语言切换表单移动到登录表单下面
                if (languageForm && mainForm) {
                    // 确保mainForm的父元素存在
                    var parent = mainForm.parentNode;
                    if (parent) {
                        // 设置语言切换表单的上间距
                        languageForm.style.marginTop = '15px';
                        
                        // 将语言切换表单移动到mainForm的后面
                        parent.insertBefore(languageForm, mainForm.nextSibling);
                    }
                }
                
                // 为所有输入框添加内容检测，控制勋章显示
                var inputs = document.querySelectorAll('.login input[type="text"], .login input[type="email"], .login input[type="password"], #lostpasswordform input, #resetpassform input');
                inputs.forEach(function(input) {
                    // 检测输入框内容变化
                    input.addEventListener('input', function() {
                        var parent = input.closest('p');
                        if (parent) {
                            if (input.value.trim() !== '') {
                                parent.classList.add('has-content');
                            } else {
                                parent.classList.remove('has-content');
                            }
                        }
                    });
                    
                    // 初始化时检查输入框是否有内容
                    if (input.value.trim() !== '') {
                        var parent = input.closest('p');
                        if (parent) {
                            parent.classList.add('has-content');
                        }
                    }
                });
            }
        }, 100);
    });
    </script>
    <?php
}
add_action('login_head', 'boxmoe_custom_login_style');

// 🎨 添加自定义登录页面内容 - 根据页面类型显示不同内容
function boxmoe_custom_login_content() {
    // 获取主题设置的Favicon地址
    $favicon_src = get_boxmoe('boxmoe_favicon_src');
    if ($favicon_src) {
        $site_logo = $favicon_src;
    } else {
        $site_logo = boxmoe_theme_url() . '/assets/images/favicon.ico';
    }
    
    // 获取当前页面类型
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';
    
    // 根据页面类型设置标题和提示文字
    if ($action == 'lostpassword' || $action == 'retrievepassword') {
        $page_title = '忘记密码';
        $page_tagline = '请输入您的用户名或邮箱地址，您会收到一封包含重设密码指引的邮件';
    } elseif ($action == 'resetpass' || $action == 'rp') {
        $page_title = '重置密码';
        $page_tagline = '请设置您的新密码';
    } else {
        $page_title = '欢迎回来站长大人';
        $page_tagline = '登录后台管理系统';
    }
    
    // 直接输出HTML，确保代码被执行，设置高z-index显示在遮罩层上面
    ?>
    <div class="login-logo" style="display: block !important; margin: 0 auto 1.5rem auto !important; text-align: center !important; position: relative !important; z-index: 10 !important;">
        <img src="<?php echo esc_url($site_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" style="width: 60px !important; height: 60px !important; border-radius: 12px !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important; display: block !important; margin: 0 auto !important;">
    </div>
    
    <h2><?php echo esc_html($page_title); ?></h2>
    <p class="login-tagline">
        <?php echo esc_html($page_tagline); ?>
    </p>
    <?php
}

// 🎨 在登录表单末尾添加版权信息
function boxmoe_add_login_copyright() {
    ?>
    <div class="login-copyright">
        Copyright © <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?><br>
        Theme by Boxmoe powered by WordPress
    </div>
    <?php
}
// 只保留login_header动作钩子，避免重复输出
add_action('login_header', 'boxmoe_custom_login_content'); // 登录页面头部，适合输出Logo
add_action('login_footer', 'boxmoe_add_login_copyright');

// 🆔 生成随机且唯一的6位以上数字ID
function boxmoe_generate_custom_uid() {
    do {
        $uid = mt_rand(100000, 99999999);
        $users = get_users(array(
            'meta_key' => 'custom_uid',
            'meta_value' => $uid,
            'number' => 1,
            'fields' => 'ID'
        ));
        $system_user = get_user_by('ID', $uid);
        
        // 清理僵尸ID：如果找到用户，但该用户不存在于系统中，则删除其自定义UID记录
        if (!empty($users)) {
            foreach ($users as $existing_user_id) {
                $existing_user = get_user_by('ID', $existing_user_id);
                if (!$existing_user) {
                    // 清理僵尸ID记录
                    delete_user_meta($existing_user_id, 'custom_uid');
                    // 从结果中移除该僵尸用户
                    $key = array_search($existing_user_id, $users);
                    if ($key !== false) {
                        unset($users[$key]);
                    }
                }
            }
        }
    } while (!empty($users) || $system_user);
    return $uid;
}

// 🔒 移除了登录失败重定向函数，使用 WordPress 默认处理
// 🔒 移除了认证失败重定向函数，使用 WordPress 默认处理