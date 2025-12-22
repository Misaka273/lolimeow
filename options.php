<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 * @author 专收爆米花
 * @author 白木 <https://gl.baimu.live/864> (二次创作)
 */

//安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}
function optionsframework_option_name() {
	return 'options-framework-theme';
}
function optionsframework_options() {
    //获取分类
	$options_categories = array();
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	//获取标签
	$options_tags = array();
	$options_tags_obj = get_tags();
	foreach ( $options_tags_obj as $tag ) {
		$options_tags[$tag->term_id] = $tag->name;
	}
	//获取页面
	$options_pages = array();
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = '请选择页面';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}
	//定义图片路径
	$image_path =  get_template_directory_uri() . '/assets/images/';
	$web_home = 'https://www.boxmoe.com';
	$THEME_VERSION = THEME_VERSION;
	$options = array();
//基础设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-basis.php';
//Banner设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-banner.php';
//SEO优化-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-seo.php';
//文章设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-artice.php';
//页面标语设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-slogan.php';
//评论设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-comment.php';  
//用户设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-user.php';
//用户身份设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-user-identity.php';
//社交图标-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-social.php';
//静态加速-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-assets.php';
//字体设置-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-fonts.php';
//系统优化-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-optimize.php';
//消息通知-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-msg.php';
//音乐播放器设置-----------------------------------------------------------
// 由初叶www.chuyel.top提供👍🏻，白木gl.baimu.live集成🎉
require_once get_template_directory() . '/core/panel/settings/set-music.php';
//主题信息-----------------------------------------------------------
require_once get_template_directory() . '/core/panel/settings/set-theme.php';

  
//-----------------------------------------------------------
	return $options;
}
