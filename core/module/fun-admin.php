<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

//boxmoe.com===后台登录页美化
function boxmoe_admin_login_style() {
    wp_enqueue_style('lolimeow-admin-login', get_template_directory_uri() . '/assets/css/admin-login.css', array(), '1.1');
}
add_action('login_enqueue_scripts', 'boxmoe_admin_login_style');

// 🔗 后台所有链接新窗口打开
function boxmoe_admin_all_links_new_tab() {
    if (get_boxmoe('boxmoe_admin_all_links_new_tab')) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('a').each(function() {
                    var href = $(this).attr('href');
                    if (href && href.indexOf('javascript') === -1 && href !== '#') {
                        $(this).attr('target', '_blank');
                    }
                });
            });
        </script>
        <?php
    }
}
add_action('admin_footer', 'boxmoe_admin_all_links_new_tab');

// 🛡️ 屏蔽后台页脚版本信息
function change_footer_admin () {return '';}
add_filter('admin_footer_text', 'change_footer_admin', 9999);
function change_footer_version() {return '';}
add_filter( 'update_footer', 'change_footer_version', 9999);

// 🎨 后台管理配色方案
function boxmoe_admin_color_scheme() {
    wp_enqueue_style('lolimeow-admin-color', get_template_directory_uri() . '/assets/css/admin-color.css', array(), '1.1');
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_color_scheme');

// 📝 文章列表显示缩略图
function boxmoe_admin_post_thumbnail_column($columns) {
    $columns['boxmoe_post_thumb'] = '缩略图';
    return $columns;
}
add_filter('manage_posts_columns', 'boxmoe_admin_post_thumbnail_column');

function boxmoe_admin_post_thumbnail_column_content($column_name, $post_id) {
    if ($column_name == 'boxmoe_post_thumb') {
        $post_thumbnail_id = get_post_thumbnail_id($post_id);
        if ($post_thumbnail_id) {
            $image = wp_get_attachment_image_src($post_thumbnail_id, 'thumbnail');
            echo '<img src="' . $image[0] . '" style="width:50px;height:50px;border-radius:3px;object-fit:cover;" />';
        } else {
            echo '<span style="color:#ddd;font-size:30px;">🖼️</span>';
        }
    }
}
add_action('manage_posts_custom_column', 'boxmoe_admin_post_thumbnail_column_content', 10, 2);

// 🛠️ 仪表盘小工具
function boxmoe_dashboard_widget_function() {
    echo '<div style="text-align:center;">
    <img src="'.boxmoe_theme_url().'/assets/images/logo.png" style="width:100px;margin-bottom:10px;">
    <h3>Lolimeow Theme</h3>
    <p>Version: '.THEME_VERSION.'</p>
    <p>By <a href="https://www.boxmoe.com" target="_blank">Boxmoe</a></p>
    </div>';
}
function boxmoe_add_dashboard_widgets() {
    wp_add_dashboard_widget('boxmoe_dashboard_widget', '关于主题', 'boxmoe_dashboard_widget_function');
}
add_action('wp_dashboard_setup', 'boxmoe_add_dashboard_widgets');


// 🧹 清理后台头部无用信息
function boxmoe_remove_admin_head_info() {
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('admin_init', 'boxmoe_remove_admin_head_info');

// 🔒 阻止非管理员访问后台
function boxmoe_restrict_admin_access() {
    // 避免重定向循环：检查当前是否正在重定向，或者是否是登录页面相关请求
    if (wp_doing_ajax()) {
        return;
    }
    // 检查是否是登录、注册或密码重置页面
    if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-register.php') !== false) {
        return;
    }
    // 检查是否已经是首页，避免循环
    if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == home_url()) {
        return;
    }
    // 检查用户是否已登录
    if (!is_user_logged_in()) {
        return;
    }
    // 检查用户是否有管理权限，只有非管理员才需要重定向
    if (!current_user_can('manage_options') && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF']) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('admin_menu', 'boxmoe_restrict_admin_access');

// 🖼️ 自定义头像字段
function boxmoe_admin_user_avatar_field($user) {
    ?>
    <h2>用户头像设置</h2>
    <table class="form-table">
        <tr>
            <th><label for="boxmoe_user_avatar">自定义头像 URL</label></th>
            <td>
                <input type="text" name="boxmoe_user_avatar" id="boxmoe_user_avatar" value="<?php echo esc_attr(get_user_meta($user->ID, 'user_avatar', true)); ?>" class="regular-text" />
                <button id="boxmoe_upload_avatar_btn" class="button">上传头像</button>
                <p class="description">请输入头像图片地址或点击上传🎉</p>
                <script>
                jQuery(document).ready(function($){
                    $('#boxmoe_upload_avatar_btn').click(function(e) {
                        e.preventDefault();
                        var image = wp.media({ 
                            title: '上传头像',
                            multiple: false
                        }).open().on('select', function(e){
                            var uploaded_image = image.state().get('selection').first();
                            var image_url = uploaded_image.toJSON().url;
                            $('#boxmoe_user_avatar').val(image_url);
                        });
                    });
                });
                </script>
            </td>
        </tr>
    </table>
    <?php
    wp_nonce_field('boxmoe_admin_avatar_nonce', 'boxmoe_admin_avatar_nonce_field');
}
add_action('show_user_profile', 'boxmoe_admin_user_avatar_field');
add_action('edit_user_profile', 'boxmoe_admin_user_avatar_field');

function boxmoe_admin_user_avatar_save($user_id) {
    if (!current_user_can('edit_user', $user_id)) { return false; }
    if (!isset($_POST['boxmoe_admin_avatar_nonce_field']) || !wp_verify_nonce($_POST['boxmoe_admin_avatar_nonce_field'], 'boxmoe_admin_avatar_nonce')) { return; } // ⬅️ nonce 校验
    $url = isset($_POST['boxmoe_user_avatar']) ? esc_url_raw($_POST['boxmoe_user_avatar']) : '';
    if ($url) {
        update_user_meta($user_id, 'user_avatar', $url); // ⬅️ 保存自定义头像 URL
    } else {
        delete_user_meta($user_id, 'user_avatar'); // ⬅️ 清除自定义头像，前端将回落到本地默认头像/QQ 头像
    }
}
add_action('personal_options_update', 'boxmoe_admin_user_avatar_save'); // ⬅️ 保存自己的资料
add_action('edit_user_profile_update', 'boxmoe_admin_user_avatar_save'); // ⬅️ 管理员保存他人资料

// 🆔 后台显示自定义用户ID
function boxmoe_admin_user_custom_uid_field($user) {
    $custom_uid = '';
    // 判断是否为用户对象（编辑模式）
    if (is_object($user) && isset($user->ID)) {
        $custom_uid = get_user_meta($user->ID, 'custom_uid', true);
        if (empty($custom_uid)) {
            $custom_uid = $user->ID;
        }
    }
    ?>
    <h2>用户ID设置</h2>
    <table class="form-table">
        <tr>
            <th><label for="custom_uid">用户ID (UID)</label></th>
            <td>
                <input type="text" name="custom_uid" id="custom_uid" value="<?php echo esc_attr($custom_uid); ?>" class="regular-text" />
                <p class="description">请输入自定义用户ID 👤<?php if (!is_object($user)) echo ' (留空则自动生成)'; ?></p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'boxmoe_admin_user_custom_uid_field'); // ⬅️ 自己的资料页显示UID
add_action('edit_user_profile', 'boxmoe_admin_user_custom_uid_field'); // ⬅️ 管理员编辑其他用户时显示UID
add_action('user_new_form', 'boxmoe_admin_user_custom_uid_field'); // ⬅️ 新增用户时显示UID

// 💾 保存新用户的自定义ID
function boxmoe_save_new_user_custom_uid($user_id) {
    if ( isset( $_POST['custom_uid'] ) ) {
        $custom_uid = sanitize_text_field( $_POST['custom_uid'] );
        if ( ! empty( $custom_uid ) ) {
            // 查重
            $users = get_users(array(
                'meta_key' => 'custom_uid',
                'meta_value' => $custom_uid,
                'exclude' => array($user_id),
                'number' => 1,
                'fields' => 'ID'
            ));

            // 检查系统ID
            $system_user = get_user_by('ID', $custom_uid);

            if (empty($users) && (!$system_user || $system_user->ID == $user_id)) {
                 update_user_meta($user_id, 'custom_uid', $custom_uid);
            }
        } else {
             // 如果没填，则自动生成
             if (function_exists('boxmoe_generate_custom_uid')) {
                 $custom_uid = boxmoe_generate_custom_uid();
                 update_user_meta($user_id, 'custom_uid', $custom_uid);
             }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'createuser') {
         // 后台添加用户但未提交custom_uid字段（理论上不会发生，除非被禁用）
         if (function_exists('boxmoe_generate_custom_uid')) {
             $custom_uid = boxmoe_generate_custom_uid();
             update_user_meta($user_id, 'custom_uid', $custom_uid);
         }
    }
}
add_action('user_register', 'boxmoe_save_new_user_custom_uid'); // ⬅️ 新用户注册保存UID

// 🆔 验证后台自定义用户ID (用于抛出错误提示)
function boxmoe_admin_user_custom_uid_validate($errors, $update, $user) {
    if (isset($_POST['custom_uid'])) {
        $custom_uid = sanitize_text_field($_POST['custom_uid']);
        $user_id = isset($user->ID) ? $user->ID : 0;
        
        $current_uid = get_user_meta($user_id, 'custom_uid', true);
        if ($custom_uid == $current_uid) {
            return;
        }

        if (empty($custom_uid)) {
            // 如果留空且是新增用户，将在 save 钩子中自动生成，这里不报错
            // 如果是编辑用户且留空，意味着什么？删除？
            // 之前的逻辑是 save 中 empty 则 return (不更新)，即保持原样?
            // 不，save 中如果 empty，确实 return 了。
            // 但如果用户想删除UID呢？之前的逻辑不支持删除。
            return;
        }

        // 查重
        $users = get_users(array(
            'meta_key' => 'custom_uid',
            'meta_value' => $custom_uid,
            'exclude' => array($user_id),
            'number' => 1,
            'fields' => 'ID'
        ));

        // 检查系统ID
        $system_user = get_user_by('ID', $custom_uid);

        if (!empty($users) || ($system_user && $system_user->ID != $user_id)) {
            $errors->add('custom_uid_error', '<strong>错误</strong>：该用户ID已存在，请更换其他ID😩');
        }
    }
}
add_action('user_profile_update_errors', 'boxmoe_admin_user_custom_uid_validate', 10, 3); // ⬅️ 验证UID冲突

// 💾 保存后台自定义用户ID
function boxmoe_admin_user_custom_uid_save($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['custom_uid'])) {
        $custom_uid = sanitize_text_field($_POST['custom_uid']);
        $current_uid = get_user_meta($user_id, 'custom_uid', true);
        
        if ($custom_uid == $current_uid) {
            return;
        }

        if (empty($custom_uid)) {
            return; 
        }

        $users = get_users(array(
            'meta_key' => 'custom_uid',
            'meta_value' => $custom_uid,
            'exclude' => array($user_id),
            'number' => 1,
            'fields' => 'ID'
        ));

        // 检查系统ID
        $system_user = get_user_by('ID', $custom_uid);

        if (!empty($users) || ($system_user && $system_user->ID != $user_id)) {
            return;
        }

        update_user_meta($user_id, 'custom_uid', $custom_uid); // ⬅️ 更新UID
    }
}
add_action('personal_options_update', 'boxmoe_admin_user_custom_uid_save'); // ⬅️ 保存自己的UID
add_action('edit_user_profile_update', 'boxmoe_admin_user_custom_uid_save'); // ⬅️ 管理员保存他人UID

// 🎨 后台错误提示弹窗化 (JS注入)
function boxmoe_admin_error_modal_script() {
    ?>
    <style>
        .boxmoe-admin-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .boxmoe-admin-modal {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: boxmoeScaleIn 0.3s ease;
        }
        .boxmoe-admin-modal h3 {
            margin-top: 0;
            color: #ff4d4f;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .boxmoe-admin-modal-content {
            font-size: 15px;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .boxmoe-admin-modal-content p {
            margin: 5px 0;
        }
        .boxmoe-admin-modal-btn {
            background: #1890ff;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .boxmoe-admin-modal-btn:hover {
            background: #40a9ff;
            transform: translateY(-2px);
        }
        @keyframes boxmoeScaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // 检测特定的错误信息
        var $errorNotice = $('.notice-error, .error');
        if ($errorNotice.length > 0) {
            var errorHtml = $errorNotice.html();
            // 检查是否包含我们定义的关键词
            if (errorHtml && errorHtml.indexOf('该用户ID已存在') !== -1) {
                // 隐藏原生提示
                $errorNotice.hide();
                
                var countdown = 3;
                
                // 处理错误信息内容，支持多行
                var contentHtml = $errorNotice.html();
                // 移除 "错误：" 前缀 (包括 strong 标签包裹的)
                contentHtml = contentHtml.replace(/<strong>错误<\/strong>：/g, '').replace(/错误：/g, '');
                
                // 创建弹窗
                var modalHtml = `
                    <div class="boxmoe-admin-modal-overlay">
                        <div class="boxmoe-admin-modal">
                            <h3>⚠️ 操作失败</h3>
                            <div class="boxmoe-admin-modal-content">${contentHtml}</div>
                            <button class="boxmoe-admin-modal-btn" onclick="jQuery('.boxmoe-admin-modal-overlay').remove()">我知道了 (<span id="boxmoe-modal-countdown">${countdown}</span>s)</button>
                        </div>
                    </div>
                `;
                var $modal = $(modalHtml);
                $('body').append($modal);
                
                var timer = setInterval(function() {
                    countdown--;
                    $('#boxmoe-modal-countdown').text(countdown);
                    if (countdown <= 0) {
                        clearInterval(timer);
                        $modal.remove();
                    }
                }, 1000);
            }
        }
    });
    </script>
    <?php
}
add_action('admin_footer', 'boxmoe_admin_error_modal_script'); // ⬅️ 注入弹窗脚本

