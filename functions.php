<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 * @author 专收爆米花
 * @author 白木 <https://gl.baimu.live/864> (二次创作)
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}
// 移除直接时区设置，改为使用WordPress核心时区机制
// 注意：WordPress会自动处理时区，无需手动设置date_default_timezone_set


//boxmoe.com===加载面板
define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/core/panel/' );
require_once dirname( __FILE__ ) . '/core/panel/options-framework.php';
require_once dirname( __FILE__ ) . '/options.php';
require_once dirname( __FILE__ ) . '/core/panel/options-framework-js.php';

// 🎯 设置登录cookie过期时间为7天
function boxmoe_set_cookie_expiry( $expiration, $user_id, $remember ) {
    if ( $remember ) {
        // 记住我时，设置为7天
        return 60 * 60 * 24 * 7;
    }
    // 否则使用默认过期时间
    return $expiration;
}
add_filter( 'auth_cookie_expiration', 'boxmoe_set_cookie_expiry', 10, 3 );

// 🎯 确保注册时的cookie也使用7天过期时间
function boxmoe_set_auth_cookie_expiry( $cookie_values, $user_id, $remember ) {
    if ( $remember ) {
        $cookie_values['expiration'] = time() + 60 * 60 * 24 * 7;
    }
    return $cookie_values;
}
add_filter( 'auth_cookie_values', 'boxmoe_set_auth_cookie_expiry', 10, 3 );
//boxmoe.com===功能模块
require_once  get_stylesheet_directory() . '/core/module/fun-basis.php';
require_once  get_stylesheet_directory() . '/core/module/fun-admin.php';
require_once  get_stylesheet_directory() . '/core/module/fun-optimize.php';
require_once  get_stylesheet_directory() . '/core/module/fun-gravatar.php';
require_once  get_stylesheet_directory() . '/core/module/fun-navwalker.php';
require_once  get_stylesheet_directory() . '/core/module/fun-user.php';
require_once  get_stylesheet_directory() . '/core/module/fun-role-manager.php'; // ⬅️ 引入角色管理功能
require_once  get_stylesheet_directory() . '/core/module/fun-context-menu.php'; // ⬅️ 引入右键菜单功能
require_once  get_stylesheet_directory() . '/core/module/fun-user-center.php';
require_once  get_stylesheet_directory() . '/core/module/fun-comments.php';
require_once  get_stylesheet_directory() . '/core/module/fun-seo.php';
require_once  get_stylesheet_directory() . '/core/module/fun-article.php';
require_once  get_stylesheet_directory() . '/core/module/fun-smtp.php';
require_once  get_stylesheet_directory() . '/core/module/fun-msg.php';
require_once  get_stylesheet_directory() . '/core/module/fun-no-category.php';
require_once  get_stylesheet_directory() . '/core/module/fun-shortcode.php';
require_once  get_stylesheet_directory() . '/core/module/fun-fonts.php';
require_once  get_stylesheet_directory() . '/core/module/fun-markdown.php';
require_once  get_stylesheet_directory() . '/core/module/fun-submenu.php'; // ⬅️ 引入子菜单整合功能
// 🔽 由初叶🍂www.chuyel.top提供，白木🥰gl.baimu.live集成
require_once  get_stylesheet_directory() . '/core/module/fun-music.php'; // ⬅️ 引入音乐播放器功能
//boxmoe.com===自定义代码
add_filter('protected_title_format', function($format){return '%s';});
add_filter('private_title_format', function($format){return '%s';});

