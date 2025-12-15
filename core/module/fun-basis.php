<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

// 安全设置--------------------------boxmoe.com--------------------------
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

// 🖼️ 允许SVG文件上传
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

// 常量定义--------------------------boxmoe.com--------------------------
$themedata = wp_get_theme();
$themeversion = $themedata['Version'];
define('THEME_VERSION', $themeversion);


// 随机字符串--------------------------boxmoe.com--------------------------
function boxmoe_random_string($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}


// 主题静态资源url--------------------------boxmoe.com--------------------------
function boxmoe_theme_url(){
    if(get_boxmoe('boxmoe_cdn_assets_switch')){

        return get_boxmoe('boxmoe_cdn_assets_url') ?: get_template_directory_uri();
    }
    return get_template_directory_uri();
}

// 前端布局--------------------------boxmoe.com--------------------------
function boxmoe_layout_setting(){
    $layout = get_boxmoe('boxmoe_blog_layout');
    $article_layout = get_boxmoe('boxmoe_article_layout_style');
    if($layout){
        if($layout == 'one'){
            echo 'col-lg-10 mx-auto';
        }elseif($layout == 'two'){
            // 三列文章布局时，加大主内容区域宽度
            if($article_layout == 'three'){
                echo 'col-lg-9';
            }else{
                echo 'col-lg-8';
            }
        }
    }else{
        echo 'col-lg-10 mx-auto';
    }
}

// Favicon--------------------------boxmoe.com--------------------------
function boxmoe_favicon(){
    $src= get_boxmoe('boxmoe_favicon_src');    
    if($src){
        echo $src;
    }else{
        echo boxmoe_theme_url().'/assets/images/favicon.ico';
    }
}

function boxmoe_filter_site_icon_url($url, $size, $blog_id){
    $src = get_boxmoe('boxmoe_favicon_src');
    if($src){
        return $src;
    }
    return boxmoe_theme_url().'/assets/images/favicon.ico';
}
add_filter('get_site_icon_url', 'boxmoe_filter_site_icon_url', 10, 3);

// LOGO--------------------------boxmoe.com--------------------------
function boxmoe_logo(){
    $src= get_boxmoe('boxmoe_logo_src');    

    if($src){
        echo '<img class="logo" src="'.$src.'" alt="'.get_bloginfo('name').'">';
    }else{
        echo '<span class="text-inverse">'.get_bloginfo('name').'</span>';
    }
}

