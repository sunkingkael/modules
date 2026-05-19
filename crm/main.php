<?php
/**
 * Module Name: Customer Relationship Management
 * Module Slug: crm
 * Description: Manage, track, and analyze current and potential customers — including contact profiles, interaction history, pipeline stages, tasks, and reporting — all from within the WordPress admin.
 * Version: 1.0.0
 * Author: BNTM
 * Icon: M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z
 */

if (!defined('ABSPATH')) exit;

define('BNTM_CRM_PATH', dirname(__FILE__) . '/');
define('BNTM_CRM_URL', plugin_dir_url(__FILE__));

// =============================================================================
// MODULE CONFIGURATION
// =============================================================================

function bntm_crm_get_pages() {
    return [
        'CRM Contact Form'    => '[bntm_crm_contact_form]',
        'CRM Customer Portal' => '[bntm_crm_customer_portal]',
        'CRM Deal View'       => '[bntm_crm_deal_view]',
        'CRM Unsubscribe'     => '[bntm_crm_unsubscribe]',
    ];
}

function bntm_crm_get_tables() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $prefix  = $wpdb->prefix;

    return [
        'bntm_crm_contacts' => "CREATE TABLE {$prefix}bntm_crm_contacts (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type ENUM('person','organisation') DEFAULT 'person',
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            email VARCHAR(191),
            phone VARCHAR(50),
            mobile VARCHAR(50),
            company VARCHAR(191),
            job_title VARCHAR(100),
            address_line_1 VARCHAR(191),
            address_line_2 VARCHAR(191),
            city VARCHAR(100),
            state VARCHAR(100),
            postcode VARCHAR(20),
            country VARCHAR(100),
            website VARCHAR(191),
            source VARCHAR(100),
            status ENUM('lead','active','churned','archived') DEFAULT 'lead',
            assigned_user_id BIGINT(20) UNSIGNED,
            email_opt_out TINYINT(1) DEFAULT 0,
            unsubscribe_token VARCHAR(64),
            wp_user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_email (email),
            INDEX idx_status (status),
            INDEX idx_assigned (assigned_user_id),
            INDEX idx_wp_user (wp_user_id)
        ) {$charset};",

        'bntm_crm_contact_meta' => "CREATE TABLE {$prefix}bntm_crm_contact_meta (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id BIGINT(20) UNSIGNED NOT NULL,
            meta_key VARCHAR(191) NOT NULL,
            meta_value LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_meta_key (meta_key)
        ) {$charset};",

        'bntm_crm_tags' => "CREATE TABLE {$prefix}bntm_crm_tags (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            colour VARCHAR(7) DEFAULT '#6c757d',
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_name (name)
        ) {$charset};",

        'bntm_crm_contact_tags' => "CREATE TABLE {$prefix}bntm_crm_contact_tags (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id BIGINT(20) UNSIGNED NOT NULL,
            tag_id BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_contact_tag (contact_id, tag_id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_tag_id (tag_id)
        ) {$charset};",

        'bntm_crm_pipelines' => "CREATE TABLE {$prefix}bntm_crm_pipelines (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(191) NOT NULL,
            description TEXT,
            is_default TINYINT(1) DEFAULT 0,
            sort_order INT(11) DEFAULT 0,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset};",

        'bntm_crm_pipeline_stages' => "CREATE TABLE {$prefix}bntm_crm_pipeline_stages (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            pipeline_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(191) NOT NULL,
            colour VARCHAR(7) DEFAULT '#0d6efd',
            sort_order INT(11) DEFAULT 0,
            probability TINYINT(3) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_pipeline_id (pipeline_id)
        ) {$charset};",

        'bntm_crm_deals' => "CREATE TABLE {$prefix}bntm_crm_deals (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(191) NOT NULL,
            contact_id BIGINT(20) UNSIGNED NOT NULL,
            pipeline_id BIGINT(20) UNSIGNED NOT NULL,
            stage_id BIGINT(20) UNSIGNED NOT NULL,
            value DECIMAL(15,2) DEFAULT 0.00,
            currency VARCHAR(10) DEFAULT 'USD',
            probability TINYINT(3) DEFAULT 0,
            source VARCHAR(100),
            status ENUM('open','won','lost','archived') DEFAULT 'open',
            close_date DATE DEFAULT NULL,
            lost_reason VARCHAR(191) DEFAULT NULL,
            access_token VARCHAR(64) DEFAULT NULL,
            token_expires_at DATETIME DEFAULT NULL,
            assigned_user_id BIGINT(20) UNSIGNED,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_stage_id (stage_id),
            INDEX idx_pipeline_id (pipeline_id),
            INDEX idx_status (status),
            INDEX idx_token (access_token)
        ) {$charset};",

        'bntm_crm_deal_line_items' => "CREATE TABLE {$prefix}bntm_crm_deal_line_items (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            deal_id BIGINT(20) UNSIGNED NOT NULL,
            description VARCHAR(191) NOT NULL,
            quantity DECIMAL(10,2) DEFAULT 1.00,
            unit_price DECIMAL(15,2) DEFAULT 0.00,
            sort_order INT(11) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_deal_id (deal_id)
        ) {$charset};",

        'bntm_crm_activities' => "CREATE TABLE {$prefix}bntm_crm_activities (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            type ENUM('call','email','meeting','note','task_completion','callback_request','deal_view','stage_change','unsubscribe') NOT NULL,
            contact_id BIGINT(20) UNSIGNED DEFAULT NULL,
            deal_id BIGINT(20) UNSIGNED DEFAULT NULL,
            subject VARCHAR(191),
            body LONGTEXT,
            outcome VARCHAR(191),
            duration_minutes INT(11) DEFAULT NULL,
            scheduled_at DATETIME DEFAULT NULL,
            logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            visible_to_customer TINYINT(1) DEFAULT 0,
            logged_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_deal_id (deal_id),
            INDEX idx_type (type),
            INDEX idx_logged_at (logged_at)
        ) {$charset};",

        'bntm_crm_tasks' => "CREATE TABLE {$prefix}bntm_crm_tasks (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(191) NOT NULL,
            description TEXT,
            contact_id BIGINT(20) UNSIGNED DEFAULT NULL,
            deal_id BIGINT(20) UNSIGNED DEFAULT NULL,
            assigned_user_id BIGINT(20) UNSIGNED,
            priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
            status ENUM('open','in_progress','done') DEFAULT 'open',
            due_date DATETIME DEFAULT NULL,
            completed_at DATETIME DEFAULT NULL,
            completed_by BIGINT(20) UNSIGNED DEFAULT NULL,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_deal_id (deal_id),
            INDEX idx_assigned (assigned_user_id),
            INDEX idx_status (status),
            INDEX idx_due_date (due_date)
        ) {$charset};",

        'bntm_crm_files' => "CREATE TABLE {$prefix}bntm_crm_files (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id BIGINT(20) UNSIGNED DEFAULT NULL,
            deal_id BIGINT(20) UNSIGNED DEFAULT NULL,
            file_name VARCHAR(191) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_type VARCHAR(100),
            file_size BIGINT(20) DEFAULT 0,
            uploaded_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_contact_id (contact_id),
            INDEX idx_deal_id (deal_id)
        ) {$charset};",

        'bntm_crm_custom_fields' => "CREATE TABLE {$prefix}bntm_crm_custom_fields (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type ENUM('contact','deal','activity') NOT NULL,
            field_key VARCHAR(100) NOT NULL,
            field_label VARCHAR(191) NOT NULL,
            field_type ENUM('text','textarea','number','select','checkbox','date','url','email') NOT NULL,
            field_options LONGTEXT DEFAULT NULL,
            is_required TINYINT(1) DEFAULT 0,
            sort_order INT(11) DEFAULT 0,
            created_by BIGINT(20) UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_object_key (object_type, field_key),
            INDEX idx_object_type (object_type)
        ) {$charset};",

        'bntm_crm_audit_log' => "CREATE TABLE {$prefix}bntm_crm_audit_log (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            object_type VARCHAR(100) NOT NULL,
            object_id BIGINT(20) UNSIGNED NOT NULL,
            action ENUM('created','updated','deleted','imported','exported','merged','stage_changed','status_changed') NOT NULL,
            changed_by BIGINT(20) UNSIGNED,
            changed_fields LONGTEXT DEFAULT NULL,
            old_values LONGTEXT DEFAULT NULL,
            new_values LONGTEXT DEFAULT NULL,
            ip_address VARCHAR(45),
            user_agent VARCHAR(500),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_object (object_type, object_id),
            INDEX idx_changed_by (changed_by),
            INDEX idx_created_at (created_at)
        ) {$charset};",

        'bntm_crm_settings' => "CREATE TABLE {$prefix}bntm_crm_settings (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(191) NOT NULL,
            setting_value LONGTEXT,
            autoload TINYINT(1) DEFAULT 1,
            updated_by BIGINT(20) UNSIGNED,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_setting_key (setting_key)
        ) {$charset};",
    ];
}

function bntm_crm_get_shortcodes() {
    return [
        'bntm_crm_contact_form'    => 'bntm_shortcode_crm_contact_form',
        'bntm_crm_customer_portal' => 'bntm_shortcode_crm_customer_portal',
        'bntm_crm_deal_view'       => 'bntm_shortcode_crm_deal_view',
        'bntm_crm_unsubscribe'     => 'bntm_shortcode_crm_unsubscribe',
    ];
}

function bntm_crm_create_tables() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $tables = bntm_crm_get_tables();
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
    return count($tables);
}

// =============================================================================
// AJAX ACTION HOOKS
// =============================================================================

// --- Dashboard ---
add_action('wp_ajax_bntm_crm_get_summary_cards',   'bntm_ajax_crm_get_summary_cards');
add_action('wp_ajax_bntm_crm_get_activity_feed',   'bntm_ajax_crm_get_activity_feed');
add_action('wp_ajax_bntm_crm_get_pipeline_chart',  'bntm_ajax_crm_get_pipeline_chart');
add_action('wp_ajax_bntm_crm_get_upcoming_tasks',  'bntm_ajax_crm_get_upcoming_tasks');
add_action('wp_ajax_bntm_crm_get_bar_graph_data',  'bntm_ajax_crm_get_bar_graph_data');

// --- Contacts ---
add_action('wp_ajax_bntm_crm_get_contacts',          'bntm_ajax_crm_get_contacts');
add_action('wp_ajax_bntm_crm_save_contact',          'bntm_ajax_crm_save_contact');
add_action('wp_ajax_bntm_crm_delete_contact',        'bntm_ajax_crm_delete_contact');
add_action('wp_ajax_bntm_crm_bulk_action_contacts',  'bntm_ajax_crm_bulk_action_contacts');
add_action('wp_ajax_bntm_crm_import_contacts',       'bntm_ajax_crm_import_contacts');
add_action('wp_ajax_bntm_crm_export_contacts',       'bntm_ajax_crm_export_contacts');
add_action('wp_ajax_bntm_crm_get_contact_detail',    'bntm_ajax_crm_get_contact_detail');
add_action('wp_ajax_bntm_crm_save_tags',             'bntm_ajax_crm_save_tags');
add_action('wp_ajax_bntm_crm_get_contact_profile',   'bntm_ajax_crm_get_contact_profile');
add_action('wp_ajax_bntm_crm_get_contact_timeline',  'bntm_ajax_crm_get_contact_timeline');
add_action('wp_ajax_bntm_crm_get_contact_tasks',     'bntm_ajax_crm_get_contact_tasks');
add_action('wp_ajax_bntm_crm_get_contact_files',     'bntm_ajax_crm_get_contact_files');
add_action('wp_ajax_bntm_crm_upload_contact_file',   'bntm_ajax_crm_upload_contact_file');
add_action('wp_ajax_bntm_crm_delete_contact_file',   'bntm_ajax_crm_delete_contact_file');

// --- Pipeline ---
add_action('wp_ajax_bntm_crm_get_pipeline_board', 'bntm_ajax_crm_get_pipeline_board');
add_action('wp_ajax_bntm_crm_move_deal_stage',    'bntm_ajax_crm_move_deal_stage');
add_action('wp_ajax_bntm_crm_save_deal',          'bntm_ajax_crm_save_deal');
add_action('wp_ajax_bntm_crm_delete_deal',        'bntm_ajax_crm_delete_deal');
add_action('wp_ajax_bntm_crm_close_deal',         'bntm_ajax_crm_close_deal');
add_action('wp_ajax_bntm_crm_get_deal_detail',    'bntm_ajax_crm_get_deal_detail');
add_action('wp_ajax_bntm_crm_get_pipelines',      'bntm_ajax_crm_get_pipelines');
add_action('wp_ajax_bntm_crm_save_pipeline',      'bntm_ajax_crm_save_pipeline');

// --- Activities ---
add_action('wp_ajax_bntm_crm_get_activities',     'bntm_ajax_crm_get_activities');
add_action('wp_ajax_bntm_crm_save_activity',      'bntm_ajax_crm_save_activity');
add_action('wp_ajax_bntm_crm_delete_activity',    'bntm_ajax_crm_delete_activity');
add_action('wp_ajax_bntm_crm_get_activity_types', 'bntm_ajax_crm_get_activity_types');

// --- Tasks ---
add_action('wp_ajax_bntm_crm_get_tasks',          'bntm_ajax_crm_get_tasks');
add_action('wp_ajax_bntm_crm_save_task',          'bntm_ajax_crm_save_task');
add_action('wp_ajax_bntm_crm_delete_task',        'bntm_ajax_crm_delete_task');
add_action('wp_ajax_bntm_crm_complete_task',      'bntm_ajax_crm_complete_task');
add_action('wp_ajax_bntm_crm_bulk_action_tasks',  'bntm_ajax_crm_bulk_action_tasks');

// --- Settings ---
add_action('wp_ajax_bntm_crm_get_settings',         'bntm_ajax_crm_get_settings');
add_action('wp_ajax_bntm_crm_save_settings',         'bntm_ajax_crm_save_settings');
add_action('wp_ajax_bntm_crm_save_pipeline_config',  'bntm_ajax_crm_save_pipeline_config');
add_action('wp_ajax_bntm_crm_save_custom_fields',    'bntm_ajax_crm_save_custom_fields');
add_action('wp_ajax_bntm_crm_detect_duplicates',     'bntm_ajax_crm_detect_duplicates');
add_action('wp_ajax_bntm_crm_merge_contacts',        'bntm_ajax_crm_merge_contacts');
add_action('wp_ajax_bntm_crm_export_all_data',       'bntm_ajax_crm_export_all_data');
add_action('wp_ajax_bntm_crm_get_audit_log',         'bntm_ajax_crm_get_audit_log');

// --- Frontend / Public AJAX ---
add_action('wp_ajax_bntm_crm_submit_contact_form',        'bntm_ajax_crm_submit_contact_form');
add_action('wp_ajax_nopriv_bntm_crm_submit_contact_form', 'bntm_ajax_crm_submit_contact_form');

add_action('wp_ajax_bntm_crm_deal_action',        'bntm_ajax_crm_deal_action');
add_action('wp_ajax_nopriv_bntm_crm_deal_action', 'bntm_ajax_crm_deal_action');

add_action('wp_ajax_bntm_crm_unsubscribe_action',        'bntm_ajax_crm_unsubscribe_action');
add_action('wp_ajax_nopriv_bntm_crm_unsubscribe_action', 'bntm_ajax_crm_unsubscribe_action');

add_action('wp_ajax_bntm_crm_resubscribe_action',        'bntm_ajax_crm_resubscribe_action');
add_action('wp_ajax_nopriv_bntm_crm_resubscribe_action', 'bntm_ajax_crm_resubscribe_action');

add_action('wp_ajax_bntm_crm_portal_callback_request',        'bntm_ajax_crm_portal_callback_request');
add_action('wp_ajax_nopriv_bntm_crm_portal_callback_request', 'bntm_ajax_crm_portal_callback_request');

add_action('wp_ajax_bntm_crm_portal_update_details',        'bntm_ajax_crm_portal_update_details');
add_action('wp_ajax_nopriv_bntm_crm_portal_update_details', 'bntm_ajax_crm_portal_update_details');

add_action('wp_ajax_bntm_crm_log_deal_view',        'bntm_ajax_crm_log_deal_view');
add_action('wp_ajax_nopriv_bntm_crm_log_deal_view', 'bntm_ajax_crm_log_deal_view');

// =============================================================================
// MAIN DASHBOARD SHORTCODE
// =============================================================================

