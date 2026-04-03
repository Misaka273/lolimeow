<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 👥 用户列表网格卡片式布局
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage User_Grid
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 用户网格UI主类
 */
class Shiroki_User_Grid_UI {

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
        add_action('wp_ajax_shiroki_get_users', array($this, 'ajax_get_users'));
        add_action('wp_ajax_shiroki_delete_user', array($this, 'ajax_delete_user'));
        add_action('wp_ajax_shiroki_bulk_action_users', array($this, 'ajax_bulk_action_users'));
        add_action('wp_ajax_shiroki_change_user_role', array($this, 'ajax_change_user_role'));

        /* 🎨 在管理页脚添加自定义UI */
        add_action('admin_footer', array($this, 'add_custom_user_ui'), 20);
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* 🎯 只在用户列表页面加载 */
        if ($hook !== 'users.php') {
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

        /* 🎨 用户网格样式 */
        wp_enqueue_style(
            'shiroki-user-grid',
            $theme_uri . '/assets/css/admin/user-grid/user-grid.css',
            array('admin-variables'),
            $version
        );

        /* 📦 用户网格脚本 */
        wp_enqueue_script(
            'shiroki-user-grid',
            $theme_uri . '/assets/js/admin/user-grid/user-grid.js',
            array('jquery'),
            $version,
            true
        );

        /* 🎯 传递AJAX配置 */
        wp_localize_script('shiroki-user-grid', 'shirokiUserConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_user_nonce'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'noItems' => '📭 暂无用户',
                'loadMore' => '加载更多',
                'delete' => '删除',
                'edit' => '编辑',
                'view' => '查看',
                'bulkActions' => '批量操作'
            )
        ));
    }

    /**
     * 🎨 添加自定义用户列表UI
     */
    public function add_custom_user_ui() {
        /* 🔍 确保 get_current_screen 函数存在 */
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        /* 🎯 检查是否在用户列表页面 */
        if (!$screen || $screen->id !== 'users') {
            return;
        }

        /* 📊 获取用户统计数据 */
        $user_counts = count_users();
        $total_users = isset($user_counts['total_users']) ? $user_counts['total_users'] : 0;
        $avail_roles = isset($user_counts['avail_roles']) ? $user_counts['avail_roles'] : array();

        /* 📝 获取当前筛选角色 */
        $current_role = isset($_GET['role']) ? sanitize_text_field($_GET['role']) : 'all';
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            /* 🔧 隐藏原版列表表格和分页 */
            $('.wp-list-table, .tablenav, .view-switch, #posts-filter').hide();

            /* 📦 插入自定义UI */
            var customUI = `
                <div class="shiroki-user-wrapper">
                    <!-- 🎯 顶部工具栏：角色筛选 + 搜索框 + 批量操作 -->
                    <div class="shiroki-user-top-bar">
                        <!-- 📊 角色筛选 -->
                        <div class="shiroki-user-filter-wrapper">
                            <span class="shiroki-user-filter-label">📊 角色筛选：</span>
                            <div class="shiroki-user-role-options">
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'all' ? 'active' : ''; ?>" data-role="all">
                                    📁 全部 (<?php echo intval($total_users); ?>)
                                </button>
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'administrator' ? 'active' : ''; ?>" data-role="administrator">
                                    🔴 管理员 (<?php echo isset($avail_roles['administrator']) ? intval($avail_roles['administrator']) : 0; ?>)
                                </button>
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'editor' ? 'active' : ''; ?>" data-role="editor">
                                    🟢 编辑 (<?php echo isset($avail_roles['editor']) ? intval($avail_roles['editor']) : 0; ?>)
                                </button>
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'author' ? 'active' : ''; ?>" data-role="author">
                                    🔵 作者 (<?php echo isset($avail_roles['author']) ? intval($avail_roles['author']) : 0; ?>)
                                </button>
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'contributor' ? 'active' : ''; ?>" data-role="contributor">
                                    🟣 贡献者 (<?php echo isset($avail_roles['contributor']) ? intval($avail_roles['contributor']) : 0; ?>)
                                </button>
                                <button class="shiroki-user-role-btn <?php echo $current_role === 'subscriber' ? 'active' : ''; ?>" data-role="subscriber">
                                    ⚪ 订阅者 (<?php echo isset($avail_roles['subscriber']) ? intval($avail_roles['subscriber']) : 0; ?>)
                                </button>
                            </div>
                        </div>

                        <!-- 📦 批量操作工具栏（初始隐藏） -->
                        <div class="shiroki-user-bulk-actions" id="shiroki-user-bulk-actions" style="display: none;">
                            <span class="shiroki-user-bulk-count">已选择 <span class="shiroki-user-bulk-count-num">0</span> 个</span>
                            <button class="shiroki-user-bulk-btn shiroki-user-bulk-change-role" data-action="change_role">
                                🎭 更改角色
                            </button>
                            <button class="shiroki-user-bulk-btn shiroki-user-bulk-delete" data-action="delete">
                                🗑️ 删除
                            </button>
                            <button class="shiroki-user-bulk-btn shiroki-user-bulk-cancel" data-action="cancel">
                                ❌ 取消
                            </button>
                        </div>

                        <!-- 🔍 搜索框 -->
                        <div class="shiroki-user-search-wrapper">
                            <div class="shiroki-user-search">
                                <input type="text"
                                       id="shiroki-user-search"
                                       placeholder="🔍 搜索用户名、邮箱、显示名称..."
                                       autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-user-loading" id="shiroki-user-loading" style="display: none;">
                        <div class="shiroki-user-loading-spinner"></div>
                        <span>⏳ 加载中...</span>
                    </div>

                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-user-grid" id="shiroki-user-grid">
                        <!-- 用户卡片将通过JavaScript动态插入 -->
                    </div>

                    <!-- 📭 空状态 -->
                    <div class="shiroki-user-empty" id="shiroki-user-empty" style="display: none;">
                        <svg class="shiroki-user-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <div class="shiroki-user-empty-text">📭 暂无用户</div>
                        <div class="shiroki-user-empty-subtext">暂无符合条件的用户</div>
                    </div>

                    <!-- 📄 分页 -->
                    <div class="shiroki-user-pagination" id="shiroki-user-pagination">
                        <!-- 分页按钮将通过JavaScript动态插入 -->
                    </div>
                </div>
            `;

            /* 📦 插入到 .wrap 容器内 */
            $('.wrap > h1').after(customUI);

            /* ➕ 在标题旁边添加新增用户按钮 */
            var addUserButton = `
                <a href="<?php echo admin_url('user-new.php'); ?>" class="shiroki-user-add-btn page-title-action">
                    ➕ 添加用户
                </a>
            `;
            $('.wrap > h1').after(addUserButton);

            /* 🪟 添加更改角色Modal窗口 */
            var roleModalHTML = `
                <div class="shiroki-user-role-modal" id="shiroki-user-role-modal" style="display: none;">
                    <div class="shiroki-user-role-modal-backdrop"></div>
                    <div class="shiroki-user-role-modal-content">
                        <div class="shiroki-user-role-modal-header">
                            <span class="shiroki-user-role-modal-title">🎭 批量更改角色</span>
                            <button class="shiroki-user-role-modal-close" id="shiroki-user-role-modal-close">✕</button>
                        </div>
                        <div class="shiroki-user-role-modal-body">
                            <p class="shiroki-user-role-hint">请选择要设置的新角色（点击选择）：</p>
                            <div class="shiroki-user-role-options" id="shiroki-user-role-options">
                                <button class="shiroki-user-role-option-btn" data-role="administrator">
                                    <span class="shiroki-user-role-option-icon">🔴</span>
                                    <span class="shiroki-user-role-option-name">管理员</span>
                                </button>
                                <button class="shiroki-user-role-option-btn" data-role="editor">
                                    <span class="shiroki-user-role-option-icon">🟢</span>
                                    <span class="shiroki-user-role-option-name">编辑</span>
                                </button>
                                <button class="shiroki-user-role-option-btn" data-role="author">
                                    <span class="shiroki-user-role-option-icon">🔵</span>
                                    <span class="shiroki-user-role-option-name">作者</span>
                                </button>
                                <button class="shiroki-user-role-option-btn" data-role="contributor">
                                    <span class="shiroki-user-role-option-icon">🟣</span>
                                    <span class="shiroki-user-role-option-name">贡献者</span>
                                </button>
                                <button class="shiroki-user-role-option-btn" data-role="subscriber">
                                    <span class="shiroki-user-role-option-icon">⚪</span>
                                    <span class="shiroki-user-role-option-name">订阅者</span>
                                </button>
                            </div>
                        </div>
                        <div class="shiroki-user-role-modal-footer">
                            <button class="shiroki-user-role-confirm" id="shiroki-user-role-confirm">✅ 确认更改</button>
                            <button class="shiroki-user-role-cancel" id="shiroki-user-role-cancel">❌ 取消</button>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(roleModalHTML);

            /* 🚀 触发自定义事件，通知JS可以初始化了 */
            $(document).trigger('shiroki-user-grid-ready');
        });
        </script>
        <?php
    }

    /**
     * 📡 AJAX获取用户列表
     */
    public function ajax_get_users() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_user_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('list_users')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
        $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        /* 📝 构建查询参数 */
        $args = array(
            'orderby' => 'registered',
            'order' => 'DESC',
            'number' => $per_page,
            'offset' => ($page - 1) * $per_page,
        );

        /* 🎭 角色筛选 */
        if ($role !== 'all') {
            $args['role'] = $role;
        }

        /* 🔍 搜索 */
        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array('user_login', 'user_email', 'display_name');
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();

        /* 📊 获取总数（用于分页） */
        $total_args = $args;
        unset($total_args['number']);
        unset($total_args['offset']);
        $total_query = new WP_User_Query($total_args);
        $total_users = $total_query->get_total();
        $total_pages = ceil($total_users / $per_page);

        $formatted_users = array();

        if ($users) {
            foreach ($users as $user) {
                $formatted_user = $this->format_user_item($user);
                if ($formatted_user !== null) {
                    $formatted_users[] = $formatted_user;
                }
            }
        }

        wp_send_json_success(array(
            'users' => $formatted_users,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_users' => $total_users
        ));
    }

    /**
     * 🎨 格式化用户数据
     */
    private function format_user_item($user) {
        if (!$user) {
            return null;
        }

        $user_id = $user->ID;
        $user_info = get_userdata($user_id);

        /* 👤 获取用户信息 */
        $user_login = $user->user_login;
        $display_name = $user->display_name;
        $user_email = $user->user_email;
        $user_registered = $user->user_registered;

        /* 🖼️ 获取用户头像 */
        $avatar = get_avatar($user_id, 96, '', '', array('class' => 'shiroki-user-avatar-img'));

        /* 🎭 获取用户角色 */
        $roles = $user_info->roles;
        $primary_role = !empty($roles) ? $roles[0] : 'subscriber';

        /* 🎨 角色名称映射 */
        $role_names = array(
            'administrator' => '管理员',
            'editor' => '编辑',
            'author' => '作者',
            'contributor' => '贡献者',
            'subscriber' => '订阅者'
        );
        $role_name = isset($role_names[$primary_role]) ? $role_names[$primary_role] : $primary_role;

        /* 📅 格式化日期 */
        $registered_date = mysql2date('Y-m-d', $user_registered);
        $time_ago = human_time_diff(strtotime($user_registered), current_time('timestamp')) . '前';

        /* 📝 获取用户文章数 */
        $post_count = count_user_posts($user_id, 'post');

        /* 🔗 获取自定义用户ID */
        $custom_uid = get_user_meta($user_id, 'custom_uid', true);
        $display_uid = !empty($custom_uid) ? $custom_uid : $user_id;

        return array(
            'id' => $user_id,
            'login' => $user_login,
            'display_name' => $display_name,
            'email' => $user_email,
            'avatar' => $avatar,
            'role' => $primary_role,
            'role_name' => $role_name,
            'registered' => $registered_date,
            'time_ago' => $time_ago,
            'post_count' => intval($post_count),
            'custom_uid' => $display_uid,
            'edit_link' => admin_url("user-edit.php?user_id={$user_id}"),
            'view_link' => get_author_posts_url($user_id)
        );
    }

    /**
     * 🗑️ AJAX删除用户
     */
    public function ajax_delete_user() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_user_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('delete_users')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $current_user_id = get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(array('message' => '无效的用户ID'));
        }

        /* 🔒 防止删除自己 */
        if ($user_id === $current_user_id) {
            wp_send_json_error(array('message' => '不能删除自己'));
        }

        /* 🔒 防止删除管理员（如果不是超级管理员） */
        $user = get_userdata($user_id);
        if ($user && in_array('administrator', $user->roles) && !is_super_admin()) {
            wp_send_json_error(array('message' => '不能删除管理员'));
        }

        $result = wp_delete_user($user_id);

        if ($result) {
            wp_send_json_success(array('message' => '已删除'));
        } else {
            wp_send_json_error(array('message' => '删除失败'));
        }
    }

    /**
     * 📦 AJAX批量操作
     */
    public function ajax_bulk_action_users() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_user_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('delete_users')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $user_ids = isset($_POST['user_ids']) ? sanitize_text_field($_POST['user_ids']) : '';
        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $current_user_id = get_current_user_id();

        if (empty($user_ids) || empty($action)) {
            wp_send_json_error(array('message' => '参数不完整'));
        }

        $ids = explode(',', $user_ids);
        $success_count = 0;
        $failed_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            /* 🔒 防止删除自己 */
            if ($id === $current_user_id) {
                $failed_count++;
                continue;
            }

            /* 🔒 防止删除管理员（如果不是超级管理员） */
            $user = get_userdata($id);
            if ($user && in_array('administrator', $user->roles) && !is_super_admin()) {
                $failed_count++;
                continue;
            }

            $result = false;
            switch ($action) {
                case 'delete':
                    $result = wp_delete_user($id);
                    break;
            }

            if ($result) {
                $success_count++;
            } else {
                $failed_count++;
            }
        }

        wp_send_json_success(array(
            'message' => "成功处理 {$success_count} 个用户" . ($failed_count > 0 ? "，{$failed_count} 个失败" : ''),
            'success_count' => $success_count,
            'failed_count' => $failed_count
        ));
    }

    /**
     * 🎭 AJAX更改用户角色
     */
    public function ajax_change_user_role() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_user_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('promote_users')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $user_ids = isset($_POST['user_ids']) ? sanitize_text_field($_POST['user_ids']) : '';
        $new_role = isset($_POST['new_role']) ? sanitize_text_field($_POST['new_role']) : '';
        $current_user_id = get_current_user_id();

        if (empty($user_ids) || empty($new_role)) {
            wp_send_json_error(array('message' => '参数不完整'));
        }

        /* 🎭 验证角色是否有效 */
        $editable_roles = get_editable_roles();
        if (!isset($editable_roles[$new_role])) {
            wp_send_json_error(array('message' => '无效的角色'));
        }

        $ids = explode(',', $user_ids);
        $success_count = 0;
        $failed_count = 0;

        foreach ($ids as $id) {
            $id = intval($id);
            if ($id <= 0) continue;

            /* 🔒 防止修改自己的角色 */
            if ($id === $current_user_id) {
                $failed_count++;
                continue;
            }

            $user = get_userdata($id);
            if (!$user) {
                $failed_count++;
                continue;
            }

            /* 🔒 防止非超级管理员修改管理员角色 */
            if (in_array('administrator', $user->roles) && !is_super_admin()) {
                $failed_count++;
                continue;
            }

            /* 🎭 设置新角色 */
            $user->set_role($new_role);
            $success_count++;
        }

        /* 🎨 角色名称映射 */
        $role_names = array(
            'administrator' => '管理员',
            'editor' => '编辑',
            'author' => '作者',
            'contributor' => '贡献者',
            'subscriber' => '订阅者'
        );
        $role_name = isset($role_names[$new_role]) ? $role_names[$new_role] : $new_role;

        wp_send_json_success(array(
            'message' => "成功将 {$success_count} 个用户角色更改为「{$role_name}」" . ($failed_count > 0 ? "，{$failed_count} 个失败" : ''),
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'new_role' => $new_role,
            'new_role_name' => $role_name
        ));
    }
}

/**
 * 🚀 初始化用户网格UI
 */
function shiroki_init_user_grid_ui() {
    Shiroki_User_Grid_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_user_grid_ui');
