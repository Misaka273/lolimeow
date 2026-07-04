<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}
// 添加管理菜单
// 菜单放置位置
add_action('admin_menu', 'boxmoe_smtp_menu', 99);
add_action('admin_enqueue_scripts', 'boxmoe_smtp_enqueue_assets', 20);
add_filter('admin_body_class', 'boxmoe_smtp_admin_body_class');

function boxmoe_smtp_is_settings_screen() {
    return is_admin() && isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'boxmoe-smtp-settings';
}

function boxmoe_smtp_admin_body_class($classes) {
    if (boxmoe_smtp_is_settings_screen()) {
        $classes .= ' boxmoe-smtp-settings-screen';
    }
    return $classes;
}

function boxmoe_smtp_enqueue_assets() {
    if (!boxmoe_smtp_is_settings_screen()) {
        return;
    }

    $css_path = get_template_directory() . '/core/panel/css/optionsframework.css';
    $version = file_exists($css_path) ? (string) filemtime($css_path) : (defined('THEME_VERSION') ? THEME_VERSION : '1.0');

    wp_enqueue_style('admin-variables', get_template_directory_uri() . '/assets/css/admin/admin-variables.css', array(), $version);
    wp_enqueue_style('lolimeow-admin-flat-rounded', get_template_directory_uri() . '/assets/css/admin-flat-rounded.css', array('admin-variables'), $version);
    wp_enqueue_style(
        'optionsframework',
        OPTIONS_FRAMEWORK_DIRECTORY . 'css/optionsframework.css',
        array('admin-variables', 'lolimeow-admin-flat-rounded'),
        $version
    );
}

