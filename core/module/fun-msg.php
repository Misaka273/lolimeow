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

//发件邮件统一模板
function boxmoe_smtp_mail_template($to, $subject, $message) {
    if (!is_email($to)) {
        error_log('错误的邮件地址：' . $to);
        return false;
    }
    if (empty($subject) || empty($message)) {
        error_log('消息错误：消息不能为空');
        return false;
    }
    $from_email = get_option('boxmoe_smtp_from');
    if (!is_email($from_email)) {
        error_log('发件人错误：错误的发件人配置');
        return false;
    }
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_email . ' <' . $from_email . '>'
    );
    $result = wp_mail($to, $subject, $message, $headers);   
    if (!$result) {
        error_log('邮件发送失败：' . $to);
    }
    return $result;
}

//新用户注册消息通知
function boxmoe_new_user_register($user_id){
    $user = get_user_by('id', $user_id);
    $subject = '[' . get_option('blogname') . '] 有新注册会员！';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
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
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
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
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">👤 有新注册会员！</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">😽管理员，您好：</p>
                <p class="greeting">您的网站有新的会员注册，详情如下：</p>
                
                <!-- 会员信息区域 -->
                <div class="info-section">
                    <h3>会员注册信息：</h3>
                    <table class="info-table">
                        <tr>
                            <td class="label">会员账号：</td>
                            <td class="value">' . $user->user_login . '</td>
                        </tr>
                        <tr>
                            <td class="label">会员邮箱：</td>
                            <td class="value">' . $user->user_email . '</td>
                        </tr>
                        <tr>
                            <td class="label">注册时间：</td>
                            <td class="value">' . $user->user_registered . '</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>
    ';

    // 获取消息接受邮箱，优先从独立SMTP设置页面获取，其次从主题选项框架获取，最后使用系统管理员邮箱
    $receive_email = get_option('boxmoe_smtp_receive_email');
    if (empty($receive_email)) {
        $receive_email = get_boxmoe('boxmoe_smtp_receive_email');
    }
    $admin_email = !empty($receive_email) ? $receive_email : get_option('admin_email');
    boxmoe_smtp_mail_template($admin_email, $subject, $message);
}

