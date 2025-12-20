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

// 使用最高优先级挂载，确保在WordPress处理登录页面之前执行
add_action('login_init', 'lolimeow_custom_logout_page', 1);

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
