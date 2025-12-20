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


// 🔧 统一头像策略：后台设置头像 > 主题自定义头像 > WordPress默认 > QQ 头像 > 本地默认
function boxmoe_get_avatar($avatar, $id_or_email, $size = 96, $default = '', $alt = '', $args = array()) {
    // 检查用户是否登录，如果未登录，直接返回空字符串
    if (!is_user_logged_in()) {
        return ''; // ⬅️ 未登录用户不显示头像
    }
    
    $email = '';
    $user_id = '';
    if (is_numeric($id_or_email)) {
        $id   = (int) $id_or_email;
        $user = get_userdata($id);
        if ($user) {
            $user_id = $user->ID;
            $email = $user->user_email;
        }
    } else if (is_object($id_or_email)) {
        if (isset($id_or_email->ID)) {
            // 如果是用户对象
            $user = $id_or_email;
            $user_id = $user->ID;
            $email = $user->user_email;
        } else if (isset($id_or_email->user_id)) {
            // 如果是评论对象等，有user_id属性
            $user_id = $id_or_email->user_id;
            if (!empty($user_id)) {
                $user = get_userdata($user_id);
                if ($user) {
                    $email = $user->user_email;
                    $user_id = $user->ID;
                }
            } else if (!empty($id_or_email->comment_author_email)) {
                $email = $id_or_email->comment_author_email;
            }
        }
    } else {
        $email = $id_or_email;
    }
    $class = isset($args['class']) 
        ? array_merge(['avatar'], is_array($args['class']) ? $args['class'] : explode(' ', $args['class'])) 
        : ['avatar'];
    $class = array_map('sanitize_html_class', $class);
    $class = esc_attr(implode(' ', array_unique($class)));

    if (!empty($user_id) && is_numeric($user_id)) {
        $user_avatar_url = get_user_meta($user_id, 'user_avatar', true);
        if ($user_avatar_url) { 
            return '<img src="' . esc_url($user_avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ 优先使用用户自定义上传头像，失败时使用默认头像
        } elseif (stripos($email, "@qq.com"))  {
            $qq = str_ireplace("@qq.com", "", $email);
            if (preg_match("/^\d+$/", $qq)) {
                $qqavatar = "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100";
                return '<img src="' . esc_url($qqavatar) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ QQ 邮箱且为纯数字，使用 QQ 头像，失败时使用默认头像
            }
        }
        // 调用WordPress默认的get_avatar_url函数，获取用户在后台设置的头像
        $wp_default_avatar_url = get_avatar_url($id_or_email, array('size' => $size));
        if (!empty($wp_default_avatar_url) && strpos($wp_default_avatar_url, 'gravatar.com') === false) {
            return '<img src="' . esc_url($wp_default_avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ 返回WordPress默认头像（排除gravatar.com）
        } else {
            return ''; // ⬅️ 无自定义头像时返回空字符串，不显示头像
        }
    } elseif (stripos($email, "@qq.com"))  {
        $qq = str_ireplace("@qq.com", "", $email);
        if (preg_match("/^\d+$/", $qq)) {
            $qqavatar = "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100";
            return '<img src="' . esc_url($qqavatar) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ 访客 QQ 邮箱为纯数字，使用 QQ 头像，失败时使用默认头像
        } else {
            // 调用WordPress默认的get_avatar_url函数，获取用户在后台设置的头像
            $wp_default_avatar_url = get_avatar_url($id_or_email, array('size' => $size));
            if (!empty($wp_default_avatar_url) && strpos($wp_default_avatar_url, 'gravatar.com') === false) {
                return '<img src="' . esc_url($wp_default_avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ 返回WordPress默认头像（排除gravatar.com）
            } else {
                return ''; // ⬅️ 其他访客邮箱，无头像时返回空字符串
            }
        }
    } else {
        // 调用WordPress默认的get_avatar_url函数，获取用户在后台设置的头像
        $wp_default_avatar_url = get_avatar_url($id_or_email, array('size' => $size));
        if (!empty($wp_default_avatar_url) && strpos($wp_default_avatar_url, 'gravatar.com') === false) {
            return '<img src="' . esc_url($wp_default_avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" onerror="this.src=\'' . esc_url(boxmoe_default_avatar_url()) . '\'" />'; // ⬅️ 返回WordPress默认头像（排除gravatar.com）
        } else {
            return ''; // ⬅️ 无邮箱信息时返回空字符串，不显示头像
        }
    }
}
add_filter('get_avatar', 'boxmoe_get_avatar', 10, 6);

// 提取头像地址--------------------------boxmoe.com--------------------------
// 🔎 提取头像地址（优先：后台设置头像 > WordPress默认 > 主题自定义头像 > QQ 头像 > 本地默认）
function boxmoe_get_avatar_url($id_or_email, $size = 100) {
    // 检查用户是否登录，如果未登录，直接返回空字符串
    if (!is_user_logged_in()) {
        return ''; // ⬅️ 未登录用户不显示头像
    }
    
    $email = '';
    $user_id = '';
    if (is_numeric($id_or_email)) {
        $user_id = intval($id_or_email);
        $user = get_userdata($user_id);
        if ($user) {
            $user_id = $user->ID;
            $email = $user->user_email;
        } else {
            $user_id = '';
        }
    } elseif (is_object($id_or_email)) {
        // 处理用户对象
        if (isset($id_or_email->ID)) {
            $user = $id_or_email;
            $user_id = $user->ID;
            $email = $user->user_email;
        } else if (isset($id_or_email->user_id)) {
            // 处理评论对象等
            $user_id = $id_or_email->user_id;
            $user = get_userdata($user_id);
            if ($user) {
                $email = $user->user_email;
            }
        }
    } else {
        $email = $id_or_email;
        $user = get_user_by('email', $email);
        if ($user) {
            $user_id = $user->ID;
        }
    }
    
    // 1. 优先检查主题自定义头像字段（user_avatar），这是用户中心上传的头像
    if (!empty($user_id)) {
        $user_avatar_url = get_user_meta($user_id, 'user_avatar', true);
        if (!empty($user_avatar_url)) {
            return $user_avatar_url; // ⬅️ 返回主题自定义头像地址（用户中心上传的头像）
        }
        
        // 2. 检查其他常见的WordPress头像插件字段
        $wp_avatar_fields = array(
            'wp_user_avatar',       // WP User Avatar插件
            'avatar_url',          // 通用头像字段
            'user_avatar_url',     // 另一个通用头像字段
            'profile_picture',     // 常见的头像字段名
            'profile_photo',       // 常见的头像字段名
            'user_profile_pic'     // 常见的头像字段名
        );
        
        foreach ($wp_avatar_fields as $field) {
            $avatar_url = get_user_meta($user_id, $field, true);
            if (!empty($avatar_url)) {
                return $avatar_url; // ⬅️ 返回找到的第一个头像地址
            }
        }
    }
    
    // 2. 调用WordPress默认的get_avatar_url函数，获取用户在后台设置的头像
    $wp_default_avatar_url = get_avatar_url($id_or_email, array('size' => $size));
    if (!empty($wp_default_avatar_url) && strpos($wp_default_avatar_url, 'gravatar.com') === false) {
        return $wp_default_avatar_url; // ⬅️ 返回WordPress默认头像地址（排除gravatar.com）
    }
    
    // 3. 检查QQ头像
    if (stripos($email, "@qq.com")) {
        $qq = str_ireplace("@qq.com", "", $email);
        if (preg_match("/^\d+$", $qq)) {
            return "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100"; // ⬅️ 返回 QQ 头像地址
        }
    }
    
    // 4. 所有头像都没有时，返回默认头像地址，确保显示头像
    return boxmoe_default_avatar_url(); // ⬅️ 没有任何头像时返回默认头像地址
}

// ⚙️ 后台默认头像选项追加
add_filter('avatar_defaults', function($defaults) {
    $url = boxmoe_default_avatar_url();
    $defaults[$url] = 'Lolimeow 默认头像'; // ⬅️ 在“设置→讨论”默认头像列表中显示
    return $defaults;
});




//get_avatar(get_the_author_meta('ID'), 100, '', '', array('class' => 'lazy'));

