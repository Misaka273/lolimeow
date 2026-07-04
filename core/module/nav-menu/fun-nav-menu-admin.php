<?php
/**
 * 导航栏设置页 UI — 与「所有文章」网格页同一套后台 UI 架构
 *
 * @package Lolimeow_Shiroki
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 导航栏设置页 URL / 请求检测（勿用 sanitize_key，会把 nav-menus.php 变成 nav-menusphp）
 */
function boxmoe_is_nav_menus_request() {
    global $pagenow;

    if (!empty($pagenow) && $pagenow === 'nav-menus.php') {
        return true;
    }

    if (!empty($_GET['page'])) {
        $page = wp_unslash($_GET['page']);
        $page = is_string($page) ? plugin_basename($page) : '';
        if ($page === 'nav-menus.php') {
            return true;
        }
    }

    if (!empty($_SERVER['REQUEST_URI']) && false !== strpos(wp_unslash($_SERVER['REQUEST_URI']), 'nav-menus.php')) {
        return true;
    }

    return false;
}

/**
 * 导航栏设置页：强制使用站点语言（settings → 常规 → 站点语言）
 */
function boxmoe_nav_menu_determine_locale($locale) {
    if (!is_admin() || !boxmoe_is_nav_menus_request()) {
        return $locale;
    }

    $site_locale = get_option('WPLANG');

    if (is_string($site_locale) && $site_locale !== '' && $site_locale !== 'en_US') {
        return $site_locale;
    }

    return $locale;
}

add_filter('determine_locale', 'boxmoe_nav_menu_determine_locale', 5);
add_filter('locale', 'boxmoe_nav_menu_determine_locale', 5);

/**
 * 导航栏设置页 UI 主类
 */
class Shiroki_Nav_Menu_Admin_UI {

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        add_action('load-nav-menus.php', array($this, 'ensure_admin_locale'), 0);
        add_action('admin_init', array($this, 'ensure_admin_locale'), 0);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_action('admin_enqueue_scripts', array($this, 'ensure_script_translations'), 100);
        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_filter('gettext', 'boxmoe_nav_menu_rename_labels_gettext', 999, 3);
        add_filter('ngettext', 'boxmoe_nav_menu_rename_labels_ngettext', 999, 5);
        add_filter('admin_title', 'boxmoe_nav_menu_admin_title', 20, 2);
        add_action('admin_footer', 'boxmoe_nav_menu_admin_footer_i18n', 100);
    }

    /**
     * 读取站点后台语言（设置 → 常规 → 站点语言）
     */
    public function get_site_admin_locale() {
        $locale = get_option('WPLANG');

        if (is_string($locale) && $locale !== '') {
            return $locale;
        }

        return get_locale();
    }

    /**
     * 导航栏设置页加载前：强制加载站点语言包（含 admin-zh_CN）
     *
     * 主题 functions.php 在 load_default_textdomain 之后才加载，
     * 若用户个人资料语言为 English，核心导航页会整页英文；此处与站点语言对齐。
     */
    public function ensure_admin_locale() {
        $site_locale = $this->get_site_admin_locale();

        if ($site_locale === '' || $site_locale === 'en_US') {
            return;
        }

        $has_admin_pack = file_exists(WP_LANG_DIR . '/admin-' . $site_locale . '.l10n.php')
            || file_exists(WP_LANG_DIR . '/admin-' . $site_locale . '.mo');

        if (!$has_admin_pack) {
            return;
        }

        if (determine_locale() !== $site_locale) {
            switch_to_locale($site_locale);
        }

        unload_textdomain('default');
        load_default_textdomain($site_locale);
    }

    /**
     * 重新绑定 nav-menu 脚本的 JSON 翻译（Bulk Select 等 JS 文案）
     */
    public function ensure_script_translations($hook) {
        if (!$this->is_nav_menu_screen($hook)) {
            return;
        }

        if (wp_script_is('nav-menu', 'registered') || wp_script_is('nav-menu', 'enqueued')) {
            wp_set_script_translations('nav-menu', 'default', WP_LANG_DIR);
        }
    }

    /**
     * 是否处于导航栏设置页
     *
     * @param string|null $hook admin_enqueue_scripts 传入的 $hook
     */
    public function is_nav_menu_screen($hook = null) {
        if (!is_admin()) {
            return false;
        }

        if (boxmoe_is_nav_menus_request()) {
            return true;
        }

        if (is_string($hook) && $hook !== '') {
            if ($hook === 'nav-menus.php' || false !== strpos($hook, 'nav-menus')) {
                return true;
            }
        }

        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && ($screen->id === 'nav-menus' || $screen->base === 'nav-menus')) {
                return true;
            }
        }

        return false;
    }

    /**
     * body class：与 post-grid 的 shiroki-post-grid-active 同模式
     */
    public function admin_body_class($classes) {
        if ($this->is_nav_menu_screen()) {
            $classes .= ' shiroki-nav-menu-active boxmoe-nav-menus-ui';
        }

        return $classes;
    }

    /**
     * 加载样式与脚本（依赖全局 admin-flat-rounded，不再单独隔离）
     */
    public function enqueue_assets($hook) {
        if (!$this->is_nav_menu_screen($hook)) {
            return;
        }

        $css_path  = get_template_directory() . '/assets/css/admin/nav-menu/nav-menu.css';
        $js_path   = get_template_directory() . '/assets/js/admin/nav-menu/nav-menu.js';
        $version   = file_exists($css_path) ? (string) filemtime($css_path) : (defined('THEME_VERSION') ? THEME_VERSION : '1.0');
        $js_ver    = file_exists($js_path) ? (string) filemtime($js_path) : $version;
        $theme_uri = get_template_directory_uri();

        $style_deps = array('admin-variables', 'lolimeow-admin-flat-rounded');

        wp_enqueue_style(
            'shiroki-nav-menu-admin',
            $theme_uri . '/assets/css/admin/nav-menu/nav-menu.css',
            $style_deps,
            $version
        );

        wp_enqueue_script(
            'shiroki-nav-menu-admin',
            $theme_uri . '/assets/js/admin/nav-menu/nav-menu.js',
            array('jquery'),
            $js_ver,
            true
        );

        wp_localize_script(
            'shiroki-nav-menu-admin',
            'boxmoeNavMenuConfig',
            array(
                'adminUrl' => admin_url(),
                'themeOptionsUrl' => admin_url('admin.php?page=boxmoe_options'),
                'widgetsUrl' => admin_url('admin.php?page=widgets.php'),
                'navMenusUrl' => admin_url('nav-menus.php'),
                'livePreviewLabel' => '实时预览管理',
                'labelMap' => boxmoe_nav_menu_label_map(),
            )
        );
    }
}