// 🔧 加载修复Prettify行号的脚本
function boxmoe_enqueue_fix_prettify_script() {
    wp_enqueue_script(
        'fix-prettify-line-numbers',
        get_template_directory_uri() . '/assets/js/fix-prettify-line-numbers.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // 🎭 加载Animate.css和WOW.js用于飞来模块动画
    wp_enqueue_style(
        'animate-css',
        'https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css',
        array(),
        '4.1.1'
    );
    
    wp_enqueue_script(
        'wow-js',
        'https://cdn.jsdelivr.net/npm/wowjs@1.1.3/dist/wow.min.js',
        array('jquery'),
        '1.1.3',
        true
    );
}
add_action('wp_enqueue_scripts', 'boxmoe_enqueue_fix_prettify_script');

//自定义文章密码保护表单
function custom_password_protected_form($form) {
    global $post;
    $label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
    $output = '<div class="password-protected-form">';
    $output .= '<h3 class="password-form-title">该文章受密码保护</h3>';
    $output .= '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
    $output .= '<div class="form-group password-form-group">';
    $output .= '<input name="post_password" id="' . $label . '" type="password" class="form-control password-input" size="20" maxlength="20" placeholder="" />';
    $output .= '<label for="' . $label . '" class="password-input-label">请输入密码查看本文内容</label>';
    $output .= '</div>';
    $output .= '<button type="submit" name="Submit" class="btn btn-primary password-submit"><i class="fa fa-lock"></i> 确认</button>';
    $output .= '</form>';
    $output .= '</div>';
    return $output;
}
add_filter('the_password_form', 'custom_password_protected_form');



// 将书签小部件标题从"书签"改为"链接"
function lolimeow_change_bookmark_title($args) {
    $args['title_li'] = __('链接');
    return $args;
}
add_filter('widget_links_args', 'lolimeow_change_bookmark_title');

// 📊 重写友链输出函数，确保显示正确的点击次数
function lolimeow_custom_wp_list_bookmarks($args = '') {
    $defaults = array(
        'orderby'          => 'name',
        'order'            => 'ASC',
        'limit'            => -1,
        'category'         => '',
        'exclude_category' => '',
        'category_name'    => '',
        'hide_invisible'   => 1,
        'show_updated'     => 0,
        'echo'             => 1,
        'categorize'       => 1,
        'title_li'         => __('Links'),
        'title_before'     => '<h2>',
        'title_after'      => '</h2>',
        'category_orderby' => 'name',
        'category_order'   => 'ASC',
        'class'            => 'linkcat',
        'category_before'  => '<li id="%id" class="%class">',
        'category_after'   => '</li>',
    );

    $parsed_args = wp_parse_args($args, $defaults);

    if (!is_array($parsed_args['class'])) {
        $parsed_args['class'] = explode(' ', $parsed_args['class']);
    }
    $parsed_args['class'] = array_map('sanitize_html_class', $parsed_args['class']);
    $parsed_args['class'] = trim(implode(' ', $parsed_args['class']));

    $output = '';

    if ($parsed_args['categorize']) {
        $cats = get_terms(array(
            'taxonomy'     => 'link_category',
            'name__like'   => $parsed_args['category_name'],
            'include'      => $parsed_args['category'],
            'exclude'      => $parsed_args['exclude_category'],
            'orderby'      => $parsed_args['category_orderby'],
            'order'        => $parsed_args['category_order'],
            'hierarchical' => 0,
        ));

        if (empty($cats)) {
            $parsed_args['categorize'] = false;
        }
    }

    if ($parsed_args['categorize']) {
        foreach ((array) $cats as $cat) {
            $bookmarks = get_bookmarks(array(
                'category' => $cat->term_id,
                'orderby'  => $parsed_args['orderby'],
                'order'    => $parsed_args['order'],
                'limit'    => $parsed_args['limit'],
            ));

            if (empty($bookmarks)) {
                continue;
            }

            $output .= str_replace(
                array('%id', '%class'),
                array("linkcat-{$cat->term_id}", $parsed_args['class']),
                $parsed_args['category_before']
            );

            $catname = apply_filters('link_category', $cat->name);
            $output .= "{$parsed_args['title_before']}{$catname}{$parsed_args['title_after']}\n";
            $output .= "<ul class='xoxo blogroll bookmark'>\n";

            foreach ((array) $bookmarks as $bookmark) {
                $output .= '<li>';
                $output .= '<a class="on" href="' . esc_url($bookmark->link_url) . '" target="_blank">';
                $output .= '<div class="info">';
                $output .= '<h3>';
                $output .= '<span class="link-title">' . esc_html($bookmark->link_name) . '</span>';
                $output .= '<span class="link-count">' . esc_html(isset($bookmark->link_clicked) ? $bookmark->link_clicked : 0) . '</span>';
                $output .= '</h3>';
                $output .= '</div>';
                $output .= '</a>';
                $output .= '</li>\n';
            }

            $output .= '</ul>\n';
            $output .= "{$parsed_args['category_after']}\n";
        }
    } else {
        $bookmarks = get_bookmarks($parsed_args);

        if (!empty($bookmarks)) {
            if (!empty($parsed_args['title_li'])) {
                $output .= str_replace(
                    array('%id', '%class'),
                    array('linkcat-' . $parsed_args['category'], $parsed_args['class']),
                    $parsed_args['category_before']
                );
                $output .= "{$parsed_args['title_before']}{$parsed_args['title_li']}{$parsed_args['title_after']}\n";
                $output .= "<ul class='xoxo blogroll bookmark'>\n";

                foreach ((array) $bookmarks as $bookmark) {
                    $output .= '<li>';
                    $output .= '<a class="on" href="' . esc_url($bookmark->link_url) . '" target="_blank">';
                    $output .= '<div class="info">';
                    $output .= '<h3>';
                    $output .= '<span class="link-title">' . esc_html($bookmark->link_name) . '</span>';
                    $output .= '<span class="link-count">' . esc_html(isset($bookmark->link_clicked) ? $bookmark->link_clicked : 0) . '</span>';
                    $output .= '</h3>';
                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= '</li>\n';
                }

                $output .= '</ul>\n';
                $output .= "{$parsed_args['category_after']}\n";
            } else {
                foreach ((array) $bookmarks as $bookmark) {
                    $output .= '<li>';
                    $output .= '<a class="on" href="' . esc_url($bookmark->link_url) . '" target="_blank">';
                    $output .= '<div class="info">';
                    $output .= '<h3>';
                    $output .= '<span class="link-title">' . esc_html($bookmark->link_name) . '</span>';
                    $output .= '<span class="link-count">' . esc_html(isset($bookmark->link_clicked) ? $bookmark->link_clicked : 0) . '</span>';
                    $output .= '</h3>';
                    $output .= '</div>';
                    $output .= '</a>';
                    $output .= '</li>\n';
                }
            }
        }
    }

    if ($parsed_args['echo']) {
        echo $output;
    } else {
        return $output;
    }
}

// 使用自定义函数替换默认函数
remove_filter('widget_links_args', 'lolimeow_change_bookmark_title');
add_filter('widget_links_args', function($args) {
    // 直接使用自定义函数输出，忽略默认输出
    $args['echo'] = false;
    return $args;
});

// 添加自定义小部件显示逻辑
add_action('widgets_init', function() {
    // 移除默认链接小部件
    unregister_widget('WP_Widget_Links');
    
    // 注册自定义链接小部件
    class Custom_Links_Widget extends WP_Widget_Links {
        public function widget($args, $instance) {
            echo $args['before_widget'];
            if (!empty($instance['title'])) {
                echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
            }
            
            // 使用自定义函数输出友链
            $widget_links_args = array(
                'title_before'     => '',
                'title_after'      => '',
                'category_before'  => '',
                'category_after'   => '',
                'show_images'      => isset($instance['images']) ? $instance['images'] : true,
                'show_description' => isset($instance['description']) ? $instance['description'] : false,
                'show_name'        => isset($instance['name']) ? $instance['name'] : false,
                'show_rating'      => isset($instance['rating']) ? $instance['rating'] : false,
                'category'         => isset($instance['category']) ? $instance['category'] : false,
                'orderby'          => isset($instance['orderby']) ? $instance['orderby'] : 'name',
                'order'            => 'rating' === $instance['orderby'] ? 'DESC' : 'ASC',
                'limit'            => isset($instance['limit']) ? $instance['limit'] : -1,
            );
            
            // 使用自定义函数输出友链
            echo '<ul class="bookmark">';
            $bookmarks = get_bookmarks($widget_links_args);
            foreach ($bookmarks as $bookmark) {
                echo '<li class="text-reveal">';
                echo '<a class="on" href="' . esc_url($bookmark->link_url) . '" target="_blank">';
                echo '<div class="info">';
                echo '<h3>';
                echo '<span class="link-title">' . esc_html($bookmark->link_name) . '</span>';
                echo '<span class="link-count">' . esc_html(isset($bookmark->link_clicked) ? $bookmark->link_clicked : 0) . '</span>';
                echo '</h3>';
                echo '</div>';
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
            
            echo $args['after_widget'];
        }
    }
    
    // 注册自定义小部件
    register_widget('Custom_Links_Widget');
});

// 🎨 美化注销提示页面 - 重新实现
function lolimeow_custom_logout_page() {
    // 直接检查当前页面是否为注销页面
    $is_logout_page = isset($_SERVER['REQUEST_URI']) && 
                      strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false && 
                      strpos($_SERVER['REQUEST_URI'], 'action=logout') !== false;
    
    if ($is_logout_page) {
        // 检查是否有POST请求，确认用户点击了"是的，注销"按钮
        if (isset($_POST['logout_confirm'])) {
            // 验证nonce
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'log-out')) {
                // 直接执行注销操作
                wp_logout();
                
                // 重定向到首页
                wp_safe_redirect(home_url());
                exit;
            }
        }
        
        // 避免重复定义常量
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        // 获取favicon URL的正确方式
        ob_start();
        boxmoe_favicon();
        $favicon_url = ob_get_clean();
        
        // 获取语言属性的正确方式
        ob_start();
        language_attributes();
        $lang_attr = ob_get_clean();
        
        // 获取logo HTML的正确方式
        ob_start();
        if (function_exists('boxmoe_logo')) {
            boxmoe_logo();
        } else {
            echo '<img src="' . get_site_icon_url() . '" alt="' . get_bloginfo('name') . '" class="logo">';
        }
        $logo_html = ob_get_clean();
        
        // 获取banner图片URL，绑定后台主题设置
        ob_start();
        if (function_exists('boxmoe_banner_image')) {
            boxmoe_banner_image();
        } else {
            echo boxmoe_theme_url() . '/assets/images/banner.jpg';
        }
        $banner_url = ob_get_clean();
        
        // 输出完整的自定义HTML页面
        $html = '<!DOCTYPE html>
<html ' . $lang_attr . '>
<head>
    <meta charset="' . get_bloginfo('charset') . '">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>确认注销 - ' . get_bloginfo('name') . '</title>
    <link rel="icon" href="' . $favicon_url . '" type="image/x-icon">
    <style>
        /* 重置样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* 主题颜色变量 */
        :root {
            --primary-color: #8b3dff;
            --secondary-color: #f0f2f5;
            --dark-color: #0f172a;
            --light-color: #ffffff;
            --gray-color: #64748b;
            --shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            --border-radius: 24px;
        }
        
        /* 基础样式 - 使用主题Banner背景 */
        body {
            font-family: "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-image: url("' . $banner_url . '");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow: hidden;
            /* 添加背景遮罩，提升文字可读性 */
            position: relative;
        }
        
        /* 背景遮罩 */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
        
        /* 玻璃拟态卡片 */
        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 460px;
            padding: 3rem 2.5rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .logout-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
        }
        
        /* Logo区域 */
        .logo-section {
            margin-bottom: 2rem;
        }
        
        .logo-section .logo {
            max-width: 100px;
            height: auto;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        /* 标题和消息 */
        h1 {
            font-size: 1.75rem;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 1rem;
        }
        
        .logout-message {
            font-size: 1rem;
            color: var(--gray-color);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        /* 按钮样式 */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .btn {
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            min-width: 120px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--light-color);
            box-shadow: 0 4px 12px rgba(139, 61, 255, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #7a20ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 61, 255, 0.4);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--dark-color);
        }
        
        .btn-secondary:hover {
            background-color: #e2e8f0;
            transform: translateY(-2px);
        }
        
        /* 底部版权 */
        .footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .footer-text {
            font-size: 0.875rem;
            color: var(--gray-color);
        }
        
        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
        
        /* 响应式设计 */
        @media (max-width: 576px) {
            .logout-container {
                padding: 2rem 1.5rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logo-section">
            ' . $logo_html . '
            <h1>确认注销</h1>
            <p class="logout-message">
                您试图要从 ' . get_bloginfo('name') . ' 注销登录。确定要注销当前的登录？
            </p>
        </div>
        
        <div class="button-group">
            <!-- 注销按钮 - 直接执行注销操作 -->
            <form method="post" action="' . esc_url(add_query_arg(array('action' => 'logout'), site_url('wp-login.php'))) . '" style="margin: 0;">
                <input type="hidden" name="logout_confirm" value="1">
                <input type="hidden" name="_wpnonce" value="' . esc_attr(wp_create_nonce('log-out')) . '">
                <button type="submit" class="btn btn-primary">是的，注销</button>
            </form>
            <!-- 取消按钮 -->
            <a href="' . home_url() . '" class="btn btn-secondary">取消</a>
        </div>
        
        <div class="footer">
            <p class="footer-text">
                Copyright © ' . date('Y') . ' <a href="' . home_url() . '">' . get_bloginfo('name') . '</a><br>
                Theme by <a href="https://www.boxmoe.com">Boxmoe</a> powered by WordPress
            </p>
        </div>
    </div>
</body>
</html>';
        
        // 输出HTML并立即退出，完全绕过WordPress默认登录页面
        echo $html;
        exit;
    }
}

// 🎨 完全自定义登录页面，与用户登录页面样式一致
function lolimeow_custom_login_page() {
    // 检查当前页面是否为登录页面（不是注销页面）
    $is_login_page = isset($_SERVER['REQUEST_URI']) && 
                     strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false && 
                     (strpos($_SERVER['REQUEST_URI'], 'action=') === false || 
                      strpos($_SERVER['REQUEST_URI'], 'action=login') !== false);
    
    // 只有GET请求才显示自定义登录页面，POST请求让WordPress正常处理
    if ($is_login_page && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // 避免重复定义常量
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        
        
        // 获取登录错误信息
        $login_error = '';
        if (isset($_GET['error'])) {
            switch ($_GET['error']) {
                case 'invalid_username':
                case 'invalid_email':
                case 'invalid_password':
                    $login_error = '<div class="alert alert-danger mt-3">用户名或密码错误，请重试。</div>';
                    break;
                case 'empty_username':
                    $login_error = '<div class="alert alert-danger mt-3">请输入用户名。</div>';
                    break;
                case 'empty_password':
                    $login_error = '<div class="alert alert-danger mt-3">请输入密码。</div>';
                    break;
                case 'expiredkey':
                    $login_error = '<div class="alert alert-danger mt-3">登录链接已过期。</div>';
                    break;
                case 'lockedout':
                    $login_error = '<div class="alert alert-danger mt-3">登录失败次数过多，请稍后再试。</div>';
                    break;
                default:
                    $login_error = '<div class="alert alert-danger mt-3">登录失败，请重试。</div>';
            }
        }
        
        // 获取favicon URL
        ob_start();
        boxmoe_favicon();
        $favicon_url = ob_get_clean();
        
        // 获取语言属性
        ob_start();
        language_attributes();
        $lang_attr = ob_get_clean();
        
        // 获取logo HTML
        ob_start();
        if (function_exists('boxmoe_logo')) {
            boxmoe_logo();
        } else {
            echo '<img src="' . get_site_icon_url() . '" alt="' . get_bloginfo('name') . '" class="logo">';
        }
        $logo_html = ob_get_clean();
        
        // 获取登录背景图片
        $login_bg = get_boxmoe('boxmoe_user_login_bg') ? get_boxmoe('boxmoe_user_login_bg') : 'https://api.boxmoe.com/random.php';
        
        // 获取注册和重置密码链接
        $register_link = boxmoe_sign_up_link_page();
        $reset_password_link = boxmoe_reset_password_link_page();
        
        // 检查是否为管理员入口访问
        $is_admin_redirect = false;
        $redirect_to = '';
        if (isset($_GET['redirect_to'])) {
            $redirect_to = urldecode($_GET['redirect_to']);
            if (strpos($redirect_to, 'wp-admin') !== false) {
                $is_admin_redirect = true;
            }
        }
        
        // 输出完整的自定义HTML页面，完全复制用户登录页面样式
        $html = '<!DOCTYPE html>
<html ' . $lang_attr . '>
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <title>登录 - ' . get_bloginfo('name') . '</title>
   <link rel="icon" href="' . $favicon_url . '" type="image/x-icon">
   ';
        
        // 加载WordPress头部脚本（简化版）
        ob_start();
        wp_head();
        $wp_head_output = ob_get_clean();
        $html .= preg_replace('/\n/', "\n    ", trim($wp_head_output)) . "\n    ";
        
        // 复制用户登录页面的完整CSS样式
        $html .= '<style>
        /* 🥳 登录页样式重构 - 玻璃拟态 */
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
            background-image: url("' . $login_bg . '");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }
        .login-page-bg::before {
            content: \'\';
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
            max-width: 460px;
            padding: 3rem 2.5rem;
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
               <a href="' . home_url() . '" class="d-inline-block transition-hover">
                   ' . $logo_html . '
               </a>
               <h3 class="mt-3 mb-1 fw-bold">欢迎回来，我的站长大人~🎉</h3>
               <p class="text-muted small mb-0">
                  如果你还没有账号可以点击
                  <a href="' . $register_link . '" class="text-primary fw-bold text-decoration-none">注册</a>
               </p>
            </div>

            <!-- 登录错误信息显示 -->
            ' . $login_error . '

            <!-- 登录表单 -->
            <form class="needs-validation mb-3" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post" id="loginform" novalidate>
               <div class="mb-3 floating-label-group">
                  <input type="text" name="log" class="form-control" id="username" required placeholder=" " value="' . (isset($_GET['login']) ? esc_attr($_GET['login']) : '') . '" />
                  <label for="username" data-default="电子邮件/用户名" data-active="账号"></label>
                  <div class="invalid-feedback">请输入用户名或邮箱。</div>
               </div>
               
               <div class="mb-4 position-relative floating-label-group">
                  <div class="password-field">
                      <input type="password" name="pwd" class="form-control fakePassword" id="password" required placeholder=" " />
                      <label for="password" data-default="请输入密码" data-active="密码"></label>
                      <i class="bi bi-eye-slash passwordToggler"></i>
                  </div>
                  <div class="invalid-feedback">请输入密码。</div>
               </div>

               <div class="d-flex align-items-center justify-content-between mb-4">
                  <div class="form-check">
                     <input class="form-check-input" type="checkbox" name="rememberme" id="rememberme" value="forever">
                     <label class="form-check-label small text-muted" for="rememberme">记住账号</label>
                  </div>
                  <a href="' . $reset_password_link . '" class="small text-primary text-decoration-none fw-bold">忘记密码?</a>
               </div>

               <input type="hidden" name="redirect_to" value="' . esc_attr($redirect_to) . '">
               <input type="hidden" name="testcookie" value="1">
               
               <div class="d-grid">
                  <button class="btn btn-primary" type="submit" name="wp-submit">
                     <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                     <span class="btn-text">立即登录</span>
                  </button>
               </div>
               <div id="login-message"></div>
            </form>

            <!-- 底部版权 -->
            <div class="text-center mt-4 pt-3 border-top border-light">
               <div class="small text-body-tertiary">
                  Copyright © ' . date('Y') . ' 
                  <span class="text-primary"><a href="' . home_url() . '" class="text-reset text-decoration-none fw-bold">' . get_bloginfo('name') . '</a></span>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g stroke="currentColor" stroke-linecap="round" stroke-width="2" data-swindex="0"><path fill="currentColor" fill-opacity="0" stroke-dasharray="34" stroke-dashoffset="34" d="M12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.4s" values="34;0"/><animate fill="freeze" attributeName="fill-opacity" begin="0.9s" dur="0.5s" values="0;1"/></path><g fill="none" stroke-dasharray="2" stroke-dashoffset="2"><path d="M0 0"><animate fill="freeze" attributeName="d" begin="0.5s" dur="0.2s" values="M12 19v1M19 12h1M12 5v-1M5 12h-1;M12 21v1M21 12h1M12 3v-1M3 12h-1"/><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.5s" dur="0.2s" values="2;0"/></path><path d="M0 0"><animate fill="freeze" attributeName="d" begin="0.7s" dur="0.2s" values="M17 17l0.5 0.5M17 7l0.5 -0.5M7 7l-0.5 -0.5M7 17l-0.5 0.5;M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"/><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" values="2;0"/></path><animateTransform attributeName="transform" dur="30s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></g></g></svg>
                        <span class="ms-2">亮色</span>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" data-swindex="0"><g stroke-dasharray="2"><path d="M12 21v1M21 12h1M12 3v-1M3 12h-1"><animate fill="freeze" attributeName="stroke-dashoffset" dur="0.2s" values="4;2"/></path><path d="M18.5 18.5l0.5 0.5M18.5 5.5l0.5 -0.5M5.5 5.5l-0.5 -0.5M5.5 18.5l-0.5 0.5"><animate fill="freeze" attributeName="stroke-dashoffset" begin="0.2s" dur="0.2s" values="4;2"/></path></g><path fill="currentColor" d="M7 6 C7 12.08 11.92 17 18 17 C18.53 17 19.05 16.96 19.56 16.89 C17.95 19.36 15.17 21 12 21 C7.03 21 3 16.97 3 12 C3 8.83 4.64 6.05 7.11 4.44 C7.04 4.95 7 5.47 7 6 Z" opacity="0"><set attributeName="opacity" begin="0.5s" to="1"/></path></g><g fill="currentColor" fill-opacity="0"><path d="m15.22 6.03l2.53-1.94L14.56 4L13.5 1l-1.06 3l-3.19.09l2.53 1.94l-.91 3.06l2.63-1.81l2.63 1.81z"><animate id="lineMdSunnyFilledLoopToMoonFilledLoopTransition0" fill="freeze" attributeName="fill-opacity" begin="0.6s;lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+6s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.2s" dur="0.4s" values="1;0"/></path><path d="M13.61 5.25L15.25 4l-2.06-.05L12.5 2l-.69 1.95L9.75 4l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+3s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+5.2s" dur="0.4s" values="1;0"/></path><path d="M19.61 12.25L21.25 11l-2.06-.05L18.5 9l-.69 1.95l-2.06.05l1.64 1.25l-.59 1.98l1.7-1.17l1.7 1.17z"><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+0.4s" dur="0.4s" values="0;1"/><animate fill="freeze" attributeName="fill-opacity" begin="lineMdSunnyFilledLoopToMoonFilledLoopTransition0.begin+2.6s" dur="0.4s" values="1;0"/></path></g></svg>
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
   ';
        
        // 加载WordPress底部脚本
        ob_start();
        wp_footer();
        $wp_footer_output = ob_get_clean();
        $html .= preg_replace('/\n/', "\n    ", trim($wp_footer_output)) . "\n    ";
        
        // 添加JavaScript - 仅用于显示加载状态，不阻止默认提交
        $html .= '<script>
      // 🔗 登录表单提交事件监听 - 仅用于显示加载状态
      document.addEventListener(\'DOMContentLoaded\', function() {
    document.getElementById(\'loginform\').addEventListener(\'submit\', function(e) {
        const loginButton = this.querySelector(\'button[type="submit"]\');
        const spinner = loginButton.querySelector(\'.spinner-border\');
        const btnText = loginButton.querySelector(\'.btn-text\');

        // 显示加载状态
        loginButton.disabled = true;
        spinner.classList.remove(\'d-none\');
        btnText.textContent = \'登录中...\';

        // 不阻止默认提交，让表单正常提交到WordPress登录处理URL
    });
});
    </script>';
        
        // 引入粒子效果脚本（如果有）
        $html .= '<script src="' . get_template_directory_uri() . '/assets/js/login-particles.js"></script>
</body></html>';
        
        // 输出HTML并立即退出，完全绕过WordPress默认登录页面
        echo $html;
        exit;
    }
}

// 使用最高优先级挂载，确保在WordPress处理登录页面之前执行
add_action('login_init', 'lolimeow_custom_logout_page', 1);
add_action('login_init', 'lolimeow_custom_login_page', 1);

// 移除默认的注销表单（双重保险）
function lolimeow_remove_default_logout_form() {
    remove_action('login_form_logout', 'wp_login_form_logout');
}
add_action('login_head', 'lolimeow_remove_default_logout_form', 1);

// 确保WordPress不会缓存注销页面
function lolimeow_disable_cache_for_logout() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'action=logout') !== false) {
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
    }
}
add_action('init', 'lolimeow_disable_cache_for_logout');

// 🔧 修改WP Fastest Cache插件菜单名称
function lolimeow_rename_wp_fastest_cache_menu() {
    // 检查WP Fastest Cache插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wp-fastest-cache/wpFastestCache.php')) {
        global $menu;
        
        // 遍历菜单数组，找到WP Fastest Cache的菜单并修改名称
        foreach ($menu as $key => $value) {
            if (strpos($value[0], 'WP Fastest Cache') !== false || strpos($value[0], 'wpFastestCache') !== false) {
                $menu[$key][0] = 'WP清理缓存';
                break;
            }
        }
    }
}
add_action('admin_menu', 'lolimeow_rename_wp_fastest_cache_menu', 999);

// 🎯 修改WP-Optimize插件菜单名称
function lolimeow_rename_wp_optimize_menu() {
    // 检查WP-Optimize插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wp-optimize/wp-optimize.php')) {
        global $menu;
        
        // 遍历菜单数组，找到WP-Optimize的菜单并修改名称
        foreach ($menu as $key => $value) {
            if (strpos($value[0], 'WP-Optimize') !== false) {
                $menu[$key][0] = 'WP优化';
                break;
            }
        }
    }
}
add_action('admin_menu', 'lolimeow_rename_wp_optimize_menu', 999);

