<?php
/**
 * 🕊️白木 原创开发 🔗gl.baimu.live
 * 🌐 51LA API 封装类
 * 🎨 拟态拟物玻璃质感设计
 *
 * @package Lolimeow_Shiroki
 * @subpackage 51LA_Stats
 * @since 1.0.0
 */

// ◀️ 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 🌐 51LA API 封装类
 */
class Shiroki_51LA_API {

    /**
     * 🎯 API 基础地址
     */
    private $api_base = 'https://v6.51.la';

    /**
     * 📝 选项名称
     */
    private $option_name = 'shiroki_51la_config';

    /**
     * 🎯 单例实例
     */
    private static $instance = null;

    /**
     * 📝 配置缓存
     */
    private $config = null;

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
        $this->config = get_option($this->option_name, array());
        $this->init_hooks();
    }

    /**
     * 🔗 初始化钩子
     */
    private function init_hooks() {
        // 💾 处理配置保存
        add_action('admin_init', array($this, 'handle_config_save'));
        // 📡 处理AJAX请求
        add_action('wp_ajax_shiroki_51la_get_data', array($this, 'ajax_get_data'));
        add_action('wp_ajax_shiroki_51la_save_config', array($this, 'ajax_save_config'));
    }

    /**
     * 💾 获取配置
     */
    public function get_config() {
        if ($this->config === null) {
            $this->config = get_option($this->option_name, array());
        }
        return $this->config;
    }

    /**
     * 💾 保存配置
     */
    public function save_config($config) {
        $defaults = array(
            'access_key' => '',
            'secret_key' => '',
            'security_type' => '2', // 默认中等安全性
            'site_id' => '',
            'enabled' => false
        );

        $config = wp_parse_args($config, $defaults);
        $config['enabled'] = !empty($config['access_key']) && !empty($config['secret_key']);

        $this->config = $config;
        update_option($this->option_name, $config);

        return $config;
    }

    /**
     * 💾 处理配置保存（表单提交）
     */
    public function handle_config_save() {
        if (!isset($_POST['shiroki_51la_save_config'])) {
            return;
        }

        if (!check_admin_referer('shiroki_51la_config_nonce', 'shiroki_51la_config_nonce')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die('权限不足');
        }

        $config = array(
            'access_key' => isset($_POST['shiroki_51la_access_key']) ? sanitize_text_field($_POST['shiroki_51la_access_key']) : '',
            'secret_key' => isset($_POST['shiroki_51la_secret_key']) ? sanitize_text_field($_POST['shiroki_51la_secret_key']) : '',
            'security_type' => isset($_POST['shiroki_51la_security_type']) ? sanitize_text_field($_POST['shiroki_51la_security_type']) : '2',
            'site_id' => isset($_POST['shiroki_51la_site_id']) ? sanitize_text_field($_POST['shiroki_51la_site_id']) : ''
        );

        $this->save_config($config);

        // 🔄 刷新页面
        wp_redirect(add_query_arg(array(
            'page' => 'shiroki-51la-stats',
            'saved' => 'true'
        ), admin_url('admin.php')));
        exit;
    }

    /**
     * 📡 AJAX 保存配置
     */
    public function ajax_save_config() {
        check_ajax_referer('shiroki_51la_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $config = array(
            'access_key' => isset($_POST['access_key']) ? sanitize_text_field($_POST['access_key']) : '',
            'secret_key' => isset($_POST['secret_key']) ? sanitize_text_field($_POST['secret_key']) : '',
            'security_type' => isset($_POST['security_type']) ? sanitize_text_field($_POST['security_type']) : '2',
            'site_id' => isset($_POST['site_id']) ? sanitize_text_field($_POST['site_id']) : ''
        );

        $this->save_config($config);

        wp_send_json_success(array('message' => '配置保存成功'));
    }

    /**
     * 📡 AJAX 获取数据
     */
    public function ajax_get_data() {
        check_ajax_referer('shiroki_51la_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '权限不足'));
        }

        $endpoint = isset($_POST['endpoint']) ? sanitize_text_field($_POST['endpoint']) : '';
        $params = isset($_POST['params']) ? $_POST['params'] : array();

        $result = $this->request($endpoint, $params);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    /**
     * 🔐 生成签名
     */
    private function generate_sign($access_key, $secret_key, $nonce, $timestamp) {
        $string = "accessKey={$access_key}&nonce={$nonce}&secretKey={$secret_key}&timestamp={$timestamp}";
        return strtoupper(hash('sha256', $string));
    }

    /**
     * 📡 发送API请求
     */
    public function request($endpoint, $params = array()) {
        $config = $this->get_config();

        if (empty($config['access_key'])) {
            return new WP_Error('no_config', '请先配置51LA API密钥');
        }

        // 📝 准备请求参数
        $access_key = $config['access_key'];
        $secret_key = $config['secret_key'];
        $security_type = $config['security_type'];
        $nonce = wp_rand(1000, 9999);
        $timestamp = strval(round(microtime(true) * 1000));

        // 🔐 生成签名
        if ($security_type === '1') {
            // 低安全性：sign = accessKey
            $sign = $access_key;
        } else {
            // 中/高安全性：SHA256签名
            $sign = $this->generate_sign($access_key, $secret_key, $nonce, $timestamp);
        }

        // 📝 构建请求体
        $body = array_merge($params, array(
            'accessKey' => $access_key,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'sign' => $sign
        ));

        // 📡 发送请求
        $response = wp_remote_post($this->api_base . $endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'JSON解析失败');
        }

        if (empty($data['success'])) {
            $message = isset($data['message']) ? $data['message'] : '请求失败';
            return new WP_Error('api_error', $message);
        }

        // 🔓 高安全性：解密数据
        if ($security_type === '3' && !empty($data['data'])) {
            $data['data'] = $this->decrypt_data($data['data'], $secret_key);
        }

        return $data;
    }

    /**
     * 🔓 解密数据（高安全性模式）
     */
    private function decrypt_data($encrypted_data, $secret_key) {
        $key = $secret_key;
        $iv = substr($secret_key, 0, 16);

        $decrypted = openssl_decrypt(
            hex2bin($encrypted_data),
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return json_decode($decrypted, true);
    }

    /**
     * 📊 获取站点列表
     */
    public function get_site_list() {
        return $this->request('/v6/sitegroup/list');
    }

    /**
     * 📊 获取概览数据
     */
    public function get_overview($site_id = '') {
        $params = array();
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/overview', $params);
    }

    /**
     * 📊 获取今日数据
     */
    public function get_today_data($site_id = '') {
        $params = array();
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/today', $params);
    }

    /**
     * 📊 获取趋势数据
     */
    public function get_trend($start_date, $end_date, $site_id = '') {
        $params = array(
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/trend', $params);
    }

    /**
     * 📊 获取来源分析
     */
    public function get_source($start_date, $end_date, $site_id = '') {
        $params = array(
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/source', $params);
    }

    /**
     * 📊 获取受访页面
     */
    public function get_pages($start_date, $end_date, $site_id = '') {
        $params = array(
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/page', $params);
    }

    /**
     * 📊 获取地域分布
     */
    public function get_geo($start_date, $end_date, $site_id = '') {
        $params = array(
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/geo', $params);
    }

    /**
     * 📊 获取设备信息
     */
    public function get_device($start_date, $end_date, $site_id = '') {
        $params = array(
            'startDate' => $start_date,
            'endDate' => $end_date
        );
        if (!empty($site_id)) {
            $params['siteId'] = $site_id;
        }
        return $this->request('/v6/report/device', $params);
    }

    /**
     * ✅ 检查是否已配置
     */
    public function is_configured() {
        $config = $this->get_config();
        return !empty($config['access_key']) && !empty($config['secret_key']);
    }
}

/**
 * 🚀 初始化51LA API
 */
function shiroki_init_51la_api() {
    Shiroki_51LA_API::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_51la_api');