// 🔗 后台加载媒体库脚本
function boxmoe_admin_profile_enqueue($hook){
    if ($hook === 'profile.php' || $hook === 'user-edit.php') {
        wp_enqueue_media(); // ⬅️ 加载 WP 媒体库
    }
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_profile_enqueue');

function boxmoe_admin_flat_rounded_enqueue($hook){
    wp_enqueue_style('lolimeow-admin-flat-rounded', get_template_directory_uri() . '/assets/css/admin-flat-rounded.css', array(), '1.1');
    wp_enqueue_script('boxmoe-admin-select-ui', get_template_directory_uri() . '/assets/js/admin-select-ui.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_flat_rounded_enqueue');

function boxmoe_admin_view_links_newtab_enqueue($hook){
    wp_enqueue_script('boxmoe-admin-view-newtab', get_template_directory_uri() . '/assets/js/admin-view-newtab.js', array(), THEME_VERSION, true);
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_view_links_newtab_enqueue');
function boxmoe_admin_clear_format_scripts($hook){
	if ($hook === 'post.php' || $hook === 'post-new.php') {
		wp_enqueue_script('boxmoe-clear-format-quicktags', get_template_directory_uri() . '/assets/js/clear-format-quicktags.js', array('quicktags'), THEME_VERSION, true);
	}
}
add_action('admin_enqueue_scripts', 'boxmoe_admin_clear_format_scripts');

function boxmoe_adminbar_viewsite_newtab($wp_admin_bar){
    $node = $wp_admin_bar->get_node('view-site');
    if ($node) {
        $node->meta['target'] = '_blank';
        $node->meta['rel'] = 'noopener noreferrer';
        $wp_admin_bar->add_node($node);
    }
    $site = $wp_admin_bar->get_node('site-name');
    if ($site) {
        $site->meta['target'] = '_blank';
        $site->meta['rel'] = 'noopener noreferrer';
        $wp_admin_bar->add_node($site);
    }
}
add_action('admin_bar_menu', 'boxmoe_adminbar_viewsite_newtab', 100);

function boxmoe_adminbar_wp_logo_to_favicon($wp_admin_bar){
    $src = get_boxmoe('boxmoe_favicon_src');
    if(!$src){
        $src = boxmoe_theme_url().'/assets/images/favicon.ico';
    }
    $logo = $wp_admin_bar->get_node('wp-logo');
    if($logo){
        $logo->title = '<img src="'.esc_url($src).'" alt="favicon" style="width:20px;height:20px;display:inline-block;vertical-align:middle;border-radius:3px;" />';
        $wp_admin_bar->add_node($logo);
    }
}
add_action('admin_bar_menu', 'boxmoe_adminbar_wp_logo_to_favicon', 50);
function boxmoe_adminbar_new_post_newtab($wp_admin_bar){
    $node = $wp_admin_bar->get_node('new-post');
    if ($node) {
        $node->meta['target'] = '_blank';
        $node->meta['rel'] = 'noopener noreferrer';
        $wp_admin_bar->add_node($node);
    }
}
add_action('admin_bar_menu', 'boxmoe_adminbar_new_post_newtab', 100);

// 🔗 文章编辑按钮新窗口打开 (Admin Bar)
function boxmoe_adminbar_edit_post_newtab($wp_admin_bar){
    if (!get_boxmoe('boxmoe_article_edit_target_blank')) return;
    
    $node = $wp_admin_bar->get_node('edit');
    if ($node) {
        $node->meta['target'] = '_blank';
        $node->meta['rel'] = 'noopener noreferrer';
        $wp_admin_bar->add_node($node);
    }
}
add_action('admin_bar_menu', 'boxmoe_adminbar_edit_post_newtab', 100);

// 🆔 后台用户列表显示自定义UID列
function boxmoe_manage_users_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'username') {
            $new_columns['custom_uid'] = '用户ID';
        }
    }
    return $new_columns;
}
add_filter('manage_users_columns', 'boxmoe_manage_users_columns');