/**
 * 是否处于导航栏设置页（nav-menus.php）— 供 gettext 等全局回调使用
 */
function boxmoe_is_nav_menus_admin_screen() {
    if (boxmoe_is_nav_menus_request()) {
        return true;
    }

    return Shiroki_Nav_Menu_Admin_UI::get_instance()->is_nav_menu_screen();
}

/**
 * 导航栏设置页文案映射表
 */
function boxmoe_nav_menu_label_map() {
    return array(
        'Save Menu' => '保存导航',
        'Add to Menu' => '添加至导航',
        'Edit Menus' => '编辑导航',
        'Manage Locations' => '管理位置',
        'Manage with Live Preview' => '实时预览管理',
        'Menu Settings' => '导航设置',
        'Menu Structure' => '导航结构',
        'Menu Name' => '导航名称',
        'Select a menu to edit:' => '选择要编辑的导航：',
        'Select a menu' => '选择导航',
        'create a new menu' => '创建新导航',
        'Create a new menu' => '创建新导航',
        'Create Menu' => '创建导航',
        'Delete Menu' => '删除导航',
        'Menu Item' => '导航项',
        'Menu Parent' => '导航父级',
        'Add Items' => '添加导航项',
        'Bulk Select' => '批量选择',
        'Select All' => '全选',
        'Most Recent' => '最近',
        'View All' => '查看全部',
        'Search' => '搜索',
        'Pages' => '页面',
        'Posts' => '文章',
        'Custom Links' => '自定义链接',
        'Categories' => '分类目录',
        'Auto add pages' => '自动添加页面',
        'Automatically add new top-level pages to this menu' => '自动将新建的顶级页面添加到此导航',
        'Display location' => '显示位置',
        'Drag the items into the order you prefer. Click the arrow on the right of the item to reveal additional configuration options.' => '将各项目拖放到您想要的顺序。点击项目右侧箭头可展开更多配置选项。',
        'Drag each item into the order you prefer. Click the arrow on the right of the item to reveal additional configuration options.' => '将各项目拖放到您想要的顺序。点击项目右侧箭头可展开更多配置选项。',
        'Click the arrow on the right of the item to configure it.' => '点击项目右侧箭头进行配置。',
        'Give your menu a name, then click Create Menu.' => '为导航命名，然后点击「创建导航」。',
        'Create your first menu below.' => '请在下方创建您的第一个导航。',
        'Edit your menu below, or create a new menu.' => '在下方编辑导航，或创建一个新导航。',
        'Menu Locations' => '导航位置',
        '保存菜单' => '保存导航',
        '添加至菜单' => '添加至导航',
        '编辑菜单' => '编辑导航',
        '选择要编辑的菜单' => '选择要编辑的导航',
        '选择菜单' => '选择导航',
        '创建新菜单' => '创建新导航',
        '或创建新菜单' => '或创建新导航',
        '创建菜单' => '创建导航',
        '删除菜单' => '删除导航',
        '菜单结构' => '导航结构',
        '菜单名称' => '导航名称',
        '菜单设置' => '导航设置',
        '菜单项' => '导航项',
        '菜单父元素' => '导航父元素',
        '菜单顺序' => '导航顺序',
        '显示高级菜单属性' => '显示高级导航属性',
        '您的菜单是空的' => '您的导航是空的',
        '没有找到菜单' => '没有找到导航',
        '新菜单' => '新导航',
        '向菜单中添加项目' => '向导航中添加项目',
        '从菜单中移除' => '从导航中移除',
        '将各项拖放到您想要的顺序。点击项目右侧的箭头可显示其他配置选项。' => '将各项目拖放到您想要的顺序。点击项目右侧箭头可展开更多配置选项。',
        '菜单' => '导航',
        'Menu' => '导航',
        'Menus' => '导航',
    );
}