function boxmoe_smtp_render_field($label, $content, $desc = '', $type = 'text', $icon = 'dashicons-admin-generic') {
    $section_class = 'section section-' . esc_attr($type) . ' col';
    ?>
    <div class="<?php echo $section_class; ?>">
        <div class="heading"><span class="dashicons <?php echo esc_attr($icon); ?>"></span><?php echo esc_html($label); ?></div>
        <div class="option">
            <div class="controls"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php if ($desc !== '') : ?>
                <div class="explain"><?php echo esc_html($desc); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// 添加SMTP设置菜单
function boxmoe_smtp_menu() {
    // 将SMTP设置添加为盒子萌主题设置的子菜单
    add_submenu_page(
        'boxmoe_options', // ⬅️ 父菜单slug（盒子萌主题设置）
        'SMTP邮局设置', // ⬅️ 页面标题
        'SMTP邮局设置', // ⬅️ 菜单标题
        'manage_options', // ⬅️ 权限
        'boxmoe-smtp-settings', // ⬅️ 菜单slug
        'boxmoe_smtp_settings_page', // ⬅️ 回调函数
    );

}

// SMTP设置页面内容
function boxmoe_smtp_settings_page() {
    $notices = array();

    if (isset($_POST['boxmoe_smtp_save'])) {
        update_option('boxmoe_smtp_host', sanitize_text_field(wp_unslash($_POST['smtp_host'])));
        update_option('boxmoe_smtp_port', sanitize_text_field(wp_unslash($_POST['smtp_port'])));
        update_option('boxmoe_smtp_user', sanitize_text_field(wp_unslash($_POST['smtp_user'])));
        update_option('boxmoe_smtp_pass', sanitize_text_field(wp_unslash($_POST['smtp_pass'])));
        update_option('boxmoe_smtp_from', sanitize_text_field(wp_unslash($_POST['smtp_from'])));
        update_option('boxmoe_smtp_name', sanitize_text_field(wp_unslash($_POST['smtp_name'])));
        update_option('boxmoe_smtp_secure', sanitize_text_field(wp_unslash($_POST['smtp_secure'])));
        update_option('boxmoe_smtp_receive_email', sanitize_text_field(wp_unslash($_POST['smtp_receive_email'])));
        $notices[] = array('type' => 'success', 'message' => '设置已保存！');
    }

    if (isset($_POST['boxmoe_smtp_test'])) {
        $smtp_switch = get_boxmoe('boxmoe_smtp_mail_switch');

        if (!$smtp_switch) {
            $notices[] = array(
                'type' => 'error',
                'message' => '测试邮件发送失败！SMTP发件系统开关未启用，请先在通知设置中启用。',
                'action' => admin_url('admin.php?page=boxmoe_options'),
                'action_label' => '前往通知设置',
            );
        } else {
            $from = get_option('boxmoe_smtp_from');
            $name = get_option('boxmoe_smtp_name');
            $to = sanitize_email(wp_unslash($_POST['test_email']));
            $subject = '测试邮件 - ' . get_bloginfo('name');
            $message = '这是一封测试邮件，如果您收到这封邮件，说明SMTP配置正确。';
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $name . ' <' . $from . '>',
                'Reply-To: ' . $name . ' <' . $from . '>',
            );

            $result = wp_mail($to, $subject, $message, $headers);

            if ($result) {
                $notices[] = array('type' => 'success', 'message' => '测试邮件发送成功！请检查收件箱和垃圾邮件文件夹。');
            } else {
                $notices[] = array('type' => 'error', 'message' => '测试邮件发送失败，请检查SMTP配置。');
            }
        }
    }

    $secure = get_option('boxmoe_smtp_secure');
    $secure_select = '<select name="smtp_secure" class="of-input">'
        . '<option value=""' . selected($secure, '', false) . '>无加密</option>'
        . '<option value="ssl"' . selected($secure, 'ssl', false) . '>SSL</option>'
        . '<option value="tls"' . selected($secure, 'tls', false) . '>TLS</option>'
        . '</select>';
    ?>
    <div id="optionsframework-wrap" class="wrap boxmoe-smtp-wrap">
        <div class="options-top-bar">
            <div class="header-set-title">
                <div class="themes-name"><span class="dashicons dashicons-email-alt"></span> SMTP邮局设置</div>
                <a class="el-button" href="<?php echo esc_url(admin_url('admin.php?page=boxmoe_options')); ?>">返回主题设置</a>
            </div>
        </div>

        <div class="options-main-content">
            <div class="options-sidebar">
                <div class="boxmoe-options-site-name">
                    <span class="dashicons dashicons-nametag"></span>
                    盒子萌主题
                    <p> - 纸鸢版🎉</p>
                </div>
                <div class="nav-tab-wrapper boxmoe-smtp-sidebar-nav">
                    <ul>
                        <li class="active">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boxmoe-smtp-settings')); ?>" class="nav-tab-active">
                                <span class="dashicons dashicons-email-alt"></span>
                                SMTP邮局设置
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=boxmoe_options')); ?>">
                                <span class="dashicons dashicons-admin-generic"></span>
                                主题设置
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div id="optionsframework-metabox" class="metabox-holder">
                <div id="optionsframework" class="postbox">
                    <?php if (!empty($notices)) : ?>
                        <div class="group smtp-notices-group">
                            <?php foreach ($notices as $notice) : ?>
                                <div class="boxmoe-smtp-notice is-<?php echo esc_attr($notice['type']); ?>">
                                    <span class="dashicons dashicons-<?php echo $notice['type'] === 'success' ? 'yes-alt' : 'warning'; ?>"></span>
                                    <span class="boxmoe-smtp-notice-text"><?php echo esc_html($notice['message']); ?></span>
                                    <?php if (!empty($notice['action'])) : ?>
                                        <a class="el-button boxmoe-smtp-notice-action" href="<?php echo esc_url($notice['action']); ?>"><?php echo esc_html($notice['action_label']); ?></a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="boxmoe-smtp-settings-form">
                        <div class="group smtp-settings-group">
                            <div class="boxmoe_tab_header"><span class="dashicons dashicons-admin-network"></span> SMTP服务器配置</div>

                            <?php
                            boxmoe_smtp_render_field(
                                'SMTP服务器',
                                '<input type="text" name="smtp_host" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_host')) . '" placeholder="例如: smtp.qq.com">',
                                '请输入 SMTP 服务器地址',
                                'text',
                                'dashicons-cloud'
                            );
                            boxmoe_smtp_render_field(
                                'SMTP端口',
                                '<input type="text" name="smtp_port" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_port')) . '" placeholder="例如: 465 (SSL) 或 587 (TLS)">',
                                '常用端口：465 (SSL)、587 (TLS)',
                                'text',
                                'dashicons-networking'
                            );
                            boxmoe_smtp_render_field(
                                '加密方式',
                                $secure_select,
                                '请选择与端口匹配的加密方式',
                                'select',
                                'dashicons-lock'
                            );
                            ?>

                            <div class="boxmoe_tab_header"><span class="dashicons dashicons-id"></span> 发件人信息</div>

                            <?php
                            boxmoe_smtp_render_field(
                                '邮箱账号',
                                '<input type="text" name="smtp_user" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_user')) . '" placeholder="您的邮箱地址">',
                                'SMTP 登录账号，通常为完整邮箱地址',
                                'text',
                                'dashicons-admin-users'
                            );
                            boxmoe_smtp_render_field(
                                '邮箱密码',
                                '<input type="password" name="smtp_pass" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_pass')) . '" placeholder="SMTP授权码或密码" autocomplete="new-password">',
                                'QQ/163 等邮箱请使用 SMTP 授权码',
                                'text',
                                'dashicons-privacy'
                            );
                            boxmoe_smtp_render_field(
                                '发件人邮箱',
                                '<input type="email" name="smtp_from" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_from')) . '" placeholder="发件人邮箱地址">',
                                '邮件 From 地址，需与邮箱账号一致或已授权',
                                'text',
                                'dashicons-email'
                            );
                            boxmoe_smtp_render_field(
                                '发件人名称',
                                '<input type="text" name="smtp_name" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_name')) . '" placeholder="发件人显示名称">',
                                '收件人看到的发件人昵称',
                                'text',
                                'dashicons-nametag'
                            );
                            boxmoe_smtp_render_field(
                                '消息接受邮箱',
                                '<input type="email" name="smtp_receive_email" class="of-input" value="' . esc_attr(get_option('boxmoe_smtp_receive_email')) . '" placeholder="用于接收通知的邮箱地址">',
                                '用于接收新评论、新会员注册等通知，留空则使用系统默认',
                                'text',
                                'dashicons-bell'
                            );
                            ?>
                        </div>

                        <div id="optionsframework-submit">
                            <input type="submit" name="boxmoe_smtp_save" class="button-primary" value="保存设置">
                            <div class="clear"></div>
                        </div>
                    </form>

                    <form method="post" class="boxmoe-smtp-test-form">
                        <div class="group smtp-test-group">
                            <div class="boxmoe_tab_header"><span class="dashicons dashicons-email-alt"></span> 测试邮件发送</div>
                            <div class="section section-text col section-smtp-test">
                                <div class="heading"><span class="dashicons dashicons-controls-play"></span>测试收件邮箱</div>
                                <div class="option">
                                    <div class="controls boxmoe-smtp-test-controls">
                                        <input type="email" name="test_email" class="of-input" required placeholder="请输入用于测试的收件邮箱">
                                        <input type="submit" name="boxmoe_smtp_test" class="button button-secondary" value="发送测试邮件">
                                    </div>
                                    <div class="explain">发送前请确认已在「通知设置」中开启 SMTP 发件系统开关，并已保存上方配置。</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// 配置WordPress邮件发送