// 📦 修改WPvivid Backup插件菜单名称
function lolimeow_rename_wpvivid_menu() {
    // 检查WPvivid Backup插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wpvivid-backuprestore/wpvivid-backuprestore.php')) {
        global $menu;
        
        // 遍历菜单数组，找到WPvivid Backup的菜单并修改名称
        foreach ($menu as $key => $value) {
            if (strpos($value[0], 'WPvivid Backup') !== false) {
                $menu[$key][0] = '网站备份';
                break;
            }
        }
    }
}
add_action('admin_menu', 'lolimeow_rename_wpvivid_menu', 999);

// 📋 修改WPvivid Backup插件工具栏菜单名称（通过过滤器）
function lolimeow_rename_wpvivid_toolbar_menu_filter($toolbar_menus) {
    // 检查WPvivid Backup插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wpvivid-backuprestore/wpvivid-backuprestore.php')) {
        // 修改主菜单标题
        if (isset($toolbar_menus['wpvivid_admin_menu'])) {
            $toolbar_menus['wpvivid_admin_menu']['title'] = '网站备份';
        }
        
        // 修改子菜单标题
        if (isset($toolbar_menus['wpvivid_admin_menu']['child']['wpvivid_admin_menu_backup'])) {
            $toolbar_menus['wpvivid_admin_menu']['child']['wpvivid_admin_menu_backup']['title'] = '备份与恢复';
        }
    }
    return $toolbar_menus;
}
add_filter('wpvivid_get_toolbar_menus', 'lolimeow_rename_wpvivid_toolbar_menu_filter', 11);

