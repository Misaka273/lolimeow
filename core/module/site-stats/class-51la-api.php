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
    private $api_base = 'https://v6-open.51.la';

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
            'mask_id' => '',
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
            'mask_id' => isset($_POST['shiroki_51la_mask_id']) ? sanitize_text_field($_POST['shiroki_51la_mask_id']) : ''
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
            'mask_id' => isset($_POST['mask_id']) ? sanitize_text_field($_POST['mask_id']) : ''
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
     * 🔤 生成随机nonce字符串
     */
    private function generate_nonce($length = 4) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';
        $max_index = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $nonce .= $characters[wp_rand(0, $max_index)];
        }
        return $nonce;
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
        // 🔤 nonce 是4位随机字符串（字母+数字），不是纯数字
        $nonce = $this->generate_nonce(4);
        $timestamp = strval(round(microtime(true) * 1000));

        // 🔐 生成签名
        if ($security_type == '1') {
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

        // 📡 发送POST请求（51LA API使用POST + JSON body）
        $url = $this->api_base . $endpoint;
        $json_body = json_encode($body);

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'body' => $json_body,
            'timeout' => 30,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        // 🔍 检查HTTP状态码
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            $body = wp_remote_retrieve_body($response);
            error_log('51LA API HTTP错误: ' . $response_code . ', 响应: ' . substr($body, 0, 500));
            // 尝试从响应体中提取具体错误信息
            $api_msg = '';
            if (!empty($body)) {
                $err_data = json_decode($body, true);
                if ($err_data && isset($err_data['message'])) {
                    $api_msg = ' — ' . $err_data['message'];
                }
            }
            return new WP_Error('http_error', 'API请求失败，HTTP状态码: ' . $response_code . $api_msg);
        }

        $body = wp_remote_retrieve_body($response);

        // 🔍 检查响应是否为空
        if (empty($body)) {
            return new WP_Error('empty_response', 'API返回空响应，请检查网络连接或API配置');
        }

        // 🔍 检查是否是HTML响应（API未正确配置）
        if (is_string($body) && strpos($body, '<!DOCTYPE') !== false) {
            return new WP_Error('html_response', 'API返回HTML页面而非JSON数据，请检查：1. API密钥是否正确 2. 是否已开通API权限 3. 接口地址是否正确');
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // 🔍 记录原始响应用于调试
            error_log('51LA API JSON解析错误: ' . json_last_error_msg());
            error_log('51LA API 原始响应: ' . substr($body, 0, 500));
            return new WP_Error('json_error', 'JSON解析失败: ' . json_last_error_msg() . '，原始响应: ' . substr($body, 0, 200));
        }

        // 🔍 检查业务响应
        if (empty($data['success'])) {
            // 🔍 记录失败响应用于调试
            error_log('51LA API 业务错误: ' . json_encode($data));
            $message = isset($data['message']) ? $data['message'] : '请求失败';
            return new WP_Error('api_error', $message . ' (code: ' . (isset($data['code']) ? $data['code'] : 'unknown') . ')');
        }

        // 🔓 高安全性：解密数据
        if ($security_type == '3') {
            if (!empty($data['data']) && is_string($data['data'])) {
                $data['data'] = $this->decrypt_data($data['data'], $secret_key);
            }
            if (!empty($data['bean']) && is_string($data['bean'])) {
                $data['bean'] = $this->decrypt_data($data['bean'], $secret_key);
            }
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
     * 接口：/open/site/list
     */
    public function get_site_list() {
        return $this->request('/open/site/list');
    }

    /**
     * 📊 获取概览数据
     * 接口：/open/overview/get
     */
    public function get_overview($mask_id = '') {
        $params = array();
        if (!empty($mask_id)) {
            $params['maskId'] = $mask_id;
        }
        return $this->request('/open/overview/get', $params);
    }

    /**
     * 📊 获取趋势数据（按小时）
     * 接口：/open/trend/hour
     * @param string $day 日期，格式 YYYY-MM-dd
     * @param string $mask_id 掩码ID
     */
    public function get_trend($day, $mask_id = '') {
        $params = array(
            'day' => $day
        );
        if (!empty($mask_id)) {
            $params['maskId'] = $mask_id;
        }
        return $this->request('/open/trend/hour', $params);
    }

    /**
     * 📊 获取实时数据
     * 接口：/open/online/data
     * @param string $type 查询类型 (ACTIVE_USER|TERMINAL|SRC|INTERVIEW|ENTRY|BROWSER|REGION)
     * @param int $minute 分钟数 (5|15|30)
     * @param string $mask_id 掩码ID
     */
    public function get_realtime($type = 'ACTIVE_USER', $minute = 15, $mask_id = '') {
        $params = array(
            'type' => $type,
            'minute' => $minute
        );
        if (!empty($mask_id)) {
            $params['maskId'] = $mask_id;
        }
        return $this->request('/open/online/data', $params);
    }

    /**
     * 📊 获取受访页面数据
     * 接口：/open/content/listInterview
     * @param string $start_day 起始日期 YYYY-MM-dd
     * @param string $end_day 结束日期 YYYY-MM-dd
     * @param string $mask_id 掩码ID
     * @param int $page 页码
     * @param int $size 每页条数
     */
    public function get_visited_pages($start_day, $end_day, $mask_id = '', $page = 1, $size = 15) {
        $params = array(
            'startDay' => $start_day,
            'endDay' => $end_day,
            'page' => $page,
            'size' => $size
        );
        if (!empty($mask_id)) {
            $params['maskId'] = $mask_id;
        }
        return $this->request('/open/content/listInterview', $params);
    }

    /**
     * 📊 获取访问明细数据
     * 接口：/open/visitor/detail/list
     * @param string $day 日期 YYYY-MM-dd
     * @param string $mask_id 掩码ID
     * @param int $page 页码
     * @param int $size 每页条数（只能为50或100）
     */
    public function get_visitor_detail($day, $mask_id = '', $page = 1, $size = 50) {
        $params = array(
            'day' => $day,
            'page' => $page,
            'size' => $size
        );
        if (!empty($mask_id)) {
            $params['maskId'] = $mask_id;
        }
        return $this->request('/open/visitor/detail/list', $params);
    }

    /**
     * ✅ 检查是否已配置
     */
    public function is_configured() {
        $config = $this->get_config();
        return !empty($config['access_key']) && !empty($config['secret_key']) && !empty($config['mask_id']);
    }
}

/**
 * 🚀 初始化51LA API
 */
function shiroki_init_51la_api() {
    Shiroki_51LA_API::get_instance();
}
add_action('after_setup_theme', 'shiroki_init_51la_api');
