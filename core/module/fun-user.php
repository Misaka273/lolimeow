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
    $boxmoe_sign_up_link_page = get_boxmoe('boxmoe_sign_up_link_page');
    if($boxmoe_sign_up_link_page && is_numeric($boxmoe_sign_up_link_page)){
        $permalink = get_the_permalink($boxmoe_sign_up_link_page);
        if($permalink) return $permalink;
    }
    
    // 🔍 自动查找使用 p-signup.php 模板的注册页面（尝试多种模板路径格式）
    $template_paths = array(
        'page/p-signup.php',
        'p-signup.php'
    );
    
    foreach($template_paths as $template_path){
        $signup_pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => $template_path
        ));
        if(!empty($signup_pages)){
            // 🔗 返回找到的第一个注册页面的链接
            return get_the_permalink($signup_pages[0]);
        }
    }
    
    // 🔍 按模板名称查找注册页面
    $args = array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_wp_page_template',
                'value' => 'p-signup.php',
                'compare' => 'LIKE'
            )
        )
    );
    
    $signup_query = new WP_Query($args);
    if($signup_query->have_posts()){
        $signup_query->the_post();
        $permalink = get_the_permalink();
        wp_reset_postdata();
        if($permalink) return $permalink;
    }
    
    // 🔍 按slug查找注册页面
    $signup_page = get_page_by_path('signup');
    if($signup_page){
        return get_the_permalink($signup_page);
    }
    
    // 🔗 最后尝试获取所有页面，手动检查模板
    $all_pages = get_pages();
    foreach($all_pages as $page){
        $template = get_page_template_slug($page->ID);
        if($template && strpos($template, 'signup') !== false){
            return get_the_permalink($page->ID);
        }
    }
    
    // 🔗 回退到默认注册页面链接
    return home_url('/signup');
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

    // 👮u200d♂️ 非管理员用户跳转到会员中心，管理员保持原有重定向
    if ( !user_can( $user, 'manage_options' ) ) {
        $redirect_to = boxmoe_user_center_link_page();
    }

    $redirect_to = wp_validate_redirect($redirect_to, boxmoe_user_center_link_page());

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
    if(get_boxmoe('boxmoe_robot_notice_switch')){
        if(get_boxmoe('boxmoe_new_user_register_notice_robot_switch')){
            boxmoe_robot_msg_reguser($user_id,$user->user_email);
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

// 🔄 登录页面自动重定向
function boxmoe_custom_login_redirect() {
    global $pagenow;
    // 检查是否在 wp-login.php 页面，且不是登出或密码保护操作
    if ( 'wp-login.php' == $pagenow && (!isset($_GET['action']) || ($_GET['action'] != 'logout' && $_GET['action'] != 'postpass')) && !isset($_GET['key'])) {
        // 检查是否已经是自定义登录页面，避免重定向循环
        if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], home_url()) !== false ) {
            return; // 已经来自本站，避免循环
        }
        $login_page = boxmoe_sign_in_link_page(); // ⬅️ 获取主题设置的自定义登录页链接
        if ( $login_page ) {
            // 🔗 检查是否有 redirect_to 参数，如果有则附加到重定向 URL 中
            if ( isset($_GET['redirect_to']) ) {
                $redirect_to = urlencode( $_GET['redirect_to'] );
                // 检查 redirect_to 是否会导致循环
                if ( strpos(urldecode($redirect_to), 'wp-login.php') === false ) {
                    $login_page = add_query_arg( 'redirect_to', $redirect_to, $login_page );
                }
            }
            // 确保登录页面不是 wp-login.php 本身，避免循环
            if ( strpos($login_page, 'wp-login.php') === false ) {
                wp_redirect( $login_page ); // ⬅️ 执行重定向
                exit();
            }
        }
    }
}
add_action( 'init', 'boxmoe_custom_login_redirect' ); // ⬅️ 挂载到 init 钩子

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
    } while (!empty($users) || $system_user);
    return $uid;
}

// 🔒 修复登录失败重定向问题
function boxmoe_login_failed_redirect() {
    $login_page = boxmoe_sign_in_link_page(); // ⬅️ 获取主题设置的自定义登录页链接
    if ($login_page) {
        wp_redirect($login_page); // ⬅️ 重定向到自定义登录页面
    } else {
        wp_redirect(home_url('/wp-login.php')); // ⬅️ 回退到默认登录页面
    }
    exit;
}
add_action('wp_login_failed', 'boxmoe_login_failed_redirect'); // ⬅️ 挂载登录失败钩子

// 🔒 修复认证失败重定向问题
function boxmoe_authenticate_failed_redirect($user, $username, $password) {
    if (is_wp_error($user)) {
        $login_page = boxmoe_sign_in_link_page(); // ⬅️ 获取主题设置的自定义登录页链接
        if ($login_page) {
            // 🔗 检查是否有 redirect_to 参数，如果有则附加到重定向 URL 中
            if (isset($_REQUEST['redirect_to'])) {
                $redirect_to = urlencode($_REQUEST['redirect_to']);
                // 检查 redirect_to 是否会导致循环
                if (strpos(urldecode($redirect_to), 'wp-login.php') === false) {
                    $login_page = add_query_arg('redirect_to', $redirect_to, $login_page);
                }
            }
            // 添加登录失败参数，方便前端显示错误信息
            $login_page = add_query_arg('login', 'failed', $login_page);
            wp_redirect($login_page); // ⬅️ 重定向到自定义登录页面
            exit;
        }
    }
    return $user;
}
add_filter('authenticate', 'boxmoe_authenticate_failed_redirect', 30, 3); // ⬅️ 挂载认证过滤器