// 📋 确保工具栏菜单名称正确修改（通过admin_bar_menu钩子）
function lolimeow_rename_wpvivid_toolbar_menu($wp_admin_bar) {
    // 检查WPvivid Backup插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wpvivid-backuprestore/wpvivid-backuprestore.php')) {
        // 获取工具栏菜单节点
        $node = $wp_admin_bar->get_node('wpvivid_admin_menu');
        
        // 如果找到了节点，修改其标题
        if ($node) {
            $wp_admin_bar->remove_node('wpvivid_admin_menu');
            $wp_admin_bar->add_menu(array(
                'id' => 'wpvivid_admin_menu',
                'title' => '<span class="dashicons-cloud ab-icon"></span>网站备份'
            ));
            
            // 检查是否有子菜单节点需要修改
            $child_node = $wp_admin_bar->get_node('wpvivid_admin_menu_backup');
            if ($child_node) {
                $wp_admin_bar->remove_node('wpvivid_admin_menu_backup');
                $wp_admin_bar->add_menu(array(
                    'id' => 'wpvivid_admin_menu_backup',
                    'parent' => 'wpvivid_admin_menu',
                    'title' => '备份与恢复',
                    'href' => admin_url('admin.php?page=WPvivid&tab-backup')
                ));
            }
        }
    }
}
add_action('admin_bar_menu', 'lolimeow_rename_wpvivid_toolbar_menu', 100);

