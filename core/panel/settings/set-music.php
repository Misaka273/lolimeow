<?php
/**
 * @link https://gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器设置面板
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

$options[] = array(
    'name' => __('音乐播放器设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-playlist-audio',
    'id' => 'boxmoe_music_player',
    'type' => 'heading'); 

$options[] = array(
    'group' => 'start',
	'group_title' => '音乐播放器基本设置',
	'name' => __('启用音乐播放器', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_switch',
	'type' => "checkbox",
	'std' => false,
	'desc' => __('开启后将在网站中显示音乐播放器', 'ui_boxmoe_com'));
    
    $options[] = array(
	'name' => __('播放器位置', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_position',
	'std' => "bottom-right",
	'type' => "select",
	'options' => array(
		'top-left' => __('左上角', 'ui_boxmoe_com'),
		'top-right' => __('右上角', 'ui_boxmoe_com'),
		'bottom-left' => __('左下角', 'ui_boxmoe_com'),
		'bottom-right' => __('右下角', 'ui_boxmoe_com')
	));
    
    $options[] = array(
	'name' => __('播放器大小', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_size',
	'std' => "medium",
	'type' => "select",
	'options' => array(
		'small' => __('小', 'ui_boxmoe_com'),
		'medium' => __('中', 'ui_boxmoe_com'),
		'large' => __('大', 'ui_boxmoe_com')
	));
    
    $options[] = array(
	'name' => __('自动播放', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_autoplay',
	'type' => "checkbox",
	'std' => false,
	'desc' => __('开启后页面加载时自动播放音乐', 'ui_boxmoe_com'));
    
    $options[] = array(
	'name' => __('默认音量', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_volume',
	'std' => "50",
	'type' => "text",
	'class' => 'mini',
	'desc' => __('设置播放器默认音量（0-100）', 'ui_boxmoe_com'));
    
    $options[] = array(
	'name' => __('循环播放', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_loop',
	'type' => "checkbox",
	'std' => true,
	'desc' => __('开启后音乐将循环播放', 'ui_boxmoe_com'));
    
    // 自定义样式设置项 - 合并到基本设置分组中
    $options[] = array(
	'name' => __('播放器主题色', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_theme_color',
	'std' => "#8b3dff",
	'type' => "color",
	'desc' => __('设置播放器的主题颜色', 'ui_boxmoe_com'));
    
    $options[] = array(
        'group' => 'end',
	'name' => __('自定义CSS', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_custom_css',
	'std' => "",
	'settings' => array('rows' => 3),
	'type' => 'textarea',
	'desc' => __('添加自定义CSS样式，用于调整播放器外观', 'ui_boxmoe_com'));
    
    $options[] = array(
        'group' => 'start',
	'group_title' => '音乐源设置',
	'name' => __('音乐服务器', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_server',
	'std' => "netease",
	'type' => "select",
	'options' => array(
		'netease' => __('网易云音乐', 'ui_boxmoe_com'),
		'tencent' => __('QQ音乐', 'ui_boxmoe_com'),
		'xiami' => __('虾米音乐', 'ui_boxmoe_com')
	));
    
    $options[] = array(
	'name' => __('音乐类型', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_type',
	'std' => "playlist",
	'type' => "select",
	'options' => array(
		'song' => __('单曲', 'ui_boxmoe_com'),
		'album' => __('专辑', 'ui_boxmoe_com'),
		'artist' => __('艺术家', 'ui_boxmoe_com'),
		'playlist' => __('歌单', 'ui_boxmoe_com')
	));
    
    $options[] = array(
	'name' => __('自定义API地址', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_api',
	'std' => "",
	'type' => "text",
	'desc' => __('输入自定义音乐API地址，留空则使用预设API', 'ui_boxmoe_com'));
    
    $options[] = array(
	'name' => __('预设API选择', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_default_api',
	'std' => "default",
	'type' => "select",
	'options' => array(
		'default' => __('默认 API', 'ui_boxmoe_com'),
		'tencent_vip' => __('仅 QQ 音乐 API（支持会员）', 'ui_boxmoe_com')
	),
	'desc' => __('选择预设的音乐API，当自定义API为空时使用', 'ui_boxmoe_com'));
    
    $options[] = array(
        'group' => 'end',
	'name' => __('音乐ID', 'ui_boxmoe_com'),
	'id' => 'boxmoe_music_player_id',
	'std' => "",
	'type' => "text",
	'desc' => __('输入音乐ID，如网易云音乐歌单ID', 'ui_boxmoe_com'));