// 🆔 后台用户列表自定义UID列内容
function boxmoe_manage_users_custom_column($value, $column_name, $user_id) {
    if ($column_name == 'custom_uid') {
        $custom_uid = get_user_meta($user_id, 'custom_uid', true);
        // 如果有自定义ID则显示，否则显示系统原始ID
        return $custom_uid ? $custom_uid : $user_id;
    }
    return $value;
}
add_filter('manage_users_custom_column', 'boxmoe_manage_users_custom_column', 10, 3);

// 📄 文章列表添加复制按钮
function boxmoe_duplicate_post_link($actions, $post) {
    if (current_user_can('edit_posts')) {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=boxmoe_duplicate_post_as_draft&post=' . $post->ID, 'boxmoe_duplicate_nonce') . '" title="复制这篇文章" rel="permalink">复制</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'boxmoe_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'boxmoe_duplicate_post_link', 10, 2);

// 📄 处理文章复制逻辑
function boxmoe_duplicate_post_as_draft() {
    global $wpdb;
    if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'boxmoe_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
        wp_die('No post to duplicate has been supplied!');
    }

    if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'boxmoe_duplicate_nonce' ) )
        return;

    $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
    $post = get_post( $post_id );

    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;

    if (isset( $post ) && $post != null) {
        $args = array(
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_author'    => $new_post_author,
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_name'      => $post->post_name,
            'post_parent'    => $post->post_parent,
            'post_password'  => $post->post_password,
            'post_status'    => 'draft',
            'post_title'     => $post->post_title,
            'post_type'      => $post->post_type,
            'to_ping'        => $post->to_ping,
            'menu_order'     => $post->menu_order
        );

        $new_post_id = wp_insert_post( $args );

        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
        }

        $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
        if (count($post_meta_infos)!=0) {
            $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
            $sql_query_sel = array();
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if( $meta_key == '_wp_old_slug' ) continue;
                $meta_value = addslashes($meta_info->meta_value);
                $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
            }
            if (!empty($sql_query_sel)) {
                 $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                 $wpdb->query($sql_query);
            }
        }

        wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
        exit;
    } else {
        wp_die('Post creation failed, could not find original post: ' . $post_id);
    }
}
add_action('admin_action_boxmoe_duplicate_post_as_draft', 'boxmoe_duplicate_post_as_draft');

