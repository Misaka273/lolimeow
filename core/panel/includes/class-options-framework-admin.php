<?php
/**
 * @package   Options_Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2014 WP Theming
 */

class Options_Framework_Admin {

	/**
     * Page hook for the options screen
     *
     * @since 1.7.0
     * @type string
     */
    protected $options_screen = null;

    /**
     * Hook in the scripts and styles
     *
     * @since 1.7.0
     */
    public function init() {

		// Gets options to load
    	$options = & Options_Framework::_optionsframework_options();

		// Checks if options are available
    	if ( $options ) {

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_custom_options_page' ) );

			// Add the required scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Settings need to be registered after admin_init
			add_action( 'admin_init', array( $this, 'settings_init' ) );

			// Adds options menu to the admin bar
			add_action( 'wp_before_admin_bar_render', array( $this, 'optionsframework_admin_bar' ) );

		}

    }

	/**
     * Registers the settings
     *
     * @since 1.7.0
     */
    function settings_init() {

		// Get the option name
		$options_framework = new Options_Framework;
	    $name = $options_framework->get_option_name();

		// Registers the settings fields and callback
		register_setting( 'optionsframework', $name, array ( $this, 'validate_options' ) );

		// Displays notice after options save
		add_action( 'optionsframework_after_validate', array( $this, 'save_options_notice' ) );

    }

	/*
	 * Define menu options
	 *
	 * Examples usage:
	 *
	 * add_filter( 'optionsframework_menu', function( $menu ) {
	 *     $menu['page_title'] = 'The Options';
	 *	   $menu['menu_title'] = 'The Options';
	 *     return $menu;
	 * });
	 *
	 * @since 1.7.0
	 *
	 */
	static function menu_settings() {

		$menu = array(

			// Modes: submenu, menu
			'mode' => 'menu',

			// Submenu default settings
			'page_title' => __( '盒子萌主题设置', 'textdomain' ),
			'menu_title' => __('盒子萌主题设置', 'textdomain'),
			'capability' => 'edit_theme_options',
			'menu_slug' => 'boxmoe_options',
            'parent_slug' => 'themes.php',

            // Menu default settings
            'icon_url' => 'dashicons-admin-generic',
            'position' => '61'

		);

		return apply_filters( 'optionsframework_menu', $menu );
	}

	/**
     * Add a subpage called "Theme Options" to the appearance menu.
     *
     * @since 1.7.0
     */
	function add_custom_options_page() {

		$menu = $this->menu_settings();

		switch ($menu['mode']) {

			case 'menu':
			// http://codex.wordpress.org/Function_Reference/add_menu_page
				$this->options_screen = add_menu_page(
					$menu['page_title'],
					$menu['menu_title'],
					$menu['capability'],
					$menu['menu_slug'],
					array($this, 'options_page'),
					$menu['icon_url'],
					$menu['position']
				);
				break;
			default:
			// http://codex.wordpress.org/Function_Reference/add_submenu_page
				$this->options_screen = add_submenu_page(
					$menu['parent_slug'],
					$menu['page_title'],
					$menu['menu_title'],
					$menu['capability'],
					$menu['menu_slug'],
					array($this, 'options_page'));
				break;   
		}

	}

	/**
     * Loads the required stylesheets
     *
     * @since 1.7.0
     */

	function enqueue_admin_styles( $hook ) {

		if ( $this->options_screen != $hook )
	        return;

		wp_enqueue_style( 'optionsframework', OPTIONS_FRAMEWORK_DIRECTORY . 'css/optionsframework.css', array(),  Options_Framework::VERSION );
		wp_enqueue_style( 'wp-color-picker' );
		// 引入主题主样式文件，包含.copy-banner的样式
		wp_enqueue_style( 'boxmoe-style', get_template_directory_uri() . '/assets/css/style.css', array(),  Options_Framework::VERSION );
	}

	/**
     * Loads the required javascript
     *
     * @since 1.7.0
     */
	function enqueue_admin_scripts( $hook ) {

		if ( $this->options_screen != $hook )
	        return;

		// Enqueue custom option panel JS
		wp_enqueue_script( 'options-custom', OPTIONS_FRAMEWORK_DIRECTORY . 'js/options-custom.js', array( 'jquery','wp-color-picker' ), Options_Framework::VERSION );

		// Inline scripts from options-interface.php
		add_action( 'admin_head', array( $this, 'of_admin_head' ) );
	}

	function of_admin_head() {
		// Hook to add custom scripts
		do_action( 'optionsframework_custom_scripts' );
	}

