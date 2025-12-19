<?php
// 🎯 主题子菜单整合功能

// 移除原有erphpdown菜单并添加为主题设置子菜单
function boxmoe_integrate_submenus() {
    // 检查erphpdown插件是否激活
    if (is_plugin_active('erphpdown/erphpdown.php')) {
        // 使用更高优先级确保erphpdown菜单已注册
        add_action('admin_menu', 'boxmoe_add_erphpdown_submenu', 99);
    }
    
    // 移除原有小部件菜单并添加为主题设置子菜单
    remove_submenu_page('themes.php', 'widgets.php');
    
    // 首先添加主题设置的主页面作为子菜单
    add_submenu_page(
        'boxmoe_options', // ⬅️ 主题设置菜单 slug
        __('盒子萌主题设置', 'textdomain'), // ⬅️ 页面标题
        __('盒子萌主题后台', 'textdomain'), // ⬅️ 菜单标题
        'edit_theme_options', // ⬅️ 权限
        'boxmoe_options' // ⬅️ 菜单 slug，指向主题配置页面
    );
    
    // 添加页面小部件作为主题设置子菜单
    add_submenu_page(
        'boxmoe_options', // ⬅️ 主题设置菜单 slug
        __('Widgets', 'textdomain'), // ⬅️ 页面标题
        __('页面小部件', 'textdomain'), // ⬅️ 菜单标题
        'edit_theme_options', // ⬅️ 权限
        'widgets.php' // ⬅️ 菜单 slug
    );
    

}

// 添加erphpdown作为主题设置子菜单
function boxmoe_add_erphpdown_submenu() {
    // 先移除erphpdown的顶级菜单
    remove_menu_page('erphpdown-main');
    
    // 检查erphpdown_main_page函数是否存在
    if (function_exists('erphpdown_main_page')) {
        // 添加erphpdown作为主题设置子菜单
        global $wpdb;
        $tx_count = $wpdb->get_var("SELECT count(ice_id) FROM $wpdb->iceget where ice_success != 1");
        
        if (current_user_can('administrator')) {
            add_submenu_page(
                'boxmoe_options', // ⬅️ 主题设置菜单 slug
                '会员管理', // ⬅️ 页面标题
                '会员管理'.($tx_count?'<span class="awaiting-mod">'.$tx_count.'</span>':'') , // ⬅️ 菜单标题
                'activate_plugins', // ⬅️ 权限
                'erphpdown-main', // ⬅️ 菜单 slug
                'erphpdown_main_page' // ⬅️ 回调函数
            );
        } else {
            add_submenu_page(
                'boxmoe_options', // ⬅️ 主题设置菜单 slug
                '会员管理', // ⬅️ 页面标题
                '会员管理', // ⬅️ 菜单标题
                'read', // ⬅️ 权限
                'erphpdown-main', // ⬅️ 菜单 slug
                'erphpdown_main_page' // ⬅️ 回调函数
            );
        }
    }
}

// 确保is_plugin_active函数可用
if (!function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// 在主题设置菜单之后添加子菜单
add_action('admin_menu', 'boxmoe_integrate_submenus', 10);