$smtp_switch = get_boxmoe('boxmoe_smtp_mail_switch');

if($smtp_switch){
    // 使用高优先级确保我们的配置不被其他插件覆盖
    add_action('phpmailer_init', 'boxmoe_smtp_config', 9999);
    function boxmoe_smtp_config($phpmailer) {
        // 获取SMTP配置
        $host = get_option('boxmoe_smtp_host');
        $port = get_option('boxmoe_smtp_port');
        $user = get_option('boxmoe_smtp_user');
        $pass = get_option('boxmoe_smtp_pass');
        $from = get_option('boxmoe_smtp_from');
        $name = get_option('boxmoe_smtp_name');
        $secure = get_option('boxmoe_smtp_secure', 'ssl');
        
        // 确保所有必要参数都已设置
        if (empty($host) || empty($port) || empty($user) || empty($pass) || empty($from)) {
            return;
        }
        
        // 强制重置所有相关配置
        $phpmailer->Mailer = 'smtp'; // 先设置为SMTP，确保后续设置生效
        
        // 启用SMTP模式
        $phpmailer->isSMTP();
        
        // 基本配置 - 强制覆盖所有现有设置
        $phpmailer->Host = $host;
        $phpmailer->Port = $port;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $user;
        $phpmailer->Password = $pass;
        
        // 强制使用配置的发件人地址，覆盖所有其他设置
        $phpmailer->setFrom($from, $name, false); // false表示不允许覆盖
        $phpmailer->From = $from;
        $phpmailer->FromName = $name;
        
        // 确保Return-Path与From一致
        $phpmailer->Sender = $from;
        
        // 设置安全协议
        switch ($secure) {
            case 'tls':
                // 使用字符串值兼容所有PHPMailer版本
                $phpmailer->SMTPSecure = 'tls';
                $phpmailer->SMTPAutoTLS = true;
                break;
            case 'ssl':
                $phpmailer->SMTPSecure = 'ssl';
                $phpmailer->SMTPAutoTLS = false;
                break;
            default:
                $phpmailer->SMTPSecure = false;
                $phpmailer->SMTPAutoTLS = false;
                break;
        }
        
        // 添加额外配置以提高可靠性
        $phpmailer->Timeout = 30; // 设置超时时间
        $phpmailer->SMTPKeepAlive = false;
        $phpmailer->CharSet = 'UTF-8';
        $phpmailer->Encoding = 'base64'; // 使用base64编码，提高兼容性
        

        
        // 确保使用正确的邮件格式
        $phpmailer->isHTML(true);
        $phpmailer->WordWrap = 70;
        
        // 最后再次确认使用SMTP，确保不被覆盖
        $phpmailer->Mailer = 'smtp';
    }
    
    // 添加直接测试SMTP连接的功能
    add_action('admin_post_boxmoe_test_smtp_connection', 'boxmoe_test_smtp_connection');
    function boxmoe_test_smtp_connection() {
        if (!current_user_can('manage_options')) {
            wp_die('无权限访问此页面');
        }
        
        // 获取SMTP配置
        $host = get_option('boxmoe_smtp_host');
        $port = get_option('boxmoe_smtp_port');
        $user = get_option('boxmoe_smtp_user');
        $pass = get_option('boxmoe_smtp_pass');
        $secure = get_option('boxmoe_smtp_secure', 'ssl');
        
        // 验证参数
        if (empty($host) || empty($port) || empty($user) || empty($pass)) {
            wp_die('请先填写完整的SMTP配置');
        }
        
        // 直接创建PHPMailer实例测试连接
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // 配置SMTP
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
            
            // 设置安全协议
            switch ($secure) {
                case 'tls':
                    // 使用字符串值兼容所有PHPMailer版本
                    $mail->SMTPSecure = 'tls';
                    break;
                case 'ssl':
                    $mail->SMTPSecure = 'ssl';
                    break;
                default:
                    $mail->SMTPSecure = false;
                    break;
            }
            
            $mail->Timeout = 10;
            $mail->SMTPDebug = 0;
            
            // 尝试连接
            $connection = $mail->smtpConnect();
            
            if ($connection) {
                echo '<div class="updated"><p>SMTP连接测试成功！</p></div>';
                $mail->smtpClose();
            } else {
                echo '<div class="error"><p>SMTP连接测试失败！无法连接到SMTP服务器。</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="error"><p>SMTP连接测试失败：' . $e->getMessage() . '</p></div>';
        }
        
        // 返回SMTP设置页面
        echo '<p><a href="admin.php?page=boxmoe-smtp-settings" class="button">返回SMTP设置</a></p>';
        exit;
    }
}
