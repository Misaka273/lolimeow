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


// 🔧 统一头像策略：自定义 > QQ 头像 > 本地默认
function boxmoe_get_avatar($avatar, $id_or_email, $size = 96, $default = '', $alt = '', $args = array()) {
    $email = '';
    $user_id = '';
    if (is_numeric($id_or_email)) {
        $id   = (int) $id_or_email;
        $user = get_userdata($id);
        $user_id = $id_or_email;
        if ($user)
            $email = $user->user_email;
            $user_id = $user->ID;
    } else if (is_object($id_or_email)) {
        $user_id = $id_or_email->user_id;
        if (!empty($user_id)) {
            $id   = (int) $id_or_email->user_id;
            $user = get_userdata($id);
            if ($user)
                $email = $user->user_email;
                $user_id = $user->ID;
        } else if (!empty($id_or_email->comment_author_email)) {
            $email = $id_or_email->comment_author_email;
        }
    } else {
        $email = $id_or_email;
    }
    $class = isset($args['class']) 
        ? array_merge(['avatar'], is_array($args['class']) ? $args['class'] : explode(' ', $args['class'])) 
        : ['avatar'];
    $class = array_map('sanitize_html_class', $class);
    $class = esc_attr(implode(' ', array_unique($class)));

    if (isset($user_id)) {
        $user_avatar_url = get_user_meta($user_id, 'user_avatar', true);
        if ($user_avatar_url) { 
            return '<img src="' . esc_url($user_avatar_url) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 优先使用用户自定义上传头像
        } elseif (stripos($email, "@qq.com"))  {
            $qq = str_ireplace("@qq.com", "", $email);
            if (preg_match("/^\d+$/", $qq)) {
                $qqavatar = "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100";
                return '<img src="' . esc_url($qqavatar) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ QQ 邮箱且为纯数字，使用 QQ 头像
            } else {
                return '<img src="' . esc_url(boxmoe_default_avatar_url()) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 非纯数字 QQ 邮箱，回退到本地默认头像
            }
        } else {
            return '<img src="' . esc_url(boxmoe_default_avatar_url()) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 无自定义头像时统一使用本地默认头像
        }
    } elseif (stripos($email, "@qq.com"))  {
        $qq = str_ireplace("@qq.com", "", $email);
        if (preg_match("/^\d+$/", $qq)) {
            $qqavatar = "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100";
            return '<img src="' . esc_url($qqavatar) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 访客 QQ 邮箱为纯数字，使用 QQ 头像
        } else {
            return '<img src="' . esc_url(boxmoe_default_avatar_url()) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 其他访客邮箱，使用本地默认头像
        }
    } else {
        return '<img src="' . esc_url(boxmoe_default_avatar_url()) . '" class="' . $class . '" width="' . $size . '" height="' . $size . '" alt="avatar" />'; // ⬅️ 无邮箱信息时统一使用本地默认头像
    }
}
add_filter('get_avatar', 'boxmoe_get_avatar', 10, 6);

// 提取头像地址--------------------------boxmoe.com--------------------------
// 🔎 提取头像地址（同策略：自定义 > QQ > 本地默认）
function boxmoe_get_avatar_url($id_or_email, $size = 100) {
    $email = '';
    $user_id = '';
        if (is_numeric($id_or_email)) {
        $user = get_userdata($id_or_email);
        if ($user) {
            $user_id = $id_or_email;
            $email = $user->user_email;
        }
    } else {
        $email = $id_or_email;
        $user = get_user_by('email', $email);
        if ($user) {
            $user_id = $user->ID;
        }
    }
    if ($user_id) {
        $user_avatar_url = get_user_meta($user_id, 'user_avatar', true);
        if ($user_avatar_url) {
            return $user_avatar_url; // ⬅️ 返回用户自定义头像地址
        }
    }
    if (stripos($email, "@qq.com")) {
        $qq = str_ireplace("@qq.com", "", $email);
        if (preg_match("/^\d+$/", $qq)) {
            return "https://" . boxmoe_qqavatar_host() . "/headimg_dl?dst_uin=" . $qq . "&spec=100"; // ⬅️ 返回 QQ 头像地址
        }
    }
    return boxmoe_default_avatar_url(); // ⬅️ 最终统一回落到本地默认头像地址
}

// ⚙️ 后台默认头像选项追加
add_filter('avatar_defaults', function($defaults) {
    $url = boxmoe_default_avatar_url();
    $defaults[$url] = 'Lolimeow 默认头像'; // ⬅️ 在“设置→讨论”默认头像列表中显示
    return $defaults;
});




//get_avatar(get_the_author_meta('ID'), 100, '', '', array('class' => 'lazy'));