// 📋 修改WP-Optimize插件子菜单名称
function lolimeow_rename_wp_optimize_submenus() {
    // 检查WP-Optimize插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wp-optimize/wp-optimize.php')) {
        global $submenu;
        
        // 遍历子菜单数组，找到WP-Optimize的子菜单并修改名称
        foreach ($submenu as $key => $value) {
            if (strpos($key, 'WP-Optimize') !== false || strpos($key, 'wp-optimize') !== false) {
                foreach ($value as $subkey => $subvalue) {
                    // 修改子菜单名称
                    switch ($subvalue[0]) {
                        case 'Database':
                            $submenu[$key][$subkey][0] = '数据库';
                            break;
                        case 'Images':
                            $submenu[$key][$subkey][0] = '图片';
                            break;
                        case 'Cache':
                            $submenu[$key][$subkey][0] = '缓存';
                            break;
                        case 'Minify':
                            $submenu[$key][$subkey][0] = '压缩';
                            break;
                        case 'Performance':
                            $submenu[$key][$subkey][0] = '性能';
                            break;
                        case 'Settings':
                            $submenu[$key][$subkey][0] = '设置';
                            break;
                        case 'Help':
                            $submenu[$key][$subkey][0] = '帮助';
                            break;
                        case 'Premium Upgrade':
                            $submenu[$key][$subkey][0] = '升级高级版';
                            break;
                    }
                }
                break;
            }
        }
    }
}
add_action('admin_menu', 'lolimeow_rename_wp_optimize_submenus', 999);

