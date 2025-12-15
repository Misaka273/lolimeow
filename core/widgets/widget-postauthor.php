<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//=======安全设置，阻止直接访问主题文件=======
if (!defined('ABSPATH')) {echo'Look your sister';exit;}
//=========================================

// 📝 当前文章作者信息小工具
class widget_postauthor extends WP_Widget {

	function __construct(){
		parent::__construct( 
			'widget_postauthor', 
			'Boxmoe_当前文章作者', 
			array( 
				'description' => __('显示当前文章作者的详细信息', 'boxmoe-com'),
				'classname'   => __('widget-postauthor', 'boxmoe-com')
			) 
		);
	}
	
	// 小工具前端显示
	public function widget( $args, $instance ) {
		// 检查是否在文章页面
		if (!is_singular('post')) {
			return;
		}
		
		// 获取当前文章的作者ID
		$author_id = get_the_author_meta('ID');
		$user = get_user_by('ID', $author_id);
		if (!$user) {
			return;
		}
		
		extract($args);
		$title = apply_filters('widget_name', $instance['title']);
		
		// 获取用户资料信息
		$nickname = $user->display_name;
		$bio = $user->description;
		$email = $user->user_email;
		$qq = get_the_author_meta('qq', $author_id);
		$github = get_the_author_meta('github', $author_id);
		$gitee = get_the_author_meta('gitee', $author_id);
		$wechat = get_the_author_meta('wechat', $author_id);
		
		// 获取作者文章数和评论数
		$post_count = count_user_posts($author_id);
		$comment_count = get_comments(array(
			'author_id' => $author_id,
			'status' => 'approve',
			'count' => true
		));
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div class="widget-content">';	
		echo '<div class="widget-profile">';
		echo '<div class="profile-avatar">';
		echo '<img src="'.boxmoe_lazy_load_images().'"  class="lazy" data-src="';
		// 使用主题自定义的头像获取函数，支持QQ头像等
		echo function_exists('boxmoe_get_avatar_url') ? boxmoe_get_avatar_url($author_id, 100) : get_avatar_url($author_id, array('size' => 100));
		echo '" alt="'.esc_html($nickname).'">';
		echo '</div>';
		echo '<h3 class="profile-name">'. esc_html($nickname) .'</h3>';
		
		// 显示个人介绍（如果有内容）
		if (!empty($bio)) {
			echo '<p class="profile-desc">'. esc_html($bio) .'</p>';
		}
		
		// 显示社交媒体图标
		echo '<div class="profile-social">';
		
		// 显示QQ图标（如果有QQ号）
		if (!empty($qq)) {
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($qq).'" title="点击复制QQ号"><i class="fa fa-qq"></i></a>';
		}
		
		// 显示邮箱图标
		if (!empty($email)) {
			// 统一使用邮箱图标
			$email_icon = 'fa-envelope';
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($email).'" title="点击复制邮箱"><i class="fa '.esc_attr($email_icon).'"></i></a>';
		}
		
		// 显示GitHub图标（如果有GitHub链接）
		if (!empty($github)) {
			echo '<a href="'.esc_url($github).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问GitHub"><i class="fa fa-github"></i></a>';
		}
		
		// 显示Gitee图标（如果有Gitee链接）
		if (!empty($gitee)) {
			echo '<a href="'.esc_url($gitee).'" class="social-link" target="_blank" rel="noopener noreferrer" title="访问Gitee"><img src="https://gitee.com/static/images/gitee-logos/logo_gitee_g_red.svg" alt="Gitee" style="width: 16px; height: 16px;"></a>';
		}
		
		// 显示微信图标（如果有微信号）
		if (!empty($wechat)) {
			echo '<a href="javascript:void(0);" class="social-link copy-btn" data-copy-text="'.esc_attr($wechat).'" title="点击复制微信"><i class="fa fa-weixin"></i></a>';
		}
		echo '</div>';
		
		// 显示作者统计数据
		echo '<div class="profile-stats">';
		echo '<div class="stat-item">';
		echo '<div class="stat-value">'. $post_count .'</div>';
		echo '<div class="stat-label">文章</div>';
		echo '</div>';
		echo '<div class="stat-item">';
		echo '<div class="stat-value">'. $comment_count .'</div>';
		echo '<div class="stat-label">评论</div>';
		echo '</div>';
		echo '</div>';
		
		// 添加CSS动画样式
		echo '<style>';
		echo '.social-link {';
		echo '    position: relative;';
		echo '    transition: transform 0.3s ease;';
		echo '    overflow: hidden;';
		echo '}';
		echo '.social-link:hover {';
		echo '    transform: translateY(-3px);';
		echo '}';
		echo '.social-link::after {';
		echo '    content: "";';
		echo '    position: absolute;';
		echo '    top: -50%;';
		echo '    left: -50%;';
		echo '    width: 200%;';
		echo '    height: 200%;';
		echo '    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);';
		echo '    transform: rotate(45deg);';
		echo '    transition: left 0.5s ease, opacity 0.3s ease;';
		echo '    opacity: 0;';
		echo '    pointer-events: none;';
		echo '}';
		echo '.social-link:hover::after {';
		echo '    left: 150%;';
		echo '    opacity: 1;';
		echo '}';
		echo '</style>';
		

		echo '</div>';
echo '</div>';
echo $after_widget;
	}
	
	// 后台表单
	public function form( $instance ) {
		$defaults = array(
			'title' => __('文章作者信息', 'boxmoe-com'),
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label>
				<?php echo __('标题：', 'boxmoe-com') ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
				name="<?php echo $this->get_field_name('title'); ?>" type="text" 
				value="<?php echo esc_attr($instance['title']); ?>" />
			</label>
		</p>
		<?php
	}
	
	// 更新小工具设置
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}
}