//评论消息通知
function boxmoe_comment_notification($comment_id){
    $comment = get_comment($comment_id);
    $post = get_post($comment->comment_post_ID);
    $subject = '[' . get_option('blogname') . '] 有新的评论消息！';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
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
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .comment-section {
                margin: 30px 0;
            }
            .comment-section h3 {
                color: #ff6b9d;
                font-size: 18px;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .post-info {
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 10px;
                margin: 20px 0;
                border-left: 4px solid #ff6b9d;
            }
            .post-title {
                font-weight: 600;
                color: #333;
                font-size: 16px;
                margin-bottom: 8px;
            }
            .comment-content {
                background-color: #fef0f6;
                padding: 20px;
                border-radius: 15px;
                margin: 20px 0;
                border: 2px solid #ffd6e7;
                font-size: 15px;
                color: #333;
                line-height: 1.8;
            }
            .comment-meta {
                font-size: 13px;
                color: #999;
                margin: 15px 0;
            }
            .action-button {
                display: inline-block;
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                color: #ffffff;
                padding: 15px 35px;
                text-decoration: none;
                border-radius: 50px;
                font-size: 15px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
                text-align: center;
                margin: 20px 0;
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
                .comment-content {
                    padding: 15px;
                    font-size: 14px;
                }
                .action-button {
                    padding: 12px 25px;
                    font-size: 14px;
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">💬 有新的评论消息！</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">😽管理员，您好：</p>
                <p class="greeting">您的文章收到了新的评论，详情如下：</p>
                
                <!-- 文章信息 -->
                <div class="post-info">
                    <div class="post-title">📝 文章：' . get_the_title($post->ID) . '</div>
                    <div class="comment-meta">👤 评论者：' . trim($comment->comment_author) . '</div>
                    <div class="comment-meta">📅 时间：' . $comment->comment_date . '</div>
                </div>
                
                <!-- 评论内容区域 -->
                <div class="comment-section">
                    <h3>评论内容：</h3>
                    <div class="comment-content">
                        ' . trim($comment->comment_content) . '
                    </div>
                </div>
                
                <!-- 操作按钮 -->
                <div style="text-align: center;">
                    <a href="' . htmlspecialchars(get_comment_link($comment_id)) . '" target="_blank" class="action-button">查看完整评论</a>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>
    ';

    // 获取消息接受邮箱，优先从独立SMTP设置页面获取，其次从主题选项框架获取，最后使用文章作者邮箱
    $receive_email = get_option('boxmoe_smtp_receive_email');
    if (empty($receive_email)) {
        $receive_email = get_boxmoe('boxmoe_smtp_receive_email');
    }
    if (!empty($receive_email)) {
        $to_email = $receive_email;
    } else {
        // 获取文章作者邮箱
        $post_author = get_user_by('id', $post->post_author);
        $to_email = $post_author ? $post_author->user_email : get_option('admin_email');
    }
    boxmoe_smtp_mail_template($to_email, $subject, $message);
}
if(get_boxmoe('boxmoe_new_comment_notice_switch')){
    add_action('comment_post', 'boxmoe_comment_notification');
}

//评论回复消息通知
function boxmoe_comment_reply_notification($comment_id) {
    $comment = get_comment($comment_id);   
    // 基础检查
    if (!$comment || !$comment->comment_parent) {
        return;
    }  
    // 获取父评论
    $parent_comment = get_comment($comment->comment_parent);
    if (!$parent_comment || !is_email($parent_comment->comment_author_email)) {
        return;
    } 
    // 获取文章
    $post = get_post($comment->comment_post_ID);
    if (!$post) {
        return;
    }   
    // 检查评论状态
    if ($comment->comment_approved !== '1') {
        return;
    }   
    $subject = '[' . get_option('blogname') . '] 有新的评论回复消息！';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
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
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .post-info {
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 10px;
                margin: 20px 0;
                border-left: 4px solid #ff6b9d;
            }
            .post-title {
                font-weight: 600;
                color: #333;
                font-size: 16px;
                margin-bottom: 8px;
            }
            .comment-section {
                margin: 30px 0;
            }
            .comment-section h3 {
                color: #ff6b9d;
                font-size: 18px;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .comment-content {
                background-color: #fef0f6;
                padding: 20px;
                border-radius: 15px;
                margin: 20px 0;
                border: 2px solid #ffd6e7;
                font-size: 15px;
                color: #333;
                line-height: 1.8;
            }
            .parent-comment {
                background-color: #f0f8fb;
                padding: 20px;
                border-radius: 15px;
                margin: 20px 0;
                border: 2px solid #b3e5fc;
                font-size: 15px;
                color: #333;
                line-height: 1.8;
            }
            .comment-meta {
                font-size: 13px;
                color: #999;
                margin: 15px 0;
            }
            .action-button {
                display: inline-block;
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                color: #ffffff;
                padding: 15px 35px;
                text-decoration: none;
                border-radius: 50px;
                font-size: 15px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
                text-align: center;
                margin: 20px 0;
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
                .comment-content,
                .parent-comment {
                    padding: 15px;
                    font-size: 14px;
                }
                .action-button {
                    padding: 12px 25px;
                    font-size: 14px;
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">💬 有新的评论回复消息！</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">亲爱的 ' . trim($parent_comment->comment_author) . '：</p>
                <p class="greeting">您的评论收到了新的回复，详情如下：</p>
                
                <!-- 文章信息 -->
                <div class="post-info">
                    <div class="post-title">📝 文章：' . get_the_title($post->ID) . '</div>
                    <div class="comment-meta">👤 回复者：' . trim($comment->comment_author) . '</div>
                    <div class="comment-meta">📅 时间：' . $comment->comment_date . '</div>
                </div>
                
                <!-- 您的原始评论 -->
                <div class="comment-section">
                    <h3>您的原始评论：</h3>
                    <div class="parent-comment">
                        ' . trim($parent_comment->comment_content) . '
                    </div>
                </div>
                
                <!-- 新回复内容 -->
                <div class="comment-section">
                    <h3>最新回复：</h3>
                    <div class="comment-content">
                        ' . trim($comment->comment_content) . '
                    </div>
                </div>
                
                <!-- 操作按钮 -->
                <div style="text-align: center;">
                    <a href="' . htmlspecialchars(get_comment_link($comment_id)) . '" target="_blank" class="action-button">查看完整回复</a>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>
    ';
    boxmoe_smtp_mail_template($parent_comment->comment_author_email, $subject, $message);
}


//找回密码邮件
function boxmoe_reset_password_email($user_login) {
    // 获取用户信息
    $user = get_user_by('login', $user_login);
    if (!$user) {
        return false;
    }
    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        return false;
    }
    $user_email = $user->user_email;
    $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $subject = '[' . get_option('blogname') . '] 密码重置请求';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
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
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .reset-section {
                margin: 30px 0;
            }
            .reset-section h3 {
                color: #ff6b9d;
                font-size: 18px;
                margin-bottom: 20px;
                font-weight: 600;
            }
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            .reset-button {
                display: inline-block;
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                color: #ffffff;
                padding: 18px 40px;
                text-decoration: none;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 700;
                box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
                text-align: center;
            }
            .link-section {
                margin: 30px 0;
            }
            .link-section p {
                color: #666;
                font-size: 14px;
                margin-bottom: 15px;
            }
            .reset-link {
                display: block;
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 10px;
                font-size: 13px;
                word-break: break-all;
                color: #666;
                border: 1px solid #eee;
            }
            .note-box {
                background-color: #fff8f0;
                border-left: 4px solid #ff6b9d;
                padding: 15px;
                margin: 25px 0;
                border-radius: 8px;
            }
            .note-text {
                color: #666;
                font-size: 14px;
                margin: 0;
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
                .reset-button {
                    padding: 15px 30px;
                    font-size: 14px;
                    width: 100%;
                }
                .reset-link {
                    font-size: 12px;
                    padding: 12px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">🔒 密码重置请求</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">尊敬的 ' . $user->user_login . '：</p>
                <p class="greeting">我们收到了您的密码重置请求。如果这不是您本人的操作，请忽略此邮件。</p>
                
                <!-- 重置按钮区域 -->
                <div class="reset-section">
                    <h3>重置您的密码</h3>
                    <p>若要重置密码，请点击下方按钮：</p>
                    <div class="button-container">
                        <a href="' . $reset_link . '" target="_blank" class="reset-button">立即重置密码</a>
                    </div>
                </div>
                
                <!-- 备用链接区域 -->
                <div class="link-section">
                    <p>或者复制以下链接到浏览器地址栏：</p>
                    <div class="reset-link">' . $reset_link . '</div>
                </div>
                
                <!-- 注意事项 -->
                <div class="note-box">
                    <p class="note-text">⚠️ 出于安全考虑，此链接将在24小时后失效</p>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>';
    
    return boxmoe_smtp_mail_template($user_email, $subject, $message);
}

//会员注册成功发生邮件
function boxmoe_new_user_register_email($user_id){
    $user = get_user_by('id', $user_id);
    $subject = '[' . get_option('blogname') . '] 会员注册成功';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端，特别是Outlook */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
                background-color: #fecfef;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(255, 107, 157, 0.2);
                /* Outlook兼容性设置 */
                border: 1px solid #ffffff;
            }
            .email-header {
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
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
            .info-text {
                font-size: 14px;
                color: #666;
                margin: 20px 0;
                line-height: 1.8;
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
            /* Outlook特定样式修复 */
            @media mso {
                .email-wrapper {
                    width: 600px !important;
                }
                .email-header {
                    /* Outlook中使用纯色背景 */
                    background-color: #ff6b9d !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">🎉 会员注册成功</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">亲爱的 ' . $user->user_login . '：</p>
                <p class="greeting">感谢您在 ' . get_option('blogname') . ' 注册会员！</p>
                
                <!-- 会员信息区域 -->
                <div class="info-section">
                    <h3>您的会员信息：</h3>
                    <table class="info-table">
                        <tr>
                            <td class="label">会员账号：</td>
                            <td class="value">' . $user->user_login . '</td>
                        </tr>
                        <tr>
                            <td class="label">会员邮箱：</td>
                            <td class="value">' . $user->user_email . '</td>
                        </tr>
                    </table>
                    <p class="info-text">请妥善保管您的会员账号和密码，如遗忘密码请在线找回。</p>
                </div>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>';
    boxmoe_smtp_mail_template($user->user_email, $subject, $message);
}
//add_action('user_register', 'boxmoe_new_user_register_email');  

//验证码注册模板
function boxmoe_verification_code_register_email($email, $verification_code = ''){
    if (func_num_args() === 1 && is_numeric($email)) {
        return;
    }
    $subject = '[' . get_option('blogname') . '] 注册验证码';
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $subject . '</title>
        <style>
            /* 基础样式，兼容大部分邮件客户端 */
            body {
                font-family: Arial, "Microsoft YaHei", sans-serif;
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
                /* 替换渐变背景为纯色，Outlook不支持渐变 */
                background-color: #ff6b9d;
                padding: 40px 30px;
                text-align: center;
            }
            .email-title {
                color: #ffffff;
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                /* 移除Outlook不支持的text-shadow */
            }
            .email-body {
                padding: 40px 30px;
            }
            .greeting {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }
            .code-section {
                text-align: center;
                margin: 30px 0;
            }
            .verification-code {
                display: inline-block;
                font-size: 36px;
                font-weight: 700;
                color: #ff6b9d;
                background-color: #fef0f6;
                padding: 20px 40px;
                border-radius: 15px;
                letter-spacing: 10px;
                border: 2px solid #ffd6e7;
            }
            .warning-box {
                background-color: #fff8f0;
                border-left: 4px solid #ff6b9d;
                padding: 15px;
                margin: 25px 0;
                border-radius: 8px;
            }
            .warning-text {
                color: #ff6b9d;
                font-weight: 600;
                margin: 0;
            }
            .info-text {
                font-size: 14px;
                color: #666;
                margin: 20px 0;
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
                .verification-code {
                    font-size: 28px;
                    padding: 15px 30px;
                    letter-spacing: 8px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <!-- 邮件头部 -->
            <div class="email-header">
                <h2 class="email-title">📧 注册验证码</h2>
            </div>
            
            <!-- 邮件正文 -->
            <div class="email-body">
                <p class="greeting">亲爱的用户：</p>
                <p class="greeting">您正在进行会员注册，以下是您的验证码：</p>
                
                <!-- 验证码显示区域 -->
                <div class="code-section">
                    <span class="verification-code">' . $verification_code . '</span>
                </div>
                
                <!-- 有效期提示 -->
                <div class="warning-box">
                    <p class="warning-text">⚠️ 有效期5分钟，请尽快使用</p>
                </div>
                
                <!-- 安全提示 -->
                <p class="info-text">请勿将验证码泄露给他人，如非本人操作，请忽略此邮件。</p>
            </div>
            
            <!-- 邮件底部 -->
            <div class="email-footer">
                此邮件由<a href="' . get_option('home') . '" rel="noopener" target="_blank">' . get_option('blogname') . '</a>系统自动发送，请勿直接回复<br>
                © ' . date('Y') . ' ' . get_option('blogname') . ' | ' . get_option('home') . '
            </div>
        </div>
    </body>
    </html>';
    return boxmoe_smtp_mail_template($email, $subject, $message);
}

// ============================================
// 机器人通知功能 - 防止重复发送
// ============================================

if(get_boxmoe('boxmoe_robot_notice_switch')){
    //机器人post接口消息统一模板 - 根据OneBot协议
    function boxmoe_robot_post_template($remote_server, $post_string) {  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
        
        // 获取Access Token
        $access_token = get_boxmoe('boxmoe_robot_api_key');
        $headers = array('Content-Type: application/json; charset=utf-8');
        if ($access_token) {
            $headers[] = 'Authorization: Bearer ' . $access_token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);                
        return $data;  
    } 

    //发送OneBot消息的通用函数
    function boxmoe_send_onebot_message($message_content) {
        // 检查总开关
        if(!get_boxmoe('boxmoe_robot_notice_switch')){
            error_log('机器人总开关未开启');
            return false;
        }
        
        $channel = get_boxmoe('boxmoe_robot_channel');
        $api_url = get_boxmoe('boxmoe_robot_api_url');
        $msg_id = get_boxmoe('boxmoe_robot_msg_user');
        
        // 记录调试信息
        error_log('发送OneBot消息: ' . $message_content);
        error_log('配置: channel=' . $channel . ', api_url=' . $api_url . ', msg_id=' . $msg_id);
        
        if(empty($api_url)) {
            error_log('OneBot通知：API URL 未配置');
            return false;
        }
        
        // 清理API URL
        $api_url = trim($api_url);
        if (!strpos($api_url, 'send_msg')) {
            if (substr($api_url, -1) !== '/') {
                $api_url .= '/';
            }
            $api_url .= 'send_msg';
        }
        
        // 根据OneBot协议构建数据
        $data = array(
            'auto_escape' => true,
            'message' => $message_content
        );
        
        // 根据消息类型设置参数
        if ($channel == 'qq_group') {
            $data['message_type'] = 'group';
            $data['group_id'] = intval($msg_id);
        } else {
            $data['message_type'] = 'private';
            $data['user_id'] = intval($msg_id);
        }
        
        $data_string = json_encode($data);
        error_log('发送数据: ' . $data_string);
        
        $result = boxmoe_robot_post_template($api_url, $data_string);
        error_log('OneBot发送结果: ' . $result);
        
        return $result;
    }

    //评论机器人通知
    function boxmoe_robot_msg_comment($comment_id){
        // 检查评论机器人开关是否开启
        if(!get_boxmoe('boxmoe_new_comment_notice_robot_switch')){
            return;
        }
        
        // 避免重复发送的检查
        static $processed_comments = array();
        if (isset($processed_comments[$comment_id])) {
            error_log('评论已处理，跳过重复发送: ' . $comment_id);
            return;
        }
        $processed_comments[$comment_id] = true;
        
        $comment = get_comment($comment_id);
        if(!$comment) return;
        
        $siteurl = get_bloginfo('url');
        $text = '文章《' . get_the_title($comment->comment_post_ID) . '》有新的评论！';
        $message = $text . "\n" . 
                  "作者: $comment->comment_author\n" .
                  "邮箱: $comment->comment_author_email\n" .
                  "评论: $comment->comment_content\n" .
                  "点击查看：$siteurl/?p=$comment->comment_post_ID#comments";
        
        return boxmoe_send_onebot_message($message);
    }

    //用户注册机器人通知
    function boxmoe_robot_msg_reguser($user_id){
        // 检查用户注册机器人开关是否开启
        if(!get_boxmoe('boxmoe_new_user_register_notice_robot_switch')){
            return;
        }
        
        // 避免重复发送的检查
        static $processed_users = array();
        if (isset($processed_users[$user_id])) {
            error_log('用户已处理，跳过重复发送: ' . $user_id);
            return;
        }
        $processed_users[$user_id] = true;
        
        error_log('机器人通知函数被调用，user_id: ' . $user_id);
        
        $user = get_user_by('id', $user_id);
        if(!$user) {
            error_log('机器人通知：未找到用户 user_id: ' . $user_id);
            return;
        }
        
        $text = '[' . get_bloginfo('name') . ']新会员注册通知！';
        $message = $text . "\n" . 
                  "用户名：{$user->user_login}\n" .
                  "邮箱：{$user->user_email}\n" .
                  "注册时间：" . date('Y-m-d H:i:s', strtotime($user->user_registered));
        
        return boxmoe_send_onebot_message($message);
    }
    
    // 只在必要时注册钩子，避免重复注册
    function boxmoe_register_robot_hooks() {
        // 移除所有可能的重复钩子
        remove_action('comment_post', 'boxmoe_robot_msg_comment');
        remove_action('user_register', 'boxmoe_robot_msg_reguser');
        
        // 根据开关注册钩子
        if(get_boxmoe('boxmoe_new_comment_notice_robot_switch')){
            add_action('comment_post', 'boxmoe_robot_msg_comment', 20, 1);
            error_log('已注册评论机器人钩子');
        }
        
        if(get_boxmoe('boxmoe_new_user_register_notice_robot_switch')){
            add_action('user_register', 'boxmoe_robot_msg_reguser', 20, 1);
            error_log('已注册用户注册机器人钩子');
        }
    }
    
    // 在init时注册钩子，确保只注册一次
    add_action('init', 'boxmoe_register_robot_hooks');
}