// 📦 修改后台外观菜单中的小工具名称为页面小部件
function boxmoe_rename_widgets_to_page_widgets() {
    global $submenu;
    // 找到外观菜单下的小工具子菜单并修改名称
    if (isset($submenu['themes.php'])) {
        foreach ($submenu['themes.php'] as $key => $item) {
            if ($item[0] == '小部件' || $item[2] == 'widgets.php') {
                $submenu['themes.php'][$key][0] = '页面小部件';
            }
        }
    }
}
add_action('admin_menu', 'boxmoe_rename_widgets_to_page_widgets', 999);

// 📦 修改后台菜单标签中的小工具名称
function boxmoe_rename_widgets_label($translated_text, $text, $domain) {
    if ($text == '小部件' && $domain == 'default') {
        return '页面小部件';
    }
    return $translated_text;
}
add_filter('gettext', 'boxmoe_rename_widgets_label', 10, 3);
add_filter('ngettext', 'boxmoe_rename_widgets_label', 10, 3);

// 🎨 允许在主题设置描述中使用span标签
function boxmoe_allow_span_tags_in_options($allowedtags) {
    $allowedtags['span'] = array(
        'class' => array(),
        'style' => array()
    );
    return $allowedtags;
}
add_filter('wp_kses_allowed_html', 'boxmoe_allow_span_tags_in_options');

// 🆔 后台分类列表添加分类ID列
function boxmoe_manage_categories_columns($columns) {
    $columns['cat_id'] = '分类ID';
    return $columns;
}
add_filter('manage_edit-category_columns', 'boxmoe_manage_categories_columns');

// 🆔 后台分类列表显示分类ID
function boxmoe_manage_categories_custom_column($content, $column_name, $term_id) {
    if ($column_name == 'cat_id') {
        return $term_id;
    }
    return $content;
}
add_filter('manage_category_custom_column', 'boxmoe_manage_categories_custom_column', 10, 3);

// 📋 后台分类列表添加分类ID列到标签分类
function boxmoe_manage_post_tag_columns($columns) {
    $columns['tag_id'] = '标签ID';
    return $columns;
}
add_filter('manage_edit-post_tag_columns', 'boxmoe_manage_post_tag_columns');

// 📋 后台分类列表显示标签ID
function boxmoe_manage_post_tag_custom_column($content, $column_name, $term_id) {
    if ($column_name == 'tag_id') {
        return $term_id;
    }
    return $content;
}
add_filter('manage_post_tag_custom_column', 'boxmoe_manage_post_tag_custom_column', 10, 3);