function bntm_shortcode_crm() {
    if (!is_user_logged_in()) {
        return '<div class="bntm-notice">Please log in.</div>';
    }

    $current_user = wp_get_current_user();
    $business_id  = $current_user->ID;
    $active_tab   = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

    ob_start();
    ?>
    <script>
    var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    var bntm_crm_nonce = '<?php echo wp_create_nonce('crm_nonce'); ?>';
    </script>

    <div class="bntm-crm-container">
        <div class="bntm-tabs">
            <a href="?tab=dashboard" class="bntm-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="?tab=contacts" class="bntm-tab <?php echo $active_tab === 'contacts' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Contacts
            </a>
            <a href="?tab=pipeline" class="bntm-tab <?php echo $active_tab === 'pipeline' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Pipeline
            </a>
            <a href="?tab=activities" class="bntm-tab <?php echo $active_tab === 'activities' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Activities
            </a>
            <a href="?tab=tasks" class="bntm-tab <?php echo $active_tab === 'tasks' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Tasks
            </a>
            <a href="?tab=settings" class="bntm-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
        </div>

        <div class="bntm-tab-content">
            <?php if ($active_tab === 'dashboard'): ?>
                <?php echo crm_dashboard_tab($business_id); ?>
            <?php elseif ($active_tab === 'contacts'): ?>
                <?php
                $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
                if ($contact_id > 0) {
                    echo crm_contact_profile_page($contact_id, $business_id);
                } else {
                    echo crm_contacts_tab($business_id);
                }
                ?>
            <?php elseif ($active_tab === 'pipeline'): ?>
                <?php echo crm_pipeline_tab($business_id); ?>
            <?php elseif ($active_tab === 'activities'): ?>
                <?php echo crm_activities_tab($business_id); ?>
            <?php elseif ($active_tab === 'tasks'): ?>
                <?php echo crm_tasks_tab($business_id); ?>
            <?php elseif ($active_tab === 'settings'): ?>
                <?php echo crm_settings_tab($business_id); ?>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .bntm-crm-container { width: 100%; }

    /* --- Form Elements --- */
    .bntm-crm-container input[type="text"],
    .bntm-crm-container input[type="email"],
    .bntm-crm-container input[type="number"],
    .bntm-crm-container input[type="date"],
    .bntm-crm-container input[type="datetime-local"],
    .bntm-crm-container input[type="url"],
    .bntm-crm-container input[type="tel"],
    .bntm-crm-container select,
    .bntm-crm-container textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
        color: #111827;
        background: #fff;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .bntm-crm-container input:focus,
    .bntm-crm-container select:focus,
    .bntm-crm-container textarea:focus {
        outline: none;
        border-color: var(--bntm-primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,0.08);
    }
    .bntm-crm-container label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 5px;
    }
    .bntm-crm-container .form-row {
        margin-bottom: 14px;
    }
    .bntm-crm-container .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .bntm-crm-container .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
    }

    /* --- Stat Cards --- */
    .bntm-stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .bntm-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border: 1px solid #f3f4f6;
    }
    .bntm-stat-card .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .bntm-stat-card .stat-content h3 {
        font-size: 13px;
        color: #6b7280;
        margin: 0 0 4px;
        font-weight: 500;
    }
    .bntm-stat-card .stat-number {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 2px;
        line-height: 1.1;
    }
    .bntm-stat-card .stat-label {
        font-size: 12px;
        color: #9ca3af;
    }

    /* --- Section --- */
    .bntm-form-section {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border: 1px solid #f3f4f6;
    }
    .bntm-form-section h3 {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    .bntm-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    .bntm-section-header h3 { margin: 0; border: none; padding: 0; }

    /* --- Table --- */
    .bntm-table-wrapper {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    .bntm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .bntm-table thead th {
        background: #f9fafb;
        padding: 11px 14px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .bntm-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.15s;
    }
    .bntm-table tbody tr:last-child { border-bottom: none; }
    .bntm-table tbody tr:hover { background: #fafafa; }
    .bntm-table tbody td {
        padding: 11px 14px;
        color: #374151;
        vertical-align: middle;
    }

    /* --- Badges --- */
    .bntm-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .bntm-badge-lead      { background: #fef3c7; color: #92400e; }
    .bntm-badge-active    { background: #d1fae5; color: #065f46; }
    .bntm-badge-churned   { background: #fee2e2; color: #991b1b; }
    .bntm-badge-archived  { background: #f3f4f6; color: #6b7280; }
    .bntm-badge-open      { background: #dbeafe; color: #1e40af; }
    .bntm-badge-won       { background: #d1fae5; color: #065f46; }
    .bntm-badge-lost      { background: #fee2e2; color: #991b1b; }
    .bntm-badge-low       { background: #f0fdf4; color: #166534; }
    .bntm-badge-medium    { background: #fef9c3; color: #854d0e; }
    .bntm-badge-high      { background: #fff7ed; color: #9a3412; }
    .bntm-badge-urgent    { background: #fee2e2; color: #991b1b; }
    .bntm-badge-done      { background: #d1fae5; color: #065f46; }
    .bntm-badge-in_progress { background: #dbeafe; color: #1e40af; }
    .bntm-badge-public    { background: #ede9fe; color: #5b21b6; }
    .bntm-badge-loggedin  { background: #dbeafe; color: #1e40af; }

    /* --- Modals --- */
    .crm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 99998;
        align-items: center;
        justify-content: center;
    }
    .crm-modal-overlay.active { display: flex; }
    .crm-modal {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.18);
        position: relative;
    }
    .crm-modal-lg { max-width: 820px; }
    .crm-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
        border-radius: 14px 14px 0 0;
    }
    .crm-modal-header h3 { margin: 0; font-size: 16px; font-weight: 600; color: #111827; }
    .crm-modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #9ca3af;
        padding: 4px;
        border-radius: 6px;
        transition: color 0.2s;
    }
    .crm-modal-close:hover { color: #374151; }
    .crm-modal-body { padding: 24px; }
    .crm-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #f9fafb;
        border-radius: 0 0 14px 14px;
    }

    /* --- Filters bar --- */
    .crm-filters-bar {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .crm-filters-bar input,
    .crm-filters-bar select {
        width: auto;
        min-width: 160px;
        flex: 1;
    }
    .crm-filters-bar .filter-actions { margin-left: auto; display: flex; gap: 8px; }

    /* --- Activity feed --- */
    .crm-activity-feed { list-style: none; margin: 0; padding: 0; }
    .crm-activity-item {
        display: flex;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .crm-activity-item:last-child { border-bottom: none; }
    .crm-activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .crm-activity-icon.type-call     { background: #dbeafe; color: #1d4ed8; }
    .crm-activity-icon.type-email    { background: #fce7f3; color: #9d174d; }
    .crm-activity-icon.type-meeting  { background: #d1fae5; color: #065f46; }
    .crm-activity-icon.type-note     { background: #fef9c3; color: #854d0e; }
    .crm-activity-icon.type-task_completion { background: #ede9fe; color: #5b21b6; }
    .crm-activity-icon.type-stage_change    { background: #f0fdf4; color: #166534; }
    .crm-activity-icon.type-deal_view       { background: #f3f4f6; color: #374151; }
    .crm-activity-icon.type-unsubscribe     { background: #fee2e2; color: #991b1b; }
    .crm-activity-icon.type-callback_request { background: #fff7ed; color: #9a3412; }
    .crm-activity-body { flex: 1; }
    .crm-activity-body strong { font-size: 14px; color: #111827; }
    .crm-activity-body p { margin: 3px 0 0; font-size: 13px; color: #6b7280; }
    .crm-activity-meta { font-size: 12px; color: #9ca3af; white-space: nowrap; }

    /* --- Kanban board --- */
    .crm-kanban-board {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding-bottom: 12px;
        min-height: 520px;
    }
    .crm-kanban-column {
        min-width: 260px;
        width: 260px;
        background: #f9fafb;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }
    .crm-kanban-col-header {
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 10px 10px 0 0;
    }
    .crm-kanban-col-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .crm-kanban-col-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .crm-kanban-col-meta { font-size: 12px; color: #9ca3af; }
    .crm-kanban-cards {
        padding: 10px;
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 60px;
    }
    .crm-kanban-cards.drag-over { background: #eff6ff; }
    .crm-deal-card {
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 12px;
        cursor: grab;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        transition: box-shadow 0.2s, transform 0.15s;
    }
    .crm-deal-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .crm-deal-card.dragging { opacity: 0.5; transform: scale(0.97); }
    .crm-deal-card-title { font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 6px; }
    .crm-deal-card-contact { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
    .crm-deal-card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #9ca3af;
    }
    .crm-deal-card-value { font-weight: 700; color: #059669; font-size: 13px; }

    /* --- Task list --- */
    .crm-task-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .crm-task-item:last-child { border-bottom: none; }
    .crm-task-checkbox { margin-top: 2px; width: 16px; height: 16px; cursor: pointer; flex-shrink: 0; }
    .crm-task-body { flex: 1; }
    .crm-task-title { font-size: 14px; font-weight: 500; color: #111827; }
    .crm-task-title.done { text-decoration: line-through; color: #9ca3af; }
    .crm-task-meta { font-size: 12px; color: #9ca3af; margin-top: 3px; display: flex; gap: 12px; flex-wrap: wrap; }
    .crm-task-overdue { color: #dc2626; font-weight: 600; }

    /* --- Toast --- */
    #crm-toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .crm-toast {
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        animation: crm-toast-in 0.3s ease;
        max-width: 340px;
    }
    .crm-toast-success { background: #059669; }
    .crm-toast-error   { background: #dc2626; }
    .crm-toast-info    { background: #2563eb; }
    @keyframes crm-toast-in {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* --- Frontend pages grid --- */
    .bntm-frontend-pages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
    }
    .bntm-page-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.2s;
    }
    .bntm-page-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.09); }
    .bntm-page-card-header {
        padding: 14px 16px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f3f4f6;
    }
    .bntm-page-card-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
    }
    .bntm-page-card-body { padding: 12px 16px; }
    .bntm-page-card-body h4 { margin: 0 0 6px; font-size: 14px; font-weight: 600; color: #111827; }
    .bntm-page-card-body p  { margin: 0; font-size: 12px; color: #6b7280; line-height: 1.5; }
    .bntm-page-card-footer {
        padding: 10px 16px 14px;
        display: flex;
        gap: 8px;
    }

    /* --- Misc --- */
    .crm-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }
    .crm-empty-state svg { margin-bottom: 12px; opacity: 0.4; }
    .crm-empty-state p { font-size: 14px; margin: 0; }
    .crm-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0 0;
        font-size: 13px;
        color: #6b7280;
    }
    .crm-pagination-btns { display: flex; gap: 6px; }
    .crm-pagination-btns button {
        padding: 5px 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        color: #374151;
        transition: background 0.15s;
    }
    .crm-pagination-btns button:hover { background: #f3f4f6; }
    .crm-pagination-btns button.active {
        background: var(--bntm-primary);
        color: #fff;
        border-color: var(--bntm-primary);
    }
    .crm-pagination-btns button:disabled { opacity: 0.4; cursor: not-allowed; }
    .crm-contact-link {
        color: var(--bntm-primary);
        text-decoration: none;
        font-weight: 500;
    }
    .crm-contact-link:hover { text-decoration: underline; }
    .crm-bulk-bar {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #1e40af;
    }
    .crm-bulk-bar.visible { display: flex; }
    .crm-bulk-bar span { font-weight: 600; }
    .crm-tag-chip {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        margin: 2px;
        color: #fff;
    }
    .crm-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bntm-primary);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .crm-detail-panel {
        position: fixed;
        top: 0;
        right: -520px;
        width: 500px;
        height: 100vh;
        background: #fff;
        box-shadow: -4px 0 24px rgba(0,0,0,0.12);
        z-index: 99997;
        transition: right 0.3s ease;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    .crm-detail-panel.open { right: 0; }
    .crm-detail-panel-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.3);
        z-index: 99996;
    }
    .crm-detail-panel-overlay.active { display: block; }
    .crm-detail-panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
    }
    .crm-detail-panel-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
    .crm-detail-panel-body { padding: 20px; flex: 1; }
    .crm-detail-field { margin-bottom: 14px; }
    .crm-detail-field label { font-size: 12px; color: #6b7280; font-weight: 500; display: block; margin-bottom: 4px; }
    .crm-detail-field span  { font-size: 14px; color: #111827; font-weight: 500; }
    @media (max-width: 768px) {
        .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        .bntm-stats-row { grid-template-columns: 1fr 1fr; }
        .crm-filters-bar { flex-direction: column; align-items: stretch; }
        .crm-filters-bar input, .crm-filters-bar select { min-width: unset; }
        .crm-detail-panel { width: 100%; right: -100%; }
        .crm-kanban-board { gap: 10px; }
        .crm-kanban-column { min-width: 230px; width: 230px; }
    }
    </style>

    <div id="crm-toast-container"></div>
    <div class="crm-detail-panel-overlay" id="crm-panel-overlay"></div>

    <script>
    (function() {
        // --- Toast ---
        window.crmToast = function(message, type) {
            type = type || 'success';
            var container = document.getElementById('crm-toast-container');
            var toast = document.createElement('div');
            toast.className = 'crm-toast crm-toast-' + type;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(function() { toast.remove(); }, 300);
            }, 3500);
        };

        // --- Modal open/close ---
        window.crmOpenModal = function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('active');
        };
        window.crmCloseModal = function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('active');
        };
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('crm-modal-overlay')) {
                e.target.classList.remove('active');
            }
            if (e.target.classList.contains('crm-modal-close')) {
                var overlay = e.target.closest('.crm-modal-overlay');
                if (overlay) overlay.classList.remove('active');
            }
        });

        // --- Slide panel ---
        window.crmOpenPanel = function(id) {
            var panel   = document.getElementById(id);
            var overlay = document.getElementById('crm-panel-overlay');
            if (panel)   panel.classList.add('open');
            if (overlay) overlay.classList.add('active');
        };
        window.crmClosePanel = function(id) {
            var panel   = document.getElementById(id);
            var overlay = document.getElementById('crm-panel-overlay');
            if (panel)   panel.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
        };
        document.getElementById('crm-panel-overlay').addEventListener('click', function() {
            document.querySelectorAll('.crm-detail-panel.open').forEach(function(p) {
                p.classList.remove('open');
            });
            this.classList.remove('active');
        });

        // --- AJAX helper ---
        window.crmAjax = function(action, data, callback) {
            data.action = action;
            data.nonce  = bntm_crm_nonce;
            var body = new FormData();
            Object.keys(data).forEach(function(k) {
                if (Array.isArray(data[k])) {
                    data[k].forEach(function(v) { body.append(k + '[]', v); });
                } else {
                    body.append(k, data[k]);
                }
            });
            fetch(ajaxurl, { method: 'POST', body: body })
                .then(function(r) { return r.json(); })
                .then(function(r) { callback(null, r); })
                .catch(function(e) { callback(e, null); });
        };

        // --- Copy URL utility ---
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('copy-page-url')) {
                var url = e.target.getAttribute('data-url');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        crmToast('URL copied to clipboard', 'success');
                    });
                } else {
                    var t = document.createElement('textarea');
                    t.value = url;
                    document.body.appendChild(t);
                    t.select();
                    document.execCommand('copy');
                    document.body.removeChild(t);
                    crmToast('URL copied to clipboard', 'success');
                }
            }
        });
    })();
    </script>
    <?php
    $content = ob_get_clean();
    return bntm_universal_container('Customer Relationship Management', $content);
}

// =============================================================================
// TAB 1 — DASHBOARD TAB
// =============================================================================

function crm_dashboard_tab($business_id) {
    global $wpdb;

    $contacts_table  = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table     = $wpdb->prefix . 'bntm_crm_deals';
    $tasks_table     = $wpdb->prefix . 'bntm_crm_tasks';
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $stages_table    = $wpdb->prefix . 'bntm_crm_pipeline_stages';

    $total_contacts = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$contacts_table} WHERE deleted_at IS NULL");
    $open_deals     = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL");
    $tasks_due      = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$tasks_table} WHERE status != 'done' AND due_date <= %s",
        date('Y-m-d 23:59:59', strtotime('+7 days'))
    ));
    $month_start    = date('Y-m-01 00:00:00');
    $month_end      = date('Y-m-t 23:59:59');
    $won_this_month = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'won' AND updated_at BETWEEN %s AND %s",
        $month_start, $month_end
    ));
    $lost_this_month = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'lost' AND updated_at BETWEEN %s AND %s",
        $month_start, $month_end
    ));
    $pipeline_value = (float) $wpdb->get_var("SELECT SUM(value) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL");

    $recent_activities = $wpdb->get_results(
        "SELECT a.*, CONCAT(c.first_name, ' ', c.last_name) AS contact_name, c.id AS cid
         FROM {$activities_table} a
         LEFT JOIN {$contacts_table} c ON a.contact_id = c.id
         ORDER BY a.logged_at DESC LIMIT 8"
    );

    $upcoming_tasks = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, CONCAT(c.first_name, ' ', c.last_name) AS contact_name
         FROM {$tasks_table} t
         LEFT JOIN {$contacts_table} c ON t.contact_id = c.id
         WHERE t.status != 'done' AND t.due_date >= %s
         ORDER BY t.due_date ASC LIMIT 6",
        current_time('mysql')
    ));

    $pipeline_stages = $wpdb->get_results(
        "SELECT ps.name, COUNT(d.id) AS deal_count, COALESCE(SUM(d.value),0) AS stage_value
         FROM {$stages_table} ps
         LEFT JOIN {$deals_table} d ON d.stage_id = ps.id AND d.status = 'open' AND d.deleted_at IS NULL
         GROUP BY ps.id, ps.name ORDER BY ps.sort_order ASC LIMIT 8"
    );

    $top_contacts = $wpdb->get_results(
        "SELECT c.id, CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                c.company, c.status, COALESCE(SUM(d.value),0) AS total_value
         FROM {$contacts_table} c
         LEFT JOIN {$deals_table} d ON d.contact_id = c.id AND d.status = 'open' AND d.deleted_at IS NULL
         WHERE c.deleted_at IS NULL
         GROUP BY c.id ORDER BY total_value DESC LIMIT 5"
    );

    ob_start();
    ?>
    <div class="crm-dashboard-toolbar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;gap:8px;">
            <button class="bntm-btn-primary bntm-btn-small" onclick="crmOpenModal('crm-quick-add-modal')">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:5px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Quick Add
            </button>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:13px;color:#6b7280;margin:0;">View:</label>
            <select id="crm-dashboard-view-mode" style="width:auto;min-width:160px;">
                <option value="cards">Summary Cards</option>
                <option value="graphs">Bar Graphs</option>
            </select>
        </div>
    </div>

    <!-- KPI Cards View -->
    <div id="crm-view-cards">
        <div class="bntm-stats-row">
            <div class="bntm-stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,var(--bntm-primary),var(--bntm-primary-hover));">
                    <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Total Contacts</h3>
                    <p class="stat-number"><?php echo number_format($total_contacts); ?></p>
                    <span class="stat-label">All records</span>
                </div>
            </div>
            <div class="bntm-stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#34d399);">
                    <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Open Deals</h3>
                    <p class="stat-number"><?php echo number_format($open_deals); ?></p>
                    <span class="stat-label"><?php echo crm_format_price($pipeline_value); ?> pipeline</span>
                </div>
            </div>
            <div class="bntm-stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                    <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Tasks Due</h3>
                    <p class="stat-number"><?php echo number_format($tasks_due); ?></p>
                    <span class="stat-label">Next 7 days</span>
                </div>
            </div>
            <div class="bntm-stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#6ee7b7);">
                    <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Won This Month</h3>
                    <p class="stat-number"><?php echo number_format($won_this_month); ?></p>
                    <span class="stat-label"><?php echo number_format($lost_this_month); ?> lost</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Graphs View -->
    <div id="crm-view-graphs" style="display:none;">
        <div class="bntm-form-section">
            <div class="bntm-section-header">
                <h3>Period-over-Period Comparison</h3>
                <select id="crm-graph-period" style="width:auto;">
                    <option value="monthly">Monthly</option>
                    <option value="weekly">Weekly</option>
                </select>
            </div>
            <div style="position:relative;height:280px;">
                <canvas id="crm-bar-chart" style="width:100%;height:100%;"></canvas>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;" class="crm-dashboard-grid">

        <!-- Recent Activity Feed -->
        <div class="bntm-form-section" style="margin-bottom:0;">
            <div class="bntm-section-header">
                <h3>Recent Activity</h3>
                <a href="?tab=activities" style="font-size:13px;color:var(--bntm-primary);text-decoration:none;">View all</a>
            </div>
            <?php if (!empty($recent_activities)): ?>
            <ul class="crm-activity-feed">
                <?php foreach ($recent_activities as $act): ?>
                <li class="crm-activity-item">
                    <div class="crm-activity-icon type-<?php echo esc_attr($act->type); ?>">
                        <?php echo crm_get_activity_icon($act->type); ?>
                    </div>
                    <div class="crm-activity-body">
                        <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $act->type))); ?></strong>
                        <?php if ($act->contact_name): ?>
                        <p>
                            <a href="?tab=contacts&contact_id=<?php echo intval($act->cid); ?>" class="crm-contact-link">
                                <?php echo esc_html(trim($act->contact_name)); ?>
                            </a>
                            <?php if ($act->subject): ?> &mdash; <?php echo esc_html($act->subject); ?><?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="crm-activity-meta">
                        <?php echo esc_html(human_time_diff(strtotime($act->logged_at), current_time('timestamp'))); ?> ago
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <div class="crm-empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p>No activity logged yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Tasks -->
        <div class="bntm-form-section" style="margin-bottom:0;">
            <div class="bntm-section-header">
                <h3>Upcoming Tasks</h3>
                <a href="?tab=tasks" style="font-size:13px;color:var(--bntm-primary);text-decoration:none;">View all</a>
            </div>
            <?php if (!empty($upcoming_tasks)): ?>
            <div>
                <?php foreach ($upcoming_tasks as $task):
                    $is_overdue = strtotime($task->due_date) < current_time('timestamp') && $task->status !== 'done';
                ?>
                <div class="crm-task-item">
                    <div style="flex:1;">
                        <div class="crm-task-title"><?php echo esc_html($task->title); ?></div>
                        <div class="crm-task-meta">
                            <span class="bntm-badge bntm-badge-<?php echo esc_attr($task->priority); ?>"><?php echo esc_html(ucfirst($task->priority)); ?></span>
                            <?php if ($task->contact_name): ?>
                            <span><?php echo esc_html(trim($task->contact_name)); ?></span>
                            <?php endif; ?>
                            <span class="<?php echo $is_overdue ? 'crm-task-overdue' : ''; ?>">
                                <?php echo $is_overdue ? 'Overdue: ' : ''; ?>
                                <?php echo esc_html(date('M j', strtotime($task->due_date))); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="crm-empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>No upcoming tasks.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;" class="crm-dashboard-grid">

        <!-- Pipeline Funnel -->
        <div class="bntm-form-section" style="margin-bottom:0;">
            <div class="bntm-section-header">
                <h3>Pipeline by Stage</h3>
                <a href="?tab=pipeline" style="font-size:13px;color:var(--bntm-primary);text-decoration:none;">Open board</a>
            </div>
            <?php if (!empty($pipeline_stages)): ?>
            <div>
                <?php
                $max_deals = max(array_column($pipeline_stages, 'deal_count'));
                $max_deals = $max_deals > 0 ? $max_deals : 1;
                foreach ($pipeline_stages as $stage):
                    $pct = round(($stage->deal_count / $max_deals) * 100);
                ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#374151;margin-bottom:4px;">
                        <span style="font-weight:500;"><?php echo esc_html($stage->name); ?></span>
                        <span style="color:#6b7280;"><?php echo intval($stage->deal_count); ?> deal<?php echo $stage->deal_count != 1 ? 's' : ''; ?> &mdash; <?php echo crm_format_price($stage->stage_value); ?></span>
                    </div>
                    <div style="height:8px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?php echo intval($pct); ?>%;background:var(--bntm-primary);border-radius:4px;transition:width 0.4s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="crm-empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p>No pipeline stages configured.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Top Contacts -->
        <div class="bntm-form-section" style="margin-bottom:0;">
            <div class="bntm-section-header">
                <h3>Top Contacts by Deal Value</h3>
                <a href="?tab=contacts" style="font-size:13px;color:var(--bntm-primary);text-decoration:none;">All contacts</a>
            </div>
            <?php if (!empty($top_contacts)): ?>
            <div>
                <?php foreach ($top_contacts as $i => $contact): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                    <div class="crm-avatar" style="font-size:11px;">
                        <?php
                        $parts = explode(' ', trim($contact->contact_name));
                        echo esc_html(strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : '')));
                        ?>
                    </div>
                    <div style="flex:1;">
                        <a href="?tab=contacts&contact_id=<?php echo intval($contact->id); ?>" class="crm-contact-link" style="font-size:14px;">
                            <?php echo esc_html(trim($contact->contact_name)); ?>
                        </a>
                        <?php if ($contact->company): ?>
                        <div style="font-size:12px;color:#9ca3af;"><?php echo esc_html($contact->company); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight:700;color:#059669;font-size:14px;">
                        <?php echo crm_format_price($contact->total_value); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="crm-empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p>No contacts with deals yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Frontend Pages -->
    <div class="bntm-form-section">
        <h3>Frontend Pages</h3>
        <p style="color:#6b7280;margin-bottom:16px;font-size:13px;">
            Public-facing pages for this module. Share these links with your customers.
        </p>
        <div class="bntm-frontend-pages-grid">

            <div class="bntm-page-card">
                <div class="bntm-page-card-header">
                    <div class="bntm-page-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="bntm-badge bntm-badge-public">Public</span>
                </div>
                <div class="bntm-page-card-body">
                    <h4>Contact Form</h4>
                    <p>Lead capture form for website visitors to submit their details into the CRM.</p>
                </div>
                <div class="bntm-page-card-footer">
                    <?php
                    $page = get_page_by_path('crm-contact-form');
                    $url  = $page ? get_permalink($page->ID) : '#';
                    ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="bntm-btn-primary bntm-btn-small">Open Page</a>
                    <button class="bntm-btn-secondary bntm-btn-small copy-page-url" data-url="<?php echo esc_url($url); ?>">Copy URL</button>
                </div>
            </div>

            <div class="bntm-page-card">
                <div class="bntm-page-card-header">
                    <div class="bntm-page-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="bntm-badge bntm-badge-loggedin">Logged In</span>
                </div>
                <div class="bntm-page-card-body">
                    <h4>Customer Portal</h4>
                    <p>Self-service portal where customers can view their deals, activity, and documents.</p>
                </div>
                <div class="bntm-page-card-footer">
                    <?php
                    $page = get_page_by_path('crm-customer-portal');
                    $url  = $page ? get_permalink($page->ID) : '#';
                    ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="bntm-btn-primary bntm-btn-small">Open Page</a>
                    <button class="bntm-btn-secondary bntm-btn-small copy-page-url" data-url="<?php echo esc_url($url); ?>">Copy URL</button>
                </div>
            </div>

            <div class="bntm-page-card">
                <div class="bntm-page-card-header">
                    <div class="bntm-page-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <span class="bntm-badge bntm-badge-public">Token Access</span>
                </div>
                <div class="bntm-page-card-body">
                    <h4>Deal / Proposal View</h4>
                    <p>Shareable branded deal page where contacts can accept or decline a proposal.</p>
                </div>
                <div class="bntm-page-card-footer">
                    <?php
                    $page = get_page_by_path('crm-deal-view');
                    $url  = $page ? get_permalink($page->ID) : '#';
                    ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="bntm-btn-primary bntm-btn-small">Open Page</a>
                    <button class="bntm-btn-secondary bntm-btn-small copy-page-url" data-url="<?php echo esc_url($url); ?>">Copy URL</button>
                </div>
            </div>

            <div class="bntm-page-card">
                <div class="bntm-page-card-header">
                    <div class="bntm-page-card-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <span class="bntm-badge bntm-badge-public">Public</span>
                </div>
                <div class="bntm-page-card-body">
                    <h4>Unsubscribe Page</h4>
                    <p>GDPR-compliant opt-out page for contacts to unsubscribe from communications.</p>
                </div>
                <div class="bntm-page-card-footer">
                    <?php
                    $page = get_page_by_path('crm-unsubscribe');
                    $url  = $page ? get_permalink($page->ID) : '#';
                    ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="bntm-btn-primary bntm-btn-small">Open Page</a>
                    <button class="bntm-btn-secondary bntm-btn-small copy-page-url" data-url="<?php echo esc_url($url); ?>">Copy URL</button>
                </div>
            </div>

        </div>
        <div id="copy-url-message"></div>
    </div>

    <!-- Quick Add Modal -->
    <div class="crm-modal-overlay" id="crm-quick-add-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Quick Add</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                    <a href="?tab=contacts" style="text-decoration:none;">
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:20px;text-align:center;transition:box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                            <div style="width:44px;height:44px;border-radius:10px;background:var(--bntm-primary);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">New Contact</div>
                        </div>
                    </a>
                    <a href="?tab=pipeline" style="text-decoration:none;">
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:20px;text-align:center;transition:box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                            <div style="width:44px;height:44px;border-radius:10px;background:#059669;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">New Deal</div>
                        </div>
                    </a>
                    <a href="?tab=tasks" style="text-decoration:none;">
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:20px;text-align:center;transition:box-shadow 0.2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
                            <div style="width:44px;height:44px;border-radius:10px;background:#f59e0b;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <div style="font-size:14px;font-weight:600;color:#111827;">New Task</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
    @media (max-width: 768px) {
        .crm-dashboard-grid { grid-template-columns: 1fr !important; }
        .crm-dashboard-toolbar { flex-direction: column; align-items: flex-start; }
    }
    </style>

    <script>
    (function() {
        var viewMode = document.getElementById('crm-dashboard-view-mode');
        var cardsEl  = document.getElementById('crm-view-cards');
        var graphsEl = document.getElementById('crm-view-graphs');
        var chartInstance = null;

        viewMode.addEventListener('change', function() {
            if (this.value === 'cards') {
                cardsEl.style.display  = '';
                graphsEl.style.display = 'none';
            } else {
                cardsEl.style.display  = 'none';
                graphsEl.style.display = '';
                loadBarChart(document.getElementById('crm-graph-period').value);
            }
        });

        document.getElementById('crm-graph-period').addEventListener('change', function() {
            loadBarChart(this.value);
        });

        function loadBarChart(period) {
            crmAjax('bntm_crm_get_bar_graph_data', { period: period }, function(err, res) {
                if (err || !res.success) return;
                renderBarChart(res.data);
            });
        }

        function renderBarChart(data) {
            var canvas = document.getElementById('crm-bar-chart');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            if (chartInstance) chartInstance = null;
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            var labels   = data.labels   || [];
            var contacts = data.contacts || [];
            var deals    = data.deals    || [];
            var won      = data.won      || [];

            var barW    = 18;
            var groupW  = 70;
            var padding = 40;
            var maxVal  = Math.max.apply(null, contacts.concat(deals, won, [1]));
            var h       = canvas.offsetHeight || 260;
            var w       = canvas.offsetWidth  || 600;
            canvas.width  = w;
            canvas.height = h;
            var chartH = h - padding - 30;

            ctx.clearRect(0, 0, w, h);

            var colors = ['#6366f1', '#059669', '#f59e0b'];
            var series = [contacts, deals, won];
            var seriesLabels = ['Contacts', 'Deals', 'Won'];

            labels.forEach(function(label, gi) {
                var x = padding + gi * groupW;
                series.forEach(function(s, si) {
                    var val   = s[gi] || 0;
                    var barH  = (val / maxVal) * chartH;
                    var bx    = x + si * (barW + 3);
                    ctx.fillStyle = colors[si];
                    ctx.beginPath();
                    ctx.roundRect(bx, h - 30 - barH, barW, barH, [3, 3, 0, 0]);
                    ctx.fill();
                });
                ctx.fillStyle = '#9ca3af';
                ctx.font = '11px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(label, x + (barW + 3) * 1, h - 12);
            });

            var legendX = padding;
            seriesLabels.forEach(function(sl, si) {
                ctx.fillStyle = colors[si];
                ctx.fillRect(legendX, 6, 12, 12);
                ctx.fillStyle = '#374151';
                ctx.font = '11px sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(sl, legendX + 16, 17);
                legendX += 80;
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 2 — CONTACTS TAB
// =============================================================================

function crm_contacts_tab($business_id) {
    global $wpdb;

    $users = get_users(['fields' => ['ID', 'display_name']]);
    $tags  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bntm_crm_tags ORDER BY name ASC");

    ob_start();
    ?>
    <div class="bntm-form-section">
        <div class="bntm-section-header">
            <h3>Contacts</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-contacts-import-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import CSV
                </button>
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-contacts-export-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </button>
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-tag-manager-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Tags
                </button>
                <button class="bntm-btn-primary bntm-btn-small" id="crm-add-contact-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Contact
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="crm-filters-bar">
            <input type="text" id="crm-contact-search" placeholder="Search name, email, company…">
            <select id="crm-contact-type-filter">
                <option value="">All Types</option>
                <option value="person">Person</option>
                <option value="organisation">Organisation</option>
            </select>
            <select id="crm-contact-status-filter">
                <option value="">All Statuses</option>
                <option value="lead">Lead</option>
                <option value="active">Active</option>
                <option value="churned">Churned</option>
                <option value="archived">Archived</option>
            </select>
            <select id="crm-contact-assigned-filter">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="crm-contact-tag-filter">
                <option value="">All Tags</option>
                <?php foreach ($tags as $tag): ?>
                <option value="<?php echo intval($tag->id); ?>"><?php echo esc_html($tag->name); ?></option>
                <?php endforeach; ?>
            </select>
            <div class="filter-actions">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-contacts-apply-filters">Filter</button>
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-contacts-reset-filters">Reset</button>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div class="crm-bulk-bar" id="crm-contacts-bulk-bar">
            <span id="crm-contacts-selected-count">0</span> selected
            <select id="crm-contacts-bulk-action" style="width:auto;min-width:160px;">
                <option value="">Bulk Action</option>
                <option value="assign">Assign to User</option>
                <option value="tag">Add Tag</option>
                <option value="delete">Delete</option>
                <option value="export">Export Selected</option>
            </select>
            <select id="crm-contacts-bulk-assign-user" style="width:auto;display:none;">
                <?php foreach ($users as $u): ?>
                <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="crm-contacts-bulk-tag" style="width:auto;display:none;">
                <?php foreach ($tags as $tag): ?>
                <option value="<?php echo intval($tag->id); ?>"><?php echo esc_html($tag->name); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="bntm-btn-primary bntm-btn-small" id="crm-contacts-bulk-apply">Apply</button>
            <button class="bntm-btn-secondary bntm-btn-small" id="crm-contacts-bulk-clear">Clear</button>
        </div>

        <!-- Table -->
        <div class="bntm-table-wrapper">
            <table class="bntm-table" id="crm-contacts-table">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="crm-contacts-select-all"></th>
                        <th class="crm-sortable" data-col="first_name" style="cursor:pointer;">Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="crm-sortable" data-col="type" style="cursor:pointer;">Type</th>
                        <th>Company</th>
                        <th class="crm-sortable" data-col="status" style="cursor:pointer;">Status</th>
                        <th>Assigned To</th>
                        <th>Tags</th>
                        <th class="crm-sortable" data-col="created_at" style="cursor:pointer;">Created</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="crm-contacts-tbody">
                    <tr><td colspan="11" style="text-align:center;padding:30px;color:#9ca3af;">Loading contacts…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="crm-pagination" id="crm-contacts-pagination"></div>
    </div>

    <!-- Add / Edit Contact Modal -->
    <div class="crm-modal-overlay" id="crm-contact-modal">
        <div class="crm-modal crm-modal-lg">
            <div class="crm-modal-header">
                <h3 id="crm-contact-modal-title">Add Contact</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-contact-id" value="">
                <div class="form-row">
                    <label>Contact Type</label>
                    <select id="crm-contact-type">
                        <option value="person">Person</option>
                        <option value="organisation">Organisation</option>
                    </select>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>First Name</label>
                        <input type="text" id="crm-contact-first-name" placeholder="First name">
                    </div>
                    <div class="form-row">
                        <label>Last Name</label>
                        <input type="text" id="crm-contact-last-name" placeholder="Last name">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Email</label>
                        <input type="email" id="crm-contact-email" placeholder="email@example.com">
                    </div>
                    <div class="form-row">
                        <label>Phone</label>
                        <input type="tel" id="crm-contact-phone" placeholder="+1 000 000 0000">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Mobile</label>
                        <input type="tel" id="crm-contact-mobile" placeholder="+1 000 000 0000">
                    </div>
                    <div class="form-row">
                        <label>Company</label>
                        <input type="text" id="crm-contact-company" placeholder="Company name">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Job Title</label>
                        <input type="text" id="crm-contact-job-title" placeholder="Job title">
                    </div>
                    <div class="form-row">
                        <label>Website</label>
                        <input type="url" id="crm-contact-website" placeholder="https://example.com">
                    </div>
                </div>
                <div class="form-row">
                    <label>Address Line 1</label>
                    <input type="text" id="crm-contact-address1" placeholder="Street address">
                </div>
                <div class="form-row">
                    <label>Address Line 2</label>
                    <input type="text" id="crm-contact-address2" placeholder="Apt, suite, etc.">
                </div>
                <div class="form-grid-3">
                    <div class="form-row">
                        <label>City</label>
                        <input type="text" id="crm-contact-city" placeholder="City">
                    </div>
                    <div class="form-row">
                        <label>State</label>
                        <input type="text" id="crm-contact-state" placeholder="State">
                    </div>
                    <div class="form-row">
                        <label>Postcode</label>
                        <input type="text" id="crm-contact-postcode" placeholder="Postcode">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Country</label>
                        <input type="text" id="crm-contact-country" placeholder="Country">
                    </div>
                    <div class="form-row">
                        <label>Source</label>
                        <input type="text" id="crm-contact-source" placeholder="e.g. Web, Referral">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Status</label>
                        <select id="crm-contact-status">
                            <option value="lead">Lead</option>
                            <option value="active">Active</option>
                            <option value="churned">Churned</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Assigned To</label>
                        <select id="crm-contact-assigned">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Tags</label>
                    <div id="crm-contact-tags-picker" style="display:flex;flex-wrap:wrap;gap:6px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;min-height:40px;">
                        <?php foreach ($tags as $tag): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:400;cursor:pointer;">
                            <input type="checkbox" class="crm-tag-checkbox" value="<?php echo intval($tag->id); ?>">
                            <span class="crm-tag-chip" style="background:<?php echo esc_attr($tag->colour); ?>;"><?php echo esc_html($tag->name); ?></span>
                        </label>
                        <?php endforeach; ?>
                        <?php if (empty($tags)): ?>
                        <span style="font-size:12px;color:#9ca3af;">No tags yet. Create tags in Settings.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-contact-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-contact-save-btn">Save Contact</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="crm-modal-overlay" id="crm-contact-delete-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3>Delete Contact</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:14px;color:#374151;margin:0;">Are you sure you want to delete this contact? This action cannot be undone. All linked deals, activities, and tasks will also be removed.</p>
                <input type="hidden" id="crm-contact-delete-id">
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-contact-delete-modal')">Cancel</button>
                <button class="bntm-btn-danger" id="crm-contact-confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="crm-modal-overlay" id="crm-contact-import-modal">
        <div class="crm-modal" style="max-width:480px;">
            <div class="crm-modal-header">
                <h3>Import Contacts from CSV</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Upload a CSV file with columns: <strong>first_name, last_name, email, phone, company, status, source</strong>. The first row must be the header row.</p>
                <div class="form-row">
                    <label>CSV File</label>
                    <input type="file" id="crm-import-file" accept=".csv" style="border:none;padding:0;">
                </div>
                <div id="crm-import-result" style="margin-top:10px;font-size:13px;"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-contact-import-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-import-submit-btn">Import</button>
            </div>
        </div>
    </div>

    <!-- Tag Manager Modal -->
    <div class="crm-modal-overlay" id="crm-tag-manager-modal">
        <div class="crm-modal" style="max-width:480px;">
            <div class="crm-modal-header">
                <h3>Tag Manager</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div style="display:flex;gap:8px;margin-bottom:16px;">
                    <input type="text" id="crm-new-tag-name" placeholder="Tag name" style="flex:1;">
                    <input type="color" id="crm-new-tag-colour" value="#6c757d" style="width:44px;height:40px;padding:2px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;">
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-add-tag-btn">Add</button>
                </div>
                <div id="crm-tags-list">
                    <?php if (!empty($tags)): ?>
                    <?php foreach ($tags as $tag): ?>
                    <div class="crm-tag-manager-row" data-tag-id="<?php echo intval($tag->id); ?>" style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                        <span class="crm-tag-chip" style="background:<?php echo esc_attr($tag->colour); ?>;"><?php echo esc_html($tag->name); ?></span>
                        <span style="flex:1;font-size:13px;color:#374151;"><?php echo esc_html($tag->name); ?></span>
                        <button class="bntm-btn-danger bntm-btn-small crm-delete-tag-btn" data-id="<?php echo intval($tag->id); ?>">Delete</button>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="font-size:13px;color:#9ca3af;">No tags yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    .crm-sortable:hover { background: #f3f4f6; }
    .crm-sortable.sort-asc::after  { content: ' \2191'; }
    .crm-sortable.sort-desc::after { content: ' \2193'; }
    </style>

    <script>
    (function() {
        var currentPage   = 1;
        var perPage       = 20;
        var sortCol       = 'created_at';
        var sortDir       = 'desc';
        var selectedIds   = [];
        var editingId     = 0;

        function getFilters() {
            return {
                search:  document.getElementById('crm-contact-search').value,
                type:    document.getElementById('crm-contact-type-filter').value,
                status:  document.getElementById('crm-contact-status-filter').value,
                assigned: document.getElementById('crm-contact-assigned-filter').value,
                tag:     document.getElementById('crm-contact-tag-filter').value,
                page:    currentPage,
                per_page: perPage,
                sort_col: sortCol,
                sort_dir: sortDir,
            };
        }

        function loadContacts() {
            var tbody = document.getElementById('crm-contacts-tbody');
            tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#9ca3af;">Loading…</td></tr>';
            crmAjax('bntm_crm_get_contacts', getFilters(), function(err, res) {
                if (err || !res.success) {
                    tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#dc2626;">Failed to load contacts.</td></tr>';
                    return;
                }
                renderContacts(res.data.contacts, res.data.total);
            });
        }

        function renderContacts(contacts, total) {
            var tbody = document.getElementById('crm-contacts-tbody');
            if (!contacts || contacts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;padding:30px;color:#9ca3af;">No contacts found.</td></tr>';
                renderPagination(0);
                return;
            }
            var html = '';
            contacts.forEach(function(c) {
                var tags = (c.tags || []).map(function(t) {
                    return '<span class="crm-tag-chip" style="background:' + escHtml(t.colour) + ';">' + escHtml(t.name) + '</span>';
                }).join('');
                var checked = selectedIds.indexOf(c.id) > -1 ? 'checked' : '';
                html += '<tr data-id="' + c.id + '">' +
                    '<td><input type="checkbox" class="crm-contact-row-check" value="' + c.id + '" ' + checked + '></td>' +
                    '<td><a href="?tab=contacts&contact_id=' + c.id + '" class="crm-contact-link">' + escHtml((c.first_name || '') + ' ' + (c.last_name || '')) + '</a></td>' +
                    '<td>' + escHtml(c.email || '') + '</td>' +
                    '<td>' + escHtml(c.phone || '') + '</td>' +
                    '<td><span class="bntm-badge" style="background:#f3f4f6;color:#374151;">' + escHtml(c.type || '') + '</span></td>' +
                    '<td>' + escHtml(c.company || '') + '</td>' +
                    '<td><span class="bntm-badge bntm-badge-' + escHtml(c.status) + '">' + escHtml(ucFirst(c.status)) + '</span></td>' +
                    '<td>' + escHtml(c.assigned_name || '') + '</td>' +
                    '<td>' + tags + '</td>' +
                    '<td>' + escHtml(c.created_at ? c.created_at.substring(0,10) : '') + '</td>' +
                    '<td style="white-space:nowrap;">' +
                        '<button class="bntm-btn-secondary bntm-btn-small crm-edit-contact-btn" data-id="' + c.id + '" style="margin-right:4px;">Edit</button>' +
                        '<button class="bntm-btn-danger bntm-btn-small crm-delete-contact-btn" data-id="' + c.id + '">Delete</button>' +
                    '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
            bindRowEvents();
            renderPagination(total);
        }

        function renderPagination(total) {
            var pages = Math.ceil(total / perPage);
            var el    = document.getElementById('crm-contacts-pagination');
            if (pages <= 1) { el.innerHTML = '<span>' + total + ' contact' + (total !== 1 ? 's' : '') + '</span>'; return; }
            var btns = '';
            btns += '<button ' + (currentPage === 1 ? 'disabled' : '') + ' id="crm-prev-page">&larr; Prev</button>';
            for (var p = 1; p <= pages; p++) {
                if (pages > 7 && p > 2 && p < pages - 1 && Math.abs(p - currentPage) > 2) {
                    if (p === 3 || p === pages - 2) btns += '<button disabled>…</button>';
                    continue;
                }
                btns += '<button class="' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            btns += '<button ' + (currentPage === pages ? 'disabled' : '') + ' id="crm-next-page">Next &rarr;</button>';
            el.innerHTML = '<span>' + total + ' contact' + (total !== 1 ? 's' : '') + '</span><div class="crm-pagination-btns">' + btns + '</div>';

            el.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() { currentPage = parseInt(this.dataset.page); loadContacts(); });
            });
            var prev = document.getElementById('crm-prev-page');
            var next = document.getElementById('crm-next-page');
            if (prev) prev.addEventListener('click', function() { currentPage--; loadContacts(); });
            if (next) next.addEventListener('click', function() { currentPage++; loadContacts(); });
        }

        function bindRowEvents() {
            document.querySelectorAll('.crm-contact-row-check').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var id = parseInt(this.value);
                    if (this.checked) {
                        if (selectedIds.indexOf(id) === -1) selectedIds.push(id);
                    } else {
                        selectedIds = selectedIds.filter(function(i) { return i !== id; });
                    }
                    updateBulkBar();
                });
            });

            document.querySelectorAll('.crm-edit-contact-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { openEditContact(parseInt(this.dataset.id)); });
            });

            document.querySelectorAll('.crm-delete-contact-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('crm-contact-delete-id').value = this.dataset.id;
                    crmOpenModal('crm-contact-delete-modal');
                });
            });
        }

        function updateBulkBar() {
            var bar = document.getElementById('crm-contacts-bulk-bar');
            document.getElementById('crm-contacts-selected-count').textContent = selectedIds.length;
            bar.classList.toggle('visible', selectedIds.length > 0);
        }

        function openAddContact() {
            editingId = 0;
            document.getElementById('crm-contact-modal-title').textContent = 'Add Contact';
            document.getElementById('crm-contact-id').value = '';
            ['first-name','last-name','email','phone','mobile','company','job-title','website','address1','address2','city','state','postcode','country','source'].forEach(function(f) {
                document.getElementById('crm-contact-' + f).value = '';
            });
            document.getElementById('crm-contact-type').value   = 'person';
            document.getElementById('crm-contact-status').value = 'lead';
            document.getElementById('crm-contact-assigned').value = '';
            document.querySelectorAll('.crm-tag-checkbox').forEach(function(cb) { cb.checked = false; });
            crmOpenModal('crm-contact-modal');
        }

        function openEditContact(id) {
            editingId = id;
            crmAjax('bntm_crm_get_contact_detail', { contact_id: id }, function(err, res) {
                if (err || !res.success) { crmToast('Failed to load contact.', 'error'); return; }
                var c = res.data;
                document.getElementById('crm-contact-modal-title').textContent = 'Edit Contact';
                document.getElementById('crm-contact-id').value          = c.id;
                document.getElementById('crm-contact-type').value        = c.type        || 'person';
                document.getElementById('crm-contact-first-name').value  = c.first_name  || '';
                document.getElementById('crm-contact-last-name').value   = c.last_name   || '';
                document.getElementById('crm-contact-email').value       = c.email       || '';
                document.getElementById('crm-contact-phone').value       = c.phone       || '';
                document.getElementById('crm-contact-mobile').value      = c.mobile      || '';
                document.getElementById('crm-contact-company').value     = c.company     || '';
                document.getElementById('crm-contact-job-title').value   = c.job_title   || '';
                document.getElementById('crm-contact-website').value     = c.website     || '';
                document.getElementById('crm-contact-address1').value    = c.address_line_1 || '';
                document.getElementById('crm-contact-address2').value    = c.address_line_2 || '';
                document.getElementById('crm-contact-city').value        = c.city        || '';
                document.getElementById('crm-contact-state').value       = c.state       || '';
                document.getElementById('crm-contact-postcode').value    = c.postcode    || '';
                document.getElementById('crm-contact-country').value     = c.country     || '';
                document.getElementById('crm-contact-source').value      = c.source      || '';
                document.getElementById('crm-contact-status').value      = c.status      || 'lead';
                document.getElementById('crm-contact-assigned').value    = c.assigned_user_id || '';
                var tagIds = (c.tags || []).map(function(t) { return parseInt(t.id); });
                document.querySelectorAll('.crm-tag-checkbox').forEach(function(cb) {
                    cb.checked = tagIds.indexOf(parseInt(cb.value)) > -1;
                });
                crmOpenModal('crm-contact-modal');
            });
        }

        function saveContact() {
            var btn = document.getElementById('crm-contact-save-btn');
            btn.disabled = true;
            var tagIds = [];
            document.querySelectorAll('.crm-tag-checkbox:checked').forEach(function(cb) { tagIds.push(cb.value); });
            var data = {
                contact_id:    document.getElementById('crm-contact-id').value,
                type:          document.getElementById('crm-contact-type').value,
                first_name:    document.getElementById('crm-contact-first-name').value,
                last_name:     document.getElementById('crm-contact-last-name').value,
                email:         document.getElementById('crm-contact-email').value,
                phone:         document.getElementById('crm-contact-phone').value,
                mobile:        document.getElementById('crm-contact-mobile').value,
                company:       document.getElementById('crm-contact-company').value,
                job_title:     document.getElementById('crm-contact-job-title').value,
                website:       document.getElementById('crm-contact-website').value,
                address_line_1: document.getElementById('crm-contact-address1').value,
                address_line_2: document.getElementById('crm-contact-address2').value,
                city:          document.getElementById('crm-contact-city').value,
                state:         document.getElementById('crm-contact-state').value,
                postcode:      document.getElementById('crm-contact-postcode').value,
                country:       document.getElementById('crm-contact-country').value,
                source:        document.getElementById('crm-contact-source').value,
                status:        document.getElementById('crm-contact-status').value,
                assigned_user_id: document.getElementById('crm-contact-assigned').value,
                tag_ids:       tagIds,
            };
            crmAjax('bntm_crm_save_contact', data, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Contact saved successfully.', 'success');
                crmCloseModal('crm-contact-modal');
                loadContacts();
            });
        }

        // Sort
        document.querySelectorAll('.crm-sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                var col = this.dataset.col;
                if (sortCol === col) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortCol = col;
                    sortDir = 'asc';
                }
                document.querySelectorAll('.crm-sortable').forEach(function(el) { el.classList.remove('sort-asc','sort-desc'); });
                this.classList.add('sort-' + sortDir);
                currentPage = 1;
                loadContacts();
            });
        });

        // Select all
        document.getElementById('crm-contacts-select-all').addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.crm-contact-row-check').forEach(function(cb) {
                cb.checked = checked;
                var id = parseInt(cb.value);
                if (checked) { if (selectedIds.indexOf(id) === -1) selectedIds.push(id); }
                else { selectedIds = selectedIds.filter(function(i) { return i !== id; }); }
            });
            updateBulkBar();
        });

        // Bulk action selects
        document.getElementById('crm-contacts-bulk-action').addEventListener('change', function() {
            document.getElementById('crm-contacts-bulk-assign-user').style.display = this.value === 'assign' ? '' : 'none';
            document.getElementById('crm-contacts-bulk-tag').style.display         = this.value === 'tag'    ? '' : 'none';
        });

        document.getElementById('crm-contacts-bulk-apply').addEventListener('click', function() {
            var action = document.getElementById('crm-contacts-bulk-action').value;
            if (!action || selectedIds.length === 0) return;
            var extra = {};
            if (action === 'assign') extra.assign_user_id = document.getElementById('crm-contacts-bulk-assign-user').value;
            if (action === 'tag')    extra.tag_id         = document.getElementById('crm-contacts-bulk-tag').value;
            if (action === 'delete' && !confirm('Delete ' + selectedIds.length + ' contacts? This cannot be undone.')) return;
            this.disabled = true;
            var self = this;
            crmAjax('bntm_crm_bulk_action_contacts', Object.assign({ action_type: action, contact_ids: selectedIds }, extra), function(err, res) {
                self.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Bulk action failed.', 'error'); return; }
                crmToast(res.data.message, 'success');
                selectedIds = [];
                updateBulkBar();
                loadContacts();
            });
        });

        document.getElementById('crm-contacts-bulk-clear').addEventListener('click', function() {
            selectedIds = [];
            document.querySelectorAll('.crm-contact-row-check').forEach(function(cb) { cb.checked = false; });
            document.getElementById('crm-contacts-select-all').checked = false;
            updateBulkBar();
        });

        // Filters
        document.getElementById('crm-contacts-apply-filters').addEventListener('click', function() { currentPage = 1; loadContacts(); });
        document.getElementById('crm-contacts-reset-filters').addEventListener('click', function() {
            document.getElementById('crm-contact-search').value          = '';
            document.getElementById('crm-contact-type-filter').value     = '';
            document.getElementById('crm-contact-status-filter').value   = '';
            document.getElementById('crm-contact-assigned-filter').value = '';
            document.getElementById('crm-contact-tag-filter').value      = '';
            currentPage = 1;
            loadContacts();
        });
        document.getElementById('crm-contact-search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { currentPage = 1; loadContacts(); }
        });

        // Add / Save
        document.getElementById('crm-add-contact-btn').addEventListener('click', openAddContact);
        document.getElementById('crm-contact-save-btn').addEventListener('click', saveContact);

        // Delete confirm
        document.getElementById('crm-contact-confirm-delete-btn').addEventListener('click', function() {
            var id  = document.getElementById('crm-contact-delete-id').value;
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_delete_contact', { contact_id: id }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                crmToast('Contact deleted.', 'success');
                crmCloseModal('crm-contact-delete-modal');
                loadContacts();
            });
        });

        // Import
        document.getElementById('crm-contacts-import-btn').addEventListener('click', function() { crmOpenModal('crm-contact-import-modal'); });
        document.getElementById('crm-import-submit-btn').addEventListener('click', function() {
            var file = document.getElementById('crm-import-file').files[0];
            if (!file) { crmToast('Please select a CSV file.', 'error'); return; }
            var btn = this;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'bntm_crm_import_contacts');
            fd.append('nonce', bntm_crm_nonce);
            fd.append('csv_file', file);
            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    var el = document.getElementById('crm-import-result');
                    if (res.success) {
                        el.innerHTML = '<span style="color:#059669;font-weight:600;">' + res.data.message + '</span>';
                        loadContacts();
                    } else {
                        el.innerHTML = '<span style="color:#dc2626;">' + (res.data ? res.data.message : 'Import failed.') + '</span>';
                    }
                })
                .catch(function() { btn.disabled = false; crmToast('Import request failed.', 'error'); });
        });

        // Export
        document.getElementById('crm-contacts-export-btn').addEventListener('click', function() {
            var params = new URLSearchParams({
                action: 'bntm_crm_export_contacts',
                nonce:  bntm_crm_nonce,
                search: document.getElementById('crm-contact-search').value,
                status: document.getElementById('crm-contact-status-filter').value,
                type:   document.getElementById('crm-contact-type-filter').value,
            });
            window.location.href = ajaxurl + '?' + params.toString();
        });

        // Tag manager
        document.getElementById('crm-tag-manager-btn').addEventListener('click', function() { crmOpenModal('crm-tag-manager-modal'); });
        document.getElementById('crm-add-tag-btn').addEventListener('click', function() {
            var name   = document.getElementById('crm-new-tag-name').value.trim();
            var colour = document.getElementById('crm-new-tag-colour').value;
            if (!name) { crmToast('Tag name is required.', 'error'); return; }
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_tags', { tag_name: name, tag_colour: colour, tag_id: '' }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                crmToast('Tag added.', 'success');
                document.getElementById('crm-new-tag-name').value = '';
                var list = document.getElementById('crm-tags-list');
                var row  = document.createElement('div');
                row.className = 'crm-tag-manager-row';
                row.dataset.tagId = res.data.tag_id;
                row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f3f4f6;';
                row.innerHTML = '<span class="crm-tag-chip" style="background:' + colour + ';">' + escHtml(name) + '</span>' +
                    '<span style="flex:1;font-size:13px;color:#374151;">' + escHtml(name) + '</span>' +
                    '<button class="bntm-btn-danger bntm-btn-small crm-delete-tag-btn" data-id="' + res.data.tag_id + '">Delete</button>';
                list.appendChild(row);
                bindDeleteTagBtns();
            });
        });

        function bindDeleteTagBtns() {
            document.querySelectorAll('.crm-delete-tag-btn').forEach(function(btn) {
                btn.onclick = function() {
                    var id  = this.dataset.id;
                    var row = this.closest('.crm-tag-manager-row');
                    if (!confirm('Delete this tag?')) return;
                    crmAjax('bntm_crm_save_tags', { tag_id: id, delete_tag: 1 }, function(err, res) {
                        if (err || !res.success) { crmToast('Failed to delete tag.', 'error'); return; }
                        if (row) row.remove();
                        crmToast('Tag deleted.', 'success');
                    });
                };
            });
        }
        bindDeleteTagBtns();

        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }

        loadContacts();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// CONTACT PROFILE PAGE
