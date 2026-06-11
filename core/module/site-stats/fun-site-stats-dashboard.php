<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 📊 站点数据统计仪表盘页面
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage Site_Stats
 * @since 1.0.0
 */

// ◀️ 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 📊 注册站点数据统计菜单
 */
function boxmoe_add_site_stats_menu() {
    // 📊 添加主菜单（默认打开自建统计仪表盘）
    add_menu_page(
        '站点数据',
        '站点数据',
        'manage_options',
        'shiroki-site-stats',
        'boxmoe_render_site_stats_dashboard',
        'dashicons-chart-area',
        3
    );

    // 📈 添加子菜单 - 统计仪表盘（自建统计）
    add_submenu_page(
        'shiroki-site-stats',
        '📈 统计仪表盘',
        '📈 统计仪表盘',
        'manage_options',
        'shiroki-site-stats',
        'boxmoe_render_site_stats_dashboard'
    );

    // 🌐 添加子菜单 - 51LA统计
    add_submenu_page(
        'shiroki-site-stats',
        '🌐 51LA统计',
        '🌐 51LA统计',
        'manage_options',
        'shiroki-51la-stats',
        'boxmoe_render_51la_stats_dashboard'
    );
}
add_action('admin_menu', 'boxmoe_add_site_stats_menu');

/**
 * 📊 渲染统计仪表盘页面
 */
function boxmoe_render_site_stats_dashboard() {
    // 🔐 检查权限
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }

    // 📊 获取统计数据
    $stats = Shiroki_Site_Stats::get_instance();

    // 📈 获取各时间段数据
    $today_views = $stats->get_today_stats('post_view');
    $yesterday_views = $stats->get_yesterday_stats('post_view');
    $week_views = $stats->get_7days_stats('post_view');
    $month_views = $stats->get_30days_stats('post_view');
    $year_views = $stats->get_year_stats('post_view');

    $today_visits = $stats->get_today_stats('site_visit');
    $yesterday_visits = $stats->get_yesterday_stats('site_visit');
    $week_visits = $stats->get_7days_stats('site_visit');
    $month_visits = $stats->get_30days_stats('site_visit');
    $year_visits = $stats->get_year_stats('site_visit');

    $today_downloads = $stats->get_today_stats('download');
    $yesterday_downloads = $stats->get_yesterday_stats('download');
    $week_downloads = $stats->get_7days_stats('download');
    $month_downloads = $stats->get_30days_stats('download');
    $year_downloads = $stats->get_year_stats('download');

    // 🔥 获取排行榜数据
    $hot_posts = $stats->get_hot_posts(10, 30);
    $hot_downloads = $stats->get_hot_downloads(10, 30);
    $user_post_ranking = $stats->get_user_post_ranking(10, 30);
    $user_activity_ranking = $stats->get_user_activity_ranking(10, 30);

    // 📈 获取趋势数据（用于图表）
    $views_trend = $stats->get_trend_data('post_view', 30);
    $visits_trend = $stats->get_trend_data('site_visit', 30);
    $downloads_trend = $stats->get_trend_data('download', 30);

    // 📝 准备图表数据
    $chart_labels = array_keys($views_trend);
    $chart_views = array_values($views_trend);
    $chart_visits = array_values($visits_trend);
    $chart_downloads = array_values($downloads_trend);
    ?>
    <div class="wrap shiroki-stats-dashboard">
        <!-- 🎯 页面标题 -->
        <div class="shiroki-stats-header">
            <h1 class="shiroki-stats-title">📊 站点数据统计</h1>
            <p class="shiroki-stats-subtitle">实时监控站点流量、用户活跃度和内容热度</p>
        </div>

        <!-- 📊 时间筛选器 -->
        <div class="shiroki-stats-filter">
            <button class="shiroki-stats-filter-btn active" data-period="today">今日</button>
            <button class="shiroki-stats-filter-btn" data-period="yesterday">昨日</button>
            <button class="shiroki-stats-filter-btn" data-period="week">7天</button>
            <button class="shiroki-stats-filter-btn" data-period="month">30天</button>
            <button class="shiroki-stats-filter-btn" data-period="year">一年</button>
        </div>

        <!-- 📈 核心指标卡片 -->
        <div class="shiroki-stats-overview">
            <!-- 📖 阅读量 -->
            <div class="shiroki-stats-card shiroki-stats-card-views">
                <div class="shiroki-stats-card-icon">📖</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">阅读量</div>
                    <div class="shiroki-stats-card-value" id="stat-views"><?php echo number_format($today_views); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_views >= $yesterday_views ? 'up' : 'down'; ?>">
                        <?php
                        $views_change = $yesterday_views > 0 ? round((($today_views - $yesterday_views) / $yesterday_views) * 100, 1) : 0;
                        echo $views_change >= 0 ? '↑' : '↓';
                        echo abs($views_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 🌐 访问量 -->
            <div class="shiroki-stats-card shiroki-stats-card-visits">
                <div class="shiroki-stats-card-icon">🌐</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">访问量</div>
                    <div class="shiroki-stats-card-value" id="stat-visits"><?php echo number_format($today_visits); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_visits >= $yesterday_visits ? 'up' : 'down'; ?>">
                        <?php
                        $visits_change = $yesterday_visits > 0 ? round((($today_visits - $yesterday_visits) / $yesterday_visits) * 100, 1) : 0;
                        echo $visits_change >= 0 ? '↑' : '↓';
                        echo abs($visits_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 📥 下载量 -->
            <div class="shiroki-stats-card shiroki-stats-card-downloads">
                <div class="shiroki-stats-card-icon">📥</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">下载量</div>
                    <div class="shiroki-stats-card-value" id="stat-downloads"><?php echo number_format($today_downloads); ?></div>
                    <div class="shiroki-stats-card-change <?php echo $today_downloads >= $yesterday_downloads ? 'up' : 'down'; ?>">
                        <?php
                        $downloads_change = $yesterday_downloads > 0 ? round((($today_downloads - $yesterday_downloads) / $yesterday_downloads) * 100, 1) : 0;
                        echo $downloads_change >= 0 ? '↑' : '↓';
                        echo abs($downloads_change) . '% 较昨日';
                        ?>
                    </div>
                </div>
            </div>

            <!-- 👥 活跃用户 -->
            <div class="shiroki-stats-card shiroki-stats-card-users">
                <div class="shiroki-stats-card-icon">👥</div>
                <div class="shiroki-stats-card-content">
                    <div class="shiroki-stats-card-label">活跃用户</div>
                    <div class="shiroki-stats-card-value"><?php echo number_format(count($user_activity_ranking)); ?></div>
                    <div class="shiroki-stats-card-change up">30天内</div>
                </div>
            </div>
        </div>

        <!-- 📈 趋势图表 -->
        <div class="shiroki-stats-chart-section">
            <div class="shiroki-stats-card shiroki-stats-chart-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-title">📈 流量趋势</span>
                    <div class="shiroki-stats-chart-legend">
                        <span class="legend-item views"><span class="legend-dot"></span>阅读量</span>
                        <span class="legend-item visits"><span class="legend-dot"></span>访问量</span>
                        <span class="legend-item downloads"><span class="legend-dot"></span>下载量</span>
                    </div>
                </div>
                <div class="shiroki-stats-chart-body">
                    <canvas id="shiroki-stats-chart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- 🔥 排行榜区域 -->
        <div class="shiroki-stats-ranking-section">
            <!-- 📄 文章热度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">🔥</span>
                    <span class="shiroki-stats-card-title">文章热度排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($hot_posts)) : ?>
                        <?php foreach ($hot_posts as $index => $post) : ?>
                            <div class="shiroki-stats-ranking-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="ranking-content">
                                    <a href="<?php echo get_permalink($post['id']); ?>" target="_blank" class="ranking-title">
                                        <?php echo esc_html($post['title']); ?>
                                    </a>
                                    <span class="ranking-meta"><?php echo esc_html($post['author']); ?> · <?php echo number_format($post['views']); ?> 阅读</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 📥 下载热度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">📥</span>
                    <span class="shiroki-stats-card-title">下载热度排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($hot_downloads)) : ?>
                        <?php foreach ($hot_downloads as $index => $download) : ?>
                            <div class="shiroki-stats-ranking-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <div class="ranking-content">
                                    <span class="ranking-title">文件 #<?php echo $download->object_id; ?></span>
                                    <span class="ranking-meta"><?php echo number_format($download->total_downloads); ?> 次下载</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ✍️ 用户发帖排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">✍️</span>
                    <span class="shiroki-stats-card-title">用户发帖排行</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($user_post_ranking)) : ?>
                        <?php foreach ($user_post_ranking as $index => $user) : ?>
                            <div class="shiroki-stats-ranking-item user-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <img src="<?php echo esc_url($user['avatar']); ?>" alt="" class="ranking-avatar">
                                <div class="ranking-content">
                                    <span class="ranking-title"><?php echo esc_html($user['name']); ?></span>
                                    <span class="ranking-meta"><?php echo number_format($user['post_count']); ?> 篇文章</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🌟 用户活跃度排行 -->
            <div class="shiroki-stats-card shiroki-stats-ranking-card">
                <div class="shiroki-stats-card-header">
                    <span class="shiroki-stats-card-icon">🌟</span>
                    <span class="shiroki-stats-card-title">用户活跃度</span>
                    <span class="shiroki-stats-card-subtitle">30天内</span>
                </div>
                <div class="shiroki-stats-ranking-list">
                    <?php if (!empty($user_activity_ranking)) : ?>
                        <?php foreach ($user_activity_ranking as $index => $user) : ?>
                            <div class="shiroki-stats-ranking-item user-item">
                                <span class="ranking-number <?php echo $index < 3 ? 'top' : ''; ?>"><?php echo $index + 1; ?></span>
                                <img src="<?php echo esc_url($user['avatar']); ?>" alt="" class="ranking-avatar">
                                <div class="ranking-content">
                                    <span class="ranking-title"><?php echo esc_html($user['name']); ?></span>
                                    <span class="ranking-meta"><?php echo number_format($user['visit_count']); ?> 次访问</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="shiroki-stats-empty">暂无数据</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 图表数据 -->
    <script type="text/javascript">
        window.shirokiStatsData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            views: <?php echo json_encode($chart_views); ?>,
            visits: <?php echo json_encode($chart_visits); ?>,
            downloads: <?php echo json_encode($chart_downloads); ?>,
            periods: {
                today: {
                    views: <?php echo $today_views; ?>,
                    visits: <?php echo $today_visits; ?>,
                    downloads: <?php echo $today_downloads; ?>
                },
                yesterday: {
                    views: <?php echo $yesterday_views; ?>,
                    visits: <?php echo $yesterday_visits; ?>,
                    downloads: <?php echo $yesterday_downloads; ?>
                },
                week: {
                    views: <?php echo $week_views; ?>,
                    visits: <?php echo $week_visits; ?>,
                    downloads: <?php echo $week_downloads; ?>
                },
                month: {
                    views: <?php echo $month_views; ?>,
                    visits: <?php echo $month_visits; ?>,
                    downloads: <?php echo $month_downloads; ?>
                },
                year: {
                    views: <?php echo $year_views; ?>,
                    visits: <?php echo $year_visits; ?>,
                    downloads: <?php echo $year_downloads; ?>
                }
            }
        };
    </script>
    <?php
}