// Banner图片--------------------------boxmoe.com--------------------------
function boxmoe_banner_image(){
    $src='';
    if(get_boxmoe('boxmoe_banner_api_switch')){
        $src= get_boxmoe('boxmoe_banner_api_url');    
    }elseif(get_boxmoe('boxmoe_banner_rand_switch')){
        $random_images = glob(get_template_directory().'/assets/images/banner/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);   
        if (!empty($random_images)) {
            $random_key = array_rand($random_images);
            $relative_path = str_replace(get_template_directory(), '', $random_images[$random_key]);
            $src = boxmoe_theme_url() . $relative_path;
        }
    }elseif(get_boxmoe('boxmoe_banner_url')){
        $src= get_boxmoe('boxmoe_banner_url');
    }else{
        $src= boxmoe_theme_url().'/assets/images/banner.jpg';
    }
    echo $src;
}

// 节日灯笼--------------------------boxmoe.com--------------------------
function boxmoe_festival_lantern(){
    if(get_boxmoe('boxmoe_festival_lantern_switch')){?>
    <div id="wp"class="wp"><div class="xnkl"><div class="deng-box2"><div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t"><?php echo get_boxmoe('boxmoe_lanternfont2','度')?></div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div></div><div class="deng-box3"><div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t"><?php echo get_boxmoe('boxmoe_lanternfont1','欢')?></div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div></div><div class="deng-box1"><div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t"><?php echo get_boxmoe('boxmoe_lanternfont4','春')?></div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div></div><div class="deng-box"><div class="deng"><div class="xian"></div><div class="deng-a"><div class="deng-b"><div class="deng-t"><?php echo get_boxmoe('boxmoe_lanternfont3','新')?></div></div></div><div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div></div></div></div></div>
    <?php
    }
}

// 高度载入--------------------------boxmoe.com--------------------------
function boxmoe_banner_height_load(){
        $pc_height = get_boxmoe('boxmoe_banner_height') ?: '580';
        $mb_height = get_boxmoe('boxmoe_banner_height_mobile') ?: '480';
        echo "<style>.boxmoe_header_banner{height:{$pc_height}px;} @media (max-width: 768px){.boxmoe_header_banner{height:{$mb_height}px;}}</style>"."\n    ";
}


// 全站变灰--------------------------boxmoe.com--------------------------
function boxmoe_body_grey(){
    if(get_boxmoe('boxmoe_body_grey_switch')){
        $css = "body{filter: grayscale(100%);}";
        wp_add_inline_style('boxmoe-style', $css);
    }
}
// 欢迎语--------------------------boxmoe.com--------------------------
function boxmoe_banner_welcome($return = false){
    $text = get_boxmoe('boxmoe_banner_font');
    $content = $text ?: 'Hello! 欢迎来到盒子萌！';
    if ($return) {
        return $content;
    }
    echo $content;
}


// 欢迎语一言 --------------------------boxmoe.com--------------------------
function boxmoe_banner_hitokoto(){
    if(get_boxmoe('boxmoe_banner_hitokoto_switch')){
        echo '<h1 class="main-title"><i class="fa fa-star spinner"></i><span id="hitokoto" class="text-gradient">加载中</span></h1>';
    }
}


// 前端资源载入--------------------------boxmoe.com--------------------------
function boxmoe_load_assets_header(){ 
    wp_enqueue_style('theme-style', boxmoe_theme_url() . '/assets/css/theme.min.css', array(), THEME_VERSION);
    wp_enqueue_style('boxmoe-style', boxmoe_theme_url() . '/assets/css/style.css', array(), THEME_VERSION);
    wp_enqueue_style('image-viewer-style', boxmoe_theme_url() . '/assets/css/image-viewer.css', array(), THEME_VERSION);
    if(get_boxmoe('boxmoe_jquery_switch')){
        wp_enqueue_script('jquery-script', boxmoe_theme_url() . '/assets/js/jquery.min.js', array(), THEME_VERSION, true);
    }
    wp_enqueue_script('theme-script', boxmoe_theme_url() . '/assets/js/theme.min.js', array(), THEME_VERSION, true);
    wp_enqueue_script('theme-lib-script', boxmoe_theme_url() . '/assets/js/lib.min.js', array(), THEME_VERSION, true);
    wp_enqueue_script('comments-script', boxmoe_theme_url() . '/assets/js/comments.js', array(), THEME_VERSION, true);
    wp_enqueue_script('boxmoe-script', boxmoe_theme_url() . '/assets/js/boxmoe.js', array(), THEME_VERSION, true);
    wp_enqueue_script('image-viewer-script', boxmoe_theme_url() . '/assets/js/image-viewer.js', array(), THEME_VERSION, true);
    if(get_boxmoe('boxmoe_sakura_switch')){
        wp_enqueue_script('sakura-script', boxmoe_theme_url() . '/assets/js/sakura.js', array(), THEME_VERSION, true);
    }

    wp_localize_script('theme-script', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'themeurl' => boxmoe_theme_url(),
        'is_user_logged_in' => is_user_logged_in() ? 'true' : 'false',
        'posts_per_page' => get_option('posts_per_page'),
        'nonce' =>wp_create_nonce('boxmoe_ajax_nonce'),
        'running_days' => get_boxmoe('boxmoe_footer_running_days_time')?:'2025-01-01',
        'hitokoto' => get_boxmoe('boxmoe_banner_hitokoto_text')?:'a'
    ));
}
add_action('wp_enqueue_scripts', 'boxmoe_load_assets_header');
add_action('wp_enqueue_scripts', 'boxmoe_body_grey', 12);