	/**
     * Builds out the options panel.
     *
	 * If we were using the Settings API as it was intended we would use
	 * do_settings_sections here.  But as we don't want the settings wrapped in a table,
	 * we'll call our own custom optionsframework_fields.  See options-interface.php
	 * for specifics on how each individual field is generated.
	 *
	 * Nonces are provided using the settings_fields()
	 *
     * @since 1.7.0
     */
	 function options_page() { ?><?php $menu = $this->menu_settings(); ?>
	  
	  <div id="optionsframework-wrap" class="wrap">
	  <?php settings_errors( 'options-framework' ); ?> 
		<div class="set-main-plane">
			<div class="set-main-menu">
			<div class="boxmoe-options-site-name">
			<span class="dashicons dashicons-nametag"></span>
			盒子萌主题
			<p> - 纸鸢版🎉</p>
			<svg width="24" height="24" viewBox="0 0 24 24">
        <path d="M11.5,22C11.64,22 11.77,22 11.9,21.96C12.55,21.82 13.09,21.38 13.34,20.78C13.44,20.54 13.5,20.27 13.5,20H9.5A2,2 0 0,0 11.5,22M18,10.5C18,7.43 15.86,4.86 13,4.18V3.5A1.5,1.5 0 0,0 11.5,2A1.5,1.5 0 0,0 10,3.5V4.18C7.13,4.86 5,7.43 5,10.5V16L3,18V19H20V18L18,16M19.97,10H21.97C21.82,6.79 20.24,3.97 17.85,2.15L16.42,3.58C18.46,5 19.82,7.35 19.97,10M6.58,3.58L5.15,2.15C2.76,3.97 1.18,6.79 1,10H3C3.18,7.35 4.54,5 6.58,3.58Z"></path>
      </svg>
    </div>
				<div class="nav-tab-wrapper">
				<?php echo Options_Framework_Interface::optionsframework_tabs(); ?>
				</div>
			</div>
	    
			<div id="optionsframework-metabox" class="metabox-holder">
		<div class="header-set-title">
		<h2 class="themes-name "><i class="navon"></i><?php echo esc_html( $menu['page_title'] ); ?></h2>
		<div class="of-search"><input type="search" id="of-search-input" placeholder="搜索设置名称" /></div>
		<div class="el-button" style="padding: 8px 16px; line-height: 1.5; display: inline-block; text-align: center;">
			<a href="https://www.boxmoe.com/706.html" target="_blank" rel="external nofollow" style="color: inherit; text-decoration: none;">📃在线文档</a>  
			🚀V<?php echo THEME_VERSION; ?>  
			🎉更新日期：2025-12-16<br>
			🥰本主题二次创作 <a href="https://gl.baimu.live/864" target="_blank" rel="external nofollow" style="color: inherit; text-decoration: underline;">白木</a>
		</div>
		</div>			
				<div id="optionsframework" class="postbox">
					<form action="options.php" method="post">
					<?php settings_fields( 'optionsframework' ); ?>
					<?php Options_Framework_Interface::optionsframework_fields(); /* Settings */ ?>
				</div>
				<?php do_action( 'optionsframework_after' ); ?>

			</div> <!-- / .wrap -->
		</div>		
		<div id="optionsframework-submit">
			<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( '保存设置', 'textdomain' ); ?>" />
			<input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( '重置所有设置', 'textdomain' ); ?>" onclick="return confirm( '<?php print esc_js( __( '警告：点击确定，之前所有设置修改都将丢失！', 'textdomain' ) ); ?>' );" />
			<div class="clear"></div>
		</div>
		<div id="of-slogan-modal-mask" class="of-modal-mask" style="display:none">
			<div id="of-slogan-modal" class="of-modal">
				<div class="of-modal-header"><?php esc_html_e('重置页面标语','textdomain'); ?></div>
				<div class="of-modal-body"><?php esc_html_e('仅重置“页面标语设置”，其他设置不受影响。是否继续？','textdomain'); ?></div>
				<div class="of-modal-actions">
					<button type="button" id="of-slogan-cancel" class="of-btn of-btn-secondary"><?php esc_html_e('取消','textdomain'); ?></button>
					<button type="button" id="of-slogan-confirm" class="of-btn of-btn-primary"><?php esc_html_e('确定','textdomain'); ?></button>
				</div>
			</div>
		</div>
					</form>
</div> 
<style id="of-slogan-modal-style">
.of-modal-mask{position:fixed;inset:0;background:rgba(0,0,0,.25);display:none;align-items:center;justify-content:center;z-index:100000}
.of-modal{background:#fff;border-radius:12px;max-width:420px;width:90%;box-shadow:0 12px 24px rgba(0,0,0,.08);padding:20px;font-size:14px}
.of-modal-header{font-weight:600;margin-bottom:8px}
.of-modal-actions{margin-top:16px;display:flex;gap:8px;justify-content:flex-end}
.of-btn{border:none;border-radius:999px;padding:8px 14px;cursor:pointer}
.of-btn-primary{background:#2271b1;color:#fff}
.of-btn-secondary{background:#f0f0f1;color:#1d2327}
.boxmoe_tab_header{position:relative;min-height:32px;padding-right:96px}
#of-reset-slogan-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);height:28px;line-height:26px;padding:0 10px;font-size:12px;z-index:10}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var navon = document.querySelector('.navon');
  var setMainPlane = document.querySelector('.set-main-plane');

  if (navon && setMainPlane) {
    navon.addEventListener('click', function(event) {
      event.stopPropagation(); 
      setMainPlane.classList.toggle('on');
    });

	document.addEventListener('click', function(event) {
      if (event.target !== navon) {
        setMainPlane.classList.remove('on');
      }
    });
  }

  // 🎉 显示顶部横幅提示
  function showTopBanner(message, duration = 5000) {
    let banner = document.querySelector('.copy-banner');
    if (!banner) {
      banner = document.createElement('div');
      banner.className = 'copy-banner';
      document.body.appendChild(banner);
    }
    banner.innerHTML = '<i class="fa fa-check-circle"></i> ' + message;
    let timer = null;
    const show = function() {
      if (timer) { try { clearTimeout(timer); } catch(_) {} }
      banner.classList.remove('mask-run');
      void banner.offsetWidth;
      banner.classList.add('mask-run');
      banner.classList.add('show');
      timer = setTimeout(function() {
        banner.classList.remove('show');
        banner.classList.remove('mask-run');
      }, duration);
    };
    show();
  }

  // 📡 检测URL参数并显示相应提示
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('settings-updated')) {
    // 直接显示默认的保存成功提示
    showTopBanner('设置已保存成功！', 5000);
  }
});

// 📡 获取并更新最新版本信息
var boxmoe_version = function () {
    fetch("https://doc.boxmoe.com/wp-json/themes/v1/version/lolimeow")
        .then(response => response.json())
        .then(data => {
            var vboxElement = document.getElementById("vbox");
            if (vboxElement) {
                vboxElement.innerHTML = data.data.version;
            }
        })
        .catch(error => {
            console.error('Error fetching version:', error);
        });
};
boxmoe_version();

var ofResetBtn=document.getElementById('of-reset-slogan-btn');
var mask=document.getElementById('of-slogan-modal-mask');
var confirmBtn=document.getElementById('of-slogan-confirm');
var cancelBtn=document.getElementById('of-slogan-cancel');
if(ofResetBtn&&mask&&confirmBtn&&cancelBtn){
  ofResetBtn.addEventListener('click',function(){mask.style.display='flex'});
  cancelBtn.addEventListener('click',function(){mask.style.display='none'});
  confirmBtn.addEventListener('click',function(){
    var form=document.querySelector('#optionsframework form');
    if(form){
      var hidden=document.createElement('input');
      hidden.type='hidden';
      hidden.name='reset_slogan';
      hidden.value='1';
      form.appendChild(hidden);
      form.submit();
    }
    mask.style.display='none';
  });
}
</script>
	<?php
	}

