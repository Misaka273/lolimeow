<?php
/**
 * Template Name: YIKAN友联
 * Description: 集成友联展示、申请、邮件通知+图形验证码+提交限流+配置项提取+按钮加载的专用页面模板
 * Copyright: YI KAN搜索导航yy4y.com博客44y4.com © 2025 保留所有权利
 */

// ========== 核心配置项（集中管理，修改超方便） | YI KAN搜索导航yy4y.com博客44y4.com ==========
define('FL_ADMIN_EMAIL', '1909824@qq.com');      // 管理员接收邮箱 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_SUBMIT_INTERVAL', 30);                // 提交间隔限制（秒） | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_CAPTCHA_LENGTH', 4);                  // 验证码位数 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_CAPTCHA_WIDTH', 120);                 // 验证码图片宽度 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_CAPTCHA_HEIGHT', 40);                 // 验证码图片高度 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_LOGO_PATH', '/logo.png');             // LOGO相对于博客根目录的路径 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_EMAIL_SUBJECT_APPLY', '【YIKAN友联】新的友情链接申请'); // 申请通知邮件标题 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_EMAIL_SUBJECT_APPROVE', '【%s】你的友情链接申请已通过！'); // 审核通过邮件标题 | YI KAN搜索导航yy4y.com博客44y4.com
define('FL_COPYRIGHT_TEXT', 'YI KAN博客44y4.com'); // 版权信息配置项 | YI KAN搜索导航yy4y.com博客44y4.com

// 启用WP原生链接管理功能 | YI KAN搜索导航yy4y.com博客44y4.com
add_filter('pre_option_link_manager_enabled', '__return_true');

// ========== 核心函数1：生成数字+字母混合图形验证码 | YI KAN搜索导航yy4y.com博客44y4.com ==========
function create_fl_image_captcha() {
    // 排除易混淆字符：0/O/1/l | YI KAN搜索导航yy4y.com博客44y4.com
    $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
    $code = '';
    for ($i=0; $i<FL_CAPTCHA_LENGTH; $i++) {
        $code .= $chars[mt_rand(0, strlen($chars)-1)];
    }
    $_SESSION['fl_captcha_code'] = $code;

    // 创建画布 | YI KAN搜索导航yy4y.com博客44y4.com
    $image = imagecreatetruecolor(FL_CAPTCHA_WIDTH, FL_CAPTCHA_HEIGHT);
    $bg_color = imagecolorallocate($image, 248, 249, 250);
    $text_color = imagecolorallocate($image, 0, 123, 186);
    $line_color = imagecolorallocate($image, 200, 200, 200);
    $dot_color = imagecolorallocate($image, 220, 220, 220);

    // 填充背景+干扰元素 | YI KAN搜索导航yy4y.com博客44y4.com
    imagefill($image, 0, 0, $bg_color);
    for ($i=0; $i<5; $i++) imageline($image, rand(0,FL_CAPTCHA_WIDTH), rand(0,FL_CAPTCHA_HEIGHT), rand(0,FL_CAPTCHA_WIDTH), rand(0,FL_CAPTCHA_HEIGHT), $line_color);
    for ($i=0; $i<80; $i++) imagesetpixel($image, rand(0,FL_CAPTCHA_WIDTH), rand(0,FL_CAPTCHA_HEIGHT), $dot_color);

    // 绘制验证码文字 | YI KAN搜索导航yy4y.com博客44y4.com
    $font_size = 16;
    $x = (FL_CAPTCHA_WIDTH - strlen($code) * $font_size) / 2;
    $y = (FL_CAPTCHA_HEIGHT - $font_size) / 2 + 10;
    imagestring($image, $font_size, $x, $y, $code, $text_color);

    // 输出图片 | YI KAN搜索导航yy4y.com博客44y4.com
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}

// 触发图形验证码生成 | YI KAN搜索导航yy4y.com博客44y4.com
if (isset($_GET['fl_captcha_img'])) {
    session_start();
    create_fl_image_captcha();
}