// =============================================================================

function crm_contact_profile_page($contact_id, $business_id) {
    global $wpdb;

    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE id = %d AND deleted_at IS NULL",
        $contact_id
    ));

    if (!$contact) {
        return '<div class="bntm-notice bntm-notice-error">Contact not found.</div>';
    }

    $tags_table  = $wpdb->prefix . 'bntm_crm_tags';
    $ctags_table = $wpdb->prefix . 'bntm_crm_contact_tags';
    $deals_table = $wpdb->prefix . 'bntm_crm_deals';
    $stages_table = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $cf_table    = $wpdb->prefix . 'bntm_crm_custom_fields';
    $meta_table  = $wpdb->prefix . 'bntm_crm_contact_meta';
    $users       = get_users(['fields' => ['ID', 'display_name']]);

    $contact_tags = $wpdb->get_results($wpdb->prepare(
        "SELECT t.* FROM {$tags_table} t
         INNER JOIN {$ctags_table} ct ON ct.tag_id = t.id
         WHERE ct.contact_id = %d",
        $contact_id
    ));

    $linked_deals = $wpdb->get_results($wpdb->prepare(
        "SELECT d.*, ps.name AS stage_name, ps.colour AS stage_colour
         FROM {$deals_table} d
         LEFT JOIN {$stages_table} ps ON ps.id = d.stage_id
         WHERE d.contact_id = %d AND d.deleted_at IS NULL
         ORDER BY d.created_at DESC",
        $contact_id
    ));

    $custom_fields = $wpdb->get_results(
        "SELECT * FROM {$cf_table} WHERE object_type = 'contact' ORDER BY sort_order ASC"
    );

    $all_tags = $wpdb->get_results("SELECT * FROM {$tags_table} ORDER BY name ASC");

    $assigned_user = $contact->assigned_user_id ? get_user_by('ID', $contact->assigned_user_id) : null;
    $initials = strtoupper(substr($contact->first_name ?? '', 0, 1) . substr($contact->last_name ?? '', 0, 1));
    $full_name = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));

    ob_start();
    ?>
    <!-- Back link -->
    <div style="margin-bottom:16px;">
        <a href="?tab=contacts" style="font-size:13px;color:var(--bntm-primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Contacts
        </a>
    </div>

    <!-- Header Bar -->
    <div class="bntm-form-section" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div class="crm-avatar" style="width:56px;height:56px;font-size:18px;">
                <?php echo esc_html($initials ?: '?'); ?>
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <h2 style="margin:0;font-size:20px;font-weight:700;color:#111827;"><?php echo esc_html($full_name ?: 'Unnamed Contact'); ?></h2>
                    <span class="bntm-badge bntm-badge-<?php echo esc_attr($contact->status); ?>"><?php echo esc_html(ucfirst($contact->status)); ?></span>
                </div>
                <div style="font-size:13px;color:#6b7280;margin-top:4px;display:flex;gap:16px;flex-wrap:wrap;">
                    <?php if ($assigned_user): ?>
                    <span>Assigned to: <strong><?php echo esc_html($assigned_user->display_name); ?></strong></span>
                    <?php endif; ?>
                    <?php if ($contact->source): ?>
                    <span>Source: <strong><?php echo esc_html($contact->source); ?></strong></span>
                    <?php endif; ?>
                    <?php if ($contact->company): ?>
                    <span><?php echo esc_html($contact->company); ?><?php if ($contact->job_title): ?> &mdash; <?php echo esc_html($contact->job_title); ?><?php endif; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-profile-edit-btn" data-id="<?php echo intval($contact->id); ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </button>
                <button class="bntm-btn-danger bntm-btn-small" id="crm-profile-delete-btn" data-id="<?php echo intval($contact->id); ?>">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Two-column layout -->
    <div style="display:grid;grid-template-columns:2fr 3fr;gap:20px;" class="crm-profile-grid">

        <!-- LEFT COLUMN -->
        <div>

            <!-- Contact Details -->
            <div class="bntm-form-section">
                <h3>Contact Details</h3>
                <?php
                $detail_fields = [
                    'Email'        => $contact->email,
                    'Phone'        => $contact->phone,
                    'Mobile'       => $contact->mobile,
                    'Company'      => $contact->company,
                    'Job Title'    => $contact->job_title,
                    'Address'      => implode(', ', array_filter([
                        $contact->address_line_1,
                        $contact->address_line_2,
                        $contact->city,
                        $contact->state,
                        $contact->postcode,
                        $contact->country,
                    ])),
                    'Website'      => $contact->website,
                    'Created'      => $contact->created_at ? date('M j, Y', strtotime($contact->created_at)) : '',
                    'Opt-Out'      => $contact->email_opt_out ? 'Yes' : 'No',
                ];
                foreach ($detail_fields as $label => $value):
                    if (!$value) continue;
                ?>
                <div class="crm-detail-field">
                    <label><?php echo esc_html($label); ?></label>
                    <?php if ($label === 'Website'): ?>
                    <span><a href="<?php echo esc_url($value); ?>" target="_blank" style="color:var(--bntm-primary);"><?php echo esc_html($value); ?></a></span>
                    <?php elseif ($label === 'Email'): ?>
                    <span><a href="mailto:<?php echo esc_attr($value); ?>" style="color:var(--bntm-primary);"><?php echo esc_html($value); ?></a></span>
                    <?php else: ?>
                    <span><?php echo esc_html($value); ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($contact_tags)): ?>
                <div class="crm-detail-field">
                    <label>Tags</label>
                    <div>
                        <?php foreach ($contact_tags as $tag): ?>
                        <span class="crm-tag-chip" style="background:<?php echo esc_attr($tag->colour); ?>;"><?php echo esc_html($tag->name); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Linked Deals -->
            <div class="bntm-form-section">
                <div class="bntm-section-header">
                    <h3>Linked Deals</h3>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-profile-add-deal-btn">+ Add Deal</button>
                </div>
                <?php if (!empty($linked_deals)): ?>
                <?php foreach ($linked_deals as $deal): ?>
                <div style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                    <div style="font-size:14px;font-weight:600;color:#111827;margin-bottom:4px;"><?php echo esc_html($deal->title); ?></div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="bntm-badge" style="background:<?php echo esc_attr($deal->stage_colour ?? '#e5e7eb'); ?>;color:#fff;"><?php echo esc_html($deal->stage_name ?? ''); ?></span>
                        <span style="font-size:13px;font-weight:600;color:#059669;"><?php echo crm_format_price($deal->value); ?></span>
                        <?php if ($deal->close_date): ?>
                        <span style="font-size:12px;color:#9ca3af;">Close: <?php echo esc_html(date('M j, Y', strtotime($deal->close_date))); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="crm-empty-state" style="padding:20px 0;">
                    <p>No deals linked yet.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Custom Fields -->
            <?php if (!empty($custom_fields)): ?>
            <div class="bntm-form-section">
                <h3>Custom Fields</h3>
                <?php foreach ($custom_fields as $cf):
                    $meta_val = $wpdb->get_var($wpdb->prepare(
                        "SELECT meta_value FROM {$meta_table} WHERE contact_id = %d AND meta_key = %s",
                        $contact_id, $cf->field_key
                    ));
                    if (!$meta_val) continue;
                ?>
                <div class="crm-detail-field">
                    <label><?php echo esc_html($cf->field_label); ?></label>
                    <span><?php echo esc_html($meta_val); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT COLUMN -->
        <div>

            <!-- Activity Timeline -->
            <div class="bntm-form-section">
                <div class="bntm-section-header">
                    <h3>Activity Timeline</h3>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-profile-log-activity-btn">+ Log Activity</button>
                </div>
                <div id="crm-profile-timeline">
                    <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Loading timeline…</div>
                </div>
            </div>

            <!-- Open Tasks -->
            <div class="bntm-form-section">
                <div class="bntm-section-header">
                    <h3>Open Tasks</h3>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-profile-add-task-btn">+ Add Task</button>
                </div>
                <div id="crm-profile-tasks">
                    <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Loading tasks…</div>
                </div>
            </div>

            <!-- Linked Files -->
            <div class="bntm-form-section">
                <div class="bntm-section-header">
                    <h3>Linked Files</h3>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-profile-upload-file-btn">+ Upload File</button>
                </div>
                <div id="crm-profile-files">
                    <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Loading files…</div>
                </div>
                <input type="file" id="crm-profile-file-input" style="display:none;" multiple>
            </div>

        </div>
    </div>

    <!-- Log Activity Modal -->
    <div class="crm-modal-overlay" id="crm-profile-activity-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Log Activity</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Activity Type</label>
                        <select id="crm-pa-type">
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="note">Note</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Date &amp; Time</label>
                        <input type="datetime-local" id="crm-pa-datetime">
                    </div>
                </div>
                <div class="form-row">
                    <label>Subject</label>
                    <input type="text" id="crm-pa-subject" placeholder="Brief subject">
                </div>
                <div class="form-row">
                    <label>Notes</label>
                    <textarea id="crm-pa-body" rows="4" placeholder="Detailed notes…"></textarea>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Outcome</label>
                        <input type="text" id="crm-pa-outcome" placeholder="e.g. Positive, Follow-up needed">
                    </div>
                    <div class="form-row">
                        <label>Duration (minutes)</label>
                        <input type="number" id="crm-pa-duration" min="0" placeholder="0">
                    </div>
                </div>
                <div class="form-row">
                    <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                        <input type="checkbox" id="crm-pa-visible">
                        Visible to customer in portal
                    </label>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-profile-activity-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-pa-save-btn">Log Activity</button>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="crm-modal-overlay" id="crm-profile-task-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Add Task</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div class="form-row">
                    <label>Task Title</label>
                    <input type="text" id="crm-pt-title" placeholder="Task title">
                </div>
                <div class="form-row">
                    <label>Description</label>
                    <textarea id="crm-pt-description" rows="3" placeholder="Optional description…"></textarea>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Priority</label>
                        <select id="crm-pt-priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Due Date</label>
                        <input type="datetime-local" id="crm-pt-due-date">
                    </div>
                </div>
                <div class="form-row">
                    <label>Assign To</label>
                    <select id="crm-pt-assigned">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-profile-task-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-pt-save-btn">Add Task</button>
            </div>
        </div>
    </div>

    <!-- Delete Contact Confirm -->
    <div class="crm-modal-overlay" id="crm-profile-delete-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3>Delete Contact</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:14px;color:#374151;margin:0;">Are you sure you want to permanently delete <strong><?php echo esc_html($full_name); ?></strong>? This cannot be undone.</p>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-profile-delete-modal')">Cancel</button>
                <button class="bntm-btn-danger" id="crm-profile-confirm-delete-btn" data-id="<?php echo intval($contact->id); ?>">Delete</button>
            </div>
        </div>
    </div>

    <style>
    @media (max-width: 900px) {
        .crm-profile-grid { grid-template-columns: 1fr !important; }
    }
    .crm-file-row {
        display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6;
    }
    .crm-file-row:last-child { border-bottom:none; }
    .crm-file-icon {
        width:34px;height:34px;border-radius:7px;background:#f3f4f6;
        display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#6b7280;
    }
    </style>

    <script>
    (function() {
        var contactId = <?php echo intval($contact_id); ?>;

        // Load timeline
        function loadTimeline() {
            crmAjax('bntm_crm_get_contact_timeline', { contact_id: contactId }, function(err, res) {
                var el = document.getElementById('crm-profile-timeline');
                if (err || !res.success || !res.data.length) {
                    el.innerHTML = '<div class="crm-empty-state" style="padding:20px 0;"><p>No activity logged yet.</p></div>';
                    return;
                }
                var html = '<ul class="crm-activity-feed">';
                res.data.forEach(function(a) {
                    html += '<li class="crm-activity-item">' +
                        '<div class="crm-activity-icon type-' + escHtml(a.type) + '">' + getActivityIcon(a.type) + '</div>' +
                        '<div class="crm-activity-body">' +
                            '<strong>' + escHtml(ucFirst(a.type.replace(/_/g,' '))) + '</strong>' +
                            (a.subject ? ' <span style="color:#6b7280;">&mdash; ' + escHtml(a.subject) + '</span>' : '') +
                            (a.body ? '<p style="margin:4px 0 0;font-size:13px;color:#374151;">' + escHtml(a.body) + '</p>' : '') +
                            '<p style="font-size:12px;color:#9ca3af;margin:3px 0 0;">' + escHtml(a.logged_by_name || '') + ' &mdash; ' + escHtml(a.logged_at || '') + '</p>' +
                        '</div>' +
                    '</li>';
                });
                html += '</ul>';
                el.innerHTML = html;
            });
        }

        // Load tasks
        function loadTasks() {
            crmAjax('bntm_crm_get_contact_tasks', { contact_id: contactId }, function(err, res) {
                var el = document.getElementById('crm-profile-tasks');
                if (err || !res.success || !res.data.length) {
                    el.innerHTML = '<div class="crm-empty-state" style="padding:20px 0;"><p>No open tasks.</p></div>';
                    return;
                }
                var html = '';
                res.data.forEach(function(t) {
                    var overdue = t.due_date && new Date(t.due_date) < new Date() && t.status !== 'done';
                    html += '<div class="crm-task-item">' +
                        '<input type="checkbox" class="crm-task-checkbox crm-profile-complete-task" data-id="' + t.id + '" ' + (t.status === 'done' ? 'checked' : '') + '>' +
                        '<div class="crm-task-body">' +
                            '<div class="crm-task-title' + (t.status === 'done' ? ' done' : '') + '">' + escHtml(t.title) + '</div>' +
                            '<div class="crm-task-meta">' +
                                '<span class="bntm-badge bntm-badge-' + escHtml(t.priority) + '">' + escHtml(ucFirst(t.priority)) + '</span>' +
                                (t.due_date ? '<span class="' + (overdue ? 'crm-task-overdue' : '') + '">' + (overdue ? 'Overdue: ' : '') + formatDate(t.due_date) + '</span>' : '') +
                            '</div>' +
                        '</div>' +
                    '</div>';
                });
                el.innerHTML = html;
                el.querySelectorAll('.crm-profile-complete-task').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        var tid = this.dataset.id;
                        crmAjax('bntm_crm_complete_task', { task_id: tid }, function(err, res) {
                            if (err || !res.success) { crmToast('Failed.', 'error'); return; }
                            crmToast('Task updated.', 'success');
                            loadTasks();
                        });
                    });
                });
            });
        }

        // Load files
        function loadFiles() {
            crmAjax('bntm_crm_get_contact_files', { contact_id: contactId }, function(err, res) {
                var el = document.getElementById('crm-profile-files');
                if (err || !res.success || !res.data.length) {
                    el.innerHTML = '<div class="crm-empty-state" style="padding:20px 0;"><p>No files uploaded yet.</p></div>';
                    return;
                }
                var html = '';
                res.data.forEach(function(f) {
                    html += '<div class="crm-file-row">' +
                        '<div class="crm-file-icon">' +
                            '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' +
                        '</div>' +
                        '<div style="flex:1;">' +
                            '<div style="font-size:14px;font-weight:500;color:#111827;">' + escHtml(f.file_name) + '</div>' +
                            '<div style="font-size:12px;color:#9ca3af;">' + escHtml(formatBytes(f.file_size)) + ' &mdash; ' + escHtml(f.created_at ? f.created_at.substring(0,10) : '') + '</div>' +
                        '</div>' +
                        '<a href="' + escHtml(f.file_url || '#') + '" target="_blank" class="bntm-btn-secondary bntm-btn-small" style="text-decoration:none;">Download</a>' +
                        '<button class="bntm-btn-danger bntm-btn-small crm-delete-file-btn" data-id="' + f.id + '">Delete</button>' +
                    '</div>';
                });
                el.innerHTML = html;
                el.querySelectorAll('.crm-delete-file-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var fid = this.dataset.id;
                        if (!confirm('Delete this file?')) return;
                        crmAjax('bntm_crm_delete_contact_file', { file_id: fid }, function(err, res) {
                            if (err || !res.success) { crmToast('Delete failed.', 'error'); return; }
                            crmToast('File deleted.', 'success');
                            loadFiles();
                        });
                    });
                });
            });
        }

        // Log activity
        document.getElementById('crm-profile-log-activity-btn').addEventListener('click', function() {
            var now = new Date();
            var pad = function(n) { return n < 10 ? '0' + n : n; };
            document.getElementById('crm-pa-datetime').value = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            crmOpenModal('crm-profile-activity-modal');
        });

        document.getElementById('crm-pa-save-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_activity', {
                type:             document.getElementById('crm-pa-type').value,
                contact_id:       contactId,
                subject:          document.getElementById('crm-pa-subject').value,
                body:             document.getElementById('crm-pa-body').value,
                outcome:          document.getElementById('crm-pa-outcome').value,
                duration_minutes: document.getElementById('crm-pa-duration').value,
                scheduled_at:     document.getElementById('crm-pa-datetime').value,
                visible_to_customer: document.getElementById('crm-pa-visible').checked ? 1 : 0,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                crmToast('Activity logged.', 'success');
                crmCloseModal('crm-profile-activity-modal');
                loadTimeline();
            });
        });

        // Add task
        document.getElementById('crm-profile-add-task-btn').addEventListener('click', function() {
            crmOpenModal('crm-profile-task-modal');
        });

        document.getElementById('crm-pt-save-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_task', {
                title:            document.getElementById('crm-pt-title').value,
                description:      document.getElementById('crm-pt-description').value,
                priority:         document.getElementById('crm-pt-priority').value,
                due_date:         document.getElementById('crm-pt-due-date').value,
                assigned_user_id: document.getElementById('crm-pt-assigned').value,
                contact_id:       contactId,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                crmToast('Task added.', 'success');
                crmCloseModal('crm-profile-task-modal');
                loadTasks();
            });
        });

        // Upload file
        document.getElementById('crm-profile-upload-file-btn').addEventListener('click', function() {
            document.getElementById('crm-profile-file-input').click();
        });

        document.getElementById('crm-profile-file-input').addEventListener('change', function() {
            var files = this.files;
            if (!files.length) return;
            var fd = new FormData();
            fd.append('action', 'bntm_crm_upload_contact_file');
            fd.append('nonce', bntm_crm_nonce);
            fd.append('contact_id', contactId);
            for (var i = 0; i < files.length; i++) { fd.append('files[]', files[i]); }
            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) { crmToast('File(s) uploaded.', 'success'); loadFiles(); }
                    else { crmToast(res.data ? res.data.message : 'Upload failed.', 'error'); }
                })
                .catch(function() { crmToast('Upload error.', 'error'); });
            this.value = '';
        });

        // Add deal
        document.getElementById('crm-profile-add-deal-btn').addEventListener('click', function() {
            window.location.href = '?tab=pipeline&new_deal_contact=' + contactId;
        });

        // Delete contact
        document.getElementById('crm-profile-delete-btn').addEventListener('click', function() {
            crmOpenModal('crm-profile-delete-modal');
        });
        document.getElementById('crm-profile-confirm-delete-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_delete_contact', { contact_id: contactId }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                crmToast('Contact deleted.', 'success');
                window.location.href = '?tab=contacts';
            });
        });

        function getActivityIcon(type) {
            var icons = {
                call:    '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
                email:   '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                meeting: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                note:    '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
            };
            return icons[type] || icons.note;
        }

        function formatDate(dt) {
            if (!dt) return '';
            var d = new Date(dt);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function formatBytes(bytes) {
            if (!bytes) return '0 B';
            var k = 1024, sizes = ['B','KB','MB','GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function escHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }

        loadTimeline();
        loadTasks();
        loadFiles();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 3 — PIPELINE TAB
// =============================================================================

function crm_pipeline_tab($business_id) {
    global $wpdb;

    $pipelines_table = $wpdb->prefix . 'bntm_crm_pipelines';
    $users           = get_users(['fields' => ['ID', 'display_name']]);
    $pipelines       = $wpdb->get_results("SELECT * FROM {$pipelines_table} ORDER BY sort_order ASC");
    $default_pipeline = null;
    foreach ($pipelines as $pl) {
        if ($pl->is_default) { $default_pipeline = $pl; break; }
    }
    if (!$default_pipeline && !empty($pipelines)) {
        $default_pipeline = $pipelines[0];
    }
    $default_pipeline_id = $default_pipeline ? intval($default_pipeline->id) : 0;

    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $contacts       = $wpdb->get_results(
        "SELECT id, first_name, last_name, company FROM {$contacts_table} WHERE deleted_at IS NULL ORDER BY first_name ASC"
    );

    ob_start();
    ?>
    <div class="bntm-form-section" style="margin-bottom:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <label style="font-size:13px;color:#6b7280;margin:0;">Pipeline:</label>
                <select id="crm-pipeline-selector" style="width:auto;min-width:180px;">
                    <?php foreach ($pipelines as $pl): ?>
                    <option value="<?php echo intval($pl->id); ?>" <?php selected($pl->id, $default_pipeline_id); ?>>
                        <?php echo esc_html($pl->name); ?>
                    </option>
                    <?php endforeach; ?>
                    <?php if (empty($pipelines)): ?>
                    <option value="">No pipelines — create one in Settings</option>
                    <?php endif; ?>
                </select>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <!-- Filters -->
                <select id="crm-pipeline-filter-owner" style="width:auto;min-width:150px;">
                    <option value="">All Owners</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="crm-pipeline-filter-date-from" style="width:auto;" placeholder="From date">
                <input type="date" id="crm-pipeline-filter-date-to" style="width:auto;" placeholder="To date">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-pipeline-apply-filters">Filter</button>
                <button class="bntm-btn-primary bntm-btn-small" id="crm-add-deal-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Deal
                </button>
            </div>
        </div>
    </div>

    <!-- Kanban board -->
    <div id="crm-kanban-board" class="crm-kanban-board">
        <div style="display:flex;align-items:center;justify-content:center;width:100%;color:#9ca3af;font-size:14px;padding:40px;">
            Loading pipeline…
        </div>
    </div>

    <!-- Deal detail slide panel -->
    <div class="crm-detail-panel" id="crm-deal-panel">
        <div class="crm-detail-panel-header">
            <h3 id="crm-deal-panel-title">Deal Details</h3>
            <button onclick="crmClosePanel('crm-deal-panel')" style="background:none;border:none;cursor:pointer;color:#9ca3af;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="crm-detail-panel-body" id="crm-deal-panel-body">
            <div style="text-align:center;padding:40px;color:#9ca3af;">Select a deal to view details.</div>
        </div>
    </div>

    <!-- Add / Edit Deal Modal -->
    <div class="crm-modal-overlay" id="crm-deal-modal">
        <div class="crm-modal crm-modal-lg">
            <div class="crm-modal-header">
                <h3 id="crm-deal-modal-title">Add Deal</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-deal-id" value="">
                <div class="form-row">
                    <label>Deal Title <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="crm-deal-title" placeholder="e.g. Website Redesign Project">
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Contact <span style="color:#dc2626;">*</span></label>
                        <select id="crm-deal-contact">
                            <option value="">Select contact…</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo intval($c->id); ?>">
                                <?php echo esc_html(trim($c->first_name . ' ' . $c->last_name) . ($c->company ? ' (' . $c->company . ')' : '')); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Pipeline <span style="color:#dc2626;">*</span></label>
                        <select id="crm-deal-pipeline">
                            <?php foreach ($pipelines as $pl): ?>
                            <option value="<?php echo intval($pl->id); ?>"><?php echo esc_html($pl->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Stage <span style="color:#dc2626;">*</span></label>
                        <select id="crm-deal-stage">
                            <option value="">Select pipeline first…</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Assigned To</label>
                        <select id="crm-deal-assigned">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Value</label>
                        <input type="number" id="crm-deal-value" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-row">
                        <label>Currency</label>
                        <select id="crm-deal-currency">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="PHP">PHP</option>
                            <option value="AED">AED</option>
                            <option value="AUD">AUD</option>
                            <option value="CAD">CAD</option>
                            <option value="SGD">SGD</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Probability (%)</label>
                        <input type="number" id="crm-deal-probability" min="0" max="100" placeholder="0">
                    </div>
                    <div class="form-row">
                        <label>Close Date</label>
                        <input type="date" id="crm-deal-close-date">
                    </div>
                </div>
                <div class="form-row">
                    <label>Source</label>
                    <input type="text" id="crm-deal-source" placeholder="e.g. Referral, Web Form">
                </div>

                <!-- Line Items -->
                <div style="margin-top:8px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <label style="margin:0;font-size:14px;font-weight:600;color:#111827;">Line Items</label>
                        <button type="button" class="bntm-btn-secondary bntm-btn-small" id="crm-add-line-item-btn">+ Add Line</button>
                    </div>
                    <div id="crm-line-items-container">
                        <div style="display:grid;grid-template-columns:1fr 80px 100px 36px;gap:8px;margin-bottom:6px;font-size:12px;color:#6b7280;font-weight:600;">
                            <span>Description</span><span>Qty</span><span>Unit Price</span><span></span>
                        </div>
                        <div id="crm-line-items-rows"></div>
                    </div>
                    <div style="text-align:right;font-size:14px;font-weight:700;color:#059669;margin-top:8px;">
                        Total: <span id="crm-line-items-total">0.00</span>
                    </div>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-deal-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-deal-save-btn">Save Deal</button>
            </div>
        </div>
    </div>

    <!-- Won / Lost Modal -->
    <div class="crm-modal-overlay" id="crm-deal-close-modal">
        <div class="crm-modal" style="max-width:440px;">
            <div class="crm-modal-header">
                <h3 id="crm-deal-close-modal-title">Close Deal</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-deal-close-id">
                <input type="hidden" id="crm-deal-close-outcome">
                <div id="crm-deal-close-reason-row" class="form-row" style="display:none;">
                    <label>Lost Reason</label>
                    <input type="text" id="crm-deal-close-reason" placeholder="Why was this deal lost?">
                </div>
                <p id="crm-deal-close-message" style="font-size:14px;color:#374151;margin:0;"></p>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-deal-close-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-deal-close-confirm-btn">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Delete Deal Modal -->
    <div class="crm-modal-overlay" id="crm-deal-delete-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3>Delete Deal</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:14px;color:#374151;margin:0;">Are you sure you want to delete this deal? This cannot be undone.</p>
                <input type="hidden" id="crm-deal-delete-id">
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-deal-delete-modal')">Cancel</button>
                <button class="bntm-btn-danger" id="crm-deal-confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <style>
    #crm-kanban-board::-webkit-scrollbar { height: 6px; }
    #crm-kanban-board::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    #crm-kanban-board::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .crm-line-item-row {
        display: grid;
        grid-template-columns: 1fr 80px 100px 36px;
        gap: 8px;
        margin-bottom: 6px;
        align-items: center;
    }
    .crm-line-item-row input { margin: 0; }
    .crm-line-remove-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #dc2626;
        padding: 4px;
        border-radius: 4px;
    }
    .crm-line-remove-btn:hover { background: #fee2e2; }
    .crm-deal-panel-section { margin-bottom: 20px; }
    .crm-deal-panel-section h4 {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f3f4f6;
    }
    </style>

    <script>
    (function() {
        var currentPipelineId = <?php echo intval($default_pipeline_id); ?>;
        var stagesCache       = {};
        var dragSrcCard       = null;
        var dragSrcColId      = null;

        // ---- Load board ----
        function loadBoard() {
            var board = document.getElementById('crm-kanban-board');
            board.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;width:100%;color:#9ca3af;font-size:14px;padding:40px;">Loading pipeline…</div>';
            crmAjax('bntm_crm_get_pipeline_board', {
                pipeline_id: currentPipelineId,
                owner:       document.getElementById('crm-pipeline-filter-owner').value,
                date_from:   document.getElementById('crm-pipeline-filter-date-from').value,
                date_to:     document.getElementById('crm-pipeline-filter-date-to').value,
            }, function(err, res) {
                if (err || !res.success) {
                    board.innerHTML = '<div style="padding:40px;color:#dc2626;font-size:14px;">Failed to load pipeline board.</div>';
                    return;
                }
                renderBoard(res.data.stages);
                stagesCache[currentPipelineId] = res.data.stages.map(function(s) {
                    return { id: s.id, name: s.name };
                });
                populateDealStageSelect(currentPipelineId);
            });
        }

        function renderBoard(stages) {
            var board = document.getElementById('crm-kanban-board');
            if (!stages || stages.length === 0) {
                board.innerHTML = '<div style="padding:40px;color:#9ca3af;font-size:14px;">No stages configured. Add stages in the Settings tab.</div>';
                return;
            }
            board.innerHTML = '';
            stages.forEach(function(stage) {
                var col     = document.createElement('div');
                col.className = 'crm-kanban-column';
                col.dataset.stageId = stage.id;

                var totalVal = (stage.deals || []).reduce(function(sum, d) { return sum + parseFloat(d.value || 0); }, 0);

                col.innerHTML =
                    '<div class="crm-kanban-col-header">' +
                        '<div class="crm-kanban-col-title">' +
                            '<span class="crm-kanban-col-dot" style="background:' + escHtml(stage.colour || '#0d6efd') + ';"></span>' +
                            escHtml(stage.name) +
                        '</div>' +
                        '<div class="crm-kanban-col-meta">' +
                            (stage.deals ? stage.deals.length : 0) + ' &bull; ' + formatCurrency(totalVal) +
                        '</div>' +
                    '</div>' +
                    '<div class="crm-kanban-cards" data-stage-id="' + stage.id + '"></div>';

                board.appendChild(col);

                var cardsEl = col.querySelector('.crm-kanban-cards');
                bindColumnDrop(cardsEl, stage.id);

                if (stage.deals && stage.deals.length) {
                    stage.deals.forEach(function(deal) {
                        cardsEl.appendChild(buildDealCard(deal));
                    });
                }
            });
        }

        function buildDealCard(deal) {
            var card = document.createElement('div');
            card.className  = 'crm-deal-card';
            card.dataset.dealId = deal.id;
            card.draggable  = true;

            var daysInStage = deal.stage_entered_at ? Math.floor((Date.now() - new Date(deal.stage_entered_at)) / 86400000) : 0;

            card.innerHTML =
                '<div class="crm-deal-card-title">' + escHtml(deal.title) + '</div>' +
                '<div class="crm-deal-card-contact">' +
                    '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' +
                    escHtml(deal.contact_name || '') +
                '</div>' +
                '<div class="crm-deal-card-meta">' +
                    '<span class="crm-deal-card-value">' + formatCurrency(deal.value) + '</span>' +
                    '<span style="color:#9ca3af;font-size:11px;">' + (daysInStage > 0 ? daysInStage + 'd' : 'Today') + '</span>' +
                '</div>' +
                (deal.close_date ? '<div style="font-size:11px;color:#9ca3af;margin-top:5px;">Close: ' + escHtml(deal.close_date) + '</div>' : '') +
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">' +
                    '<span style="font-size:11px;color:#9ca3af;">' + escHtml(deal.assigned_name || 'Unassigned') + '</span>' +
                    '<div style="display:flex;gap:4px;">' +
                        '<button class="bntm-btn-secondary bntm-btn-small crm-won-deal-btn" data-id="' + deal.id + '" style="font-size:11px;padding:2px 6px;" title="Mark Won">Won</button>' +
                        '<button class="bntm-btn-danger bntm-btn-small crm-lost-deal-btn" data-id="' + deal.id + '" style="font-size:11px;padding:2px 6px;" title="Mark Lost">Lost</button>' +
                    '</div>' +
                '</div>';

            card.addEventListener('click', function(e) {
                if (e.target.closest('button')) return;
                openDealPanel(deal.id);
            });

            card.querySelector('.crm-won-deal-btn').addEventListener('click', function(e) {
                e.stopPropagation();
                openCloseDeal(deal.id, 'won');
            });
            card.querySelector('.crm-lost-deal-btn').addEventListener('click', function(e) {
                e.stopPropagation();
                openCloseDeal(deal.id, 'lost');
            });

            card.addEventListener('dragstart', function(e) {
                dragSrcCard   = card;
                dragSrcColId  = card.closest('.crm-kanban-cards').dataset.stageId;
                card.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', deal.id);
            });
            card.addEventListener('dragend', function() {
                card.classList.remove('dragging');
            });

            return card;
        }

        function bindColumnDrop(cardsEl, stageId) {
            cardsEl.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                cardsEl.classList.add('drag-over');
            });
            cardsEl.addEventListener('dragleave', function() {
                cardsEl.classList.remove('drag-over');
            });
            cardsEl.addEventListener('drop', function(e) {
                e.preventDefault();
                cardsEl.classList.remove('drag-over');
                var dealId = parseInt(e.dataTransfer.getData('text/plain'));
                var fromStageId = parseInt(dragSrcColId);
                var toStageId   = parseInt(stageId);
                if (!dealId || fromStageId === toStageId) return;
                if (dragSrcCard) cardsEl.appendChild(dragSrcCard);
                crmAjax('bntm_crm_move_deal_stage', {
                    deal_id:      dealId,
                    stage_id:     toStageId,
                    pipeline_id:  currentPipelineId,
                }, function(err, res) {
                    if (err || !res.success) {
                        crmToast('Failed to move deal.', 'error');
                        loadBoard();
                        return;
                    }
                    crmToast('Deal moved.', 'success');
                    loadBoard();
                });
            });
        }

        // ---- Deal panel ----
        function openDealPanel(dealId) {
            var body = document.getElementById('crm-deal-panel-body');
            body.innerHTML = '<div style="text-align:center;padding:40px;color:#9ca3af;">Loading…</div>';
            crmOpenPanel('crm-deal-panel');
            crmAjax('bntm_crm_get_deal_detail', { deal_id: dealId }, function(err, res) {
                if (err || !res.success) {
                    body.innerHTML = '<div style="color:#dc2626;padding:20px;">Failed to load deal.</div>';
                    return;
                }
                var d = res.data;
                document.getElementById('crm-deal-panel-title').textContent = d.title;
                body.innerHTML =
                    '<div class="crm-deal-panel-section">' +
                        '<h4>Deal Info</h4>' +
                        detailField('Value', formatCurrency(d.value) + ' ' + escHtml(d.currency || '')) +
                        detailField('Stage', escHtml(d.stage_name || '')) +
                        detailField('Status', escHtml(ucFirst(d.status || ''))) +
                        detailField('Probability', (d.probability || 0) + '%') +
                        detailField('Close Date', escHtml(d.close_date || '—')) +
                        detailField('Source', escHtml(d.source || '—')) +
                        detailField('Assigned To', escHtml(d.assigned_name || 'Unassigned')) +
                        (d.lost_reason ? detailField('Lost Reason', escHtml(d.lost_reason)) : '') +
                    '</div>' +
                    '<div class="crm-deal-panel-section">' +
                        '<h4>Contact</h4>' +
                        '<a href="?tab=contacts&contact_id=' + escHtml(d.contact_id) + '" class="crm-contact-link">' + escHtml(d.contact_name || '') + '</a>' +
                    '</div>' +
                    (d.line_items && d.line_items.length ?
                        '<div class="crm-deal-panel-section"><h4>Line Items</h4>' +
                        renderLineItemsReadonly(d.line_items) + '</div>' : '') +
                    '<div class="crm-deal-panel-section">' +
                        '<h4>Recent Activity</h4>' +
                        renderPanelActivities(d.activities || []) +
                    '</div>' +
                    '<div class="crm-deal-panel-section">' +
                        '<h4>Attached Files</h4>' +
                        renderPanelFiles(d.files || []) +
                    '</div>' +
                    '<div style="display:flex;gap:8px;margin-top:12px;">' +
                        '<button class="bntm-btn-secondary bntm-btn-small" id="crm-panel-edit-deal-btn" data-id="' + d.id + '">Edit Deal</button>' +
                        '<button class="bntm-btn-danger bntm-btn-small" id="crm-panel-delete-deal-btn" data-id="' + d.id + '">Delete</button>' +
                    '</div>';

                document.getElementById('crm-panel-edit-deal-btn').addEventListener('click', function() {
                    crmClosePanel('crm-deal-panel');
                    openEditDeal(parseInt(this.dataset.id));
                });
                document.getElementById('crm-panel-delete-deal-btn').addEventListener('click', function() {
                    document.getElementById('crm-deal-delete-id').value = this.dataset.id;
                    crmClosePanel('crm-deal-panel');
                    crmOpenModal('crm-deal-delete-modal');
                });
            });
        }

        function detailField(label, value) {
            return '<div class="crm-detail-field"><label>' + escHtml(label) + '</label><span>' + value + '</span></div>';
        }

        function renderLineItemsReadonly(items) {
            var html = '<div style="font-size:13px;">';
            var total = 0;
            items.forEach(function(item) {
                var lineTotal = parseFloat(item.quantity || 1) * parseFloat(item.unit_price || 0);
                total += lineTotal;
                html += '<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f3f4f6;">' +
                    '<span style="color:#374151;">' + escHtml(item.description) + ' x' + escHtml(String(item.quantity)) + '</span>' +
                    '<span style="font-weight:600;color:#111827;">' + formatCurrency(lineTotal) + '</span>' +
                '</div>';
            });
            html += '<div style="text-align:right;font-weight:700;color:#059669;margin-top:8px;">Total: ' + formatCurrency(total) + '</div>';
            html += '</div>';
            return html;
        }

        function renderPanelActivities(activities) {
            if (!activities.length) return '<p style="font-size:13px;color:#9ca3af;">No activity logged.</p>';
            var html = '<ul class="crm-activity-feed" style="margin:0;padding:0;">';
            activities.slice(0, 5).forEach(function(a) {
                html += '<li class="crm-activity-item">' +
                    '<div class="crm-activity-icon type-' + escHtml(a.type) + '" style="width:28px;height:28px;">' +
                        '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>' +
                    '</div>' +
                    '<div class="crm-activity-body">' +
                        '<strong style="font-size:13px;">' + escHtml(ucFirst(a.type)) + '</strong>' +
                        (a.subject ? ' — ' + escHtml(a.subject) : '') +
                        '<p style="font-size:11px;color:#9ca3af;margin:2px 0 0;">' + escHtml(a.logged_at ? a.logged_at.substring(0,10) : '') + '</p>' +
                    '</div>' +
                '</li>';
            });
            html += '</ul>';
            return html;
        }

        function renderPanelFiles(files) {
            if (!files.length) return '<p style="font-size:13px;color:#9ca3af;">No files attached.</p>';
            var html = '';
            files.forEach(function(f) {
                html += '<div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f3f4f6;">' +
                    '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>' +
                    '<span style="flex:1;font-size:13px;color:#374151;">' + escHtml(f.file_name) + '</span>' +
                    '<a href="' + escHtml(f.file_url || '#') + '" target="_blank" style="font-size:12px;color:var(--bntm-primary);">Download</a>' +
                '</div>';
            });
            return html;
        }

        // ---- Add / Edit deal ----
        function openAddDeal() {
            document.getElementById('crm-deal-modal-title').textContent = 'Add Deal';
            document.getElementById('crm-deal-id').value       = '';
            document.getElementById('crm-deal-title').value    = '';
            document.getElementById('crm-deal-contact').value  = '';
            document.getElementById('crm-deal-pipeline').value = currentPipelineId;
            document.getElementById('crm-deal-value').value    = '';
            document.getElementById('crm-deal-currency').value = 'USD';
            document.getElementById('crm-deal-probability').value = '';
            document.getElementById('crm-deal-close-date').value  = '';
            document.getElementById('crm-deal-source').value      = '';
            document.getElementById('crm-deal-assigned').value    = '';
            document.getElementById('crm-line-items-rows').innerHTML = '';
            updateLineTotal();
            loadStagesForPipeline(currentPipelineId, 0);
            crmOpenModal('crm-deal-modal');
        }

        function openEditDeal(dealId) {
            crmAjax('bntm_crm_get_deal_detail', { deal_id: dealId }, function(err, res) {
                if (err || !res.success) { crmToast('Failed to load deal.', 'error'); return; }
                var d = res.data;
                document.getElementById('crm-deal-modal-title').textContent = 'Edit Deal';
                document.getElementById('crm-deal-id').value         = d.id;
                document.getElementById('crm-deal-title').value       = d.title || '';
                document.getElementById('crm-deal-contact').value     = d.contact_id || '';
                document.getElementById('crm-deal-pipeline').value    = d.pipeline_id || currentPipelineId;
                document.getElementById('crm-deal-value').value       = d.value || '';
                document.getElementById('crm-deal-currency').value    = d.currency || 'USD';
                document.getElementById('crm-deal-probability').value = d.probability || '';
                document.getElementById('crm-deal-close-date').value  = d.close_date || '';
                document.getElementById('crm-deal-source').value      = d.source || '';
                document.getElementById('crm-deal-assigned').value    = d.assigned_user_id || '';
                document.getElementById('crm-line-items-rows').innerHTML = '';
                (d.line_items || []).forEach(function(li) { addLineItemRow(li.description, li.quantity, li.unit_price); });
                updateLineTotal();
                loadStagesForPipeline(d.pipeline_id, d.stage_id);
                crmOpenModal('crm-deal-modal');
            });
        }

        function loadStagesForPipeline(pipelineId, selectedStageId) {
            if (stagesCache[pipelineId]) {
                populateStageSelect(stagesCache[pipelineId], selectedStageId);
                return;
            }
            crmAjax('bntm_crm_get_pipeline_board', { pipeline_id: pipelineId }, function(err, res) {
                if (err || !res.success) return;
                stagesCache[pipelineId] = (res.data.stages || []).map(function(s) { return { id: s.id, name: s.name }; });
                populateStageSelect(stagesCache[pipelineId], selectedStageId);
            });
        }

        function populateDealStageSelect(pipelineId) {
            if (stagesCache[pipelineId]) {
                populateStageSelect(stagesCache[pipelineId], 0);
            }
        }

        function populateStageSelect(stages, selectedId) {
            var sel = document.getElementById('crm-deal-stage');
            sel.innerHTML = '';
            stages.forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                if (parseInt(s.id) === parseInt(selectedId)) opt.selected = true;
                sel.appendChild(opt);
            });
        }

        document.getElementById('crm-deal-pipeline').addEventListener('change', function() {
            loadStagesForPipeline(this.value, 0);
        });

        // ---- Line items ----
        function addLineItemRow(desc, qty, price) {
            var row = document.createElement('div');
            row.className = 'crm-line-item-row';
            row.innerHTML =
                '<input type="text" class="li-desc" placeholder="Description" value="' + escHtml(desc || '') + '">' +
                '<input type="number" class="li-qty" min="0" step="0.01" placeholder="1" value="' + escHtml(String(qty || 1)) + '">' +
                '<input type="number" class="li-price" min="0" step="0.01" placeholder="0.00" value="' + escHtml(String(price || '')) + '">' +
                '<button type="button" class="crm-line-remove-btn">' +
                    '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>';
            row.querySelector('.crm-line-remove-btn').addEventListener('click', function() {
                row.remove();
                updateLineTotal();
            });
            row.querySelector('.li-qty').addEventListener('input', updateLineTotal);
            row.querySelector('.li-price').addEventListener('input', updateLineTotal);
            document.getElementById('crm-line-items-rows').appendChild(row);
        }

        function updateLineTotal() {
            var total = 0;
            document.querySelectorAll('.crm-line-item-row').forEach(function(row) {
                var qty   = parseFloat(row.querySelector('.li-qty').value) || 0;
                var price = parseFloat(row.querySelector('.li-price').value) || 0;
                total += qty * price;
            });
            document.getElementById('crm-line-items-total').textContent = total.toFixed(2);
        }

        document.getElementById('crm-add-line-item-btn').addEventListener('click', function() {
            addLineItemRow('', 1, '');
        });

        // ---- Save deal ----
        document.getElementById('crm-deal-save-btn').addEventListener('click', function() {
            var btn = this;
            if (!document.getElementById('crm-deal-title').value.trim()) {
                crmToast('Deal title is required.', 'error'); return;
            }
            if (!document.getElementById('crm-deal-contact').value) {
                crmToast('Please select a contact.', 'error'); return;
            }
            btn.disabled = true;
            var lineItems = [];
            document.querySelectorAll('.crm-line-item-row').forEach(function(row, idx) {
                lineItems.push({
                    description: row.querySelector('.li-desc').value,
                    quantity:    row.querySelector('.li-qty').value,
                    unit_price:  row.querySelector('.li-price').value,
                    sort_order:  idx,
                });
            });
            crmAjax('bntm_crm_save_deal', {
                deal_id:         document.getElementById('crm-deal-id').value,
                title:           document.getElementById('crm-deal-title').value,
                contact_id:      document.getElementById('crm-deal-contact').value,
                pipeline_id:     document.getElementById('crm-deal-pipeline').value,
                stage_id:        document.getElementById('crm-deal-stage').value,
                value:           document.getElementById('crm-deal-value').value,
                currency:        document.getElementById('crm-deal-currency').value,
                probability:     document.getElementById('crm-deal-probability').value,
                close_date:      document.getElementById('crm-deal-close-date').value,
                source:          document.getElementById('crm-deal-source').value,
                assigned_user_id: document.getElementById('crm-deal-assigned').value,
                line_items:      JSON.stringify(lineItems),
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Deal saved.', 'success');
                crmCloseModal('crm-deal-modal');
                loadBoard();
            });
        });

        // ---- Won / Lost ----
        function openCloseDeal(dealId, outcome) {
            document.getElementById('crm-deal-close-id').value      = dealId;
            document.getElementById('crm-deal-close-outcome').value  = outcome;
            var reasonRow = document.getElementById('crm-deal-close-reason-row');
            var msgEl     = document.getElementById('crm-deal-close-message');
            if (outcome === 'won') {
                document.getElementById('crm-deal-close-modal-title').textContent = 'Mark Deal as Won';
                msgEl.textContent = 'Congratulations! Mark this deal as Won?';
                reasonRow.style.display = 'none';
            } else {
                document.getElementById('crm-deal-close-modal-title').textContent = 'Mark Deal as Lost';
                msgEl.textContent = 'Mark this deal as Lost?';
                reasonRow.style.display = '';
                document.getElementById('crm-deal-close-reason').value = '';
            }
            crmOpenModal('crm-deal-close-modal');
        }

        document.getElementById('crm-deal-close-confirm-btn').addEventListener('click', function() {
            var btn     = this;
            var dealId  = document.getElementById('crm-deal-close-id').value;
            var outcome = document.getElementById('crm-deal-close-outcome').value;
            var reason  = document.getElementById('crm-deal-close-reason').value;
            btn.disabled = true;
            crmAjax('bntm_crm_close_deal', { deal_id: dealId, outcome: outcome, lost_reason: reason }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                crmToast('Deal marked as ' + outcome + '.', 'success');
                crmCloseModal('crm-deal-close-modal');
                loadBoard();
            });
        });

        // ---- Delete deal ----
        document.getElementById('crm-deal-confirm-delete-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_delete_deal', { deal_id: document.getElementById('crm-deal-delete-id').value }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                crmToast('Deal deleted.', 'success');
                crmCloseModal('crm-deal-delete-modal');
                loadBoard();
            });
        });

        // ---- Pipeline selector / filters ----
        document.getElementById('crm-pipeline-selector').addEventListener('change', function() {
            currentPipelineId = parseInt(this.value);
            loadBoard();
        });
        document.getElementById('crm-pipeline-apply-filters').addEventListener('click', loadBoard);
        document.getElementById('crm-add-deal-btn').addEventListener('click', openAddDeal);

        // Pre-fill contact if coming from contact profile
        var urlParams = new URLSearchParams(window.location.search);
        var preContact = urlParams.get('new_deal_contact');
        if (preContact) { openAddDeal(); document.getElementById('crm-deal-contact').value = preContact; }

        function formatCurrency(val) {
            return parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }

        loadBoard();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 4 — ACTIVITIES TAB