/**
 * 📊 AJAX 获取统计数据
 */
function boxmoe_ajax_get_stats_data() {
    check_ajax_referer('shiroki_stats_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => '权限不足'));
    }

    $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';
    $stats = Shiroki_Site_Stats::get_instance();

    $data = array();

    switch ($period) {
        case 'today':
            $data['views'] = $stats->get_today_stats('post_view');
            $data['visits'] = $stats->get_today_stats('site_visit');
            $data['downloads'] = $stats->get_today_stats('download');
            break;
        case 'yesterday':
            $data['views'] = $stats->get_yesterday_stats('post_view');
            $data['visits'] = $stats->get_yesterday_stats('site_visit');
            $data['downloads'] = $stats->get_yesterday_stats('download');
            break;
        case 'week':
            $data['views'] = $stats->get_7days_stats('post_view');
            $data['visits'] = $stats->get_7days_stats('site_visit');
            $data['downloads'] = $stats->get_7days_stats('download');
            break;
        case 'month':
            $data['views'] = $stats->get_30days_stats('post_view');
            $data['visits'] = $stats->get_30days_stats('site_visit');
            $data['downloads'] = $stats->get_30days_stats('download');
            break;
        case 'year':
            $data['views'] = $stats->get_year_stats('post_view');
            $data['visits'] = $stats->get_year_stats('site_visit');
            $data['downloads'] = $stats->get_year_stats('download');
            break;
    }

    wp_send_json_success($data);
}
add_action('wp_ajax_shiroki_get_stats_data', 'boxmoe_ajax_get_stats_data');

/**
 * 🌐 渲染51LA统计仪表盘页面
 */
