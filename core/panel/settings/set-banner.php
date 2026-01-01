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

$options[] = array(
    'name' => __( 'Banner设置', 'ui_boxmoe_com' ),
    'icon' => 'dashicons-format-gallery',
    'desc' => __( '（导航下的图片设置）', 'ui_boxmoe_com' ),
    'type' => 'heading'
);
    $options[] = array(
        'group' => 'start',
		'group_title' => 'Banner欢迎语一言设置',
		'name' => __( 'Banner欢迎语', 'ui_boxmoe_com' ),
		'desc' => __('（留空则不显示）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_font',
		'std' => 'Hello! 欢迎来到盒子萌-🎉纸鸢版！',
		'type' => 'text');
    $options[] = array(
		'name' => __('banner一言开关', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_hitokoto_switch',
		'type' => "checkbox",
		'std' => true,
		);
    $options[] = array(
		'name' => __('关闭彩虹色banner欢迎语', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_rainbow_switch',
		'type' => "checkbox",
		'std' => false,
		);
    $options[] = array(
		'name' => __('关闭打字效果', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_typing_switch',
		'desc' => __('（开启后欢迎语将直接显示，不显示打字动画）', 'ui_boxmoe_com'),
		'type' => "checkbox",
		'std' => false,
		);
        $hitokoto_array = array(
			'a' => __('动画', 'ui_boxmoe_com'),
			'b' => __('漫画', 'ui_boxmoe_com'),
			'c' => __('游戏', 'ui_boxmoe_com'),
			'd' => __('文学', 'ui_boxmoe_com'),
			'e' => __('原创', 'ui_boxmoe_com'),
			'f' => __('来自网络', 'ui_boxmoe_com'),	
			'g' => __('其他', 'ui_boxmoe_com'),
			'h' => __('影视', 'ui_boxmoe_com'),
			'i' => __('诗词', 'ui_boxmoe_com'),
			'j' => __('网易云', 'ui_boxmoe_com'),
			'k' => __('哲学', 'ui_boxmoe_com'),
		);
    $options[] = array(
        'group' => 'end',
		'name' => __('选择一言句子类型', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_hitokoto_text',
		'type' => 'select',
		'options' => $hitokoto_array);
    $options[] = array(
        'group' => 'start',
		'group_title' => '自定义banner高度开关',
		'id' => 'boxmoe_banner_height_switch',
		'type' => "checkbox",
		'std' => false,
		);
    $options[] = array(
		'name' => __( '[PC端]Banner高度 留空则默认580', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_banner_height',
		'std' => '580',
		'class' => 'mini',
		'type' => 'text');
	$options[] = array(
		'name' => __( '[手机端]Banner高度 留空默认480', 'ui_boxmoe_com' ),
		'id' => 'boxmoe_banner_height_m',
		'std' => '480',
		'class' => 'mini',
		'group' => 'end',
		'type' => 'text');	
    $options[] = array(
		'name' => __('自定义Banner背景图', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_url',
		'std' => $image_path.'banner.jpg',
		'type' => 'upload');
    $options[] = array(
		'group' => 'start',
		'group_title' => 'Banner随机图片',
		'name' => __('Banner开启本地随机图片', 'ui_boxmoe_com'),
		'desc' => __('（自动检索本地assets/images/banner/文件夹的jpg\jpeg\png\gif\webp图片）', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_rand_switch',
		'class' => 'mini',
        'std' => false,
		'type' => 'checkbox');
    $options[] = array(
		'name' => __('使用外链APi-Banner图片', 'ui_boxmoe_com'),
		'desc' => __('（开启后上方本地设置图片功能全失效）', 'ui_boxmoe_com'),		
		'id' => 'boxmoe_banner_api_switch',
		'type' => "checkbox",
		'std' => false,
		);
	$options[] = array(
        'group' => 'end',
		'name' => __('图片外链APi链接', 'ui_boxmoe_com'),
		'id' => 'boxmoe_banner_api_url',
		'std' => 'https://api.boxmoe.com/random.php?size=mw1024',
		'type' => 'text');     