// =============================================================================

function crm_activities_tab($business_id) {
    global $wpdb;

    $users          = get_users(['fields' => ['ID', 'display_name']]);
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $contacts       = $wpdb->get_results(
        "SELECT id, first_name, last_name FROM {$contacts_table} WHERE deleted_at IS NULL ORDER BY first_name ASC"
    );
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';
    $deals          = $wpdb->get_results(
        "SELECT id, title FROM {$deals_table} WHERE deleted_at IS NULL AND status = 'open' ORDER BY title ASC"
    );

    ob_start();
    ?>
    <div class="bntm-form-section">
        <div class="bntm-section-header">
            <h3>Activities</h3>
            <button class="bntm-btn-primary bntm-btn-small" id="crm-log-activity-btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Log Activity
            </button>
        </div>

        <!-- Filters -->
        <div class="crm-filters-bar">
            <select id="crm-activity-type-filter" style="min-width:140px;">
                <option value="">All Types</option>
                <option value="call">Call</option>
                <option value="email">Email</option>
                <option value="meeting">Meeting</option>
                <option value="note">Note</option>
                <option value="task_completion">Task Completion</option>
            </select>
            <select id="crm-activity-user-filter" style="min-width:150px;">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="crm-activity-contact-filter" style="min-width:180px;">
                <option value="">All Contacts</option>
                <?php foreach ($contacts as $c): ?>
                <option value="<?php echo intval($c->id); ?>"><?php echo esc_html(trim($c->first_name . ' ' . $c->last_name)); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" id="crm-activity-date-from" style="width:auto;">
            <input type="date" id="crm-activity-date-to" style="width:auto;">
            <div class="filter-actions">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-activities-apply-filters">Filter</button>
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-activities-reset-filters">Reset</button>
            </div>
        </div>

        <!-- Overdue alert -->
        <div id="crm-overdue-alert" style="display:none;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:14px;font-size:13px;color:#dc2626;display:flex;align-items:center;gap:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span id="crm-overdue-text"></span>
        </div>

        <!-- Activities table -->
        <div class="bntm-table-wrapper">
            <table class="bntm-table">
                <thead>
                    <tr>
                        <th style="width:40px;">Type</th>
                        <th>Subject</th>
                        <th>Contact</th>
                        <th>Deal</th>
                        <th>Logged By</th>
                        <th>Date</th>
                        <th>Duration</th>
                        <th style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="crm-activities-tbody">
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:#9ca3af;">Loading activities…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="crm-pagination" id="crm-activities-pagination"></div>
    </div>

    <!-- Upcoming Scheduled -->
    <div class="bntm-form-section">
        <h3>Upcoming Scheduled Activities</h3>
        <div id="crm-upcoming-activities">
            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">Loading…</div>
        </div>
    </div>

    <!-- Log / Edit Activity Modal -->
    <div class="crm-modal-overlay" id="crm-activity-modal">
        <div class="crm-modal crm-modal-lg">
            <div class="crm-modal-header">
                <h3 id="crm-activity-modal-title">Log Activity</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-activity-id" value="">
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Activity Type <span style="color:#dc2626;">*</span></label>
                        <select id="crm-act-type">
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="note">Note</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Date &amp; Time</label>
                        <input type="datetime-local" id="crm-act-datetime">
                    </div>
                </div>
                <div class="form-row">
                    <label>Subject</label>
                    <input type="text" id="crm-act-subject" placeholder="Brief subject line">
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Contact</label>
                        <select id="crm-act-contact">
                            <option value="">No contact</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo intval($c->id); ?>"><?php echo esc_html(trim($c->first_name . ' ' . $c->last_name)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Deal</label>
                        <select id="crm-act-deal">
                            <option value="">No deal</option>
                            <?php foreach ($deals as $d): ?>
                            <option value="<?php echo intval($d->id); ?>"><?php echo esc_html($d->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label>Notes</label>
                    <textarea id="crm-act-body" rows="4" placeholder="Detailed notes…"></textarea>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Outcome</label>
                        <input type="text" id="crm-act-outcome" placeholder="e.g. Positive, No answer">
                    </div>
                    <div class="form-row">
                        <label>Duration (minutes)</label>
                        <input type="number" id="crm-act-duration" min="0" placeholder="0">
                    </div>
                </div>
                <div class="form-row">
                    <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                        <input type="checkbox" id="crm-act-visible">
                        Visible to customer in portal
                    </label>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-activity-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-activity-save-btn">Save Activity</button>
            </div>
        </div>
    </div>

    <!-- Delete Activity Modal -->
    <div class="crm-modal-overlay" id="crm-activity-delete-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3>Delete Activity</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:14px;color:#374151;margin:0;">Are you sure you want to delete this activity? This cannot be undone.</p>
                <input type="hidden" id="crm-activity-delete-id">
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-activity-delete-modal')">Cancel</button>
                <button class="bntm-btn-danger" id="crm-activity-confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var currentPage = 1;
        var perPage     = 20;

        function getFilters() {
            return {
                type:       document.getElementById('crm-activity-type-filter').value,
                user_id:    document.getElementById('crm-activity-user-filter').value,
                contact_id: document.getElementById('crm-activity-contact-filter').value,
                date_from:  document.getElementById('crm-activity-date-from').value,
                date_to:    document.getElementById('crm-activity-date-to').value,
                page:       currentPage,
                per_page:   perPage,
            };
        }

        function loadActivities() {
            var tbody = document.getElementById('crm-activities-tbody');
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9ca3af;">Loading…</td></tr>';
            crmAjax('bntm_crm_get_activities', getFilters(), function(err, res) {
                if (err || !res.success) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#dc2626;">Failed to load activities.</td></tr>';
                    return;
                }
                renderActivities(res.data.activities, res.data.total, res.data.overdue_count);
            });
        }

        function renderActivities(activities, total, overdueCount) {
            var tbody = document.getElementById('crm-activities-tbody');
            var overdueEl = document.getElementById('crm-overdue-alert');
            if (overdueCount > 0) {
                document.getElementById('crm-overdue-text').textContent = overdueCount + ' overdue scheduled activit' + (overdueCount === 1 ? 'y' : 'ies') + ' require attention.';
                overdueEl.style.display = 'flex';
            } else {
                overdueEl.style.display = 'none';
            }
            if (!activities || activities.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#9ca3af;">No activities found.</td></tr>';
                renderActPagination(0);
                return;
            }
            var typeColors = { call:'#dbeafe', email:'#fce7f3', meeting:'#d1fae5', note:'#fef9c3', task_completion:'#ede9fe', stage_change:'#f0fdf4', deal_view:'#f3f4f6', unsubscribe:'#fee2e2', callback_request:'#fff7ed' };
            var html = '';
            activities.forEach(function(a) {
                var bg = typeColors[a.type] || '#f3f4f6';
                html += '<tr>' +
                    '<td><span style="display:inline-flex;width:32px;height:32px;border-radius:50%;background:' + bg + ';align-items:center;justify-content:center;" title="' + escHtml(a.type) + '">' +
                        crm_activity_icon_svg(a.type) +
                    '</span></td>' +
                    '<td style="max-width:200px;"><strong style="font-size:13px;">' + escHtml(a.subject || ucFirst(a.type)) + '</strong>' +
                        (a.body ? '<div style="font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">' + escHtml(a.body) + '</div>' : '') +
                    '</td>' +
                    '<td>' + (a.contact_name ? '<a href="?tab=contacts&contact_id=' + a.contact_id + '" class="crm-contact-link" style="font-size:13px;">' + escHtml(a.contact_name) + '</a>' : '<span style="color:#9ca3af;font-size:13px;">—</span>') + '</td>' +
                    '<td style="font-size:13px;color:#374151;">' + escHtml(a.deal_title || '—') + '</td>' +
                    '<td style="font-size:13px;color:#374151;">' + escHtml(a.logged_by_name || '—') + '</td>' +
                    '<td style="font-size:13px;color:#374151;white-space:nowrap;">' + escHtml(a.logged_at ? a.logged_at.substring(0,16).replace('T',' ') : '') + '</td>' +
                    '<td style="font-size:13px;color:#374151;">' + (a.duration_minutes ? a.duration_minutes + ' min' : '—') + '</td>' +
                    '<td style="white-space:nowrap;">' +
                        '<button class="bntm-btn-secondary bntm-btn-small crm-edit-activity-btn" data-id="' + a.id + '" style="margin-right:4px;">Edit</button>' +
                        '<button class="bntm-btn-danger bntm-btn-small crm-delete-activity-btn" data-id="' + a.id + '">Delete</button>' +
                    '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
            bindActivityRowEvents();
            renderActPagination(total);
        }

        function renderActPagination(total) {
            var pages = Math.ceil(total / perPage);
            var el    = document.getElementById('crm-activities-pagination');
            if (pages <= 1) { el.innerHTML = '<span>' + total + ' activit' + (total !== 1 ? 'ies' : 'y') + '</span>'; return; }
            var btns = '<button ' + (currentPage === 1 ? 'disabled' : '') + ' id="crm-act-prev">&larr;</button>';
            for (var p = 1; p <= pages; p++) {
                btns += '<button class="' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            btns += '<button ' + (currentPage === pages ? 'disabled' : '') + ' id="crm-act-next">&rarr;</button>';
            el.innerHTML = '<span>' + total + ' activit' + (total !== 1 ? 'ies' : 'y') + '</span><div class="crm-pagination-btns">' + btns + '</div>';
            el.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() { currentPage = parseInt(this.dataset.page); loadActivities(); });
            });
            var prev = document.getElementById('crm-act-prev');
            var next = document.getElementById('crm-act-next');
            if (prev) prev.addEventListener('click', function() { currentPage--; loadActivities(); });
            if (next) next.addEventListener('click', function() { currentPage++; loadActivities(); });
        }

        function bindActivityRowEvents() {
            document.querySelectorAll('.crm-edit-activity-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { openEditActivity(parseInt(this.dataset.id)); });
            });
            document.querySelectorAll('.crm-delete-activity-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('crm-activity-delete-id').value = this.dataset.id;
                    crmOpenModal('crm-activity-delete-modal');
                });
            });
        }

        function loadUpcoming() {
            crmAjax('bntm_crm_get_activities', { upcoming: 1, per_page: 6 }, function(err, res) {
                var el = document.getElementById('crm-upcoming-activities');
                if (err || !res.success || !res.data.activities.length) {
                    el.innerHTML = '<div class="crm-empty-state" style="padding:20px 0;"><p>No upcoming scheduled activities.</p></div>';
                    return;
                }
                var html = '<ul class="crm-activity-feed">';
                res.data.activities.forEach(function(a) {
                    html += '<li class="crm-activity-item">' +
                        '<div class="crm-activity-icon type-' + escHtml(a.type) + '">' + crm_activity_icon_svg(a.type) + '</div>' +
                        '<div class="crm-activity-body">' +
                            '<strong>' + escHtml(ucFirst(a.type)) + '</strong>' +
                            (a.subject ? ' — ' + escHtml(a.subject) : '') +
                            '<p style="font-size:12px;color:#9ca3af;margin:3px 0 0;">' +
                                (a.contact_name ? escHtml(a.contact_name) + ' &mdash; ' : '') +
                                escHtml(a.scheduled_at ? a.scheduled_at.substring(0,16).replace('T',' ') : '') +
                            '</p>' +
                        '</div>' +
                    '</li>';
                });
                html += '</ul>';
                el.innerHTML = html;
            });
        }

        function openLogActivity() {
            document.getElementById('crm-activity-modal-title').textContent = 'Log Activity';
            document.getElementById('crm-activity-id').value  = '';
            document.getElementById('crm-act-type').value     = 'call';
            document.getElementById('crm-act-subject').value  = '';
            document.getElementById('crm-act-body').value     = '';
            document.getElementById('crm-act-outcome').value  = '';
            document.getElementById('crm-act-duration').value = '';
            document.getElementById('crm-act-contact').value  = '';
            document.getElementById('crm-act-deal').value     = '';
            document.getElementById('crm-act-visible').checked = false;
            var now = new Date();
            var pad = function(n) { return n < 10 ? '0' + n : n; };
            document.getElementById('crm-act-datetime').value = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
            crmOpenModal('crm-activity-modal');
        }

        function openEditActivity(id) {
            crmAjax('bntm_crm_get_activities', { activity_id: id }, function(err, res) {
                if (err || !res.success || !res.data.activities.length) { crmToast('Failed to load activity.', 'error'); return; }
                var a = res.data.activities[0];
                document.getElementById('crm-activity-modal-title').textContent = 'Edit Activity';
                document.getElementById('crm-activity-id').value   = a.id;
                document.getElementById('crm-act-type').value      = a.type || 'call';
                document.getElementById('crm-act-subject').value   = a.subject || '';
                document.getElementById('crm-act-body').value      = a.body || '';
                document.getElementById('crm-act-outcome').value   = a.outcome || '';
                document.getElementById('crm-act-duration').value  = a.duration_minutes || '';
                document.getElementById('crm-act-contact').value   = a.contact_id || '';
                document.getElementById('crm-act-deal').value      = a.deal_id || '';
                document.getElementById('crm-act-visible').checked = parseInt(a.visible_to_customer) === 1;
                document.getElementById('crm-act-datetime').value  = a.scheduled_at ? a.scheduled_at.substring(0,16) : '';
                crmOpenModal('crm-activity-modal');
            });
        }

        document.getElementById('crm-log-activity-btn').addEventListener('click', openLogActivity);

        document.getElementById('crm-activity-save-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_activity', {
                activity_id:      document.getElementById('crm-activity-id').value,
                type:             document.getElementById('crm-act-type').value,
                subject:          document.getElementById('crm-act-subject').value,
                body:             document.getElementById('crm-act-body').value,
                outcome:          document.getElementById('crm-act-outcome').value,
                duration_minutes: document.getElementById('crm-act-duration').value,
                contact_id:       document.getElementById('crm-act-contact').value,
                deal_id:          document.getElementById('crm-act-deal').value,
                scheduled_at:     document.getElementById('crm-act-datetime').value,
                visible_to_customer: document.getElementById('crm-act-visible').checked ? 1 : 0,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Activity saved.', 'success');
                crmCloseModal('crm-activity-modal');
                loadActivities();
                loadUpcoming();
            });
        });

        document.getElementById('crm-activity-confirm-delete-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_delete_activity', { activity_id: document.getElementById('crm-activity-delete-id').value }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                crmToast('Activity deleted.', 'success');
                crmCloseModal('crm-activity-delete-modal');
                loadActivities();
                loadUpcoming();
            });
        });

        document.getElementById('crm-activities-apply-filters').addEventListener('click', function() { currentPage = 1; loadActivities(); });
        document.getElementById('crm-activities-reset-filters').addEventListener('click', function() {
            document.getElementById('crm-activity-type-filter').value    = '';
            document.getElementById('crm-activity-user-filter').value    = '';
            document.getElementById('crm-activity-contact-filter').value = '';
            document.getElementById('crm-activity-date-from').value      = '';
            document.getElementById('crm-activity-date-to').value        = '';
            currentPage = 1;
            loadActivities();
        });

        function crm_activity_icon_svg(type) {
            var icons = {
                call:    '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
                email:   '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                meeting: '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
                note:    '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                task_completion: '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            };
            return icons[type] || icons.note;
        }

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }

        loadActivities();
        loadUpcoming();
    })();
    </script>
    <?php
    return ob_get_clean();
}

