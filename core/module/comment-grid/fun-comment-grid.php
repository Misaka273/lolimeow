<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💬 评论列表网格卡片式布局
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Comment_Grid
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 评论网格UI主类
 */
class Shiroki_Comment_Grid_UI {

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📝 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 🚀 构造函数
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * 🔗 初始化钩子
     */
    private function init_hooks() {
        /* 🎨 加载自定义样式和脚本 */
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        /* 📡 AJAX处理 */
        add_action('wp_ajax_shiroki_get_comments', array($this, 'ajax_get_comments'));
        add_action('wp_ajax_shiroki_approve_comment', array($this, 'ajax_approve_comment'));
        add_action('wp_ajax_shiroki_unapprove_comment', array($this, 'ajax_unapprove_comment'));
        add_action('wp_ajax_shiroki_spam_comment', array($this, 'ajax_spam_comment'));
        add_action('wp_ajax_shiroki_unspam_comment', array($this, 'ajax_unspam_comment'));
        add_action('wp_ajax_shiroki_trash_comment', array($this, 'ajax_trash_comment'));
        add_action('wp_ajax_shiroki_untrash_comment', array($this, 'ajax_untrash_comment'));
        add_action('wp_ajax_shiroki_delete_comment', array($this, 'ajax_delete_comment'));
        add_action('wp_ajax_shiroki_reply_comment', array($this, 'ajax_reply_comment'));
        add_action('wp_ajax_shiroki_bulk_action_comments', array($this, 'ajax_bulk_action_comments'));
        add_action('wp_ajax_shiroki_get_comment_content', array($this, 'ajax_get_comment_content'));

        /* 🎨 在管理页脚添加自定义UI */
        add_action('admin_footer', array($this, 'add_custom_comment_ui'), 20);
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* 🎯 只在评论列表页面加载 */
        if ($hook !== 'edit-comments.php') {
            return;
        }

        $theme_uri = get_template_directory_uri();
        $version = wp_get_theme()->get('Version');

        /* 🎨 先加载统一变量文件 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );

        /* 🎨 复用媒体库样式 */
        wp_enqueue_style(
            'shiroki-comment-grid',
            $theme_uri . '/assets/css/admin/comment-grid/comment-grid.css',
            array('admin-variables'),
            $version
        );

        /* 📦 评论网格脚本 */
        wp_enqueue_script(
            'shiroki-comment-grid',
            $theme_uri . '/assets/js/admin/comment-grid/comment-grid.js',
            array('jquery'),
            $version,
            true
        );

        /* 🎯 传递AJAX配置 */
        wp_localize_script('shiroki-comment-grid', 'shirokiCommentConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_comment_nonce'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无评论',
                'loadMore' => '加载更多',
                'approve' => '批准',
                'unapprove' => '驳回',
                'spam' => '标记垃圾',
                'unspam' => '恢复',
                'trash' => '移至回收站',
                'untrash' => '还原',
                'delete' => '永久删除',
                'reply' => '回复',
                'edit' => '编辑',
                'view' => '查看',
                'copyLink' => '复制链接',
                'copyContent' => '复制内容',
                'bulkActions' => '批量操作'
            )
        ));
    }

    /**
     * 🎨 添加自定义评论列表UI
     */
    public function add_custom_comment_ui() {
        /* 🔍 确保 get_current_screen 函数存在 */
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        /* 🎯 检查是否在评论列表页面 */
        if (!$screen || $screen->id !== 'edit-comments') {
            return;
        }

        /* 📊 获取评论状态数量 */
        $comment_counts = wp_count_comments();

        /* ◀️ 确保 $comment_counts 是对象 */
        if (!is_object($comment_counts)) {
            $comment_counts = new stdClass();
            $comment_counts->approved = 0;
            $comment_counts->moderated = 0;
            $comment_counts->spam = 0;
            $comment_counts->trash = 0;
            $comment_counts->total_comments = 0;
        }

        /* 📝 获取当前筛选状态 */
        $current_status = isset($_GET['comment_status']) ? sanitize_text_field($_GET['comment_status']) : 'all';
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            /* 🔧 隐藏原版列表表格和分页 */
            $('.wp-list-table, .tablenav, .view-switch, #comments-form').hide();

            /* 📝 获取当前post ID（如果有） */
            var postId = '<?php echo isset($_GET['p']) ? intval($_GET['p']) : ''; ?>';

            /* 📦 插入自定义UI */
            var customUI = `
                <div class="shiroki-comment-wrapper">
                    <!-- 🎯 顶部工具栏：状态筛选 + 搜索框 + 批量操作 -->
                    <div class="shiroki-comment-top-bar">
                        <!-- 📊 状态筛选 -->
                        <div class="shiroki-comment-filter-wrapper">
                            <span class="shiroki-comment-filter-label">📊 状态筛选：</span>
                            <div class="shiroki-comment-status-options">
                                <button class="shiroki-comment-status-btn <?php echo $current_status === 'all' ? 'active' : ''; ?>" data-status="all">
                                    📁 全部 (<?php echo intval($comment_counts->total_comments); ?>)
                                </button>
                                <button class="shiroki-comment-status-btn <?php echo $current_status === 'moderated' ? 'active' : ''; ?>" data-status="moderated">
                                    🟠 待审核 (<?php echo intval($comment_counts->moderated); ?>)
                                </button>
                                <button class="shiroki-comment-status-btn <?php echo $current_status === 'approved' ? 'active' : ''; ?>" data-status="approved">
                                    🟢 已批准 (<?php echo intval($comment_counts->approved); ?>)
                                </button>
                                <button class="shiroki-comment-status-btn <?php echo $current_status === 'spam' ? 'active' : ''; ?>" data-status="spam">
                                    🚫 垃圾评论 (<?php echo intval($comment_counts->spam); ?>)
                                </button>
                                <button class="shiroki-comment-status-btn <?php echo $current_status === 'trash' ? 'active' : ''; ?>" data-status="trash">
                                    🗑️ 回收站 (<?php echo intval($comment_counts->trash); ?>)
                                </button>
                            </div>
                        </div>

                        <!--  批量操作工具栏（初始隐藏） -->
                        <div class="shiroki-comment-bulk-actions" id="shiroki-comment-bulk-actions" style="display: none;">
                            <span class="shiroki-comment-bulk-count">已选择 <span class="shiroki-comment-bulk-count-num">0</span> 个</span>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-approve" data-action="approve">
                                ✅ 批准
                            </button>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-unapprove" data-action="unapprove">
                                ❌ 驳回
                            </button>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-spam" data-action="spam">
                                🚫 标记垃圾
                            </button>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-trash" data-action="trash">
                                🗑️ 移至回收站
                            </button>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-delete" data-action="delete">
                                🗑️ 永久删除
                            </button>
                            <button class="shiroki-comment-bulk-btn shiroki-comment-bulk-cancel" data-action="cancel">
                                ❌ 取消选择
                            </button>
                        </div>

                        <!-- 🔍 搜索框 -->
                        <div class="shiroki-comment-search-wrapper">
                            <div class="shiroki-comment-search">
                                <input type="text"
                                       id="shiroki-comment-search"
                                       placeholder="🔍 搜索评论内容...评论人"
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-comment-loading" id="shiroki-comment-loading" style="display: none;">
                        <div class="shiroki-comment-loading-spinner"></div>
                        <span>⏳ 加载中...</span>
                    </div>

                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-comment-grid" id="shiroki-comment-grid">
                        <!-- 评论卡片将通过JavaScript动态插入 -->
                    </div>

                    <!-- 📭 空状态 -->
                    <div class="shiroki-comment-empty" id="shiroki-comment-empty" style="display: none;">
                        <svg class="shiroki-comment-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <div class="shiroki-comment-empty-text">📭 暂无评论</div>
                        <div class="shiroki-comment-empty-subtext">暂无符合条件的评论</div>
                    </div>

                </div>
            `;

            /* 📦 插入到 .wrap 容器内 */
            $('.wrap > h1').after(customUI);

            /* 🚀 触发自定义事件，通知JS可以初始化了 */
            $(document).trigger('shiroki-comment-grid-ready', [postId]);
        });
        </script>
        <?php
    }

    /**
     * 📡 AJAX获取评论列表
     */
    public function ajax_get_comments() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 8;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        /* 🔍 搜索模式下返回所有匹配结果，不使用分页
         * ◀️ 前端搜索时会重置加载，需要获取所有结果 */
        $is_search_mode = !empty($search);

        /* 📝 构建查询参数 */
        $args = array(
            'orderby' => 'comment_date_gmt',
            'order' => 'DESC'
        );

        /* 📄 非搜索模式下使用分页 */
        if (!$is_search_mode) {
            $args['number'] = $per_page;
            $args['offset'] = ($page - 1) * $per_page;
        }

        /* 📊 自定义排序逻辑
         * ◀️ 「全部」分类：待审核排最前，回收站排最后
         * ◀️ 其他分类：按日期倒序，最新的在前 */
        $shiroki_comment_order_callback = function($clauses) use ($status) {
            global $wpdb;
            if ($status === 'all') {
                /* 📁 「全部」分类：先按状态排序，再按日期倒序 */
                $clauses['orderby'] = "FIELD({$wpdb->comments}.comment_approved, '0', '1', 'spam', 'trash') ASC, {$wpdb->comments}.comment_date_gmt DESC";
            } else {
                /* 📂 其他分类（已批准、待审核、垃圾、回收站）：只按日期倒序 */
                $clauses['orderby'] = "{$wpdb->comments}.comment_date_gmt DESC";
            }
            return $clauses;
        };
        add_filter('comments_clauses', $shiroki_comment_order_callback);

        /* 📊 状态筛选 */
        if ($status === 'moderated') {
            $args['status'] = 'hold';
        } elseif ($status === 'approved') {
            $args['status'] = 'approve';
        } elseif ($status === 'spam') {
            $args['status'] = 'spam';
        } elseif ($status === 'trash') {
            $args['status'] = 'trash';
        } else {
            /* 📁 「全部」分类包含所有状态（approve、hold、spam、trash）
             * ◀️ WordPress 的 'all' 不包含 spam 和 trash，需要明确指定所有状态 */
            $args['status'] = array('approve', 'hold', 'spam', 'trash');
        }

        /* 📝 文章筛选 */
        if ($post_id > 0) {
            $args['post_id'] = $post_id;
        }

        /* 🔍 搜索 */
        if (!empty($search)) {
            $args['search'] = $search;
        }

        $comments_query = new WP_Comment_Query($args);
        $comments = $comments_query->comments;

        /* 🧹 移除自定义排序 filter，避免影响其他查询 */
        remove_filter('comments_clauses', $shiroki_comment_order_callback);

        /* 📊 获取总数（用于判断是否有更多） */
        if (!$is_search_mode) {
            $total_args = $args;
            unset($total_args['number']);
            unset($total_args['offset']);
            $total_comments = get_comments($total_args);
            $has_more = ($page * $per_page) < count($total_comments);
        } else {
            /* 🔍 搜索模式下没有更多 */
            $has_more = false;
        }

        $formatted_comments = array();

        if ($comments) {
            foreach ($comments as $comment) {
                $formatted_comment = $this->format_comment_item($comment);
                if ($formatted_comment !== null) {
                    $formatted_comments[] = $formatted_comment;
                }
            }
        }

        wp_send_json_success(array(
            'comments' => $formatted_comments,
            'current_page' => $page,
            'has_more' => $has_more
        ));
    }

    /**
     * 🎨 格式化评论数据
     */
    private function format_comment_item($comment) {
        if (!$comment) {
            return null;
        }

        /* 👤 获取作者信息 */
        $author_name = $comment->comment_author;
        $author_email = $comment->comment_author_email;
        $author_url = $comment->comment_author_url;
        $author_ip = $comment->comment_author_IP;

        /* 🖼️ 获取作者头像 */
        $avatar = get_avatar($author_email, 64, '', '', array('class' => 'shiroki-comment-avatar-img'));

        /* 📝 获取评论内容 */
        $content = wp_kses_post($comment->comment_content);
        $content_excerpt = wp_trim_words($content, 30, '...');

        /* 📄 获取关联文章 */
        $post = get_post($comment->comment_post_ID);
        $post_title = $post ? $post->post_title : '未知文章';
        $post_link = $post ? get_permalink($post->ID) : '';
        $post_edit_link = $post ? get_edit_post_link($post->ID, 'raw') : '';

        /* 📅 格式化日期 */
        $date = mysql2date('Y-m-d H:i', $comment->comment_date);
        $time_ago = human_time_diff(strtotime($comment->comment_date_gmt), current_time('timestamp')) . '前';

        /* 📊 获取回复数量 */
        $reply_count = get_comments(array(
            'parent' => $comment->comment_ID,
            'count' => true
        ));

        /* 🏷️ 判断评论类型 */
        $is_pingback = $comment->comment_type === 'pingback';
        $is_trackback = $comment->comment_type === 'trackback';
        $comment_type_label = '';
        if ($is_pingback) {
            $comment_type_label = 'Pingback';
        } elseif ($is_trackback) {
            $comment_type_label = 'Trackback';
        }

        return array(
            'id' => $comment->comment_ID,
            'author_name' => $author_name,
            'author_email' => $author_email,
            'author_url' => $author_url,
            'author_ip' => $author_ip,
            'avatar' => $avatar,
            'content' => $content,
            'content_excerpt' => $content_excerpt,
            'post_id' => $comment->comment_post_ID,
            'post_title' => $post_title,
            'post_link' => $post_link,
            'post_edit_link' => $post_edit_link,
            'date' => $date,
            'time_ago' => $time_ago,
            'status' => $comment->comment_approved,
            'type' => $comment->comment_type,
            'type_label' => $comment_type_label,
            'reply_count' => intval($reply_count),
            'parent' => $comment->comment_parent,
            'edit_link' => admin_url("comment.php?action=editcomment&c={$comment->comment_ID}"),
            'view_link' => get_comment_link($comment)
        );
    }

    /**
     * ✅ AJAX批准评论
     */
    public function ajax_approve_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_set_comment_status($comment_id, 'approve');

        if ($result) {
            wp_send_json_success(array('message' => '已批准'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * ❌ AJAX驳回评论
     */
    public function ajax_unapprove_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_set_comment_status($comment_id, 'hold');

        if ($result) {
            wp_send_json_success(array('message' => '已驳回'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * 🚫 AJAX标记垃圾评论
     */
    public function ajax_spam_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_spam_comment($comment_id);

        if ($result) {
            wp_send_json_success(array('message' => '已标记为垃圾评论'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * ♻️ AJAX恢复垃圾评论
     */
    public function ajax_unspam_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_unspam_comment($comment_id);

        if ($result) {
            wp_send_json_success(array('message' => '已恢复'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * 🗑️ AJAX移至回收站
     */
    public function ajax_trash_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_trash_comment($comment_id);

        if ($result) {
            wp_send_json_success(array('message' => '已移至回收站'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * ♻️ AJAX从回收站还原
     */
    public function ajax_untrash_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_untrash_comment($comment_id);

        if ($result) {
            wp_send_json_success(array('message' => '已还原'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * 🗑️ AJAX永久删除
     */
    public function ajax_delete_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $result = wp_delete_comment($comment_id, true);

        if ($result) {
            wp_send_json_success(array('message' => '已永久删除'));
        } else {
            wp_send_json_error(array('message' => '操作失败'));
        }
    }

    /**
     * 💬 AJAX回复评论
     */
    public function ajax_reply_comment() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_comment')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (!$comment_id || empty($content)) {
            wp_send_json_error(array('message' => '参数不完整'));
        }

        $parent_comment = get_comment($comment_id);
        if (!$parent_comment) {
            wp_send_json_error(array('message' => '父评论不存在'));
        }

        $user = wp_get_current_user();

        $comment_data = array(
            'comment_post_ID' => $parent_comment->comment_post_ID,
            'comment_author' => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_url' => $user->user_url,
            'comment_content' => $content,
            'comment_parent' => $comment_id,
            'user_id' => $user->ID,
            'comment_approved' => 1
        );

        $new_comment_id = wp_insert_comment($comment_data);

        if ($new_comment_id) {
            $new_comment = get_comment($new_comment_id);
            wp_send_json_success(array(
                'message' => '回复成功',
                'comment' => $this->format_comment_item($new_comment)
            ));
        } else {
            wp_send_json_error(array('message' => '回复失败'));
        }
    }

    /**
     * 📦 AJAX批量操作
     */
    public function ajax_bulk_action_comments() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('moderate_comments')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_ids = isset($_POST['comment_ids']) ? sanitize_text_field($_POST['comment_ids']) : '';
        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';

        if (empty($comment_ids) || empty($action)) {
            wp_send_json_error(array('message' => '参数不完整'));
        }

        $ids = explode(',', $comment_ids);
        $success_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            $result = false;
            switch ($action) {
                case 'approve':
                    $result = wp_set_comment_status($id, 'approve');
                    break;
                case 'unapprove':
                    $result = wp_set_comment_status($id, 'hold');
                    break;
                case 'spam':
                    $result = wp_spam_comment($id);
                    break;
                case 'unspam':
                    $result = wp_unspam_comment($id);
                    break;
                case 'trash':
                    $result = wp_trash_comment($id);
                    break;
                case 'untrash':
                    $result = wp_untrash_comment($id);
                    break;
                case 'delete':
                    $result = wp_delete_comment($id, true);
                    break;
            }

            if ($result) {
                $success_count++;
            }
        }

        wp_send_json_success(array(
            'message' => "已成功处理 {$success_count} 条评论",
            'count' => $success_count
        ));
    }

    /**
     * 📋 AJAX获取评论内容
     */
    public function ajax_get_comment_content() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_comment_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

        if (!$comment_id) {
            wp_send_json_error(array('message' => '无效的评论ID'));
        }

        $comment = get_comment($comment_id);

        if (!$comment) {
            wp_send_json_error(array('message' => '评论不存在'));
        }

        /* 📝 构建完整的评论内容 */
        $full_content = '';
        $full_content .= "作者：" . $comment->comment_author . " <" . $comment->comment_author_email . ">\n";
        $full_content .= "文章：" . get_the_title($comment->comment_post_ID) . "\n";
        $full_content .= "时间：" . $comment->comment_date . "\n";
        $full_content .= "链接：" . get_comment_link($comment) . "\n";
        $full_content .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $full_content .= strip_tags($comment->comment_content);

        wp_send_json_success(array(
            'content' => $full_content,
            'author' => $comment->comment_author,
            'date' => $comment->comment_date
        ));
    }
}

/**
 * 🚀 初始化评论网格UI
 */
function shiroki_init_comment_grid_ui() {
    Shiroki_Comment_Grid_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_comment_grid_ui');
