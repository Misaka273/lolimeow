<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🧩 小工具管理页面 - 拖拽式侧边栏配置
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Widget_Manager
 * @since 1.0.0
 */

/* ◀️ 防止直接访问 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 小工具管理UI主类
 */
class Shiroki_Widget_Manager_UI {

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
        add_action('wp_ajax_shiroki_get_widgets', array($this, 'ajax_get_widgets'));
        add_action('wp_ajax_shiroki_save_widget_order', array($this, 'ajax_save_widget_order'));
        add_action('wp_ajax_shiroki_activate_widget', array($this, 'ajax_activate_widget'));
        add_action('wp_ajax_shiroki_deactivate_widget', array($this, 'ajax_deactivate_widget'));
        add_action('wp_ajax_shiroki_update_widget_settings', array($this, 'ajax_update_widget_settings'));
        add_action('wp_ajax_shiroki_reset_widgets', array($this, 'ajax_reset_widgets'));

        /* 🎨 在管理页脚添加自定义UI */
        add_action('admin_footer', array($this, 'add_custom_widget_ui'), 20);
    }

    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        /* 🎯 只在小工具页面加载 */
        if ($hook !== 'widgets.php') {
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

        /* 🎨 小工具管理样式 */
        wp_enqueue_style(
            'shiroki-widget-manager',
            $theme_uri . '/assets/css/admin/widget-manager/widget-manager.css',
            array('admin-variables'),
            $version
        );

        /* 📷 加载WordPress媒体上传库 */
        wp_enqueue_media();

        /* 📦 小工具管理脚本 */
        wp_enqueue_script(
            'shiroki-widget-manager',
            $theme_uri . '/assets/js/admin/widget-manager/widget-manager.js',
            array('jquery', 'jquery-ui-sortable'),
            $version,
            true
        );

        /* 🎯 传递AJAX配置 */
        wp_localize_script('shiroki-widget-manager', 'shirokiWidgetConfig', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('shiroki_widget_nonce'),
            'strings' => array(
                'loading' => '⏳ 加载中...',
                'saveSuccess' => '✅ 保存成功',
                'saveError' => '❌ 保存失败',
                'confirmReset' => '确定要重置所有小工具设置吗？此操作不可恢复！',
                'dragHint' => '💡 拖拽小工具到右侧边栏区域',
                'emptySidebar' => '📭 该侧边栏暂无小工具'
            )
        ));
    }

    /**
     * 🎨 添加自定义小工具管理UI
     */
    public function add_custom_widget_ui() {
        /* 🔍 确保 get_current_screen 函数存在 */
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        /* 🎯 检查是否在小工具页面 */
        if (!$screen || $screen->id !== 'widgets') {
            return;
        }

        /* 📊 获取所有已注册的小工具 */
        global $wp_widget_factory;
        $available_widgets = array();
        if ($wp_widget_factory && !empty($wp_widget_factory->widgets)) {
            foreach ($wp_widget_factory->widgets as $widget_class => $widget_obj) {
                $available_widgets[] = array(
                    'id' => $widget_obj->id_base,
                    'name' => $widget_obj->name,
                    'description' => !empty($widget_obj->widget_options['description']) ? $widget_obj->widget_options['description'] : '',
                    'class' => $widget_class
                );
            }
        }

        /* 📊 获取所有侧边栏 */
        $sidebars = $this->get_theme_sidebars();
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            /* 🔧 隐藏原版小工具界面 */
            $('.widget-liquid-left, .widget-liquid-right, .widgets-holder-wrap, #widgets-right').hide();
            $('.wrap > h1').nextAll('.notice, .updated, .error').hide();

            /* 📦 插入自定义UI */
            var customUI = `
                <div class="shiroki-widget-wrapper">
                    <!-- 🎯 顶部工具栏 -->
                    <div class="shiroki-widget-top-bar">
                        <div class="shiroki-widget-header-left">
                            <span class="shiroki-widget-title">🧩 小工具管理</span>
                            <span class="shiroki-widget-subtitle">拖拽配置您的侧边栏与底部栏，自动保存</span>
                        </div>
                        <div class="shiroki-widget-actions">
                            <button class="shiroki-widget-btn shiroki-widget-btn-reset" id="shiroki-widget-reset">
                                <span class="shiroki-widget-btn-icon">🔄</span>
                                <span class="shiroki-widget-btn-text">重置</span>
                            </button>
                        </div>
                    </div>

                    <!-- 📦 主内容区域 -->
                    <div class="shiroki-widget-main">
                        <!-- 📋 左侧：可用小工具库 -->
                        <div class="shiroki-widget-library">
                            <div class="shiroki-widget-library-header">
                                <span class="shiroki-widget-library-title">📦 可用小工具</span>
                                <div class="shiroki-widget-search">
                                    <input type="text" id="shiroki-widget-search" placeholder="搜索小工具...">
                                </div>
                            </div>
                            <div class="shiroki-widget-library-content" id="shiroki-widget-library">
                                <!-- 小工具列表将通过JS动态加载 -->
                            </div>
                        </div>

                        <!-- 📋 右侧：侧边栏区域 -->
                        <div class="shiroki-widget-sidebars">
                            <?php foreach ($sidebars as $sidebar_id => $sidebar): ?>
                            <div class="shiroki-widget-sidebar" data-sidebar="<?php echo esc_attr($sidebar_id); ?>">
                                <div class="shiroki-widget-sidebar-header">
                                    <div class="shiroki-widget-sidebar-info">
                                        <span class="shiroki-widget-sidebar-icon"><?php echo esc_html($sidebar['icon']); ?></span>
                                        <span class="shiroki-widget-sidebar-name"><?php echo esc_html($sidebar['name']); ?></span>
                                    </div>
                                    <span class="shiroki-widget-sidebar-count">
                                        <span class="shiroki-widget-count-num" data-sidebar="<?php echo esc_attr($sidebar_id); ?>">0</span> 个小工具
                                    </span>
                                </div>
                                <div class="shiroki-widget-sidebar-content" data-sidebar="<?php echo esc_attr($sidebar_id); ?>">
                                    <div class="shiroki-widget-drop-zone">
                                        <span class="shiroki-widget-drop-hint">📥 拖拽小工具到此处</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- ⏳ 加载状态 -->
                    <div class="shiroki-widget-loading" id="shiroki-widget-loading" style="display: none;">
                        <div class="shiroki-widget-loading-spinner"></div>
                        <span>⏳ 加载中...</span>
                    </div>
                </div>
            `;

            /* 📦 插入到 .wrap 容器内 */
            $('.wrap > h1').after(customUI);

            /* 🪟 添加小工具设置Modal窗口 */
            var widgetModalHTML = `
                <div class="shiroki-widget-modal" id="shiroki-widget-modal" style="display: none;">
                    <div class="shiroki-widget-modal-backdrop"></div>
                    <div class="shiroki-widget-modal-content">
                        <div class="shiroki-widget-modal-header">
                            <span class="shiroki-widget-modal-title">⚙️ 小工具设置</span>
                            <button type="button" class="shiroki-widget-modal-close" id="shiroki-widget-modal-close">✕</button>
                        </div>
                        <div class="shiroki-widget-modal-body" id="shiroki-widget-modal-body">
                            <!-- 设置表单将动态插入 -->
                        </div>
                        <div class="shiroki-widget-modal-footer">
                            <button type="button" class="shiroki-widget-modal-btn shiroki-widget-modal-delete" id="shiroki-widget-modal-delete">
                                <span>🗑️</span> 删除
                            </button>
                            <div class="shiroki-widget-modal-actions">
                                <button type="button" class="shiroki-widget-modal-btn shiroki-widget-modal-cancel" id="shiroki-widget-modal-cancel">取消</button>
                                <button type="button" class="shiroki-widget-modal-btn shiroki-widget-modal-confirm" id="shiroki-widget-modal-confirm">保存设置</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(widgetModalHTML);

            /* ⚠️ 添加确认对话框Modal */
            var confirmModalHTML = `
                <div class="shiroki-widget-modal" id="shiroki-confirm-modal" style="display: none;">
                    <div class="shiroki-widget-modal-backdrop"></div>
                    <div class="shiroki-widget-modal-content shiroki-confirm-modal-content">
                        <div class="shiroki-widget-modal-header">
                            <span class="shiroki-widget-modal-title">⚠️ 确认操作</span>
                            <button type="button" class="shiroki-widget-modal-close" id="shiroki-confirm-modal-close">✕</button>
                        </div>
                        <div class="shiroki-widget-modal-body" id="shiroki-confirm-modal-body">
                            <div class="shiroki-confirm-message"></div>
                        </div>
                        <div class="shiroki-widget-modal-footer">
                            <div class="shiroki-widget-modal-actions" style="margin-left: auto;">
                                <button type="button" class="shiroki-widget-modal-btn shiroki-widget-modal-cancel" id="shiroki-confirm-modal-cancel">取消</button>
                                <button type="button" class="shiroki-widget-modal-btn shiroki-widget-modal-confirm" id="shiroki-confirm-modal-confirm">确认</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(confirmModalHTML);

            /* 🚀 触发自定义事件，通知JS可以初始化了 */
            $(document).trigger('shiroki-widget-manager-ready');
        });
        </script>
        <?php
    }

    /**
     * 📋 获取主题侧边栏
     */
    private function get_theme_sidebars() {
        global $wp_registered_sidebars;

        $sidebars = array();

        /* 🎯 默认侧边栏配置 */
        $default_sidebars = array(
            'sidebar-1' => array(
                'name' => '全站侧栏展示',
                'icon' => '🌐',
                'description' => '显示在所有页面的侧边栏'
            ),
            'sidebar-home' => array(
                'name' => '首页侧栏展示',
                'icon' => '🏠',
                'description' => '仅在首页显示的侧边栏'
            ),
            'sidebar-post' => array(
                'name' => '文章页侧栏展示',
                'icon' => '📝',
                'description' => '仅在文章页面显示的侧边栏'
            ),
            'sidebar-page' => array(
                'name' => '页面侧栏展示',
                'icon' => '📄',
                'description' => '仅在独立页面显示的侧边栏'
            ),
            'sidebar-footer' => array(
                'name' => '底部栏展示',
                'icon' => '🦶',
                'description' => '页面底部区域'
            ),
            'wp_inactive_widgets' => array(
                'name' => '未启用的小工具',
                'icon' => '📦',
                'description' => '已停用的小工具'
            )
        );

        /* 🔄 合并已注册的侧边栏 */
        if (!empty($wp_registered_sidebars)) {
            foreach ($wp_registered_sidebars as $id => $sidebar) {
                if (isset($default_sidebars[$id])) {
                    $sidebars[$id] = array_merge($default_sidebars[$id], array(
                        'id' => $id,
                        'description' => $sidebar['description']
                    ));
                } else {
                    $sidebars[$id] = array(
                        'id' => $id,
                        'name' => $sidebar['name'],
                        'icon' => '📦',
                        'description' => $sidebar['description']
                    );
                }
            }
        }

        /* 🔄 添加未启用的小工具侧边栏（如果不存在） */
        if (!isset($sidebars['wp_inactive_widgets'])) {
            $sidebars['wp_inactive_widgets'] = $default_sidebars['wp_inactive_widgets'];
            $sidebars['wp_inactive_widgets']['id'] = 'wp_inactive_widgets';
        }

        /* 🔄 如果没有注册侧边栏，使用默认配置 */
        if (empty($sidebars)) {
            foreach ($default_sidebars as $id => $sidebar) {
                $sidebars[$id] = array_merge($sidebar, array('id' => $id));
            }
        }

        return $sidebars;
    }

    /**
     * 📡 AJAX获取小工具数据
     */
    public function ajax_get_widgets() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        /* 📊 获取可用小工具 */
        global $wp_widget_factory;
        $available_widgets = array();

        if ($wp_widget_factory && !empty($wp_widget_factory->widgets)) {
            foreach ($wp_widget_factory->widgets as $widget_class => $widget_obj) {
                $available_widgets[] = array(
                    'id' => $widget_obj->id_base,
                    'name' => $widget_obj->name,
                    'description' => !empty($widget_obj->widget_options['description']) ? $widget_obj->widget_options['description'] : '',
                    'class' => $widget_class,
                    'icon' => $this->get_widget_icon($widget_obj->id_base)
                );
            }
        }

        /* 📊 获取侧边栏中的小工具 */
        $sidebars_widgets = get_option('sidebars_widgets', array());
        $active_widgets = array();

        foreach ($sidebars_widgets as $sidebar_id => $widgets) {
            if ($sidebar_id === 'array_version') continue;
            if (!is_array($widgets)) continue;

            $active_widgets[$sidebar_id] = array();
            foreach ($widgets as $widget_id) {
                $widget_data = $this->get_widget_instance($widget_id);
                if ($widget_data) {
                    $active_widgets[$sidebar_id][] = $widget_data;
                }
            }
        }

        wp_send_json_success(array(
            'available_widgets' => $available_widgets,
            'active_widgets' => $active_widgets,
            'sidebars' => $this->get_theme_sidebars()
        ));
    }

    /**
     * 🎨 获取小工具图标
     */
    private function get_widget_icon($id_base) {
        $icon_map = array(
            'archives' => '📚',
            'calendar' => '📅',
            'wp_widget_calendar' => '📅',
            'categories' => '📁',
            'custom_html' => '💻',
            'media_audio' => '🎵',
            'wp_widget_media_audio' => '🎵',
            'audio' => '🎵',
            'media_gallery' => '🖼️',
            'wp_widget_media_gallery' => '🖼️',
            'gallery' => '🖼️',
            'media_image' => '📷',
            'wp_widget_media_image' => '📷',
            'media_video' => '🎬',
            'wp_widget_media_video' => '🎬',
            'video' => '🎬',
            'meta' => '⚙️',
            'nav_menu' => '🧭',
            'pages' => '📄',
            'recent-comments' => '💬',
            'recent-posts' => '📰',
            'rss' => '📡',
            'rss_errors' => '📡',
            'search' => '🔍',
            'tag_cloud' => '🏷️',
            'text' => '📝',
            'block' => '🧱',
            'wp_block' => '🧱',
            'links' => '🔗',
            'link' => '🔗'
        );

        return isset($icon_map[$id_base]) ? $icon_map[$id_base] : '📦';
    }

    /**
     * 📋 获取小工具实例数据
     */
    private function get_widget_instance($widget_id) {
        global $wp_registered_widgets;

        if (!isset($wp_registered_widgets[$widget_id])) {
            return null;
        }

        $widget = $wp_registered_widgets[$widget_id];
        $callback = $widget['callback'];

        /* 🔄 处理不同形式的回调 */
        $widget_obj = null;
        $id_base = '';
        $widget_name = '';

        if (is_array($callback) && isset($callback[0])) {
            /* 📝 标准小工具对象 */
            $widget_obj = $callback[0];
            if (is_object($widget_obj)) {
                $id_base = isset($widget_obj->id_base) ? $widget_obj->id_base : '';
                $widget_name = isset($widget_obj->name) ? $widget_obj->name : '';
            }
        } elseif (is_string($callback) && is_callable($callback)) {
            /* 📝 函数名回调 */
            $widget_name = $callback;
        } elseif (is_object($callback) && ($callback instanceof Closure)) {
            /* 📝 闭包回调 */
            $widget_name = '自定义小工具';
        }

        /* 🔄 如果无法获取 id_base，从 widget_id 解析 */
        if (empty($id_base)) {
            /* 🔢 尝试从 widget_id 解析 id_base */
            if (preg_match('/^(.+)-\d+$/', $widget_id, $matches)) {
                $id_base = $matches[1];
            } else {
                $id_base = $widget_id;
            }
        }

        /* 🔢 获取实例编号 */
        $instance_number = 1;
        if (preg_match('/-' . preg_quote($id_base, '/') . '-(\d+)$/', $widget_id, $matches)) {
            $instance_number = intval($matches[1]);
        } elseif (preg_match('/-(\d+)$/', $widget_id, $matches)) {
            $instance_number = intval($matches[1]);
        }

        /* 📊 获取设置 */
        $settings = get_option('widget_' . $id_base, array());
        $instance = isset($settings[$instance_number]) ? $settings[$instance_number] : array();

        /* 📝 获取小工具名称 */
        if (empty($widget_name) && $widget_obj && is_object($widget_obj) && isset($widget_obj->name)) {
            $widget_name = $widget_obj->name;
        }
        if (empty($widget_name)) {
            $widget_name = $id_base;
        }

        return array(
            'widget_id' => $widget_id,
            'id_base' => $id_base,
            'name' => $widget_name,
            'title' => isset($instance['title']) ? $instance['title'] : '',
            'instance' => $instance,
            'icon' => $this->get_widget_icon($id_base)
        );
    }

    /**
     * 💾 AJAX保存小工具排序
     */
    public function ajax_save_widget_order() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $sidebar_id = isset($_POST['sidebar_id']) ? sanitize_text_field($_POST['sidebar_id']) : '';
        $widgets = isset($_POST['widgets']) ? $_POST['widgets'] : array();

        if (empty($sidebar_id)) {
            wp_send_json_error(array('message' => '无效的侧边栏'));
        }

        /* 📊 获取当前设置 */
        $sidebars_widgets = get_option('sidebars_widgets', array());

        /* 🔄 更新指定侧边栏的小工具列表 */
        $sidebars_widgets[$sidebar_id] = array();
        foreach ($widgets as $widget) {
            if (is_array($widget) && isset($widget['widget_id'])) {
                $sidebars_widgets[$sidebar_id][] = sanitize_text_field($widget['widget_id']);
            }
        }

        /* 💾 保存设置 */
        update_option('sidebars_widgets', $sidebars_widgets);

        wp_send_json_success(array('message' => '保存成功'));
    }

    /**
     * ➕ AJAX激活小工具
     */
    public function ajax_activate_widget() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $sidebar_id = isset($_POST['sidebar_id']) ? sanitize_text_field($_POST['sidebar_id']) : '';
        $id_base = isset($_POST['id_base']) ? sanitize_text_field($_POST['id_base']) : '';

        if (empty($sidebar_id) || empty($id_base)) {
            wp_send_json_error(array('message' => '参数不完整'));
        }

        /* 📊 获取小工具设置 */
        $widget_option = get_option('widget_' . $id_base, array());
        $multi_number = !empty($widget_option['_multiwidget']) ? $widget_option['_multiwidget'] : 1;
        $widget_option[$multi_number] = array();
        $widget_option['_multiwidget'] = $multi_number + 1;

        update_option('widget_' . $id_base, $widget_option);

        /* 📊 生成小工具ID */
        $widget_id = $id_base . '-' . $multi_number;

        /* 📊 添加到侧边栏 */
        $sidebars_widgets = get_option('sidebars_widgets', array());
        if (!isset($sidebars_widgets[$sidebar_id])) {
            $sidebars_widgets[$sidebar_id] = array();
        }
        $sidebars_widgets[$sidebar_id][] = $widget_id;
        update_option('sidebars_widgets', $sidebars_widgets);

        /* 📝 获取小工具名称 */
        global $wp_widget_factory;
        $widget_name = $id_base;
        if ($wp_widget_factory && !empty($wp_widget_factory->widgets)) {
            foreach ($wp_widget_factory->widgets as $widget_obj) {
                if ($widget_obj->id_base === $id_base) {
                    $widget_name = $widget_obj->name;
                    break;
                }
            }
        }

        wp_send_json_success(array(
            'message' => '添加成功',
            'widget_id' => $widget_id,
            'widget' => array(
                'widget_id' => $widget_id,
                'id_base' => $id_base,
                'name' => $widget_name,
                'title' => '',
                'icon' => $this->get_widget_icon($id_base)
            )
        ));
    }

    /**
     * 🗑️ AJAX停用小工具
     */
    public function ajax_deactivate_widget() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $widget_id = isset($_POST['widget_id']) ? sanitize_text_field($_POST['widget_id']) : '';
        $sidebar_id = isset($_POST['sidebar_id']) ? sanitize_text_field($_POST['sidebar_id']) : '';

        if (empty($widget_id)) {
            wp_send_json_error(array('message' => '无效的小工具ID'));
        }

        /* 📊 获取当前侧边栏设置 */
        $sidebars_widgets = get_option('sidebars_widgets', array());
        
        /* 🔄 确保 wp_inactive_widgets 存在 */
        if (!isset($sidebars_widgets['wp_inactive_widgets'])) {
            $sidebars_widgets['wp_inactive_widgets'] = array();
        }
        
        /* 📝 如果是从未启用的小工具侧边栏删除，则彻底删除 */
        if ($sidebar_id === 'wp_inactive_widgets') {
            /* 🗑️ 从未启用列表中移除 */
            $sidebars_widgets['wp_inactive_widgets'] = array_diff(
                $sidebars_widgets['wp_inactive_widgets'], 
                array($widget_id)
            );
            
            /* 🗑️ 删除小工具实例数据 */
            $this->delete_widget_instance($widget_id);
        } else {
            /* 🔄 从其他侧边栏移除，并添加到未启用列表 */
            foreach ($sidebars_widgets as $sid => $widgets) {
                if (is_array($widgets) && $sid !== 'wp_inactive_widgets') {
                    if (in_array($widget_id, $widgets)) {
                        /* 🔄 从原侧边栏移除 */
                        $sidebars_widgets[$sid] = array_diff($widgets, array($widget_id));
                        /* ➕ 添加到未启用列表（如果不存在） */
                        if (!in_array($widget_id, $sidebars_widgets['wp_inactive_widgets'])) {
                            $sidebars_widgets['wp_inactive_widgets'][] = $widget_id;
                        }
                    }
                }
            }
        }
        
        update_option('sidebars_widgets', $sidebars_widgets);

        wp_send_json_success(array('message' => '删除成功'));
    }

    /**
     * 🗑️ 删除小工具实例数据
     */
    private function delete_widget_instance($widget_id) {
        /* 🔢 解析小工具ID */
        preg_match('/^(.+)-(\d+)$/', $widget_id, $matches);
        if (empty($matches)) {
            return;
        }

        $id_base = $matches[1];
        $instance_number = intval($matches[2]);

        /* 📊 获取小工具设置 */
        $widget_option = get_option('widget_' . $id_base, array());
        
        /* 🗑️ 删除实例 */
        if (isset($widget_option[$instance_number])) {
            unset($widget_option[$instance_number]);
            update_option('widget_' . $id_base, $widget_option);
        }
    }

    /**
     * ⚙️ AJAX更新小工具设置
     */
    public function ajax_update_widget_settings() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $widget_id = isset($_POST['widget_id']) ? sanitize_text_field($_POST['widget_id']) : '';
        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();

        if (empty($widget_id)) {
            wp_send_json_error(array('message' => '无效的小工具ID'));
        }

        /* 🔢 解析小工具ID */
        preg_match('/^(.*)-(\d+)$/', $widget_id, $matches);
        if (empty($matches)) {
            wp_send_json_error(array('message' => '无效的小工具ID格式'));
        }

        $id_base = $matches[1];
        $instance_number = intval($matches[2]);

        /* 📊 获取并更新设置 */
        $widget_option = get_option('widget_' . $id_base, array());

        /* 🔄 合并现有设置和新设置 */
        $current_settings = isset($widget_option[$instance_number]) ? $widget_option[$instance_number] : array();
        $new_settings = $this->sanitize_widget_settings($id_base, $settings);

        /* 📝 合并设置（保留现有字段，更新新字段） */
        $widget_option[$instance_number] = array_merge($current_settings, $new_settings);

        /* 💾 保存到数据库 */
        update_option('widget_' . $id_base, $widget_option);

        wp_send_json_success(array(
            'message' => '设置已保存',
            'debug' => array(
                'id_base' => $id_base,
                'instance_number' => $instance_number,
                'new_settings' => $new_settings
            )
        ));
    }

    /**
     * 🧹 清理小工具设置
     */
    private function sanitize_widget_settings($id_base, $settings) {
        $sanitized = array();

        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * 🔄 AJAX重置小工具
     */
    public function ajax_reset_widgets() {
        /* 🔐 验证nonce */
        if (!check_ajax_referer('shiroki_widget_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }

        /* 🔐 检查权限 */
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        /* 🔄 重置侧边栏小工具 */
        $default_sidebars = array(
            'wp_inactive_widgets' => array(),
            'array_version' => 3
        );

        update_option('sidebars_widgets', $default_sidebars);

        wp_send_json_success(array('message' => '已重置为默认设置'));
    }
}

/**
 * 🚀 初始化小工具管理UI
 */
function shiroki_init_widget_manager_ui() {
    Shiroki_Widget_Manager_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_widget_manager_ui');