function boxmoe_render_51la_stats_dashboard() {
    // 🔐 检查权限
    if (!current_user_can('manage_options')) {
        wp_die('权限不足');
    }

    // 💾 获取配置
    $api = Shiroki_51LA_API::get_instance();
    $config = $api->get_config();
    $is_configured = $api->is_configured();

    // 📝 确保配置项存在，避免未定义数组键警告
    $config = wp_parse_args($config, array(
        'access_key' => '',
        'secret_key' => '',
        'site_id' => '',
        'security_type' => '2'
    ));

    // 📝 获取保存状态
    $saved = isset($_GET['saved']) && $_GET['saved'] === 'true';

    // 📊 如果已配置，尝试获取数据
    $overview_data = null;
    $today_data = null;
    $error_message = '';

    if ($is_configured) {
        $overview_result = $api->get_overview($config['site_id']);
        if (!is_wp_error($overview_result)) {
            $overview_data = $overview_result;
        } else {
            $error_message = $overview_result->get_error_message();
        }

        $today_result = $api->get_today_data($config['site_id']);
        if (!is_wp_error($today_result)) {
            $today_data = $today_result;
        }
    }

    // 📝 获取趋势数据（最近7天）
    $trend_data = null;
    if ($is_configured) {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-6 days'));
        $trend_result = $api->get_trend($start_date, $end_date, $config['site_id']);
        if (!is_wp_error($trend_result)) {
            $trend_data = $trend_result;
        }
    }

    // 📝 获取来路分析数据（最近7天）
    $source_data = null;
    if ($is_configured) {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-6 days'));
        $source_result = $api->get_source($start_date, $end_date, $config['site_id']);
        if (!is_wp_error($source_result)) {
            $source_data = $source_result;
        }
    }

    // 📝 准备图表数据
    $chart_labels = array();
    $chart_pv = array();
    $chart_uv = array();

    if ($trend_data && !empty($trend_data['data'])) {
        foreach ($trend_data['data'] as $item) {
            $chart_labels[] = date('m/d', strtotime($item['date']));
            $chart_pv[] = intval($item['pv']);
            $chart_uv[] = intval($item['uv']);
        }
    }
    ?>
    <div class="wrap shiroki-51la-dashboard">
        <!-- 🎯 页面标题 -->
        <div class="shiroki-51la-header">
            <h1 class="shiroki-51la-title">🌐 51LA 统计</h1>
            <p class="shiroki-51la-subtitle">通过 51LA API 获取专业网站流量分析数据</p>
        </div>

        <?php if ($saved) : ?>
        <!-- ✅ 保存成功提示 -->
        <div class="shiroki-51la-notice success">
            <span class="notice-icon">✅</span>
            <span class="notice-text">配置保存成功！</span>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)) : ?>
        <!-- ❌ 错误提示 -->
        <div class="shiroki-51la-notice error">
            <span class="notice-icon">❌</span>
            <span class="notice-text"><?php echo esc_html($error_message); ?></span>
        </div>
        <?php endif; ?>

        <!-- ⚙️ 配置区域 -->
        <div class="shiroki-51la-card shiroki-51la-config-card <?php echo $is_configured ? 'is-collapsed' : 'is-expanded'; ?>">
            <div class="shiroki-51la-card-header shiroki-51la-config-header" style="cursor: pointer;">
                <span class="shiroki-51la-card-icon">⚙️</span>
                <span class="shiroki-51la-card-title">API 配置</span>
                <span class="shiroki-51la-config-status <?php echo $is_configured ? 'active' : 'inactive'; ?>">
                    <?php echo $is_configured ? '🟢 已配置' : '🔴 未配置'; ?>
                </span>
                <span class="shiroki-51la-toggle-icon"><?php echo $is_configured ? '▼' : '▲'; ?></span>
            </div>
            <div class="shiroki-51la-card-body shiroki-51la-config-body">
                <form method="post" action="" class="shiroki-51la-config-form">
                    <?php wp_nonce_field('shiroki_51la_config_nonce', 'shiroki_51la_config_nonce'); ?>

                    <div class="shiroki-51la-form-row">
                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_access_key">Access Key <span class="required">*</span></label>
                            <input type="text"
                                   name="shiroki_51la_access_key"
                                   id="shiroki_51la_access_key"
                                   value="<?php echo esc_attr($config['access_key']); ?>"
                                   placeholder="请输入 51LA Access Key"
                                   required>
                            <p class="field-description">在 <a href="https://v6.51.la/user/application/openapi" target="_blank">51LA 开放平台</a> 获取</p>
                        </div>

                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_secret_key">Secret Key <span class="required">*</span></label>
                            <input type="password"
                                   name="shiroki_51la_secret_key"
                                   id="shiroki_51la_secret_key"
                                   value="<?php echo esc_attr($config['secret_key']); ?>"
                                   placeholder="请输入 51LA Secret Key"
                                   required>
                            <p class="field-description">用于生成 API 请求签名</p>
                        </div>
                    </div>

                    <div class="shiroki-51la-form-row">
                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_site_id">站点 ID</label>
                            <input type="text"
                                   name="shiroki_51la_site_id"
                                   id="shiroki_51la_site_id"
                                   value="<?php echo esc_attr($config['site_id']); ?>"
                                   placeholder="可选，指定要查看的站点">
                            <p class="field-description">留空则使用默认站点</p>
                        </div>

                        <div class="shiroki-51la-form-field">
                            <label for="shiroki_51la_security_type">安全等级</label>
                            <select name="shiroki_51la_security_type" id="shiroki_51la_security_type">
                                <option value="1" <?php selected($config['security_type'], '1'); ?>>低安全性（仅 AccessKey）</option>
                                <option value="2" <?php selected($config['security_type'], '2'); ?>>中安全性（推荐）</option>
                                <option value="3" <?php selected($config['security_type'], '3'); ?>>高安全性（双向加密）</option>
                            </select>
                            <p class="field-description">建议选择中等安全性</p>
                        </div>
                    </div>

                    <div class="shiroki-51la-form-actions">
                        <button type="submit" name="shiroki_51la_save_config" class="shiroki-51la-btn shiroki-51la-btn-primary">
                            <span class="btn-icon">💾</span>
                            <span>保存配置</span>
                        </button>
                        <a href="https://v6.51.la/user/application/openapi" target="_blank" class="shiroki-51la-btn shiroki-51la-btn-secondary">
                            <span class="btn-icon">🔗</span>
                            <span>获取 API 密钥</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- 🗂️ 标签切换菜单 -->
        <div class="shiroki-51la-tabs">
            <button type="button" class="shiroki-51la-tab active" data-tab="overview">
                <span class="tab-icon">📊</span>
                <span class="tab-text">数据概览</span>
            </button>
            <button type="button" class="shiroki-51la-tab" data-tab="external-links">
                <span class="tab-icon">🔗</span>
                <span class="tab-text">外部链接</span>
            </button>
            <button type="button" class="shiroki-51la-tab" data-tab="guide">
                <span class="tab-icon">📖</span>
                <span class="tab-text">使用说明</span>
            </button>
        </div>

        <?php
        // 📝 准备数据（真实数据或演示数据）
        $has_real_data = $is_configured && $overview_data && !empty($overview_data['data']);
        $demo_mode = !$is_configured;

        // 📊 统计数据
        $today_pv = $has_real_data && isset($today_data['data']['curPv']) ? intval($today_data['data']['curPv']) : ($demo_mode ? 1234 : 0);
        $today_uv = $has_real_data && isset($today_data['data']['curUv']) ? intval($today_data['data']['curUv']) : ($demo_mode ? 567 : 0);
        $month_pv = $has_real_data && isset($today_data['data']['monthPv']) ? intval($today_data['data']['monthPv']) : ($demo_mode ? 45678 : 0);
        $total_pv = $has_real_data && isset($today_data['data']['totalPv']) ? intval($today_data['data']['totalPv']) : ($demo_mode ? 1234567 : 0);

        // 📈 趋势数据
        $chart_labels = array();
        $chart_pv = array();
        $chart_uv = array();

        if ($has_real_data && $trend_data && !empty($trend_data['data'])) {
            foreach ($trend_data['data'] as $item) {
                $chart_labels[] = date('m/d', strtotime($item['date']));
                $chart_pv[] = intval($item['pv']);
                $chart_uv[] = intval($item['uv']);
            }
        } elseif ($demo_mode) {
            // 🎨 生成演示数据
            for ($i = 6; $i >= 0; $i--) {
                $chart_labels[] = date('m/d', strtotime("-{$i} days"));
                $chart_pv[] = rand(800, 1500);
                $chart_uv[] = rand(400, 800);
            }
        }
        ?>

        <!-- 📊 数据概览标签页 -->
        <div class="shiroki-51la-tab-content active" data-tab-content="overview">
            <!-- 📊 数据概览 -->
        <div class="shiroki-51la-overview">
            <!-- 📈 今日 PV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-label">今日 PV</div>
                    <div class="stat-value" id="51la-today-pv">
                        <?php echo number_format($today_pv); ?>
                    </div>
                    <div class="stat-change">
                        浏览量
                    </div>
                </div>
            </div>

            <!-- 👥 今日 UV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-label">今日 UV</div>
                    <div class="stat-value" id="51la-today-uv">
                        <?php echo number_format($today_uv); ?>
                    </div>
                    <div class="stat-change">
                        独立访客
                    </div>
                </div>
            </div>

            <!-- 📊 本月 PV -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-label">本月 PV</div>
                    <div class="stat-value" id="51la-month-pv">
                        <?php echo number_format($month_pv); ?>
                    </div>
                    <div class="stat-change">
                        累计浏览
                    </div>
                </div>
            </div>

            <!-- 🌐 总访问量 -->
            <div class="shiroki-51la-stat-card">
                <div class="stat-icon">🌐</div>
                <div class="stat-content">
                    <div class="stat-label">总访问量</div>
                    <div class="stat-value" id="51la-total-pv">
                        <?php echo number_format($total_pv); ?>
                    </div>
                    <div class="stat-change">
                        历史累计
                    </div>
                </div>
            </div>
        </div>

        <!-- 📈 趋势图表 -->
        <div class="shiroki-51la-card shiroki-51la-chart-card">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">📈</span>
                <span class="shiroki-51la-card-title">流量趋势（最近7天）</span>
                <div class="shiroki-51la-chart-legend">
                    <span class="legend-item pv"><span class="legend-dot"></span>浏览量(PV)</span>
                    <span class="legend-item uv"><span class="legend-dot"></span>访客数(UV)</span>
                </div>
            </div>
            <div class="shiroki-51la-chart-body">
                <canvas id="shiroki-51la-chart" height="300"></canvas>
            </div>
        </div>

        <!--  来路分析 -->
        <div class="shiroki-51la-card shiroki-51la-source-card">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">🔗</span>
                <span class="shiroki-51la-card-title">来路分析（最近7天）</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 📝 准备来路数据
                $source_list = array();
                if ($has_real_data && $source_data && !empty($source_data['data'])) {
                    $source_list = $source_data['data'];
                } elseif ($demo_mode) {
                    // 🎨 演示数据
                    $source_list = array(
                        array('source' => '百度搜索', 'pv' => 4523, 'uv' => 2341, 'ratio' => 35.2),
                        array('source' => '直接访问', 'pv' => 3124, 'uv' => 1892, 'ratio' => 24.3),
                        array('source' => 'Google', 'pv' => 1856, 'uv' => 1234, 'ratio' => 14.5),
                        array('source' => '必应搜索', 'pv' => 1234, 'uv' => 876, 'ratio' => 9.6),
                        array('source' => '搜狗搜索', 'pv' => 987, 'uv' => 654, 'ratio' => 7.7),
                        array('source' => '外链', 'pv' => 654, 'uv' => 432, 'ratio' => 5.1),
                        array('source' => '其他', 'pv' => 432, 'uv' => 298, 'ratio' => 3.6)
                    );
                }

                if (!empty($source_list)) :
                    // 📊 计算总数
                    $total_pv = array_sum(array_column($source_list, 'pv'));
                ?>
                <div class="shiroki-51la-source-table-wrapper">
                    <table class="shiroki-51la-source-table">
                        <thead>
                            <tr>
                                <th class="col-rank">排名</th>
                                <th class="col-source">来路来源</th>
                                <th class="col-pv">浏览量(PV)</th>
                                <th class="col-uv">访客数(UV)</th>
                                <th class="col-ratio">占比</th>
                                <th class="col-bar">趋势</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($source_list as $index => $item) :
                                $rank = $index + 1;
                                $source_name = isset($item['source']) ? $item['source'] : '未知来源';
                                $pv = isset($item['pv']) ? intval($item['pv']) : 0;
                                $uv = isset($item['uv']) ? intval($item['uv']) : 0;
                                $ratio = isset($item['ratio']) ? floatval($item['ratio']) : ($total_pv > 0 ? round($pv / $total_pv * 100, 1) : 0);

                                // 🎨 排名样式
                                $rank_class = '';
                                if ($rank === 1) $rank_class = 'rank-1';
                                elseif ($rank === 2) $rank_class = 'rank-2';
                                elseif ($rank === 3) $rank_class = 'rank-3';
                            ?>
                            <tr>
                                <td class="col-rank">
                                    <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                                </td>
                                <td class="col-source">
                                    <span class="source-name"><?php echo esc_html($source_name); ?></span>
                                </td>
                                <td class="col-pv"><?php echo number_format($pv); ?></td>
                                <td class="col-uv"><?php echo number_format($uv); ?></td>
                                <td class="col-ratio"><?php echo $ratio; ?>%</td>
                                <td class="col-bar">
                                    <div class="ratio-bar">
                                        <div class="ratio-fill" style="width: <?php echo min($ratio, 100); ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="shiroki-51la-empty">
                    <p>暂无来路数据</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- � 外部链接分析（数据概览页显示前10个） -->
        <div class="shiroki-51la-card shiroki-51la-external-links-preview">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">🔗</span>
                <span class="shiroki-51la-card-title">外部链接分析（TOP 10）</span>
                <a href="#" class="view-more-link" data-tab-trigger="external-links">查看全部 →</a>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 🎨 演示数据 - 外部链接 TOP 10
                $external_links_top10 = array(
                    array('link' => 'https://www.baidu.com/s?wd=site+example', 'domain' => 'baidu.com', 'pv' => 4523, 'uv' => 2341),
                    array('link' => 'https://www.google.com/search?q=wordpress+theme', 'domain' => 'google.com', 'pv' => 3124, 'uv' => 1856),
                    array('link' => 'https://www.bing.com/search?q=lolimeow', 'domain' => 'bing.com', 'pv' => 2345, 'uv' => 1234),
                    array('link' => 'https://www.zhihu.com/question/12345678', 'domain' => 'zhihu.com', 'pv' => 1876, 'uv' => 987),
                    array('link' => 'https://weibo.com/share?url=example', 'domain' => 'weibo.com', 'pv' => 1654, 'uv' => 876),
                    array('link' => 'https://www.douyin.com/video/abc123456', 'domain' => 'douyin.com', 'pv' => 1432, 'uv' => 765),
                    array('link' => 'https://www.bilibili.com/video/BV123456', 'domain' => 'bilibili.com', 'pv' => 1234, 'uv' => 654),
                    array('link' => 'https://mp.weixin.qq.com/s/abc123', 'domain' => 'mp.weixin.qq.com', 'pv' => 1098, 'uv' => 543),
                    array('link' => 'https://www.xiaohongshu.com/discovery/item/123', 'domain' => 'xiaohongshu.com', 'pv' => 876, 'uv' => 432),
                    array('link' => 'https://www.csdn.net/article/2024/12345', 'domain' => 'csdn.net', 'pv' => 654, 'uv' => 321),
                );
                ?>
                <div class="shiroki-51la-external-links-list">
                    <?php foreach ($external_links_top10 as $index => $item) :
                        $rank = $index + 1;
                        $rank_class = '';
                        if ($rank === 1) $rank_class = 'rank-1';
                        elseif ($rank === 2) $rank_class = 'rank-2';
                        elseif ($rank === 3) $rank_class = 'rank-3';
                    ?>
                    <div class="external-link-item">
                        <div class="link-rank">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="link-info">
                            <a href="<?php echo esc_url($item['link']); ?>" target="_blank" class="link-url" title="<?php echo esc_attr($item['link']); ?>">
                                <?php echo esc_html($item['domain']); ?>
                            </a>
                            <span class="link-full"><?php echo esc_html(mb_strimwidth($item['link'], 0, 60, '...')); ?></span>
                        </div>
                        <div class="link-stats">
                            <div class="stat-item">
                                <span class="stat-label">浏览量</span>
                                <span class="stat-value"><?php echo number_format($item['pv']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">访客数</span>
                                <span class="stat-value"><?php echo number_format($item['uv']); ?></span>
                            </div>
                        </div>
                        <div class="link-trend">
                            <div class="trend-bar">
                                <div class="trend-fill" style="width: <?php echo min(($item['pv'] / 5000) * 100, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 📄 受访页分析（TOP 10） -->
        <div class="shiroki-51la-card shiroki-51la-visited-pages-preview">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">📄</span>
                <span class="shiroki-51la-card-title">受访页面（TOP 10）</span>
                <span class="header-hint">用户访问最多的页面</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 🎨 演示数据 - 受访页面 TOP 10
                $visited_pages_top10 = array(
                    array('url' => '/home', 'title' => '首页', 'pv' => 12543, 'uv' => 8234, 'avg_time' => '02:34'),
                    array('url' => '/article/wordpress-theme', 'title' => 'WordPress主题推荐', 'pv' => 8765, 'uv' => 6543, 'avg_time' => '05:12'),
                    array('url' => '/category/tech', 'title' => '技术分类', 'pv' => 6543, 'uv' => 4321, 'avg_time' => '03:45'),
                    array('url' => '/about', 'title' => '关于我们', 'pv' => 5432, 'uv' => 3876, 'avg_time' => '01:56'),
                    array('url' => '/article/seo-guide', 'title' => 'SEO优化指南', 'pv' => 4321, 'uv' => 3210, 'avg_time' => '08:23'),
                    array('url' => '/contact', 'title' => '联系我们', 'pv' => 3456, 'uv' => 2345, 'avg_time' => '01:12'),
                    array('url' => '/article/web-design', 'title' => '网页设计技巧', 'pv' => 2987, 'uv' => 2134, 'avg_time' => '06:45'),
                    array('url' => '/category/life', 'title' => '生活随笔', 'pv' => 2654, 'uv' => 1876, 'avg_time' => '04:32'),
                    array('url' => '/article/php-tutorial', 'title' => 'PHP入门教程', 'pv' => 2345, 'uv' => 1654, 'avg_time' => '12:34'),
                    array('url' => '/guestbook', 'title' => '留言板', 'pv' => 1876, 'uv' => 1234, 'avg_time' => '02:18'),
                );
                ?>
                <div class="shiroki-51la-pages-list">
                    <?php foreach ($visited_pages_top10 as $index => $item) :
                        $rank = $index + 1;
                        $rank_class = '';
                        if ($rank === 1) $rank_class = 'rank-1';
                        elseif ($rank === 2) $rank_class = 'rank-2';
                        elseif ($rank === 3) $rank_class = 'rank-3';
                    ?>
                    <div class="page-item">
                        <div class="page-rank">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="page-info">
                            <span class="page-title"><?php echo esc_html($item['title']); ?></span>
                            <span class="page-url"><?php echo esc_html($item['url']); ?></span>
                        </div>
                        <div class="page-stats">
                            <div class="stat-item">
                                <span class="stat-label">浏览量</span>
                                <span class="stat-value"><?php echo number_format($item['pv']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">访客数</span>
                                <span class="stat-value"><?php echo number_format($item['uv']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">平均停留</span>
                                <span class="stat-value"><?php echo $item['avg_time']; ?></span>
                            </div>
                        </div>
                        <div class="page-trend">
                            <div class="trend-bar">
                                <div class="trend-fill" style="width: <?php echo min(($item['pv'] / 13000) * 100, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- � 入口页分析（TOP 10） -->
        <div class="shiroki-51la-card shiroki-51la-entry-pages-preview">
            <div class="shiroki-51la-card-header">
                <span class="shiroki-51la-card-icon">🚪</span>
                <span class="shiroki-51la-card-title">入口页面（TOP 10）</span>
                <span class="header-hint">用户进入网站的第一页</span>
            </div>
            <div class="shiroki-51la-card-body">
                <?php
                // 🎨 演示数据 - 入口页面 TOP 10
                $entry_pages_top10 = array(
                    array('url' => '/home', 'title' => '首页', 'entry_count' => 9876, 'entry_rate' => '78.5%', 'bounce_rate' => '32.1%'),
                    array('url' => '/article/wordpress-theme', 'title' => 'WordPress主题推荐', 'entry_count' => 2345, 'entry_rate' => '18.6%', 'bounce_rate' => '45.2%'),
                    array('url' => '/category/tech', 'title' => '技术分类', 'entry_count' => 876, 'entry_rate' => '6.9%', 'bounce_rate' => '38.7%'),
                    array('url' => '/about', 'title' => '关于我们', 'entry_count' => 543, 'entry_rate' => '4.3%', 'bounce_rate' => '52.3%'),
                    array('url' => '/article/seo-guide', 'title' => 'SEO优化指南', 'entry_count' => 432, 'entry_rate' => '3.4%', 'bounce_rate' => '41.8%'),
                    array('url' => '/contact', 'title' => '联系我们', 'entry_count' => 321, 'entry_rate' => '2.5%', 'bounce_rate' => '65.4%'),
                    array('url' => '/article/web-design', 'title' => '网页设计技巧', 'entry_count' => 287, 'entry_rate' => '2.3%', 'bounce_rate' => '35.6%'),
                    array('url' => '/category/life', 'title' => '生活随笔', 'entry_count' => 234, 'entry_rate' => '1.9%', 'bounce_rate' => '48.9%'),
                    array('url' => '/article/php-tutorial', 'title' => 'PHP入门教程', 'entry_count' => 198, 'entry_rate' => '1.6%', 'bounce_rate' => '28.3%'),
                    array('url' => '/guestbook', 'title' => '留言板', 'entry_count' => 156, 'entry_rate' => '1.2%', 'bounce_rate' => '58.7%'),
                );
                ?>
                <div class="shiroki-51la-pages-list">
                    <?php foreach ($entry_pages_top10 as $index => $item) :
                        $rank = $index + 1;
                        $rank_class = '';
                        if ($rank === 1) $rank_class = 'rank-1';
                        elseif ($rank === 2) $rank_class = 'rank-2';
                        elseif ($rank === 3) $rank_class = 'rank-3';
                    ?>
                    <div class="page-item entry-page-item">
                        <div class="page-rank">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="page-info">
                            <span class="page-title"><?php echo esc_html($item['title']); ?></span>
                            <span class="page-url"><?php echo esc_html($item['url']); ?></span>
                        </div>
                        <div class="page-stats">
                            <div class="stat-item">
                                <span class="stat-label">入口次数</span>
                                <span class="stat-value"><?php echo number_format($item['entry_count']); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">入口占比</span>
                                <span class="stat-value"><?php echo $item['entry_rate']; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">跳出率</span>
                                <span class="stat-value <?php echo floatval($item['bounce_rate']) > 50 ? 'high-bounce' : 'low-bounce'; ?>"><?php echo $item['bounce_rate']; ?></span>
                            </div>
                        </div>
                        <div class="page-trend">
                            <div class="trend-bar">
                                <div class="trend-fill" style="width: <?php echo min(floatval($item['entry_rate']) * 1.2, 100); ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- �📋 数据说明 -->
        <div class="shiroki-51la-details">
            <div class="shiroki-51la-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">ℹ️</span>
                    <span class="shiroki-51la-card-title">数据说明</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <ul class="shiroki-51la-info-list">
                        <li><strong>PV (Page View)</strong>：页面浏览量，每次页面加载计为一次</li>
                        <li><strong>UV (Unique Visitor)</strong>：独立访客数，按用户去重统计</li>
                        <li><strong>IP</strong>：独立 IP 数，按访问者 IP 去重</li>
                        <li><strong>数据更新</strong>：51LA 数据实时更新，可能存在短暂延迟</li>
                        <?php if ($demo_mode) : ?>
                        <li style="color: var(--admin-warning-text);"><strong>⚠️ 演示模式</strong>：当前显示的是演示数据，配置 API 后将显示真实数据</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($demo_mode || !empty($chart_labels)) : ?>
        <!-- 📊 图表数据 -->
        <script type="text/javascript">
            window.shiroki51LAData = {
                labels: <?php echo json_encode($chart_labels); ?>,
                pv: <?php echo json_encode($chart_pv); ?>,
                uv: <?php echo json_encode($chart_uv); ?>
            };
        </script>
        <?php endif; ?>

        <?php if ($is_configured && !$has_real_data) : ?>
        <!-- ⏳ 加载中 -->
        <div class="shiroki-51la-loading">
            <div class="loading-spinner"></div>
            <p>正在加载数据...</p>
        </div>
        <?php endif; ?>
        </div>

        <!-- 📖 使用说明标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="guide">
            <div class="shiroki-51la-card shiroki-51la-guide-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">📖</span>
                    <span class="shiroki-51la-card-title">51LA 统计使用指南</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <div class="guide-section">
                        <h4>🚀 什么是 51LA 统计？</h4>
                        <p>51LA 是国内知名的免费网站流量统计服务，提供专业的网站访问数据分析功能，帮助您了解网站的访问情况和用户行为。</p>
                    </div>

                    <div class="guide-section">
                        <h4>📝 如何获取 API 密钥？</h4>
                        <ol class="guide-steps">
                            <li>
                                <strong>注册/登录账号</strong>
                                <p>访问 <a href="https://v6.51.la" target="_blank">v6.51.la</a> 注册或登录您的 51LA 账号</p>
                            </li>
                            <li>
                                <strong>添加站点</strong>
                                <p>在控制台中添加您的网站，获取统计代码并部署到网站上</p>
                            </li>
                            <li>
                                <strong>申请 API 权限</strong>
                                <p>进入「用户中心」→「应用管理」→「开放接口」，申请开通 API 权限</p>
                            </li>
                            <li>
                                <strong>获取密钥</strong>
                                <p>系统会自动生成 <code>AccessKey</code> 和 <code>SecretKey</code>，请妥善保存</p>
                            </li>
                            <li>
                                <strong>配置插件</strong>
                                <p>将获取到的密钥填入上方配置表单，点击保存即可</p>
                            </li>
                        </ol>
                    </div>

                    <div class="guide-section">
                        <h4>📊 数据指标说明</h4>
                        <ul class="guide-metrics">
                            <li><strong>PV (Page View)</strong> - 页面浏览量，每次页面加载计为一次</li>
                            <li><strong>UV (Unique Visitor)</strong> - 独立访客数，按用户去重统计</li>
                            <li><strong>IP</strong> - 独立 IP 数，按访问者 IP 去重</li>
                            <li><strong>跳出率</strong> - 只访问一个页面就离开的访客比例</li>
                            <li><strong>平均停留时长</strong> - 访客在网站的平均停留时间</li>
                        </ul>
                    </div>

                    <div class="guide-section">
                        <h4>⚠️ 注意事项</h4>
                        <ul class="guide-tips">
                            <li>API 数据有一定延迟，通常为 5-15 分钟</li>
                            <li>免费版 API 有调用频率限制，请合理使用</li>
                            <li>请妥善保管您的 SecretKey，不要泄露给他人</li>
                            <li>如需更详细的数据分析，请访问 <a href="https://v6.51.la" target="_blank">51LA 官网</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔗 外部链接统计标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="external-links">
            <!-- 🎯 外部链接统计仪表盘 -->
            <div class="shiroki-51la-external-dashboard">
                <!-- 📊 统计卡片 -->
                <div class="shiroki-51la-external-stats">
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">🔗</div>
                        <div class="stat-content">
                            <div class="stat-label">链接总计</div>
                            <div class="stat-value" id="external-link-count">156</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">🌐</div>
                        <div class="stat-content">
                            <div class="stat-label">IP总数</div>
                            <div class="stat-value" id="external-ip-count">12,456</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">👁️</div>
                        <div class="stat-content">
                            <div class="stat-label">总浏览量</div>
                            <div class="stat-value" id="external-pv-count">45,678</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-content">
                            <div class="stat-label">总访客数</div>
                            <div class="stat-value" id="external-uv-count">23,456</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">✨</div>
                        <div class="stat-content">
                            <div class="stat-label">总新访客数</div>
                            <div class="stat-value" id="external-new-visitor-count">8,234</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">💬</div>
                        <div class="stat-content">
                            <div class="stat-label">总会话数</div>
                            <div class="stat-value" id="external-session-count">34,567</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">📉</div>
                        <div class="stat-content">
                            <div class="stat-label">总跳出率</div>
                            <div class="stat-value" id="external-bounce-rate">42.5%</div>
                        </div>
                    </div>
                    <div class="shiroki-51la-stat-card">
                        <div class="stat-icon">⏱️</div>
                        <div class="stat-content">
                            <div class="stat-label">平均访问时长</div>
                            <div class="stat-value" id="external-avg-duration">03:45</div>
                        </div>
                    </div>
                </div>

                <!-- 🔍 筛选器 -->
                <div class="shiroki-51la-filter-bar">
                    <!-- 📅 时间筛选 -->
                    <div class="filter-group">
                        <label>时间范围</label>
                        <div class="filter-buttons" id="time-filter">
                            <button type="button" class="filter-btn active" data-time="today">今日</button>
                            <button type="button" class="filter-btn" data-time="yesterday">昨日</button>
                            <button type="button" class="filter-btn" data-time="before-yesterday">前日</button>
                            <button type="button" class="filter-btn" data-time="7days">最近7日</button>
                            <button type="button" class="filter-btn" data-time="30days">最近30日</button>
                            <button type="button" class="filter-btn" data-time="90days">最近90日</button>
                            <button type="button" class="filter-btn" data-time="custom">自定义</button>
                        </div>
                        <!-- 📅 自定义日期选择器（默认隐藏） -->
                        <div class="custom-date-range" id="custom-date-range" style="display: none;">
                            <input type="date" id="date-start" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                            <span>至</span>
                            <input type="date" id="date-end" value="<?php echo date('Y-m-d'); ?>">
                            <button type="button" class="shiroki-51la-btn shiroki-51la-btn-primary" id="apply-custom-date">应用</button>
                        </div>
                    </div>

                    <!-- 📱 设备类型筛选 -->
                    <div class="filter-group">
                        <label>设备类型</label>
                        <div class="filter-buttons" id="device-filter">
                            <button type="button" class="filter-btn active" data-device="all">全部</button>
                            <button type="button" class="filter-btn" data-device="desktop">电脑端</button>
                            <button type="button" class="filter-btn" data-device="mobile">移动端</button>
                        </div>
                    </div>

                    <!-- 👤 访客类型筛选 -->
                    <div class="filter-group">
                        <label>访客类型</label>
                        <div class="filter-buttons" id="visitor-filter">
                            <button type="button" class="filter-btn active" data-visitor="all">全部</button>
                            <button type="button" class="filter-btn" data-visitor="new">新访客</button>
                            <button type="button" class="filter-btn" data-visitor="returning">老访客</button>
                        </div>
                    </div>
                </div>

                <!-- � 外部链接数据表格 -->
                <div class="shiroki-51la-card shiroki-51la-external-table-card">
                    <div class="shiroki-51la-card-header">
                        <span class="shiroki-51la-card-icon">📋</span>
                        <span class="shiroki-51la-card-title">外部链接详情</span>
                        <div class="table-actions">
                            <button type="button" class="shiroki-51la-btn shiroki-51la-btn-secondary" id="export-data">
                                <span class="btn-icon">📥</span>
                                <span>导出数据</span>
                            </button>
                        </div>
                    </div>
                    <div class="shiroki-51la-card-body">
                        <div class="shiroki-51la-table-wrapper">
                            <table class="shiroki-51la-data-table" id="external-links-table">
                                <thead>
                                    <tr>
                                        <th class="col-link">外部链接</th>
                                        <th class="col-ip">IP数量</th>
                                        <th class="col-pv">浏览量</th>
                                        <th class="col-uv">访客数量</th>
                                        <th class="col-new">新访客数量</th>
                                        <th class="col-session">会话数量</th>
                                        <th class="col-bounce">跳出率</th>
                                        <th class="col-duration">平均访问时长</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // 🎨 演示数据
                                    $external_links_demo = array(
                                        array('link' => 'https://www.baidu.com/s?wd=example', 'ip' => 2341, 'pv' => 4523, 'uv' => 2341, 'new' => 892, 'session' => 2890, 'bounce' => '38.2%', 'duration' => '04:12'),
                                        array('link' => 'https://www.google.com/search?q=demo', 'ip' => 1856, 'pv' => 3124, 'uv' => 1856, 'new' => 654, 'session' => 2100, 'bounce' => '42.5%', 'duration' => '03:28'),
                                        array('link' => 'https://www.bing.com/search?q=test', 'ip' => 1234, 'pv' => 2345, 'uv' => 1234, 'new' => 432, 'session' => 1560, 'bounce' => '45.1%', 'duration' => '02:56'),
                                        array('link' => 'https://www.zhihu.com/question/12345', 'ip' => 987, 'pv' => 1876, 'uv' => 987, 'new' => 345, 'session' => 1200, 'bounce' => '35.8%', 'duration' => '05:23'),
                                        array('link' => 'https://weibo.com/share', 'ip' => 876, 'pv' => 1654, 'uv' => 876, 'new' => 298, 'session' => 980, 'bounce' => '48.2%', 'duration' => '02:15'),
                                        array('link' => 'https://www.douyin.com/video/abc123', 'ip' => 765, 'pv' => 1432, 'uv' => 765, 'new' => 432, 'session' => 890, 'bounce' => '52.3%', 'duration' => '01:45'),
                                        array('link' => 'https://www.bilibili.com/video/bv123', 'ip' => 654, 'pv' => 1234, 'uv' => 654, 'new' => 234, 'session' => 760, 'bounce' => '41.2%', 'duration' => '06:34'),
                                        array('link' => 'https://mp.weixin.qq.com/s/xxx', 'ip' => 543, 'pv' => 1098, 'uv' => 543, 'new' => 187, 'session' => 650, 'bounce' => '39.8%', 'duration' => '04:56'),
                                        array('link' => 'https://www.xiaohongshu.com/discovery', 'ip' => 432, 'pv' => 876, 'uv' => 432, 'new' => 156, 'session' => 540, 'bounce' => '44.5%', 'duration' => '03:12'),
                                        array('link' => 'https://www.csdn.net/article/123', 'ip' => 321, 'pv' => 654, 'uv' => 321, 'new' => 98, 'session' => 430, 'bounce' => '46.8%', 'duration' => '02:48'),
                                    );

                                    foreach ($external_links_demo as $item) :
                                    ?>
                                    <tr>
                                        <td class="col-link">
                                            <a href="<?php echo esc_url($item['link']); ?>" target="_blank" class="external-link" title="<?php echo esc_attr($item['link']); ?>">
                                                <?php echo esc_html(mb_strimwidth($item['link'], 0, 50, '...')); ?>
                                            </a>
                                        </td>
                                        <td class="col-ip"><?php echo number_format($item['ip']); ?></td>
                                        <td class="col-pv"><?php echo number_format($item['pv']); ?></td>
                                        <td class="col-uv"><?php echo number_format($item['uv']); ?></td>
                                        <td class="col-new"><?php echo number_format($item['new']); ?></td>
                                        <td class="col-session"><?php echo number_format($item['session']); ?></td>
                                        <td class="col-bounce"><?php echo $item['bounce']; ?></td>
                                        <td class="col-duration"><?php echo $item['duration']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 📄 分页 -->
                        <div class="shiroki-51la-pagination">
                            <div class="pagination-info">
                                显示第 <span id="page-start">1</span> 到 <span id="page-end">10</span> 条，共 <span id="total-items">156</span> 条
                            </div>
                            <div class="pagination-buttons">
                                <button type="button" class="page-btn" disabled>上一页</button>
                                <button type="button" class="page-btn active">1</button>
                                <button type="button" class="page-btn">2</button>
                                <button type="button" class="page-btn">3</button>
                                <span class="page-ellipsis">...</span>
                                <button type="button" class="page-btn">16</button>
                                <button type="button" class="page-btn">下一页</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📖 使用说明标签页 -->
        <div class="shiroki-51la-tab-content" data-tab-content="guide">
            <div class="shiroki-51la-card shiroki-51la-guide-card">
                <div class="shiroki-51la-card-header">
                    <span class="shiroki-51la-card-icon">📖</span>
                    <span class="shiroki-51la-card-title">51LA 统计使用指南</span>
                </div>
                <div class="shiroki-51la-card-body">
                    <div class="guide-section">
                        <h4>🚀 什么是 51LA 统计？</h4>
                        <p>51LA 是国内知名的免费网站流量统计服务，提供专业的网站访问数据分析功能，帮助您了解网站的访问情况和用户行为。</p>
                    </div>

                    <div class="guide-section">
                        <h4>📝 如何获取 API 密钥？</h4>
                        <ol class="guide-steps">
                            <li>
                                <strong>注册/登录账号</strong>
                                <p>访问 <a href="https://v6.51.la" target="_blank">v6.51.la</a> 注册或登录您的 51LA 账号</p>
                            </li>
                            <li>
                                <strong>添加站点</strong>
                                <p>在控制台中添加您的网站，获取统计代码并部署到网站上</p>
                            </li>
                            <li>
                                <strong>申请 API 权限</strong>
                                <p>进入「用户中心」→「应用管理」→「开放接口」，申请开通 API 权限</p>
                            </li>
                            <li>
                                <strong>获取密钥</strong>
                                <p>系统会自动生成 <code>AccessKey</code> 和 <code>SecretKey</code>，请妥善保存</p>
                            </li>
                            <li>
                                <strong>配置插件</strong>
                                <p>将获取到的密钥填入上方配置表单，点击保存即可</p>
                            </li>
                        </ol>
                    </div>

                    <div class="guide-section">
                        <h4>📊 数据指标说明</h4>
                        <ul class="guide-metrics">
                            <li><strong>PV (Page View)</strong> - 页面浏览量，每次页面加载计为一次</li>
                            <li><strong>UV (Unique Visitor)</strong> - 独立访客数，按用户去重统计</li>
                            <li><strong>IP</strong> - 独立 IP 数，按访问者 IP 去重</li>
                            <li><strong>跳出率</strong> - 只访问一个页面就离开的访客比例</li>
                            <li><strong>平均停留时长</strong> - 访客在网站的平均停留时间</li>
                        </ul>
                    </div>

                    <div class="guide-section">
                        <h4>⚠️ 注意事项</h4>
                        <ul class="guide-tips">
                            <li>API 数据有一定延迟，通常为 5-15 分钟</li>
                            <li>免费版 API 有调用频率限制，请合理使用</li>
                            <li>请妥善保管您的 SecretKey，不要泄露给他人</li>
                            <li>如需更详细的数据分析，请访问 <a href="https://v6.51.la" target="_blank">51LA 官网</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($demo_mode || !empty($chart_labels)) : ?>
    <!-- 📊 图表数据 -->
    <script type="text/javascript">
        window.shiroki51LAData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            pv: <?php echo json_encode($chart_pv); ?>,
            uv: <?php echo json_encode($chart_uv); ?>
        };
    </script>
    <?php endif; ?>
    <?php
}