/**
 * 替换字符串中的「菜单」为「导航」
 */
function boxmoe_nav_menu_apply_label($text) {
    if (!is_string($text) || $text === '') {
        return $text;
    }

    $map = boxmoe_nav_menu_label_map();

    if (isset($map[$text])) {
        return $map[$text];
    }

    if (strpos($text, '菜单') !== false) {
        return str_replace('菜单', '导航', $text);
    }

    return $text;
}

/**
 * 导航栏设置页：gettext 文案替换
 */
function boxmoe_nav_menu_rename_labels_gettext($translated, $text, $domain) {
    if ($domain !== 'default' || !boxmoe_is_nav_menus_admin_screen()) {
        return $translated;
    }

    $map = boxmoe_nav_menu_label_map();

    if (isset($map[$text])) {
        return $map[$text];
    }

    if (isset($map[$translated])) {
        return $map[$translated];
    }

    if (strpos($translated, '菜单') !== false) {
        return str_replace('菜单', '导航', $translated);
    }

    return $translated;
}

/**
 * 导航栏设置页：ngettext 文案替换
 */
function boxmoe_nav_menu_rename_labels_ngettext($translated, $single, $plural, $number, $domain) {
    if ($domain !== 'default' || !boxmoe_is_nav_menus_admin_screen()) {
        return $translated;
    }

    return boxmoe_nav_menu_apply_label($translated);
}

/**
 * 导航栏设置页：浏览器标题替换
 */
function boxmoe_nav_menu_admin_title($admin_title, $title) {
    if (!boxmoe_is_nav_menus_admin_screen()) {
        return $admin_title;
    }

    return boxmoe_nav_menu_apply_label($admin_title);
}

/**
 * 导航栏设置页：DOM 兜底翻译（部分英文原文不经过 gettext）
 */
function boxmoe_nav_menu_admin_footer_i18n() {
    if (!boxmoe_is_nav_menus_admin_screen()) {
        return;
    }

    $map = boxmoe_nav_menu_label_map();
    ?>
    <script>
    (function () {
        var map = <?php echo wp_json_encode($map); ?>;

        function patchTextNode(el) {
            if (!el) {
                return;
            }

            if (el.matches && el.matches('input[type="submit"], input[type="button"]')) {
                if (map[el.value]) {
                    el.value = map[el.value];
                }
                return;
            }

            var text = (el.textContent || '').trim();
            if (map[text]) {
                el.textContent = map[text];
            }
        }

        function patchNavMenuLabels(root) {
            var selectors = [
                '.menu-instructions',
                '.manage-menus',
                '.post-body p',
                '.tabs-panel',
                'label',
                'legend',
                '.accordion-section-title',
                '.nav-tab',
                '.button',
                '.button-link',
                '.bulk-select-button-label',
                '.boxmoe-nav-menus-live-preview',
                '.boxmoe-nav-menus-workspace',
                '#nav-menus-frame',
                '#menu-settings-column',
                'input[type="submit"]'
            ].join(', ');

            (root || document).querySelectorAll(selectors).forEach(function (el) {
                if (el.childElementCount === 0 || el.tagName === 'INPUT') {
                    patchTextNode(el);
                }
            });
        }

        function bootPatch() {
            patchNavMenuLabels(document);
            window.setTimeout(function () {
                patchNavMenuLabels(document);
            }, 300);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootPatch);
        } else {
            bootPatch();
        }

        if (window.jQuery) {
            window.jQuery(document).on('menu-item-added menu-item-deleted wp-menu-state-restored', bootPatch);
        }
    })();
    </script>
    <?php
}

Shiroki_Nav_Menu_Admin_UI::get_instance();
