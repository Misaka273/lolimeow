<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 💎 拟态拟物玻璃质感媒体库UI设计
 * 🎨 替换原版WordPress媒体库为网格卡片式布局
 * 
 * @package Lolimeow_Shiroki
 * @subpackage Media_Library
 * @since 1.0.0
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🔗 媒体库UI主类
 */
class Shiroki_Media_Library_UI {
    
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
        // 加载自定义样式和脚本
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX处理
        add_action('wp_ajax_shiroki_get_media_items', array($this, 'ajax_get_media_items'));
        add_action('wp_ajax_shiroki_copy_media_url', array($this, 'ajax_copy_media_url'));
        add_action('wp_ajax_shiroki_delete_media', array($this, 'ajax_delete_media'));
        
        // 修改媒体库每页显示数量
        add_filter('upload_per_page', array($this, 'set_media_per_page'));
        
        // 强制使用网格视图
        add_action('load-upload.php', array($this, 'force_grid_view'));
        
        // 在管理页脚添加自定义HTML
        add_action('admin_footer', array($this, 'add_custom_media_ui'));
    }
    
    /**
     * 🎨 加载样式和脚本
     */
    public function enqueue_assets($hook) {
        $theme_uri = get_template_directory_uri();
        $version = wp_get_theme()->get('Version');

        /* 🎨 先加载统一变量文件 */
        wp_enqueue_style(
            'admin-variables',
            $theme_uri . '/assets/css/admin/admin-variables.css',
            array(),
            $version
        );

        /* ☀️ 媒体库页面（upload.php）样式 */
        if ($hook === 'upload.php') {
            // ☀️ 加载CSS
            wp_enqueue_style(
                'shiroki-media-library',
                $theme_uri . '/assets/css/admin/media-library/media-library.css',
                array('admin-variables'), // ◀️ 依赖变量文件
                $version
            );

            // 📦 加载JavaScript
            wp_enqueue_script(
                'shiroki-media-library',
                $theme_uri . '/assets/js/admin/media-library/media-library.js',
                array('jquery'),
                $version,
                true
            );

            // 🎯 传递AJAX配置
            wp_localize_script('shiroki-media-library', 'shirokiMediaConfig', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'adminUrl' => admin_url(),
                'nonce' => wp_create_nonce('shiroki_media_nonce'),
                'strings' => array(
                    'copySuccess' => '✅ 已复制到剪贴板',
                    'copyError' => '❌ 复制失败',
                    'loading' => '⏳ 加载中...',
                    'noItems' => '📭 暂无媒体文件',
                    'loadMore' => '加载更多'
                )
            ));
        }

        /* 📤 添加新媒体页面（media-new.php）样式 */
        /* 🔍 使用多种方式检测 media-new.php 页面 */
        $is_media_new = false;
        
        // 方式1  通过 hook 名称检测
        if ($hook === 'media-new' || $hook === 'media_page_add-new') {
            $is_media_new = true;
        }
        
        // 方式2  通过当前页面文件名检测
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'media-new.php') !== false) {
            $is_media_new = true;
        }
        
        // 方式3  通过 get_current_screen() 检测
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && ($screen->id === 'media-new' || $screen->base === 'media-new')) {
                $is_media_new = true;
            }
        }
        
        if ($is_media_new) {
            // ☀️ 加载media-new页面专用CSS
            wp_enqueue_style(
                'shiroki-media-new',
                $theme_uri . '/assets/css/admin/media-library/media-new.css',
                array('admin-variables'), // ◀️ 依赖变量文件
                $version
            );
        }
    }
    
    /**
     * 📐 强制使用网格视图
     */
    public function force_grid_view() {
        // 如果用户访问的是列表视图，重定向到网格视图
        if (!isset($_GET['mode']) || $_GET['mode'] !== 'grid') {
            wp_redirect(admin_url('upload.php?mode=grid'));
            exit;
        }
    }
    
    /**
     * 🎨 添加自定义媒体UI
     */
    public function add_custom_media_ui() {
        $screen = get_current_screen();
        
        ?>
        <?php
        /* ◀️ 如果不是媒体库页面，不输出自定义UI代码 */
        if (!$screen || $screen->id !== 'upload') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // 完全隐藏原版UI元素
            $('.view-switch, .wp-list-table, .tablenav, .media-toolbar, .attachments-wrapper, .attachments-browser').hide();

            // 隐藏WordPress原生的媒体网格（只在主页面操作）
            $('.wp-media-grid').hide();
            // 注意：不要隐藏 .media-frame-content .attachments，因为这会影响上传弹窗中的媒体库显示
            // 改为只隐藏主页面上的特定元素
            $('.wrap > .media-frame-content .attachments').hide();
            
            // 获取原生的【添加新媒体文件】按钮并移动到顶部
            var $uploadButton = $('.page-title-action');
            
            // 在媒体库标题后插入自定义UI
            var customUI = `
                <div class="shiroki-media-wrapper">
                    <!-- 🎯 顶部工具栏：排序方式 + 批量操作 + 添加媒体按钮 -->
                    <div class="shiroki-media-top-bar">
                        <!-- 📊 排序方式 -->
                        <div class="shiroki-media-sort-wrapper">
                            <span class="shiroki-media-sort-label">📊 排序方式：</span>
                            <div class="shiroki-media-sort-options">
                                <button class="shiroki-media-sort-btn active" data-sort="date" data-order="desc">
                                    📅 日期
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="name" data-order="asc">
                                    📝 名称
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="size" data-order="desc">
                                    📦 大小
                                </button>
                                <button class="shiroki-media-sort-btn" data-sort="type" data-order="asc">
                                    🏷️ 类型
                                </button>
                            </div>
                        </div>

                        <!-- 📦 批量操作工具栏（初始隐藏） -->
                        <div class="shiroki-media-bulk-actions" id="shiroki-media-bulk-actions" style="display: none;">
                            <span class="shiroki-media-bulk-count">已选择 <span class="shiroki-media-bulk-count-num">0</span> 项</span>
                            <button class="shiroki-media-bulk-btn shiroki-media-bulk-delete" data-action="delete">
                                🗑️ 批量删除
                            </button>
                            <button class="shiroki-media-bulk-btn shiroki-media-bulk-cancel" data-action="cancel">
                                ❌ 取消选择
                            </button>
                        </div>

                        <!-- 📤 添加媒体按钮容器 -->
                        <div class="shiroki-media-upload-wrapper">
                            <div class="shiroki-media-upload-container"></div>
                        </div>
                    </div>
                    
                    <!-- 🧰 自定义媒体库工具栏 -->
                    <div class="shiroki-media-toolbar">
                        <!-- 🔍 搜索框 -->
                        <div class="shiroki-media-search">
                            <input type="text" 
                                   id="shiroki-media-search" 
                                   placeholder="🔍 搜索媒体文件..."
                                   autocomplete="off">
                        </div>
                        
                        <!-- 🏷️ 筛选按钮 -->
                        <div class="shiroki-media-filters">
                            <button class="shiroki-media-filter-btn active" data-filter="all">
                                📁 全部
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="image">
                                🖼️ 图片
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="video">
                                🎬 视频
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="audio">
                                🎵 音频
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="document">
                                📄 文档
                            </button>
                            <button class="shiroki-media-filter-btn" data-filter="file">
                                📦 文件
                            </button>
                        </div>
                    </div>
                    
                    <!-- 📦 自定义网格容器 -->
                    <div class="shiroki-media-grid" id="shiroki-media-grid">
                        <!-- 媒体卡片将通过JavaScript动态插入 -->
                    </div>
                    
                    <!-- 📭 空状态 -->
                    <div class="shiroki-media-empty" id="shiroki-media-empty" style="display: none;">
                        <svg class="shiroki-media-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <div class="shiroki-media-empty-text">📭 暂无媒体文件</div>
                        <div class="shiroki-media-empty-subtext">点击上方【添加新媒体文件】按钮上传</div>
                    </div>
                </div>
            `;
            
            // 插入到 .wrap 容器内
            $('.wrap > h1').after(customUI);
            
            // 创建新的上传按钮（使用按钮而不是链接，以便通过JS触发上传区域展开）
            var $newUploadBtn = $('<button>', {
                type: 'button',
                class: 'page-title-action shiroki-upload-btn',
                text: '添加媒体文件'
            });

            // 创建内联上传区域（初始隐藏）
            var $uploadArea = $(`
                <div class="shiroki-upload-area" style="display: none;">
                    <div class="shiroki-upload-dropzone">
                        <div class="shiroki-upload-close">×</div>
                        <div class="shiroki-upload-content">
                            <div class="shiroki-upload-icon">📤</div>
                            <h3 class="shiroki-upload-title">拖文件至此可上传</h3>
                            <p class="shiroki-upload-or">或</p>
                            <button type="button" class="shiroki-upload-select-btn">选择文件</button>
                            <p class="shiroki-upload-max-size">最大上传文件大小：<?php echo size_format(wp_max_upload_size()); ?></p>
                        </div>
                        <div class="shiroki-upload-progress" style="display: none;">
                            <div class="shiroki-upload-progress-bar">
                                <div class="shiroki-upload-progress-fill"></div>
                            </div>
                            <p class="shiroki-upload-progress-text">正在上传...</p>
                        </div>
                    </div>
                </div>
            `);

            // 将按钮和上传区域添加到容器
            $('.shiroki-media-upload-container').append($newUploadBtn);
            $('.shiroki-media-toolbar').before($uploadArea);

            // 绑定点击事件，展开/收起上传区域
            $newUploadBtn.on('click', function(e) {
                e.preventDefault();
                $uploadArea.slideToggle(300);
                $(this).toggleClass('active');
            });

            // 关闭按钮
            $uploadArea.find('.shiroki-upload-close').on('click', function() {
                $uploadArea.slideUp(300);
                $newUploadBtn.removeClass('active');
            });

            // 使用WordPress原生上传功能
            if (typeof wp !== 'undefined' && wp.Uploader) {
                // 创建上传器配置
                // 注意：不传递 post_id，让附件成为未关联的媒体文件
                var uploaderConfig = {
                    browser: $uploadArea.find('.shiroki-upload-select-btn')[0],
                    dropzone: $uploadArea.find('.shiroki-upload-dropzone')[0],
                    params: {
                        action: 'upload-attachment',
                        _wpnonce: '<?php echo wp_create_nonce('media-form'); ?>'
                    },
                    multipart_params: {
                        action: 'upload-attachment',
                        _wpnonce: '<?php echo wp_create_nonce('media-form'); ?>'
                    },
                    url: '<?php echo admin_url('admin-ajax.php'); ?>'
                };

                var uploader = new wp.Uploader(uploaderConfig);

                // 文件添加到队列
                uploader.uploader.bind('FilesAdded', function(up, files) {
                    console.log('📤 已选择 ' + files.length + ' 个文件');
                    // 自动开始上传
                    up.start();
                });

                // 上传开始
                uploader.uploader.bind('BeforeUpload', function(up, file) {
                    $uploadArea.find('.shiroki-upload-content').hide();
                    $uploadArea.find('.shiroki-upload-progress').show();
                    console.log('📤 开始上传:', file.name);
                });

                // 上传进度
                uploader.uploader.bind('UploadProgress', function(up, file) {
                    var percent = file.percent;
                    $uploadArea.find('.shiroki-upload-progress-fill').css('width', percent + '%');
                    $uploadArea.find('.shiroki-upload-progress-text').text('正在上传: ' + file.name + ' (' + percent + '%)');
                });

                // 上传完成
                uploader.uploader.bind('FileUploaded', function(up, file, response) {
                    console.log('📤 服务器响应:', response.response);
                    if (response.response) {
                        try {
                            // WordPress 返回的响应格式可能是 JSON 字符串
                            var responseData = response.response;
                            // 如果响应包含 < 字符，可能是 HTML 错误页面
                            if (responseData.indexOf('<') === 0) {
                                console.error('❌ 服务器返回 HTML 而非 JSON:', responseData.substring(0, 200));
                                return;
                            }
                            var data = JSON.parse(responseData);
                            // WordPress upload-attachment 返回格式: { success: true, data: {...} }
                            // 或者直接在 data 中包含附件信息
                            if (data.success || (data.data && data.data.id)) {
                                console.log('✅ 上传成功:', data.data.title || data.data.file);
                            } else if (data.data && data.data.message) {
                                console.error('❌ 上传失败:', data.data.message);
                            } else {
                                console.error('❌ 上传失败:', data);
                            }
                        } catch (e) {
                            console.error('❌ 解析响应失败:', e, '响应内容:', response.response.substring(0, 500));
                        }
                    }
                });

                // 所有上传完成
                uploader.uploader.bind('UploadComplete', function() {
                    console.log('✅ 所有文件上传完成');
                    setTimeout(function() {
                        $uploadArea.find('.shiroki-upload-progress').hide();
                        $uploadArea.find('.shiroki-upload-content').show();
                        $uploadArea.slideUp(300);
                        $newUploadBtn.removeClass('active');

                        // 刷新媒体库
                        if (typeof ShirokiMediaLibrary !== 'undefined') {
                            ShirokiMediaLibrary.state.page = 1;
                            ShirokiMediaLibrary.state.hasMore = true;
                            ShirokiMediaLibrary.$grid.empty();
                            ShirokiMediaLibrary.loadMediaItems();
                        }
                    }, 1000);
                });

                // 上传错误
                uploader.uploader.bind('Error', function(up, error) {
                    console.error('❌ 上传错误:', error);
                    alert('上传失败: ' + (error.message || '未知错误'));
                    $uploadArea.find('.shiroki-upload-progress').hide();
                    $uploadArea.find('.shiroki-upload-content').show();
                });

                // 拖拽事件
                var $dropzone = $uploadArea.find('.shiroki-upload-dropzone');

                $dropzone.on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('drag-over');
                });

                $dropzone.on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');
                });

                $dropzone.on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');
                });
            }
            
            // 隐藏原生的按钮
            if ($uploadButton.length) {
                $uploadButton.hide();
            }
            
            // 触发加载媒体 - 使用延迟确保JS已加载
            setTimeout(function() {
                if (typeof ShirokiMediaLibrary !== 'undefined' && ShirokiMediaLibrary.loadMediaItems) {
                    ShirokiMediaLibrary.loadMediaItems();
                } else {
                    console.error('❌ ShirokiMediaLibrary 未加载');
                }
            }, 200);
        });
        </script>
        <?php
    }
    
    /**
     * 📡 AJAX获取媒体项目
     */
    public function ajax_get_media_items() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_media_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 8; /* ◀️ 默认每页8条 */
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
        $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'desc';
        
        /* 🔍 是否为搜索模式 */
        $is_search_mode = !empty($search);
        
        // 🔀 设置排序参数
        $orderby = 'date';
        switch ($sort) {
            case 'name':
                $orderby = 'title';
                break;
            case 'date':
                $orderby = 'date';
                break;
            case 'size':
                // 文件大小排序需要自定义
                $orderby = 'meta_value_num';
                $args['meta_key'] = '_wp_attachment_filesize';
                break;
            case 'type':
                $orderby = 'post_mime_type';
                break;
        }
        
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'orderby' => $orderby,
            'order' => strtoupper($order)
        );
        
        /* 📄 非搜索模式下使用分页，搜索模式加载所有结果 */
        if (!$is_search_mode) {
            $args['posts_per_page'] = $per_page; /* ◀️ 懒加载每页8条 */
            $args['paged'] = $page;
        } else {
            /* 🔍 搜索模式加载所有匹配结果 */
            $args['posts_per_page'] = -1;
        }
        
        // 🔍 搜索条件
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        // 🏷️ 筛选条件
        if ($filter !== 'all') {
            switch ($filter) {
                case 'image':
                    $args['post_mime_type'] = 'image';
                    break;
                case 'video':
                    $args['post_mime_type'] = 'video';
                    break;
                case 'audio':
                    $args['post_mime_type'] = 'audio';
                    break;
                case 'document':
                    // 使用 SQL 过滤器来筛选文档类型
                    add_filter('posts_where', array($this, 'filter_document_mime_type'));
                    break;
                case 'file':
                    // 筛选文件类型（字体、exe、zip等）
                    add_filter('posts_where', array($this, 'filter_file_mime_type'));
                    break;
            }
        }
        
        $query = new WP_Query($args);
        $items = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $items[] = $this->format_media_item($post_id);
            }
        }
        
        wp_reset_postdata();
        
        // 移除文档筛选的过滤器
        if ($filter === 'document') {
            remove_filter('posts_where', array($this, 'filter_document_mime_type'));
        }
        
        // 移除文件筛选的过滤器
        if ($filter === 'file') {
            remove_filter('posts_where', array($this, 'filter_file_mime_type'));
        }
        
        /* 📊 判断是否还有更多数据 */
        if ($is_search_mode) {
            /* 🔍 搜索模式下一次性返回所有结果，没有更多 */
            $has_more = false;
        } else {
            /* 📄 非搜索模式下判断是否还有更多页 */
            $has_more = $query->max_num_pages > $page;
        }
        
        wp_send_json_success(array(
            'items' => $items,
            'has_more' => $has_more,
            'total' => $query->found_posts
        ));
    }
    
    /**
     * 📄 文档类型筛选过滤器
     */
    public function filter_document_mime_type($where) {
        global $wpdb;
        // 只匹配明确的文档类型（PDF、Office文档、OpenDocument等）
        $document_types = array(
            // PDF
            'application/pdf',
            // Microsoft Office
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // OpenDocument
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.formula',
            // RTF
            'application/rtf',
            'text/rtf',
            // Markdown
            'text/markdown',
            'text/x-markdown'
        );
        $types_str = "'" . implode("', '", $document_types) . "'";
        $where .= " AND {$wpdb->posts}.post_mime_type IN ({$types_str})";

        // 排除可执行文件和代码文件扩展名（即使它们的MIME类型是text/plain或application/octet-stream）
        $exclude_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'log', 'ini', 'conf');
        foreach ($exclude_extensions as $ext) {
            $where .= " AND {$wpdb->posts}.guid NOT LIKE '%.{$ext}'";
            $where .= " AND {$wpdb->posts}.post_title NOT LIKE '%.{$ext}'";
        }

        return $where;
    }
    
    /**
     * 📦 文件类型筛选过滤器（字体、exe、zip、代码文件等）
     */
    public function filter_file_mime_type($where) {
        global $wpdb;

        // 根据文件扩展名匹配（优先）
        $file_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'dmg', 'pkg', 'deb', 'rpm', 'zip', 'rar', '7z', 'tar', 'gz', 'html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'hpp', 'cs', 'go', 'rs', 'swift', 'kt', 'rb', 'pl', 'sql', 'yaml', 'yml', 'toml', 'ini', 'conf', 'cfg', 'log');

        $where .= " AND (";
        $ext_conditions = array();
        foreach ($file_extensions as $ext) {
            $ext_conditions[] = "{$wpdb->posts}.guid LIKE '%.{$ext}'";
            $ext_conditions[] = "{$wpdb->posts}.post_title LIKE '%.{$ext}'";
        }
        $where .= implode(" OR ", $ext_conditions);

        // 或者匹配特定的MIME类型
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'font/%'";
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'application/x-font-%'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/vnd.ms-fontobject'";
        $where .= " OR {$wpdb->posts}.post_mime_type LIKE 'application/font-%'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/zip'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-zip-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-rar-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-7z-compressed'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/gzip'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-tar'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-msdownload'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-exe'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-msdos-program'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-executable'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/html'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-javascript'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/css'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/json'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/xml'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/xml'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-bat'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/bat'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/x-python'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/x-php'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'text/x-php'";
        $where .= " OR {$wpdb->posts}.post_mime_type = 'application/octet-stream'";
        $where .= ")";

        return $where;
    }
    
    /**
     * 🎨 格式化媒体项目数据
     */
    private function format_media_item($post_id) {
        $post = get_post($post_id);
        $metadata = wp_get_attachment_metadata($post_id);
        $file_url = wp_get_attachment_url($post_id);
        $file_path = get_attached_file($post_id);
        $mime_type = get_post_mime_type($post_id);
        
        // 📐 获取文件尺寸
        $dimensions = '';
        if (isset($metadata['width']) && isset($metadata['height'])) {
            $dimensions = $metadata['width'] . '×' . $metadata['height'];
        }
        
        // 📦 获取文件大小
        $file_size = '';
        if (file_exists($file_path)) {
            $file_size = size_format(filesize($file_path));
        }
        
        // 🖼️ 获取缩略图
        $thumbnail = wp_get_attachment_image_src($post_id, 'medium');
        $thumbnail_url = $thumbnail ? $thumbnail[0] : '';
        
        // 🔣 获取文件类型图标
        $file_icon = $this->get_file_icon_svg($mime_type);
        
        // 🏷️ 获取文件扩展名（用于类型判断和格式勋章）
        $file_extension = $this->get_file_extension($file_url);

        return array(
            'id' => $post_id,
            'title' => $post->post_title,
            'caption' => $post->post_excerpt,
            'description' => $post->post_content,
            'url' => $file_url,
            'thumbnail' => $thumbnail_url,
            'mime_type' => $mime_type,
            'file_type' => $this->get_file_type($mime_type, $file_extension),
            'file_size' => $file_size,
            'dimensions' => $dimensions,
            'date' => get_the_date('Y-m-d H:i', $post_id),
            'icon_svg' => $file_icon,
            'file_extension' => $file_extension,
            'edit_link' => get_edit_post_link($post_id, 'raw'),
            'delete_link' => get_delete_post_link($post_id, '', true)
        );
    }
    
    /**
     * 🏷️ 获取文件扩展名
     */
    private function get_file_extension($file_url) {
        $extension = pathinfo($file_url, PATHINFO_EXTENSION);
        return strtolower($extension);
    }
    
    /**
     * 🔣 获取文件类型SVG图标
     */
    private function get_file_icon_svg($mime_type) {
        // 🖼️ 图片
        if (strpos($mime_type, 'image/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="#63b3ed" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        }
        
        // 🎬 视频
        if (strpos($mime_type, 'video/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="#f687b3" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>';
        }
        
        // 🎵 音频
        if (strpos($mime_type, 'audio/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="#9f7aea" stroke-width="2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>';
        }
        
        // 📄 PDF
        if ($mime_type === 'application/pdf') {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="#fc8181" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
        }
        
        // 📊 文档
        if (strpos($mime_type, 'application/') === 0) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="#68d391" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
        }
        
        // 📦 默认
        return '<svg viewBox="0 0 24 24" fill="none" stroke="#a0aec0" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>';
    }
    
    /**
     * 📁 获取文件类型分类
     */
    private function get_file_type($mime_type, $file_extension = '') {
        if (strpos($mime_type, 'image/') === 0) return 'image';
        if (strpos($mime_type, 'video/') === 0) return 'video';
        if (strpos($mime_type, 'audio/') === 0) return 'audio';

        // 检查是否为文档类型（明确的文档格式）
        if ($this->is_document_type($mime_type, $file_extension)) {
            return 'document';
        }

        // 默认归类为文件类型
        return 'file';
    }
    
    /**
     * 📦 检查是否为文件类型
     */
    private function is_file_type($mime_type, $file_extension = '') {
        $ext = strtolower($file_extension);

        // 根据扩展名判断（优先）
        $file_extensions = array('exe', 'bat', 'cmd', 'sh', 'bin', 'dmg', 'pkg', 'deb', 'rpm');
        $code_extensions = array('html', 'htm', 'js', 'css', 'json', 'xml', 'py', 'php', 'java', 'cpp', 'c', 'h', 'hpp', 'cs', 'go', 'rs', 'swift', 'kt', 'rb', 'pl', 'sql', 'yaml', 'yml', 'toml', 'ini', 'conf', 'cfg', 'log');

        if (in_array($ext, $file_extensions) || in_array($ext, $code_extensions)) {
            return true;
        }

        // 字体文件
        if (strpos($mime_type, 'font/') === 0) return true;
        if (strpos($mime_type, 'application/x-font-') === 0) return true;
        if ($mime_type === 'application/vnd.ms-fontobject') return true;
        if (strpos($mime_type, 'application/font-') === 0) return true;

        // 压缩包
        if ($mime_type === 'application/zip') return true;
        if ($mime_type === 'application/x-zip-compressed') return true;
        if ($mime_type === 'application/x-rar-compressed') return true;
        if ($mime_type === 'application/x-7z-compressed') return true;
        if ($mime_type === 'application/gzip') return true;
        if ($mime_type === 'application/x-tar') return true;

        // 可执行文件
        if ($mime_type === 'application/x-msdownload') return true;
        if ($mime_type === 'application/x-exe') return true;
        if ($mime_type === 'application/x-msdos-program') return true;
        if ($mime_type === 'application/x-executable') return true;

        // 代码文件（HTML、JS、CSS、BAT等）
        if ($mime_type === 'text/html') return true;
        if ($mime_type === 'text/javascript') return true;
        if ($mime_type === 'application/javascript') return true;
        if ($mime_type === 'application/x-javascript') return true;
        if ($mime_type === 'text/css') return true;
        if ($mime_type === 'application/json') return true;
        if ($mime_type === 'text/xml') return true;
        if ($mime_type === 'application/xml') return true;
        if ($mime_type === 'application/x-bat') return true;
        if ($mime_type === 'application/bat') return true;
        if ($mime_type === 'text/x-python') return true;
        if ($mime_type === 'application/x-php') return true;
        if ($mime_type === 'text/x-php') return true;

        return false;
    }

    /**
     * 📄 检查是否为文档类型
     */
    private function is_document_type($mime_type, $file_extension = '') {
        $ext = strtolower($file_extension);

        // 根据扩展名判断（明确的文档格式）
        $document_extensions = array(
            // Microsoft Office
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            // OpenDocument
            'odt', 'ods', 'odp', 'odg', 'odf',
            // PDF
            'pdf',
            // 文本文档
            'rtf', 'tex', 'txt',
            // Markdown
            'md', 'markdown',
            // 电子书
            'epub', 'mobi', 'azw', 'azw3'
        );

        if (in_array($ext, $document_extensions)) {
            return true;
        }

        // 根据 MIME 类型判断
        $document_mime_types = array(
            // PDF
            'application/pdf',
            // Microsoft Word
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Microsoft Excel
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // Microsoft PowerPoint
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            // OpenDocument
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.formula',
            // RTF
            'application/rtf',
            'text/rtf',
            // Markdown
            'text/markdown',
            'text/x-markdown'
        );

        if (in_array($mime_type, $document_mime_types)) {
            return true;
        }

        return false;
    }

    /**
     * 📋 AJAX复制媒体URL
     */
    public function ajax_copy_media_url() {
        // 验证nonce
        check_ajax_referer('shiroki_media_nonce', 'nonce');
        
        // 检查权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的媒体ID');
        }
        
        $url = wp_get_attachment_url($post_id);
        
        if (!$url) {
            wp_send_json_error('获取URL失败');
        }
        
        wp_send_json_success(array('url' => $url));
    }
    
    /**
     * 🗑️ AJAX删除媒体文件
     */
    public function ajax_delete_media() {
        // 验证nonce
        if (!check_ajax_referer('shiroki_media_nonce', 'nonce', false)) {
            wp_send_json_error('安全验证失败');
        }
        
        // 检查权限
        if (!current_user_can('delete_posts')) {
            wp_send_json_error('权限不足');
        }
        
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('无效的媒体ID');
        }
        
        // 检查是否为附件
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'attachment') {
            wp_send_json_error('不是有效的媒体文件');
        }
        
        // 使用WordPress原生的wp_delete_attachment函数删除
        $result = wp_delete_attachment($post_id, true);
        
        // wp_delete_attachment 成功时返回被删除的附件对象，失败时返回false或WP_Error
        if ($result === false || is_wp_error($result)) {
            $error_msg = is_wp_error($result) ? $result->get_error_message() : '删除失败';
            wp_send_json_error($error_msg);
        }
        
        wp_send_json_success(array('message' => '删除成功', 'deleted_id' => $post_id));
    }
    
    /**
     * 📄 设置媒体库每页显示数量
     */
    public function set_media_per_page($per_page) {
        return 20;
    }
}

/**
 * 🚀 初始化媒体库UI
 */
function shiroki_init_media_library_ui() {
    Shiroki_Media_Library_UI::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_media_library_ui');