// 📋 修改WPvivid Backup插件子菜单名称
function lolimeow_rename_wpvivid_submenus() {
    // 检查WPvivid Backup插件是否已安装
    if (file_exists(WP_PLUGIN_DIR . '/wpvivid-backuprestore/wpvivid-backuprestore.php')) {
        global $submenu;
        
        // 遍历子菜单数组，找到WPvivid Backup的子菜单并修改名称
        foreach ($submenu as $key => $value) {
            if (strpos($key, 'WPvivid') !== false || strpos($key, 'wpvivid') !== false) {
                foreach ($value as $subkey => $subvalue) {
                    // 修改子菜单名称
                    switch ($subvalue[0]) {
                        case 'Backup & Restore':
                            $submenu[$key][$subkey][0] = '备份与恢复';
                            break;
                        case 'Settings':
                            $submenu[$key][$subkey][0] = '设置';
                            break;
                    }
                }
                break;
            }
        }
    }
}
add_action('admin_menu', 'lolimeow_rename_wpvivid_submenus', 999);

// 🎨 动态修改主题名称在后台显示，添加版本号
function lolimeow_dynamic_theme_name_in_admin($prepared_themes) {
    // 获取当前主题信息
    $current_theme = wp_get_theme();
    $theme_slug = $current_theme->get('TextDomain');
    $theme_version = $current_theme->get('Version');
    $current_theme_dir = basename(get_template_directory());
    
    // 遍历所有准备好的主题数据
    foreach ($prepared_themes as &$theme_data) {
        // 检查数组中是否存在'stylesheet'键
        if (isset($theme_data['stylesheet'])) {
            // 检查是否是当前主题
            if ($theme_data['stylesheet'] === $theme_slug || $theme_data['stylesheet'] === $current_theme_dir) {
                // 动态添加版本号到主题名称
                $theme_data['name'] = $current_theme->get('Name') . ' ' . $theme_version;
                break;
            }
        }
    }
    
    return $prepared_themes;
}
add_filter('wp_prepare_themes_for_js', 'lolimeow_dynamic_theme_name_in_admin');