// 前端内容载入--------------------------boxmoe.com--------------------------
function boxmoe_load_assets_footer(){?>
          <div class="col-md-4 text-center text-md-start">
            <a class="mb-2 mb-lg-0 d-block" href="<?php echo home_url(); ?>">
            <?php boxmoe_logo(); ?></a>
          </div>
          <div class="col-md-8 col-lg-4 ">
            <div class="small mb-3 mb-lg-0 text-center">
                <?php if(get_boxmoe('boxmoe_footer_seo')): ?>
                    <ul class="nav flex-row align-items-center mt-sm-0 justify-content-center nav-footer">
                        <?php echo get_boxmoe('boxmoe_footer_seo');?>
                    </ul>
                <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4">
            <div class="d-flex align-items-center justify-content-center justify-content-md-end" id="social-links">
              <div class="text-center text-md-end">
                <?php if(get_boxmoe('boxmoe_social_instagram')): ?>
                <a href="<?php echo get_boxmoe('boxmoe_social_instagram'); ?>" class="text-reset btn btn-social btn-instagram" target="_blank">
                  <i class="fa fa-instagram"></i>
                </a>
                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_telegram')): ?>
                <a href="<?php echo get_boxmoe('boxmoe_social_telegram'); ?>" class="text-reset btn btn-social btn-telegram" target="_blank">
                  <i class="fa fa-telegram"></i>
                </a>
                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_github')): ?>
                <a href="<?php echo get_boxmoe('boxmoe_social_github'); ?>" class="text-reset btn btn-social btn-github" target="_blank">
                  <i class="fa fa-github"></i>
                </a>
                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_qq')): ?>
                <a href="https://wpa.qq.com/msgrd?v=3&amp;uin=<?php echo get_boxmoe('boxmoe_social_qq'); ?>&amp;site=qq&amp;menu=yes" class="text-reset btn btn-social btn-qq" target="_blank">
                  <i class="fa fa-qq"></i>
                </a>

                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_wechat')): ?>
                <a href="<?php echo get_boxmoe('boxmoe_social_wechat'); ?>" data-fancybox class="text-reset btn btn-social btn-wechat">
                  <i class="fa fa-weixin"></i>
                </a>
                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_weibo')): ?>
                <a href="<?php echo get_boxmoe('boxmoe_social_weibo'); ?>" class="text-reset btn btn-social btn-weibo" target="_blank">
                  <i class="fa fa-weibo"></i>
                </a>
                <?php endif; ?>
                <?php if(get_boxmoe('boxmoe_social_email')): ?>
                <a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=<?php echo get_boxmoe('boxmoe_social_email'); ?>" class="text-reset btn btn-social btn-email" target="_blank">
                  <i class="fa fa-envelope"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-12 text-center mt-3 copyright">
          <span><?php echo get_boxmoe('boxmoe_footer_copyright_hidden') ? '' : 'Copyright'; ?> © <?php echo date('Y'); ?> <a href="<?php echo home_url(); ?>"><?php echo get_bloginfo('name'); ?></a> <?php echo get_boxmoe('boxmoe_footer_info','Powered by WordPress'); ?> </span>
          <span><?php echo get_boxmoe('boxmoe_footer_theme_by_text','Theme by <a href="https://www.boxmoe.com" target="_blank">Boxmoe</a>'); ?></span>
          <?php if(get_boxmoe('boxmoe_footer_running_days_switch')): ?>
          <span class="runtime-line">
            <i class="fa fa-clock-o runtime-icon"></i>
            <span class="runtime-prefix"><?php echo get_boxmoe('boxmoe_footer_running_days_prefix','本站已在地球上苟活了'); ?></span>
            <span id="runtime-days" class="runtime-num runtime-days">0</span> <?php echo get_boxmoe('boxmoe_footer_running_days_suffix','天'); ?>
            <span id="runtime-hours" class="runtime-num runtime-hours">0</span> <?php echo get_boxmoe('boxmoe_footer_running_days_suffix_hours','时'); ?>
            <span id="runtime-minutes" class="runtime-num runtime-minutes">0</span> <?php echo get_boxmoe('boxmoe_footer_running_days_suffix_minutes','分'); ?>
            <span id="runtime-seconds" class="runtime-num runtime-seconds">0</span> <?php echo get_boxmoe('boxmoe_footer_running_days_suffix_seconds','秒'); ?>
          </span>
          <?php endif; ?>
          <?php if(get_boxmoe('boxmoe_footer_dataquery_switch')): ?>
          <span><?php echo get_num_queries(); ?> queries in <?php echo timer_stop(0,3); ?> s</span>
          <?php endif; ?>
          <span style="display:none;"><?php echo get_boxmoe('boxmoe_trackcode'); ?></span>
           </div>
<?php
}


// 注册导航菜单--------------------------boxmoe.com--------------------------
function boxmoe_register_menus() {
    register_nav_menus([
        'boxmoe-menu' => __('主导航菜单', 'boxmoe')
    ]);
}
add_action('after_setup_theme', 'boxmoe_register_menus');