// ========== 核心函数2：发送审核通过通知邮件（默认博客LOGO） | YI KAN搜索导航yy4y.com博客44y4.com ==========
function send_friendlink_approved_email($to_email, $site_name, $site_url, $your_site_name = '', $your_site_url = '') {
    $your_site_name = empty($your_site_name) ? get_option('blogname') : $your_site_name;
    $your_site_url = empty($your_site_url) ? get_option('siteurl') : $your_site_url;
    $logo_url = $your_site_url . FL_LOGO_PATH;
    $subject = sprintf(FL_EMAIL_SUBJECT_APPROVE, $your_site_name);

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$subject}</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, 'Microsoft YaHei', sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
                background-color: #fecfef;
            }
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(255, 107, 157, 0.2);
            }
            .email-header {
                background: linear-gradient(to right, #ff6b9d, #fecfef);
                padding: 40px 30px;
                text-align: center;
            }
            .email-logo {
                text-align: center;
                margin-bottom: 20px;
            }
            .email-logo img {
                max-width: 120px;
                height: auto;
                border-radius: 50%;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                background-color: #ffffff;
                padding: 10px;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .info-section {
                margin: 30px 0;
            }
            .info-section h3 {
                color: #ff6b9d;
                font-size: 18px;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .info-table td {
                padding: 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            .info-table .label {
                font-weight: 600;
                color: #666;
                width: 35%;
            }
            .info-table .value {
                color: #333;
                font-size: 15px;
            }
            .info-table .value a {
                color: #ff6b9d;
                text-decoration: none;
                font-weight: 600;
            }
            .email-footer {
                background-color: #f9f9f9;
                padding: 25px 30px;
                text-align: center;
                font-size: 13px;
                color: #999;
                border-top: 1px solid #eee;
            }
            .email-footer a {
                color: #ff6b9d;
                text-decoration: none;
                font-weight: 600;
            }
            /* 响应式设计 */
            @media only screen and (max-width: 640px) {
                .email-wrapper {
                    margin: 10px;
                    border-radius: 15px;
                }
                .email-header {
                    padding: 30px 20px;
                }
                .email-title {
                    font-size: 24px;
                }
                .email-body {
                    padding: 30px 20px;
                }
                .info-table td {
                    padding: 12px;
                    display: block;
                    width: 100%;
                }
                .info-table .label {
                    width: 100%;
                    padding-bottom: 5px;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-wrapper'>
            <!-- 邮件头部 -->
            <div class='email-header'>
                <div class='email-logo'><img src='{$logo_url}' alt='{$your_site_name}' onerror='this.style.display="none"'></div>
                <h2 class='email-title'>🎉 你的友情链接申请已通过！</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class='email-body'>
                <p class='greeting'>尊敬的 {$site_name} 站长：</p>
                <p class='greeting'>你的友情链接申请已审核通过，我们已将你的站点添加到「{$your_site_name}」的友情链接列表中～</p>
                
                <!-- 站点信息区域 -->
                <div class='info-section'>
                    <h3>友情链接信息</h3>
                    <table class='info-table'>
                        <tr>
                            <td class='label'>你的站点：</td>
                            <td class='value'><a href='{$site_url}' target='_blank'>{$site_url}</a></td>
                        </tr>
                        <tr>
                            <td class='label'>本站地址：</td>
                            <td class='value'><a href='{$your_site_url}' target='_blank'>{$your_site_url}</a></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class='email-footer'>
                {$your_site_name} © " . date('Y') . " | {$FL_COPYRIGHT_TEXT}
            </div>
        </div>
    </body>
    </html>
    ";
    
    // 邮件头部配置 | YI KAN搜索导航yy4y.com博客44y4.com
    $headers = [
        'From: ' . get_option('blogname') . ' <' . get_option('admin_email') . '>',
        'Content-Type: text/html; charset=UTF-8'
    ];
    return wp_mail($to_email, $subject, $message, $headers);
}

// ========== 核心函数3：发送新申请通知邮件（默认博客LOGO） | YI KAN搜索导航yy4y.com博客44y4.com ==========
function send_friendlink_apply_notification($admin_email, $site_name, $site_url, $contact_email, $remarks) {
    $your_site_name = get_option('blogname');
    $your_site_url = get_option('siteurl');
    $logo_url = $your_site_url . FL_LOGO_PATH;
    $subject = FL_EMAIL_SUBJECT_APPLY . ' - ' . $site_name;

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$subject}</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, 'Microsoft YaHei', sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
                background-color: #fecfef;
            }
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(255, 107, 157, 0.2);
            }
            .email-header {
                background: linear-gradient(to right, #ff6b9d, #fecfef);
                padding: 40px 30px;
                text-align: center;
            }
            .email-logo {
                text-align: center;
                margin-bottom: 20px;
            }
            .email-logo img {
                max-width: 120px;
                height: auto;
                border-radius: 50%;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                background-color: #ffffff;
                padding: 10px;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .info-section {
                margin: 30px 0;
            }
            .info-section h3 {
                color: #ff6b9d;
                font-size: 18px;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            .info-table td {
                padding: 15px;
                border-bottom: 1px solid #f0f0f0;
            }
            .info-table .label {
                font-weight: 600;
                color: #666;
                width: 35%;
            }
            .info-table .value {
                color: #333;
                font-size: 15px;
            }
            .info-table .value a {
                color: #ff6b9d;
                text-decoration: none;
                font-weight: 600;
            }
            .email-footer {
                background-color: #f9f9f9;
                padding: 25px 30px;
                text-align: center;
                font-size: 13px;
                color: #999;
                border-top: 1px solid #eee;
            }
            .email-footer a {
                color: #ff6b9d;
                text-decoration: none;
                font-weight: 600;
            }
            /* 响应式设计 */
            @media only screen and (max-width: 640px) {
                .email-wrapper {
                    margin: 10px;
                    border-radius: 15px;
                }
                .email-header {
                    padding: 30px 20px;
                }
                .email-title {
                    font-size: 24px;
                }
                .email-body {
                    padding: 30px 20px;
                }
                .info-table td {
                    padding: 12px;
                    display: block;
                    width: 100%;
                }
                .info-table .label {
                    width: 100%;
                    padding-bottom: 5px;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-wrapper'>
            <!-- 邮件头部 -->
            <div class='email-header'>
                <div class='email-logo'><img src='{$logo_url}' alt='{$your_site_name}' onerror='this.style.display="none"'></div>
                <h2 class='email-title'>📢 你有新的友联申请</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class='email-body'>
                <!-- 申请信息区域 -->
                <div class='info-section'>
                    <h3>友联申请详情</h3>
                    <table class='info-table'>
                        <tr>
                            <td class='label'>申请站点：</td>
                            <td class='value'>{$site_name}</td>
                        </tr>
                        <tr>
                            <td class='label'>站点地址：</td>
                            <td class='value'><a href='{$site_url}' target='_blank'>{$site_url}</a></td>
                        </tr>
                        <tr>
                            <td class='label'>联系邮箱：</td>
                            <td class='value'>{$contact_email}</td>
                        </tr>
                        <tr>
                            <td class='label'>备注信息：</td>
                            <td class='value'>" . ($remarks ?: '无') . "</td>
                        </tr>
                        <tr>
                            <td class='label'>申请时间：</td>
                            <td class='value'>" . current_time('Y-m-d H:i:s') . "</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class='email-footer'>
                {$your_site_name} © " . date('Y') . " | {$FL_COPYRIGHT_TEXT}
            </div>
        </div>
    </body>
    </html>
    ";
    
    // 邮件头部配置 | YI KAN搜索导航yy4y.com博客44y4.com
    $headers = [
        'From: ' . $site_name . ' <' . $contact_email . '>',
        'Content-Type: text/html; charset=UTF-8'
    ];
    return wp_mail($admin_email, $subject, $message, $headers);
}

// ========== 处理管理员发送通知请求 | YI KAN搜索导航yy4y.com博客44y4.com ==========
if (isset($_POST['send_friendlink_notice_front']) && is_user_logged_in() && current_user_can('manage_options')) {
    // 安全验证 | YI KAN搜索导航yy4y.com博客44y4.com
    if (!isset($_POST['friendlink_notice_nonce']) || !wp_verify_nonce($_POST['friendlink_notice_nonce'], 'send_friendlink_notice_front_action')) {
        $notice_error = '安全验证失败！';
    } else {
        // 数据清洗与验证 | YI KAN搜索导航yy4y.com博客44y4.com
        $to_email = sanitize_email($_POST['friendlink_notice_email']);
        $site_name = sanitize_text_field($_POST['friendlink_notice_sitename']);
        $site_url = esc_url_raw($_POST['friendlink_notice_siteurl']);
        $your_site_name = sanitize_text_field($_POST['friendlink_notice_mysitename']);
        $your_site_url = esc_url_raw($_POST['friendlink_notice_mysiteurl']);
        
        if (empty($to_email) || !is_email($to_email)) {
            $notice_error = '请填写有效的申请人邮箱！';
        } elseif (empty($site_name)) {
            $notice_error = '请填写申请人站点名称！';
        } elseif (empty($site_url) || !filter_var($site_url, FILTER_VALIDATE_URL)) {
            $notice_error = '请填写有效的申请人站点地址！';
        } else {
            // 发送通知邮件 | YI KAN搜索导航yy4y.com博客44y4.com
            if (send_friendlink_approved_email($to_email, $site_name, $site_url, $your_site_name, $your_site_url)) {
                $notice_success = '✅ 通知邮件已成功发送！';
            } else {
                $notice_error = '❌ 邮件发送失败，请检查邮箱配置！';
            }
        }
    }
}

// ========== 处理用户友联申请（含限流+验证码） | YI KAN搜索导航yy4y.com博客44y4.com ==========
if (session_status() == PHP_SESSION_NONE) {
    @session_start();
    // Session检测 | YI KAN搜索导航yy4y.com博客44y4.com
    if (!isset($_SESSION)) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('服务器session未启用，无法提交申请！'), 'fl_type' => 'error'], get_permalink()));
        exit;
    }
}

if (isset($_POST['yikan_fl_action']) && $_POST['yikan_fl_action'] === 'send_email') {
    // 安全验证 | YI KAN搜索导航yy4y.com博客44y4.com
    if (!isset($_POST['yikan_fl_nonce_field']) || !wp_verify_nonce($_POST['yikan_fl_nonce_field'], 'yikan_fl_nonce')) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('安全验证失败！'), 'fl_type' => 'error'], get_permalink()));
        exit;
    }

    // 限制提交频率（核心功能） | YI KAN搜索导航yy4y.com博客44y4.com
    $ip = $_SERVER['REMOTE_ADDR'];
    $cache_key = 'fl_ip_limit_' . $ip;
    if (isset($_SESSION[$cache_key]) && time() - $_SESSION[$cache_key] < FL_SUBMIT_INTERVAL) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode("提交过于频繁，请".FL_SUBMIT_INTERVAL."秒后再试！"), 'fl_type' => 'error'], get_permalink()));
        exit;
    }

    // 验证码验证 | YI KAN搜索导航yy4y.com博客44y4.com
    $user_captcha = sanitize_text_field($_POST['fl_captcha']);
    $session_captcha = isset($_SESSION['fl_captcha_code']) ? $_SESSION['fl_captcha_code'] : '';
    if (empty($user_captcha) || strtolower($user_captcha) != strtolower($session_captcha)) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('验证码错误！'), 'fl_type' => 'error'], get_permalink()));
        exit;
    }

    // 数据清洗与验证 | YI KAN搜索导航yy4y.com博客44y4.com
    $site_name = sanitize_text_field($_POST['site_name']);
    $site_url = esc_url_raw($_POST['site_url']);
    $contact_email = sanitize_email($_POST['contact_email']);
    $remarks = sanitize_textarea_field($_POST['remarks']);

    if (empty($site_name) || empty($site_url) || empty($contact_email) || !is_email($contact_email)) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('请填写所有必填项且邮箱格式正确！'), 'fl_type' => 'error'], get_permalink()));
        exit;
    }

    // 记录提交时间+清空验证码 | YI KAN搜索导航yy4y.com博客44y4.com
    $_SESSION[$cache_key] = time();
    unset($_SESSION['fl_captcha_code']);
    
    // 发送通知邮件 | YI KAN搜索导航yy4y.com博客44y4.com
    if (send_friendlink_apply_notification(FL_ADMIN_EMAIL, $site_name, $site_url, $contact_email, $remarks)) {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('申请提交成功！我们会尽快审核'), 'fl_type' => 'success'], get_permalink()));
    } else {
        wp_redirect(add_query_arg(['fl_msg' => urlencode('邮件发送失败，请手动发邮件到'.FL_ADMIN_EMAIL), 'fl_type' => 'error'], get_permalink()));
    }
    exit;
}

