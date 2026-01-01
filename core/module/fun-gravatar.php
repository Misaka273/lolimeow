<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}


// Gravatar头像--------------------------boxmoe.com--------------------------
// 🖼️ 本地默认头像地址
function boxmoe_default_avatar_url() {
    return get_stylesheet_directory_uri() . '/assets/images/touxiang.jpg'; // ⬅️ 返回主题内默认头像路径
}


function boxmoe_qqavatar_host() {
    $qqavatar_Url = 'q2.qlogo.cn';
    switch (get_boxmoe('boxmoe_qqavatar_url')) {
        case 'Q1':
            $qqavatar_Url = 'q1.qlogo.cn';
            break;
        case 'Q2':
            $qqavatar_Url = 'q2.qlogo.cn';
            break;
        case 'Q3':
            $qqavatar_Url = 'q3.qlogo.cn';
            break;
        case 'Q4':
            $qqavatar_Url = 'q4.qlogo.cn';
        default:
            $qqavatar_Url = 'q2.qlogo.cn';
    }
    return $qqavatar_Url;
}


// 🔧 统一头像策略：用户自定义头像 > QQ头像 > WordPress默认头像 > 本地默认头像
function boxmoe_get_avatar($avatar, $id_or_email, $size = 96, $default = '', $alt = '', $args = array()) {
    $class = isset($args['class']) 
        ? array_merge(['avatar'], is_array($args['class']) ? $args['class'] : explode(' ', $args['class'])) 
        : ['avatar'];
    $class = array_map('sanitize_html_class', $class);
    $class = esc_attr(implode(' ', array_unique($class)));

    // 直接使用boxmoe_get_avatar_url函数获取头像URL，确保所有地方的头像逻辑一致
    $avatar_url = boxmoe_get_avatar_url($id_or_email, $size);
    
    // 返回完整的img标签
    return '<img src="' . esc_url($avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />';
}
add_filter('get_avatar', 'boxmoe_get_avatar', 10, 6);

// 提取头像地址--------------------------boxmoe.com--------------------------
// 🎨 使用WordPress原生逻辑，确保与后台显示一致
function boxmoe_get_avatar_url($id_or_email, $size = 100) {
    // 首先获取用户信息，确定用户邮箱
    $email = '';
    $user = false;
    
    if (is_numeric($id_or_email)) {
        $user = get_userdata($id_or_email);
    } elseif (is_object($id_or_email)) {
        if (isset($id_or_email->user_id)) {
            $user = get_userdata($id_or_email->user_id);
        } elseif (isset($id_or_email->ID)) {
            $user = $id_or_email;
        }
    } else {
        $email = $id_or_email;
        $user = get_user_by('email', $email);
    }
    
    // 如果是用户对象，获取邮箱
    if ($user) {
        $email = $user->user_email;
    }
    
    // 检查用户自定义头像（优先）
    if ($user) {
        $user_avatar_url = get_user_meta($user->ID, 'user_avatar', true);
        if (!empty($user_avatar_url)) {
            return $user_avatar_url;
        }
    }
    
    // 检查QQ邮箱并返回QQ头像（优先级高于WordPress默认头像）
    if (stripos($email, '@qq.com') !== false) {
        $qq = str_ireplace('@qq.com', '', $email);
        if (preg_match('/^\d+$/', $qq)) {
            // 根据size参数动态调整QQ头像尺寸
            $qq_size = $size <= 40 ? 40 : ($size <= 100 ? 100 : ($size <= 200 ? 200 : 400));
            return 'https://' . boxmoe_qqavatar_host() . '/headimg_dl?dst_uin=' . $qq . '&spec=' . $qq_size;
        }
    }
    
    // 尝试使用WordPress原生get_avatar_url函数获取头像
    $wp_avatar_url = get_avatar_url($id_or_email, array('size' => $size));
    
    // 如果获取成功且不是gravatar.com的头像，直接返回
    if (!empty($wp_avatar_url) && strpos($wp_avatar_url, 'gravatar.com') === false) {
        return $wp_avatar_url;
    }
    
    // 最后返回默认头像
    return boxmoe_default_avatar_url();
}

// ⚙️ 后台默认头像选项追加
add_filter('avatar_defaults', function($defaults) {
    $url = boxmoe_default_avatar_url();
    $defaults[$url] = 'Lolimeow 默认头像'; // ⬅️ 在“设置→讨论”默认头像列表中显示
    return $defaults;
});




//get_avatar(get_the_author_meta('ID'), 100, '', '', array('class' => 'lazy'));