// 导航菜单--------------------------boxmoe.com--------------------------
function boxmoe_nav_menu(){
    $menu_args = [
        'theme_location' => 'boxmoe-menu',
        'container' => false,
        'menu_class' => 'navbar-nav mx-auto align-items-lg-center',
        'walker' => new bootstrap_5_wp_nav_menu_walker(),
        'depth' => 3,
        'fallback_cb' => false
    ];
    if (has_nav_menu('boxmoe-menu')) {
        wp_nav_menu($menu_args);
    } else {
        echo '<div class="navbar-nav mx-auto align-items-lg-center">请先在后台创建并分配菜单</div>';
    }
}

// 🔗 导航菜单新窗口打开控制
function boxmoe_nav_target_blank_filter($items, $args) {
    if ($args->theme_location == 'boxmoe-menu' && get_boxmoe('boxmoe_nav_target_blank')) {
        foreach ($items as $item) {
            // 排除含有子菜单的父级项目 (通常只是 dropdown toggle)
            if (!in_array('menu-item-has-children', $item->classes)) {
                 $item->target = '_blank';
            }
        }
    }
    return $items;
}
add_filter('wp_nav_menu_objects', 'boxmoe_nav_target_blank_filter', 10, 2);

// 侧栏模块--------------------------boxmoe.com--------------------------
if (function_exists('register_sidebar')){
    // 设置边框样式
	$boxmoe_border='';
	if(get_boxmoe('boxmoe_blog_border') == 'default' ){
		$boxmoe_border='';
		}elseif(get_boxmoe('boxmoe_blog_border') == 'border'){
		$boxmoe_border='blog-border';
		}elseif(get_boxmoe('boxmoe_blog_border') == 'shadow'){
		$boxmoe_border='blog-shadow';
        }elseif(get_boxmoe('boxmoe_blog_border') == 'lines'){
        $boxmoe_border='blog-lines';
        }
        // 只有双栏布局才注册其他侧边栏
        if(get_boxmoe('boxmoe_blog_layout') == 'two'){
            $widgets = array(
                'site_sidebar' => __('全站侧栏展示', 'boxmoe-com'),
                'home_sidebar' => __('首页侧栏展示', 'boxmoe-com'),
                'post_sidebar' => __('文章页侧栏展示', 'boxmoe-com'),
                'page_sidebar' => __('页面侧栏展示', 'boxmoe-com'),
            );
    
            foreach ($widgets as $key => $value) {
                register_sidebar(array(
                    'name'          => $value,
                    'id'            => 'widget_'.$key,
                    'before_widget' => '<div class="widget '.$boxmoe_border.' %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h4 class="widget-title">',
                    'after_title'   => '</h4>'
                ));
            }
            require_once get_template_directory() . '/core/widgets/widget-set.php';
        }
    
    // 注册底部栏小部件区域（无论布局如何都注册）
    register_sidebar(array(
        'name'          => __('底部栏展示', 'boxmoe-com'),
        'id'            => 'widget_footer_widgets',
        'before_widget' => '<div class="widget '.$boxmoe_border.' %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>'
    ));

    
}



// 懒加载图片--------------------------boxmoe.com--------------------------
function boxmoe_lazy_load_images(){
    if(get_boxmoe('boxmoe_lazy_load_images')){
        $src = get_boxmoe('boxmoe_lazy_load_images');
    }else{
        $src = boxmoe_theme_url().'/assets/images/loading.gif';
    }
    return $src;
}


// 边框设置--------------------------boxmoe.com--------------------------
function boxmoe_border_setting(){
    $border = get_boxmoe('boxmoe_blog_border');
    if($border){
        if($border == 'default'){
            echo '';
        }elseif($border == 'border'){
            echo 'blog-border';
        }elseif($border == 'shadow'){
            echo 'blog-shadow';
        }elseif($border == 'lines'){
            echo 'blog-lines';
        }
    }else{
        echo 'blog-border';
    }
}



// 搜索结果排除所有页面--------------------------boxmoe.com--------------------------
function boxmoe_search_exclude_pages($query) {
    if ($query->is_search) {
        $query->set('post_type', 'post');
    }
    return $query;
}
add_filter('pre_get_posts', 'boxmoe_search_exclude_pages');


// 开启友情链接--------------------------boxmoe.com--------------------------
add_filter( 'pre_option_link_manager_enabled', '__return_true' );

function boxmoe_allow_woff_uploads($mimes){
    $mimes['woff'] = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    return $mimes;
}
add_filter('upload_mimes','boxmoe_allow_woff_uploads');