// ========== 页面前端展示 | YI KAN搜索导航yy4y.com博客44y4.com ==========
get_header();
?>

<div class="yikan-friendlink-page" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- 友联列表区域 | YI KAN搜索导航yy4y.com博客44y4.com -->
    <div class="fl-list-section" style="margin-bottom: 50px;">
        <h1 style="color: #333; font-size: 28px; margin-bottom: 30px; text-align: center;"><?php the_title(); ?></h1>
        <h2 style="color: #444; font-size: 22px; margin-bottom: 20px;">YIKAN友联</h2>
        
        <?php
        // 获取友情链接列表 | YI KAN搜索导航yy4y.com博客44y4.com
        $friendlinks = get_bookmarks([
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_invisible' => 1
        ]);
        ?>

        <?php if (!empty($friendlinks)) : ?>
            <ul style="list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start;">
                <?php foreach ($friendlinks as $link) : ?>
                    <li style="padding: 10px 20px; background: #f8f9fa; border-radius: 6px; transition: all 0.3s ease;">
                        <a href="<?php echo esc_url($link->link_url); ?>" target="_blank" style="color: #2d3748; text-decoration: none; font-size: 16px;">
                            <?php echo esc_html($link->link_name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p style="color: #666; font-size: 16px; line-height: 1.6;">暂无友情链接，欢迎提交申请～</p>
        <?php endif; ?>
        
        <!-- 版权声明 | YI KAN搜索导航yy4y.com博客44y4.com -->
        <p style="color: #999; font-size: 14px; margin-top: 20px; text-align: right;">
            © <?php echo date('Y'); ?> <?php echo FL_COPYRIGHT_TEXT; ?> 版权所有
        </p>
    </div>

    <!-- 管理员专属：邮件发送模块 | YI KAN搜索导航yy4y.com博客44y4.com -->
    <?php if (is_user_logged_in() && current_user_can('manage_options')) : ?>
    <div class="fl-notice-section" style="max-width: 800px; margin: 0 auto 50px; border-top: 1px solid #eee; padding-top: 40px;">
        <h2 style="color: #444; font-size: 22px; margin-bottom: 20px;">📧 友联审核通过通知发送（管理员专用）</h2>
        <div style="background: #f0f8fb; padding: 30px; border: 1px solid #007cba; border-radius: 8px;">
            <?php if (isset($notice_success)) : ?>
                <div style="padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo $notice_success; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($notice_error)) : ?>
                <div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 15px;">
                    <?php echo $notice_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" style="margin-top: 15px;">
                <?php wp_nonce_field('send_friendlink_notice_front_action', 'friendlink_notice_nonce'); ?>
                <input type="hidden" name="send_friendlink_notice_front" value="1">

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">申请人邮箱 *</label>
                    <input type="email" name="friendlink_notice_email" required
                           placeholder="粘贴申请人的联系邮箱"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">申请人站点名 *</label>
                    <input type="text" name="friendlink_notice_sitename" required
                           placeholder="输入申请人的站点名称"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">申请人站点地址 *</label>
                    <input type="url" name="friendlink_notice_siteurl" required
                           placeholder="输入申请人的站点地址"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">你的站点名</label>
                    <input type="text" name="friendlink_notice_mysitename" value="<?php echo get_option('blogname'); ?>"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">你的站点地址</label>
                    <input type="url" name="friendlink_notice_mysiteurl" value="<?php echo get_option('siteurl'); ?>"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <button type="submit" style="background: #007cba; color: #fff; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s;">
                    发送审核通过通知
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- 友联申请区域（带图形验证码+限流+按钮加载） | YI KAN搜索导航yy4y.com博客44y4.com -->
    <div class="fl-apply-section" style="max-width: 800px; margin: 0 auto; border-top: 1px solid #eee; padding-top: 40px;">
        <h2 style="color: #444; font-size: 22px; margin-bottom: 20px;">YIKAN友联申请</h2>
        <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="margin-bottom: 20px; color: #666; line-height: 1.8;">
                <p>请填写以下信息申请友联，申请前请先在你的站点添加本站链接（名称：<?php echo get_option('blogname'); ?>，地址：<?php echo get_option('siteurl'); ?>），审核通过后会第一时间邮件通知你。</p>
                <!-- 版权提示 | YI KAN搜索导航yy4y.com博客44y4.com -->
                <p style="font-size: 12px; color: #999;">
                    本友联系统由 <?php echo FL_COPYRIGHT_TEXT; ?> 开发提供
                </p>
            </div>

            <form id="fl-apply-form" method="post" action="" style="margin-top: 20px;">
                <?php wp_nonce_field('yikan_fl_nonce', 'yikan_fl_nonce_field'); ?>
                <input type="hidden" name="yikan_fl_action" value="send_email">

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">网站名称：*</label>
                    <input type="text" name="site_name" required
                           placeholder="比如：YI KAN博客"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">网站地址：*</label>
                    <input type="url" name="site_url" required
                           placeholder="比如：https://www.44y4.com"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">联系邮箱：*</label>
                    <input type="email" name="contact_email" required
                           placeholder="比如：1909824@qq.com"
                           style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">备注：</label>
                    <textarea name="remarks" rows="4"
                              placeholder="这里是您的网站的介绍"
                              style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                <!-- 图形验证码区域 | YI KAN搜索导航yy4y.com博客44y4.com -->
                <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                    <label style="display: block; margin-bottom: 0; font-weight: 500; color: #333;">验证码：*</label>
                    <input type="text" name="fl_captcha" required
                           placeholder="请输入图形中的字符"
                           style="flex: 1; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                    <img src="<?php echo get_permalink(); ?>?fl_captcha_img=1" alt="验证码" 
                         style="width: <?php echo FL_CAPTCHA_WIDTH; ?>px; height: <?php echo FL_CAPTCHA_HEIGHT; ?>px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;"
                         onclick="this.src='<?php echo get_permalink(); ?>?fl_captcha_img=1&t='+Math.random()">
                    <span style="font-size: 12px; color: #666;">点击验证码刷新</span>
                </div>

                <button type="submit" id="fl-submit-btn" style="background: #007cba; color: #fff; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background 0.3s;">
                    提交申请
                </button>

                <?php if (isset($_GET['fl_msg'])) : ?>
                    <div style="margin-top: 15px; font-size: 14px; color: <?php echo $_GET['fl_type'] === 'success' ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo urldecode($_GET['fl_msg']); ?>
                    </div>
                <?php endif; ?>
            </form>

            <!-- 按钮加载状态JS | YI KAN搜索导航yy4y.com博客44y4.com -->
            <script>
                const form = document.getElementById('fl-apply-form');
                const btn = document.getElementById('fl-submit-btn');
                form.addEventListener('submit', function(e) {
                    btn.disabled = true;
                    btn.innerHTML = '提交中...';
                    btn.style.backgroundColor = '#6c757d';
                });
            </script>
        </div>
    </div>
</div>

<?php
// 加载页脚 | YI KAN搜索导航yy4y.com博客44y4.com
get_footer();
?>