	/**
	 * Validate Options.
	 *
	 * This runs after the submit/reset button has been clicked and
	 * validates the inputs.
	 *
	 * @uses $_POST['reset'] to restore default options
	 */
	function validate_options( $input ) {

		// 🥳 增加资源限制，防止保存时出现 502 错误
		@set_time_limit( 300 );
		@ini_set( 'memory_limit', '512M' );

		/*
		 * Restore Defaults.
		 *
		 * In the event that the user clicked the "Restore Defaults"
		 * button, the options defined in the theme's options.php
		 * file will be added to the option for the active theme.
		 */

		if ( isset( $_POST['reset'] ) ) {
			// 不使用默认的WordPress提示框，改为使用自定义的顶部横幅提示
			// add_settings_error( 'options-framework', 'restore_defaults', __( '已恢复默认选项!', 'textdomain' ), 'updated fade' );
			// 提示信息将通过JavaScript在前端显示
			return $this->get_default_values();
		}

		/*
		 * Reset only Page Slogan options
		 */
		if ( isset( $_POST['reset_slogan'] ) ) {
			$options_framework = new Options_Framework;
			$name = $options_framework->get_option_name();
			$current = get_option( $name );
			$defaults = $this->get_default_values();
			$slogan_keys = array(
				'boxmoe_slogan_home_text',
				'boxmoe_slogan_category_text',
				'boxmoe_slogan_tag_text',
				'boxmoe_slogan_search_text',
				'boxmoe_slogan_404_text',
				'boxmoe_slogan_author_text',
				'boxmoe_slogan_date_text',
				'boxmoe_slogan_archive_text',
				'boxmoe_slogan_post_text',
				'boxmoe_slogan_page_text',
				'boxmoe_slogan_page_links_text',
				'boxmoe_slogan_page_user_center_text',
			);
			foreach ( $slogan_keys as $key ) {
				if ( isset( $defaults[$key] ) ) {
					$current[$key] = $defaults[$key];
				}
			}
			// 不使用默认的WordPress提示框，改为使用自定义的顶部横幅提示
			// add_settings_error( 'options-framework', 'restore_slogan_defaults', __( '页面标语已恢复默认值！', 'textdomain' ), 'updated fade' );
			// 提示信息将通过JavaScript在前端显示
			return $current;
		}

		/*
		 * 设置默认值防止未定义键警告
		 */
		$defaults = $this->get_default_values();
		$input = wp_parse_args( $input, $defaults );

		$clean = array();
		$options = & Options_Framework::_optionsframework_options();
		foreach ( $options as $option ) {

			if ( ! isset( $option['id'] ) ) {
				continue;
			}

			if ( ! isset( $option['type'] ) ) {
				continue;
			}

			$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
				$input[$id] = false;
			}

			// Set each item in the multicheck to false if it wasn't sent in the $_POST
			if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
				foreach ( $option['options'] as $key => $value ) {
					$input[$id][$key] = false;
				}
			}

			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
				$clean[$id] = apply_filters( 'of_sanitize_' . $option['type'], $input[$id], $option );
			}
		}

		// Hook to run after validation
		do_action( 'optionsframework_after_validate', $clean );

		// WordPress会自动添加settings-updated参数，我们不需要手动添加
		// 我们可以直接使用JavaScript检测这个参数来显示提示
		// 对于不同的操作，我们可以通过检查POST数据来确定显示什么提示
		// 但是由于WordPress的设置API会自动重定向，我们无法直接在重定向后获取POST数据
		// 所以我们直接在JavaScript中处理不同的操作类型
		// 不需要使用transient API来存储操作类型

		// 修改WordPress的重定向URL，添加操作类型参数
		if ( isset( $_POST['reset'] ) ) {
			// 恢复默认设置
			add_filter( 'redirect_post_location', function( $location ) {
				return add_query_arg( array( 'reset' => 'true' ), $location );
			} );
		} elseif ( isset( $_POST['reset_slogan'] ) ) {
			// 重置页面标语
			add_filter( 'redirect_post_location', function( $location ) {
				return add_query_arg( array( 'reset_slogan' => 'true' ), $location );
			} );
		}

		return $clean;
	}

	/**
	 * Display message when options have been saved
	 */

	function save_options_notice() {
		// 不使用默认的WordPress提示框，改为使用自定义的顶部横幅提示
		// add_settings_error( 'options-framework', 'save_options', __( '设置已保存成功！', 'textdomain' ), 'updated fade' );
		// 提示信息将通过JavaScript在前端显示
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */
	function get_default_values() {
		$output = array();
		$config = & Options_Framework::_optionsframework_options();
		foreach ( (array) $config as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
			if ( ! isset( $option['std'] ) ) {
				continue;
			}
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
			if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
				$output[$option['id']] = apply_filters( 'of_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
		return $output;
	}

	/**
	 * Add options menu item to admin bar
	 */

	function optionsframework_admin_bar() {

		$menu = $this->menu_settings();

		global $wp_admin_bar;

		if ( 'menu' == $menu['mode'] ) {
			$href = admin_url( 'admin.php?page=' . $menu['menu_slug'] );
		} else {
			$href = admin_url( 'themes.php?page=' . $menu['menu_slug'] );
		}

		$args = array(
			'parent' => 'appearance',
			'id' => 'of_theme_options',
			'title' => $menu['menu_title'],
			'href' => $href
		);

		$wp_admin_bar->add_menu( apply_filters( 'optionsframework_admin_bar', $args ) );
	}

}
