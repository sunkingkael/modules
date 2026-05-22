<?php
/**
 * Module Name: Customer Relationship Management
 * Module Slug: crm
 * Description: Manage, track, and analyze current and potential customers — including contact profiles, interaction history, pipeline stages, tasks, and reporting — all from within the WordPress admin.
 * Version: 1.0.0
 * Author: BNTM
 * Icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
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

// =============================================================================
// TAB 5 — TASKS TAB
// =============================================================================

function crm_tasks_tab($business_id) {
    global $wpdb;

    $users          = get_users(['fields' => ['ID', 'display_name']]);
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $contacts       = $wpdb->get_results(
        "SELECT id, first_name, last_name FROM {$contacts_table} WHERE deleted_at IS NULL ORDER BY first_name ASC"
    );
    $deals_table = $wpdb->prefix . 'bntm_crm_deals';
    $deals       = $wpdb->get_results(
        "SELECT id, title FROM {$deals_table} WHERE deleted_at IS NULL AND status = 'open' ORDER BY title ASC"
    );
    $current_user_id = get_current_user_id();

    ob_start();
    ?>
    <div class="bntm-form-section">
        <div class="bntm-section-header">
            <h3>Tasks</h3>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <div style="display:flex;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;">
                    <button class="crm-task-scope-btn active" data-scope="my" style="padding:6px 14px;font-size:13px;border:none;cursor:pointer;background:#fff;color:#374151;transition:background 0.15s;">My Tasks</button>
                    <button class="crm-task-scope-btn" data-scope="all" style="padding:6px 14px;font-size:13px;border:none;cursor:pointer;background:#fff;color:#374151;border-left:1px solid #e5e7eb;transition:background 0.15s;">All Tasks</button>
                </div>
                <button class="bntm-btn-primary bntm-btn-small" id="crm-add-task-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Task
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="crm-filters-bar">
            <input type="text" id="crm-task-search" placeholder="Search tasks…" style="min-width:180px;">
            <select id="crm-task-status-filter" style="min-width:140px;">
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
            </select>
            <select id="crm-task-priority-filter" style="min-width:140px;">
                <option value="">All Priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
            <select id="crm-task-assigned-filter" style="min-width:160px;">
                <option value="">All Assignees</option>
                <?php foreach ($users as $u): ?>
                <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" id="crm-task-due-from" style="width:auto;">
            <input type="date" id="crm-task-due-to" style="width:auto;">
            <div class="filter-actions">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-tasks-apply-filters">Filter</button>
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-tasks-reset-filters">Reset</button>
            </div>
        </div>

        <!-- Bulk action bar -->
        <div class="crm-bulk-bar" id="crm-tasks-bulk-bar">
            <span id="crm-tasks-selected-count">0</span> selected
            <select id="crm-tasks-bulk-action" style="width:auto;min-width:160px;">
                <option value="">Bulk Action</option>
                <option value="complete">Mark Complete</option>
                <option value="delete">Delete</option>
            </select>
            <button class="bntm-btn-primary bntm-btn-small" id="crm-tasks-bulk-apply">Apply</button>
            <button class="bntm-btn-secondary bntm-btn-small" id="crm-tasks-bulk-clear">Clear</button>
        </div>

        <!-- Tasks table -->
        <div class="bntm-table-wrapper">
            <table class="bntm-table">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="crm-tasks-select-all"></th>
                        <th style="width:36px;"></th>
                        <th>Title</th>
                        <th>Contact</th>
                        <th>Deal</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="crm-tasks-tbody">
                    <tr><td colspan="10" style="text-align:center;padding:30px;color:#9ca3af;">Loading tasks…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="crm-pagination" id="crm-tasks-pagination"></div>
    </div>

    <!-- Add / Edit Task Modal -->
    <div class="crm-modal-overlay" id="crm-task-modal">
        <div class="crm-modal crm-modal-lg">
            <div class="crm-modal-header">
                <h3 id="crm-task-modal-title">Add Task</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-task-id" value="">
                <div class="form-row">
                    <label>Task Title <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="crm-task-title" placeholder="Task title">
                </div>
                <div class="form-row">
                    <label>Description</label>
                    <textarea id="crm-task-description" rows="3" placeholder="Optional description…"></textarea>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Priority</label>
                        <select id="crm-task-priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Status</label>
                        <select id="crm-task-status">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Due Date</label>
                        <input type="datetime-local" id="crm-task-due-date">
                    </div>
                    <div class="form-row">
                        <label>Assign To</label>
                        <select id="crm-task-assigned">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?php echo intval($u->ID); ?>" <?php selected($u->ID, $current_user_id); ?>>
                                <?php echo esc_html($u->display_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Linked Contact</label>
                        <select id="crm-task-contact">
                            <option value="">No contact</option>
                            <?php foreach ($contacts as $c): ?>
                            <option value="<?php echo intval($c->id); ?>">
                                <?php echo esc_html(trim($c->first_name . ' ' . $c->last_name)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Linked Deal</label>
                        <select id="crm-task-deal">
                            <option value="">No deal</option>
                            <?php foreach ($deals as $d): ?>
                            <option value="<?php echo intval($d->id); ?>"><?php echo esc_html($d->title); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-task-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-task-save-btn">Save Task</button>
            </div>
        </div>
    </div>

    <!-- Delete Task Modal -->
    <div class="crm-modal-overlay" id="crm-task-delete-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3>Delete Task</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <p style="font-size:14px;color:#374151;margin:0;">Are you sure you want to delete this task? This cannot be undone.</p>
                <input type="hidden" id="crm-task-delete-id">
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-task-delete-modal')">Cancel</button>
                <button class="bntm-btn-danger" id="crm-task-confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <style>
    .crm-task-scope-btn.active {
        background: var(--bntm-primary) !important;
        color: #fff !important;
    }
    .crm-priority-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        flex-shrink: 0;
    }
    </style>

    <script>
    (function() {
        var currentPage    = 1;
        var perPage        = 20;
        var taskScope      = 'my';
        var selectedIds    = [];
        var currentUserId  = <?php echo intval($current_user_id); ?>;

        var priorityColors = { low: '#22c55e', medium: '#f59e0b', high: '#f97316', urgent: '#dc2626' };

        function getFilters() {
            return {
                scope:       taskScope,
                search:      document.getElementById('crm-task-search').value,
                status:      document.getElementById('crm-task-status-filter').value,
                priority:    document.getElementById('crm-task-priority-filter').value,
                assigned_id: document.getElementById('crm-task-assigned-filter').value,
                due_from:    document.getElementById('crm-task-due-from').value,
                due_to:      document.getElementById('crm-task-due-to').value,
                page:        currentPage,
                per_page:    perPage,
            };
        }

        function loadTasks() {
            var tbody = document.getElementById('crm-tasks-tbody');
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:30px;color:#9ca3af;">Loading…</td></tr>';
            crmAjax('bntm_crm_get_tasks', getFilters(), function(err, res) {
                if (err || !res.success) {
                    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:30px;color:#dc2626;">Failed to load tasks.</td></tr>';
                    return;
                }
                renderTasks(res.data.tasks, res.data.total);
            });
        }

        function renderTasks(tasks, total) {
            var tbody = document.getElementById('crm-tasks-tbody');
            if (!tasks || tasks.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:30px;color:#9ca3af;">No tasks found.</td></tr>';
                renderTaskPagination(0);
                return;
            }
            var now  = new Date();
            var html = '';
            tasks.forEach(function(t) {
                var isOverdue   = t.due_date && new Date(t.due_date) < now && t.status !== 'done';
                var isDone      = t.status === 'done';
                var checked     = selectedIds.indexOf(t.id) > -1 ? 'checked' : '';
                var dueDateStr  = '';
                if (t.due_date) {
                    var d = new Date(t.due_date);
                    dueDateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                }
                html += '<tr style="' + (isDone ? 'opacity:0.6;' : '') + (isOverdue ? 'background:#fff7f7;' : '') + '">' +
                    '<td><input type="checkbox" class="crm-task-row-check" value="' + t.id + '" ' + checked + '></td>' +
                    '<td>' +
                        '<input type="checkbox" class="crm-task-complete-check" data-id="' + t.id + '" title="Mark complete" ' + (isDone ? 'checked' : '') + ' style="width:16px;height:16px;cursor:pointer;">' +
                    '</td>' +
                    '<td>' +
                        '<span style="font-size:14px;font-weight:500;color:#111827;' + (isDone ? 'text-decoration:line-through;color:#9ca3af;' : '') + '">' + escHtml(t.title) + '</span>' +
                        (t.description ? '<div style="font-size:12px;color:#9ca3af;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">' + escHtml(t.description) + '</div>' : '') +
                    '</td>' +
                    '<td>' +
                        (t.contact_name
                            ? '<a href="?tab=contacts&contact_id=' + t.contact_id + '" class="crm-contact-link" style="font-size:13px;">' + escHtml(t.contact_name) + '</a>'
                            : '<span style="color:#9ca3af;font-size:13px;">—</span>') +
                    '</td>' +
                    '<td style="font-size:13px;color:#374151;">' + escHtml(t.deal_title || '—') + '</td>' +
                    '<td>' +
                        '<span style="display:inline-flex;align-items:center;">' +
                            '<span class="crm-priority-dot" style="background:' + (priorityColors[t.priority] || '#9ca3af') + ';"></span>' +
                            '<span class="bntm-badge bntm-badge-' + escHtml(t.priority) + '">' + escHtml(ucFirst(t.priority)) + '</span>' +
                        '</span>' +
                    '</td>' +
                    '<td><span class="bntm-badge bntm-badge-' + escHtml(t.status) + '">' + escHtml(ucFirst(t.status.replace('_',' '))) + '</span></td>' +
                    '<td style="font-size:13px;color:#374151;">' + escHtml(t.assigned_name || 'Unassigned') + '</td>' +
                    '<td style="font-size:13px;white-space:nowrap;" class="' + (isOverdue ? 'crm-task-overdue' : 'color:#374151;') + '">' +
                        (isOverdue ? '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>' : '') +
                        escHtml(dueDateStr || '—') +
                    '</td>' +
                    '<td style="white-space:nowrap;">' +
                        '<button class="bntm-btn-secondary bntm-btn-small crm-edit-task-btn" data-id="' + t.id + '" style="margin-right:4px;">Edit</button>' +
                        '<button class="bntm-btn-danger bntm-btn-small crm-delete-task-btn" data-id="' + t.id + '">Delete</button>' +
                    '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
            bindTaskRowEvents();
            renderTaskPagination(total);
        }

        function renderTaskPagination(total) {
            var pages = Math.ceil(total / perPage);
            var el    = document.getElementById('crm-tasks-pagination');
            if (pages <= 1) { el.innerHTML = '<span>' + total + ' task' + (total !== 1 ? 's' : '') + '</span>'; return; }
            var btns = '<button ' + (currentPage === 1 ? 'disabled' : '') + ' id="crm-task-prev">&larr;</button>';
            for (var p = 1; p <= pages; p++) {
                btns += '<button class="' + (p === currentPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            btns += '<button ' + (currentPage === pages ? 'disabled' : '') + ' id="crm-task-next">&rarr;</button>';
            el.innerHTML = '<span>' + total + ' task' + (total !== 1 ? 's' : '') + '</span><div class="crm-pagination-btns">' + btns + '</div>';
            el.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() { currentPage = parseInt(this.dataset.page); loadTasks(); });
            });
            var prev = document.getElementById('crm-task-prev');
            var next = document.getElementById('crm-task-next');
            if (prev) prev.addEventListener('click', function() { currentPage--; loadTasks(); });
            if (next) next.addEventListener('click', function() { currentPage++; loadTasks(); });
        }

        function bindTaskRowEvents() {
            document.querySelectorAll('.crm-task-row-check').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var id = parseInt(this.value);
                    if (this.checked) { if (selectedIds.indexOf(id) === -1) selectedIds.push(id); }
                    else { selectedIds = selectedIds.filter(function(i) { return i !== id; }); }
                    updateTaskBulkBar();
                });
            });

            document.querySelectorAll('.crm-task-complete-check').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var taskId = this.dataset.id;
                    var self   = this;
                    self.disabled = true;
                    crmAjax('bntm_crm_complete_task', { task_id: taskId }, function(err, res) {
                        self.disabled = false;
                        if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); self.checked = !self.checked; return; }
                        crmToast('Task updated.', 'success');
                        loadTasks();
                    });
                });
            });

            document.querySelectorAll('.crm-edit-task-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { openEditTask(parseInt(this.dataset.id)); });
            });

            document.querySelectorAll('.crm-delete-task-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('crm-task-delete-id').value = this.dataset.id;
                    crmOpenModal('crm-task-delete-modal');
                });
            });
        }

        function updateTaskBulkBar() {
            var bar = document.getElementById('crm-tasks-bulk-bar');
            document.getElementById('crm-tasks-selected-count').textContent = selectedIds.length;
            bar.classList.toggle('visible', selectedIds.length > 0);
        }

        // ---- Scope toggle ----
        document.querySelectorAll('.crm-task-scope-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.crm-task-scope-btn').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                taskScope   = this.dataset.scope;
                currentPage = 1;
                loadTasks();
            });
        });

        // ---- Select all ----
        document.getElementById('crm-tasks-select-all').addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.crm-task-row-check').forEach(function(cb) {
                cb.checked = checked;
                var id = parseInt(cb.value);
                if (checked) { if (selectedIds.indexOf(id) === -1) selectedIds.push(id); }
                else { selectedIds = selectedIds.filter(function(i) { return i !== id; }); }
            });
            updateTaskBulkBar();
        });

        // ---- Bulk actions ----
        document.getElementById('crm-tasks-bulk-apply').addEventListener('click', function() {
            var action = document.getElementById('crm-tasks-bulk-action').value;
            if (!action || selectedIds.length === 0) { crmToast('Select tasks and a bulk action.', 'error'); return; }
            if (action === 'delete' && !confirm('Delete ' + selectedIds.length + ' task(s)? This cannot be undone.')) return;
            var btn  = this;
            btn.disabled = true;
            crmAjax('bntm_crm_bulk_action_tasks', { action_type: action, task_ids: selectedIds }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Bulk action failed.', 'error'); return; }
                crmToast(res.data.message, 'success');
                selectedIds = [];
                updateTaskBulkBar();
                document.getElementById('crm-tasks-select-all').checked = false;
                loadTasks();
            });
        });

        document.getElementById('crm-tasks-bulk-clear').addEventListener('click', function() {
            selectedIds = [];
            document.querySelectorAll('.crm-task-row-check').forEach(function(cb) { cb.checked = false; });
            document.getElementById('crm-tasks-select-all').checked = false;
            updateTaskBulkBar();
        });

        // ---- Filters ----
        document.getElementById('crm-tasks-apply-filters').addEventListener('click', function() { currentPage = 1; loadTasks(); });
        document.getElementById('crm-tasks-reset-filters').addEventListener('click', function() {
            document.getElementById('crm-task-search').value          = '';
            document.getElementById('crm-task-status-filter').value   = '';
            document.getElementById('crm-task-priority-filter').value = '';
            document.getElementById('crm-task-assigned-filter').value = '';
            document.getElementById('crm-task-due-from').value        = '';
            document.getElementById('crm-task-due-to').value          = '';
            currentPage = 1;
            loadTasks();
        });
        document.getElementById('crm-task-search').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { currentPage = 1; loadTasks(); }
        });

        // ---- Add / Edit task ----
        function openAddTask() {
            document.getElementById('crm-task-modal-title').textContent = 'Add Task';
            document.getElementById('crm-task-id').value          = '';
            document.getElementById('crm-task-title').value       = '';
            document.getElementById('crm-task-description').value = '';
            document.getElementById('crm-task-priority').value    = 'medium';
            document.getElementById('crm-task-status').value      = 'open';
            document.getElementById('crm-task-due-date').value    = '';
            document.getElementById('crm-task-assigned').value    = currentUserId;
            document.getElementById('crm-task-contact').value     = '';
            document.getElementById('crm-task-deal').value        = '';
            crmOpenModal('crm-task-modal');
        }

        function openEditTask(id) {
            crmAjax('bntm_crm_get_tasks', { task_id: id }, function(err, res) {
                if (err || !res.success || !res.data.tasks.length) { crmToast('Failed to load task.', 'error'); return; }
                var t = res.data.tasks[0];
                document.getElementById('crm-task-modal-title').textContent = 'Edit Task';
                document.getElementById('crm-task-id').value          = t.id;
                document.getElementById('crm-task-title').value       = t.title       || '';
                document.getElementById('crm-task-description').value = t.description || '';
                document.getElementById('crm-task-priority').value    = t.priority    || 'medium';
                document.getElementById('crm-task-status').value      = t.status      || 'open';
                document.getElementById('crm-task-due-date').value    = t.due_date    ? t.due_date.substring(0, 16) : '';
                document.getElementById('crm-task-assigned').value    = t.assigned_user_id || '';
                document.getElementById('crm-task-contact').value     = t.contact_id  || '';
                document.getElementById('crm-task-deal').value        = t.deal_id     || '';
                crmOpenModal('crm-task-modal');
            });
        }

        document.getElementById('crm-add-task-btn').addEventListener('click', openAddTask);

        document.getElementById('crm-task-save-btn').addEventListener('click', function() {
            var btn = this;
            if (!document.getElementById('crm-task-title').value.trim()) {
                crmToast('Task title is required.', 'error'); return;
            }
            btn.disabled = true;
            crmAjax('bntm_crm_save_task', {
                task_id:         document.getElementById('crm-task-id').value,
                title:           document.getElementById('crm-task-title').value,
                description:     document.getElementById('crm-task-description').value,
                priority:        document.getElementById('crm-task-priority').value,
                status:          document.getElementById('crm-task-status').value,
                due_date:        document.getElementById('crm-task-due-date').value,
                assigned_user_id: document.getElementById('crm-task-assigned').value,
                contact_id:      document.getElementById('crm-task-contact').value,
                deal_id:         document.getElementById('crm-task-deal').value,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Task saved.', 'success');
                crmCloseModal('crm-task-modal');
                loadTasks();
            });
        });

        // ---- Delete task ----
        document.getElementById('crm-task-confirm-delete-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_delete_task', { task_id: document.getElementById('crm-task-delete-id').value }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                crmToast('Task deleted.', 'success');
                crmCloseModal('crm-task-delete-modal');
                loadTasks();
            });
        });

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }

        loadTasks();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 6 — SETTINGS TAB
// =============================================================================

function crm_settings_tab($business_id) {
    global $wpdb;

    $users            = get_users(['fields' => ['ID', 'display_name']]);
    $pipelines_table  = $wpdb->prefix . 'bntm_crm_pipelines';
    $stages_table     = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $cf_table         = $wpdb->prefix . 'bntm_crm_custom_fields';
    $settings_table   = $wpdb->prefix . 'bntm_crm_settings';
    $audit_table      = $wpdb->prefix . 'bntm_crm_audit_log';

    $pipelines        = $wpdb->get_results("SELECT * FROM {$pipelines_table} ORDER BY sort_order ASC");
    $custom_fields    = $wpdb->get_results("SELECT * FROM {$cf_table} ORDER BY object_type ASC, sort_order ASC");

    $get_setting = function($key, $default = '') use ($wpdb, $settings_table) {
        $val = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", $key));
        return $val !== null ? $val : $default;
    };

    $sources      = json_decode($get_setting('crm_contact_sources', '["Web","Referral","Cold Call","Email Campaign","Event","Social Media","Other"]'), true) ?: [];
    $lost_reasons = json_decode($get_setting('crm_lost_reasons',    '["Price","Competition","No budget","Timing","No response"]'), true) ?: [];

    ob_start();
    ?>
    <!-- Settings sub-tabs -->
    <div style="display:flex;gap:0;border-bottom:1px solid #e5e7eb;margin-bottom:20px;overflow-x:auto;">
        <?php
        $stabs = [
            'pipelines'     => 'Pipelines',
            'custom_fields' => 'Custom Fields',
            'sources'       => 'Sources & Reasons',
            'general'       => 'General',
            'data'          => 'Data Tools',
            'audit'         => 'Audit Log',
        ];
        foreach ($stabs as $key => $label):
        ?>
        <button class="crm-settings-stab" data-stab="<?php echo esc_attr($key); ?>"
            style="padding:10px 18px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:500;color:#6b7280;border-bottom:2px solid transparent;white-space:nowrap;transition:all 0.15s;">
            <?php echo esc_html($label); ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- PIPELINES -->
    <div class="crm-stab-panel" id="crm-stab-pipelines">
        <div class="bntm-form-section">
            <div class="bntm-section-header">
                <h3>Pipeline Manager</h3>
                <button class="bntm-btn-primary bntm-btn-small" id="crm-add-pipeline-btn">+ Add Pipeline</button>
            </div>
            <div id="crm-pipelines-list">
                <?php if (!empty($pipelines)): ?>
                <?php foreach ($pipelines as $pl):
                    $stages = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$stages_table} WHERE pipeline_id = %d ORDER BY sort_order ASC",
                        $pl->id
                    ));
                ?>
                <div class="crm-pipeline-item" data-pipeline-id="<?php echo intval($pl->id); ?>"
                    style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div>
                            <strong style="font-size:15px;color:#111827;"><?php echo esc_html($pl->name); ?></strong>
                            <?php if ($pl->is_default): ?>
                            <span class="bntm-badge" style="background:#d1fae5;color:#065f46;margin-left:8px;">Default</span>
                            <?php endif; ?>
                            <?php if ($pl->description): ?>
                            <div style="font-size:12px;color:#9ca3af;margin-top:3px;"><?php echo esc_html($pl->description); ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button class="bntm-btn-secondary bntm-btn-small crm-edit-pipeline-btn"
                                data-id="<?php echo intval($pl->id); ?>"
                                data-name="<?php echo esc_attr($pl->name); ?>"
                                data-desc="<?php echo esc_attr($pl->description); ?>"
                                data-default="<?php echo intval($pl->is_default); ?>">Edit</button>
                            <button class="bntm-btn-danger bntm-btn-small crm-delete-pipeline-btn"
                                data-id="<?php echo intval($pl->id); ?>">Delete</button>
                        </div>
                    </div>
                    <!-- Stages -->
                    <div>
                        <div style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Stages</div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;" id="crm-stages-list-<?php echo intval($pl->id); ?>">
                            <?php foreach ($stages as $stage): ?>
                            <div style="display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;border:1px solid #e5e7eb;font-size:12px;background:#fff;">
                                <span style="width:8px;height:8px;border-radius:50%;background:<?php echo esc_attr($stage->colour); ?>;display:inline-block;"></span>
                                <span><?php echo esc_html($stage->name); ?></span>
                                <span style="color:#9ca3af;"><?php echo intval($stage->probability); ?>%</span>
                                <button class="crm-delete-stage-btn" data-id="<?php echo intval($stage->id); ?>" data-pipeline="<?php echo intval($pl->id); ?>"
                                    style="background:none;border:none;cursor:pointer;color:#dc2626;padding:0 2px;font-size:13px;">&times;</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                            <input type="text" class="crm-new-stage-name" data-pipeline="<?php echo intval($pl->id); ?>"
                                placeholder="Stage name" style="width:160px;">
                            <input type="color" class="crm-new-stage-colour" value="#0d6efd"
                                style="width:40px;height:36px;padding:2px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;">
                            <input type="number" class="crm-new-stage-prob" min="0" max="100" placeholder="Prob %" style="width:80px;">
                            <button class="bntm-btn-secondary bntm-btn-small crm-add-stage-btn"
                                data-pipeline="<?php echo intval($pl->id); ?>">+ Add Stage</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="crm-empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p>No pipelines yet. Create your first pipeline above.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CUSTOM FIELDS -->
    <div class="crm-stab-panel" id="crm-stab-custom_fields" style="display:none;">
        <div class="bntm-form-section">
            <div class="bntm-section-header">
                <h3>Custom Fields Builder</h3>
                <button class="bntm-btn-primary bntm-btn-small" id="crm-add-cf-btn">+ Add Field</button>
            </div>
            <?php foreach (['contact' => 'Contact Fields', 'deal' => 'Deal Fields', 'activity' => 'Activity Fields'] as $obj_type => $obj_label): ?>
            <div style="margin-bottom:20px;">
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;"><?php echo esc_html($obj_label); ?></div>
                <?php
                $obj_fields = array_filter($custom_fields, function($cf) use ($obj_type) { return $cf->object_type === $obj_type; });
                if (!empty($obj_fields)):
                ?>
                <div class="bntm-table-wrapper">
                    <table class="bntm-table">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Key</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Options</th>
                                <th style="width:80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($obj_fields as $cf): ?>
                            <tr>
                                <td style="font-weight:500;"><?php echo esc_html($cf->field_label); ?></td>
                                <td><code style="font-size:12px;background:#f3f4f6;padding:2px 6px;border-radius:4px;"><?php echo esc_html($cf->field_key); ?></code></td>
                                <td><?php echo esc_html(ucfirst($cf->field_type)); ?></td>
                                <td><?php echo $cf->is_required ? '<span style="color:#059669;font-weight:600;">Yes</span>' : '<span style="color:#9ca3af;">No</span>'; ?></td>
                                <td style="font-size:12px;color:#6b7280;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?php echo esc_html($cf->field_options ?: '—'); ?>
                                </td>
                                <td>
                                    <button class="bntm-btn-danger bntm-btn-small crm-delete-cf-btn" data-id="<?php echo intval($cf->id); ?>">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="font-size:13px;color:#9ca3af;">No custom fields for <?php echo esc_html(strtolower($obj_label)); ?> yet.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SOURCES & REASONS -->
    <div class="crm-stab-panel" id="crm-stab-sources" style="display:none;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="bntm-form-section" style="margin-bottom:0;">
                <h3>Contact Sources</h3>
                <div id="crm-sources-list" style="margin-bottom:12px;">
                    <?php foreach ($sources as $src): ?>
                    <div class="crm-source-item" style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;">
                        <span style="flex:1;font-size:13px;color:#374151;"><?php echo esc_html($src); ?></span>
                        <button class="bntm-btn-danger bntm-btn-small crm-delete-source-btn" data-val="<?php echo esc_attr($src); ?>">&times;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="crm-new-source" placeholder="New source…" style="flex:1;">
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-add-source-btn">Add</button>
                </div>
            </div>
            <div class="bntm-form-section" style="margin-bottom:0;">
                <h3>Lost Reasons</h3>
                <div id="crm-lost-reasons-list" style="margin-bottom:12px;">
                    <?php foreach ($lost_reasons as $reason): ?>
                    <div class="crm-reason-item" style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;">
                        <span style="flex:1;font-size:13px;color:#374151;"><?php echo esc_html($reason); ?></span>
                        <button class="bntm-btn-danger bntm-btn-small crm-delete-reason-btn" data-val="<?php echo esc_attr($reason); ?>">&times;</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="crm-new-reason" placeholder="New lost reason…" style="flex:1;">
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-add-reason-btn">Add</button>
                </div>
            </div>
        </div>
        <div style="text-align:right;margin-top:14px;">
            <button class="bntm-btn-primary bntm-btn-small" id="crm-save-sources-btn">Save Changes</button>
        </div>
    </div>

    <!-- GENERAL -->
    <div class="crm-stab-panel" id="crm-stab-general" style="display:none;">
        <div class="bntm-form-section">
            <h3>General Settings</h3>
            <div class="form-grid-2">
                <div class="form-row">
                    <label>Default Currency</label>
                    <select id="crm-setting-currency">
                        <?php foreach (['USD','EUR','GBP','PHP','AED','AUD','CAD','SGD'] as $cur): ?>
                        <option value="<?php echo esc_attr($cur); ?>" <?php selected($get_setting('crm_currency','USD'), $cur); ?>><?php echo esc_html($cur); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label>Default Assigned User</label>
                    <select id="crm-setting-default-user">
                        <option value="">None</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?php echo intval($u->ID); ?>" <?php selected($get_setting('crm_default_assigned_user',''), $u->ID); ?>>
                            <?php echo esc_html($u->display_name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="border-top:1px solid #f3f4f6;margin:16px 0 14px;padding-top:14px;">
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Contact Form Settings</div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                            <input type="checkbox" id="crm-setting-recaptcha" <?php checked($get_setting('crm_enable_recaptcha','0'), '1'); ?>>
                            Enable reCAPTCHA
                        </label>
                    </div>
                    <div class="form-row">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                            <input type="checkbox" id="crm-setting-gdpr" <?php checked($get_setting('crm_enable_gdpr','0'), '1'); ?>>
                            Show GDPR consent checkbox
                        </label>
                    </div>
                    <div class="form-row">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                            <input type="checkbox" id="crm-setting-autoresponder" <?php checked($get_setting('crm_enable_autoresponder','0'), '1'); ?>>
                            Auto-responder email to submitter
                        </label>
                    </div>
                    <div class="form-row">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                            <input type="checkbox" id="crm-setting-admin-notify" <?php checked($get_setting('crm_admin_notify_new_lead','1'), '1'); ?>>
                            Notify admin on new lead
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <label>Success Message</label>
                    <textarea id="crm-setting-success-msg" rows="2"><?php echo esc_textarea($get_setting('crm_contact_form_success_msg', 'Thank you! We will be in touch shortly.')); ?></textarea>
                </div>
            </div>
            <div style="border-top:1px solid #f3f4f6;margin:16px 0 14px;padding-top:14px;">
                <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:12px;">Email Integration</div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>From Name</label>
                        <input type="text" id="crm-setting-from-name" value="<?php echo esc_attr($get_setting('crm_email_from_name', get_bloginfo('name'))); ?>">
                    </div>
                    <div class="form-row">
                        <label>From Email</label>
                        <input type="email" id="crm-setting-from-email" value="<?php echo esc_attr($get_setting('crm_email_from_email', get_option('admin_email'))); ?>">
                    </div>
                </div>
            </div>
            <div style="text-align:right;">
                <button class="bntm-btn-primary" id="crm-save-general-settings-btn">Save Settings</button>
            </div>
        </div>
    </div>

    <!-- DATA TOOLS -->
    <div class="crm-stab-panel" id="crm-stab-data" style="display:none;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

            <div class="bntm-form-section" style="margin-bottom:0;">
                <h3>Duplicate Detection</h3>
                <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Scan for contacts that share the same email address and merge them.</p>
                <button class="bntm-btn-secondary" id="crm-detect-duplicates-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Scan for Duplicates
                </button>
                <div id="crm-duplicates-result" style="margin-top:14px;"></div>
            </div>

            <div class="bntm-form-section" style="margin-bottom:0;">
                <h3>Full Data Export</h3>
                <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Export all contacts, deals, and activities as a ZIP of CSV files.</p>
                <button class="bntm-btn-secondary" id="crm-export-all-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export All Data
                </button>
            </div>

            <div class="bntm-form-section" style="margin-bottom:0;border:1px solid #fecaca;">
                <h3 style="color:#dc2626;">Module Reset</h3>
                <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Permanently delete all CRM data including contacts, deals, activities, tasks, and files. This action cannot be undone.</p>
                <button class="bntm-btn-danger" id="crm-reset-module-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Reset Module
                </button>
            </div>

        </div>
    </div>

    <!-- AUDIT LOG -->
    <div class="crm-stab-panel" id="crm-stab-audit" style="display:none;">
        <div class="bntm-form-section">
            <div class="bntm-section-header">
                <h3>Audit Log</h3>
                <span style="font-size:12px;color:#9ca3af;">Read-only record of all data changes</span>
            </div>
            <div class="crm-filters-bar" style="margin-bottom:14px;">
                <select id="crm-audit-action-filter" style="min-width:160px;">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="imported">Imported</option>
                    <option value="exported">Exported</option>
                    <option value="merged">Merged</option>
                    <option value="stage_changed">Stage Changed</option>
                    <option value="status_changed">Status Changed</option>
                </select>
                <select id="crm-audit-user-filter" style="min-width:160px;">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?php echo intval($u->ID); ?>"><?php echo esc_html($u->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="crm-audit-date-from" style="width:auto;">
                <input type="date" id="crm-audit-date-to" style="width:auto;">
                <button class="bntm-btn-secondary bntm-btn-small" id="crm-audit-apply-filters">Filter</button>
            </div>
            <div class="bntm-table-wrapper">
                <table class="bntm-table">
                    <thead>
                        <tr>
                            <th>Object</th>
                            <th>Action</th>
                            <th>Changed By</th>
                            <th>Changed Fields</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="crm-audit-tbody">
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;">Loading audit log…</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="crm-pagination" id="crm-audit-pagination"></div>
        </div>
    </div>

    <!-- Add Pipeline Modal -->
    <div class="crm-modal-overlay" id="crm-pipeline-modal">
        <div class="crm-modal" style="max-width:480px;">
            <div class="crm-modal-header">
                <h3 id="crm-pipeline-modal-title">Add Pipeline</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-pipeline-id" value="">
                <div class="form-row">
                    <label>Pipeline Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="crm-pipeline-name" placeholder="e.g. Sales Pipeline">
                </div>
                <div class="form-row">
                    <label>Description</label>
                    <textarea id="crm-pipeline-desc" rows="2" placeholder="Optional description…"></textarea>
                </div>
                <div class="form-row">
                    <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;">
                        <input type="checkbox" id="crm-pipeline-default">
                        Set as default pipeline
                    </label>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-pipeline-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-pipeline-save-btn">Save Pipeline</button>
            </div>
        </div>
    </div>

    <!-- Add Custom Field Modal -->
    <div class="crm-modal-overlay" id="crm-cf-modal">
        <div class="crm-modal" style="max-width:480px;">
            <div class="crm-modal-header">
                <h3>Add Custom Field</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div class="form-row">
                    <label>Object Type <span style="color:#dc2626;">*</span></label>
                    <select id="crm-cf-object-type">
                        <option value="contact">Contact</option>
                        <option value="deal">Deal</option>
                        <option value="activity">Activity</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>Field Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="crm-cf-label" placeholder="e.g. Industry">
                </div>
                <div class="form-row">
                    <label>Field Key <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="crm-cf-key" placeholder="e.g. industry (lowercase, underscores only)">
                </div>
                <div class="form-grid-2">
                    <div class="form-row">
                        <label>Field Type <span style="color:#dc2626;">*</span></label>
                        <select id="crm-cf-type">
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="select">Select (Dropdown)</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="date">Date</option>
                            <option value="url">URL</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label style="display:inline-flex;align-items:center;gap:8px;font-weight:400;margin-top:24px;">
                            <input type="checkbox" id="crm-cf-required">
                            Required field
                        </label>
                    </div>
                </div>
                <div class="form-row" id="crm-cf-options-row" style="display:none;">
                    <label>Options <span style="font-size:12px;font-weight:400;color:#9ca3af;">(comma-separated)</span></label>
                    <input type="text" id="crm-cf-options" placeholder="Option 1, Option 2, Option 3">
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-cf-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-cf-save-btn">Add Field</button>
            </div>
        </div>
    </div>

    <!-- Merge Contacts Modal -->
    <div class="crm-modal-overlay" id="crm-merge-modal">
        <div class="crm-modal" style="max-width:500px;">
            <div class="crm-modal-header">
                <h3>Merge Duplicate Contacts</h3>
                <button class="crm-modal-close" type="button">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body" id="crm-merge-modal-body">
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-merge-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-merge-confirm-btn">Merge Contacts</button>
            </div>
        </div>
    </div>

    <style>
    .crm-settings-stab.active {
        color: var(--bntm-primary) !important;
        border-bottom-color: var(--bntm-primary) !important;
        background: none;
    }
    </style>

    <script>
    (function() {
        var auditPage    = 1;
        var auditPerPage = 25;

        // ---- Sub-tab switching ----
        var stabs = document.querySelectorAll('.crm-settings-stab');
        stabs.forEach(function(btn) {
            btn.addEventListener('click', function() {
                stabs.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                document.querySelectorAll('.crm-stab-panel').forEach(function(p) { p.style.display = 'none'; });
                var panel = document.getElementById('crm-stab-' + this.dataset.stab);
                if (panel) panel.style.display = '';
                if (this.dataset.stab === 'audit') loadAuditLog();
            });
        });
        stabs[0].classList.add('active');

        // ---- Pipeline ----
        document.getElementById('crm-add-pipeline-btn').addEventListener('click', function() {
            document.getElementById('crm-pipeline-modal-title').textContent = 'Add Pipeline';
            document.getElementById('crm-pipeline-id').value   = '';
            document.getElementById('crm-pipeline-name').value = '';
            document.getElementById('crm-pipeline-desc').value = '';
            document.getElementById('crm-pipeline-default').checked = false;
            crmOpenModal('crm-pipeline-modal');
        });

        document.querySelectorAll('.crm-edit-pipeline-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('crm-pipeline-modal-title').textContent = 'Edit Pipeline';
                document.getElementById('crm-pipeline-id').value    = this.dataset.id;
                document.getElementById('crm-pipeline-name').value  = this.dataset.name;
                document.getElementById('crm-pipeline-desc').value  = this.dataset.desc;
                document.getElementById('crm-pipeline-default').checked = this.dataset.default === '1';
                crmOpenModal('crm-pipeline-modal');
            });
        });

        document.getElementById('crm-pipeline-save-btn').addEventListener('click', function() {
            var name = document.getElementById('crm-pipeline-name').value.trim();
            if (!name) { crmToast('Pipeline name is required.', 'error'); return; }
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_pipeline', {
                pipeline_id:  document.getElementById('crm-pipeline-id').value,
                name:         name,
                description:  document.getElementById('crm-pipeline-desc').value,
                is_default:   document.getElementById('crm-pipeline-default').checked ? 1 : 0,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Pipeline saved.', 'success');
                crmCloseModal('crm-pipeline-modal');
                window.location.reload();
            });
        });

        document.querySelectorAll('.crm-delete-pipeline-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Delete this pipeline? All associated stages and deals will be permanently removed.')) return;
                var id = this.dataset.id;
                crmAjax('bntm_crm_save_pipeline_config', { action_type: 'delete_pipeline', pipeline_id: id }, function(err, res) {
                    if (err || !res.success) { crmToast(res ? res.data.message : 'Delete failed.', 'error'); return; }
                    crmToast('Pipeline deleted.', 'success');
                    window.location.reload();
                });
            });
        });

        // ---- Stages ----
        document.querySelectorAll('.crm-add-stage-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var pipelineId  = this.dataset.pipeline;
                var container   = this.closest('.crm-pipeline-item');
                var name        = container.querySelector('.crm-new-stage-name').value.trim();
                var colour      = container.querySelector('.crm-new-stage-colour').value;
                var probability = container.querySelector('.crm-new-stage-prob').value || 0;
                if (!name) { crmToast('Stage name is required.', 'error'); return; }
                var b = this;
                b.disabled = true;
                crmAjax('bntm_crm_save_pipeline_config', {
                    action_type:  'add_stage',
                    pipeline_id:  pipelineId,
                    stage_name:   name,
                    stage_colour: colour,
                    probability:  probability,
                }, function(err, res) {
                    b.disabled = false;
                    if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                    crmToast('Stage added.', 'success');
                    var stageList = document.getElementById('crm-stages-list-' + pipelineId);
                    var chip = document.createElement('div');
                    chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;border:1px solid #e5e7eb;font-size:12px;background:#fff;';
                    chip.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:' + colour + ';display:inline-block;"></span>' +
                        '<span>' + escHtml(name) + '</span>' +
                        '<span style="color:#9ca3af;">' + probability + '%</span>' +
                        '<button class="crm-delete-stage-btn" data-id="' + res.data.stage_id + '" data-pipeline="' + pipelineId + '" style="background:none;border:none;cursor:pointer;color:#dc2626;padding:0 2px;font-size:13px;">&times;</button>';
                    stageList.appendChild(chip);
                    chip.querySelector('.crm-delete-stage-btn').addEventListener('click', deleteStageHandler);
                    container.querySelector('.crm-new-stage-name').value  = '';
                    container.querySelector('.crm-new-stage-prob').value  = '';
                });
            });
        });

        function deleteStageHandler() {
            var stageId    = this.dataset.id;
            var pipelineId = this.dataset.pipeline;
            if (!confirm('Delete this stage? Deals in this stage will be unassigned.')) return;
            var chip = this.closest('div');
            crmAjax('bntm_crm_save_pipeline_config', { action_type: 'delete_stage', stage_id: stageId, pipeline_id: pipelineId }, function(err, res) {
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                if (chip) chip.remove();
                crmToast('Stage deleted.', 'success');
            });
        }
        document.querySelectorAll('.crm-delete-stage-btn').forEach(function(btn) {
            btn.addEventListener('click', deleteStageHandler);
        });

        // ---- Custom Fields ----
        document.getElementById('crm-add-cf-btn').addEventListener('click', function() {
            document.getElementById('crm-cf-label').value   = '';
            document.getElementById('crm-cf-key').value     = '';
            document.getElementById('crm-cf-options').value = '';
            document.getElementById('crm-cf-required').checked = false;
            document.getElementById('crm-cf-type').value        = 'text';
            document.getElementById('crm-cf-options-row').style.display = 'none';
            crmOpenModal('crm-cf-modal');
        });

        document.getElementById('crm-cf-label').addEventListener('input', function() {
            var key = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/__+/g, '_');
            document.getElementById('crm-cf-key').value = key;
        });

        document.getElementById('crm-cf-type').addEventListener('change', function() {
            document.getElementById('crm-cf-options-row').style.display = this.value === 'select' ? '' : 'none';
        });

        document.getElementById('crm-cf-save-btn').addEventListener('click', function() {
            var label = document.getElementById('crm-cf-label').value.trim();
            var key   = document.getElementById('crm-cf-key').value.trim();
            var type  = document.getElementById('crm-cf-type').value;
            if (!label || !key) { crmToast('Label and key are required.', 'error'); return; }
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_custom_fields', {
                action_type:  'add',
                object_type:  document.getElementById('crm-cf-object-type').value,
                field_label:  label,
                field_key:    key,
                field_type:   type,
                field_options: document.getElementById('crm-cf-options').value,
                is_required:  document.getElementById('crm-cf-required').checked ? 1 : 0,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                crmToast('Custom field added.', 'success');
                crmCloseModal('crm-cf-modal');
                window.location.reload();
            });
        });

        document.querySelectorAll('.crm-delete-cf-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Delete this custom field? Existing data for this field will be lost.')) return;
                var id = this.dataset.id;
                crmAjax('bntm_crm_save_custom_fields', { action_type: 'delete', field_id: id }, function(err, res) {
                    if (err || !res.success) { crmToast(res ? res.data.message : 'Failed.', 'error'); return; }
                    crmToast('Field deleted.', 'success');
                    window.location.reload();
                });
            });
        });

        // ---- Sources & Reasons ----
        var sourcesArr     = <?php echo json_encode($sources); ?>;
        var lostReasonsArr = <?php echo json_encode($lost_reasons); ?>;

        document.getElementById('crm-add-source-btn').addEventListener('click', function() {
            var val = document.getElementById('crm-new-source').value.trim();
            if (!val || sourcesArr.indexOf(val) > -1) { crmToast('Enter a unique source name.', 'error'); return; }
            sourcesArr.push(val);
            var list = document.getElementById('crm-sources-list');
            var row  = document.createElement('div');
            row.className = 'crm-source-item';
            row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;';
            row.innerHTML = '<span style="flex:1;font-size:13px;color:#374151;">' + escHtml(val) + '</span>' +
                '<button class="bntm-btn-danger bntm-btn-small crm-delete-source-btn" data-val="' + escHtml(val) + '">&times;</button>';
            row.querySelector('.crm-delete-source-btn').addEventListener('click', sourceDeleteHandler);
            list.appendChild(row);
            document.getElementById('crm-new-source').value = '';
        });

        function sourceDeleteHandler() {
            var val = this.dataset.val;
            sourcesArr = sourcesArr.filter(function(s) { return s !== val; });
            this.closest('.crm-source-item').remove();
        }
        document.querySelectorAll('.crm-delete-source-btn').forEach(function(btn) { btn.addEventListener('click', sourceDeleteHandler); });

        document.getElementById('crm-add-reason-btn').addEventListener('click', function() {
            var val = document.getElementById('crm-new-reason').value.trim();
            if (!val || lostReasonsArr.indexOf(val) > -1) { crmToast('Enter a unique reason.', 'error'); return; }
            lostReasonsArr.push(val);
            var list = document.getElementById('crm-lost-reasons-list');
            var row  = document.createElement('div');
            row.className = 'crm-reason-item';
            row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3f4f6;';
            row.innerHTML = '<span style="flex:1;font-size:13px;color:#374151;">' + escHtml(val) + '</span>' +
                '<button class="bntm-btn-danger bntm-btn-small crm-delete-reason-btn" data-val="' + escHtml(val) + '">&times;</button>';
            row.querySelector('.crm-delete-reason-btn').addEventListener('click', reasonDeleteHandler);
            list.appendChild(row);
            document.getElementById('crm-new-reason').value = '';
        });

        function reasonDeleteHandler() {
            var val = this.dataset.val;
            lostReasonsArr = lostReasonsArr.filter(function(r) { return r !== val; });
            this.closest('.crm-reason-item').remove();
        }
        document.querySelectorAll('.crm-delete-reason-btn').forEach(function(btn) { btn.addEventListener('click', reasonDeleteHandler); });

        document.getElementById('crm-save-sources-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_settings', {
                crm_contact_sources: JSON.stringify(sourcesArr),
                crm_lost_reasons:    JSON.stringify(lostReasonsArr),
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Sources and reasons saved.', 'success');
            });
        });

        // ---- General settings ----
        document.getElementById('crm-save-general-settings-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_settings', {
                crm_currency:                  document.getElementById('crm-setting-currency').value,
                crm_default_assigned_user:     document.getElementById('crm-setting-default-user').value,
                crm_enable_recaptcha:          document.getElementById('crm-setting-recaptcha').checked ? '1' : '0',
                crm_enable_gdpr:               document.getElementById('crm-setting-gdpr').checked ? '1' : '0',
                crm_enable_autoresponder:      document.getElementById('crm-setting-autoresponder').checked ? '1' : '0',
                crm_admin_notify_new_lead:     document.getElementById('crm-setting-admin-notify').checked ? '1' : '0',
                crm_contact_form_success_msg:  document.getElementById('crm-setting-success-msg').value,
                crm_email_from_name:           document.getElementById('crm-setting-from-name').value,
                crm_email_from_email:          document.getElementById('crm-setting-from-email').value,
            }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Save failed.', 'error'); return; }
                crmToast('Settings saved.', 'success');
            });
        });

        // ---- Data tools ----
        document.getElementById('crm-detect-duplicates-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Scanning…';
            crmAjax('bntm_crm_detect_duplicates', {}, function(err, res) {
                btn.disabled = false;
                btn.textContent = 'Scan for Duplicates';
                var el = document.getElementById('crm-duplicates-result');
                if (err || !res.success) { el.innerHTML = '<span style="color:#dc2626;">Scan failed.</span>'; return; }
                var groups = res.data.groups || [];
                if (!groups.length) {
                    el.innerHTML = '<span style="color:#059669;font-weight:600;">No duplicates found.</span>';
                    return;
                }
                var html = '<div style="font-size:13px;color:#374151;margin-bottom:8px;font-weight:600;">' + groups.length + ' duplicate group(s) found:</div>';
                groups.forEach(function(g) {
                    html += '<div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px;">';
                    html += '<div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Email: <strong>' + escHtml(g.email) + '</strong></div>';
                    html += '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;">';
                    g.contacts.forEach(function(c) {
                        html += '<span style="font-size:12px;padding:3px 8px;background:#f3f4f6;border-radius:5px;">' + escHtml(c.name) + ' (ID:' + c.id + ')</span>';
                    });
                    html += '</div>';
                    html += '<button class="bntm-btn-secondary bntm-btn-small crm-open-merge-btn" data-group=\'' + JSON.stringify(g) + '\'>Merge</button>';
                    html += '</div>';
                });
                el.innerHTML = html;
                el.querySelectorAll('.crm-open-merge-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() { openMergeModal(JSON.parse(this.dataset.group)); });
                });
            });
        });

        function openMergeModal(group) {
            var body = document.getElementById('crm-merge-modal-body');
            var html = '<p style="font-size:13px;color:#374151;margin-bottom:14px;">Select the primary contact to keep. The others will be merged into it and deleted.</p>';
            html += '<div>';
            group.contacts.forEach(function(c) {
                html += '<label style="display:flex;align-items:center;gap:8px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:6px;cursor:pointer;">' +
                    '<input type="radio" name="crm-merge-primary" value="' + c.id + '">' +
                    '<span style="font-size:13px;font-weight:500;color:#111827;">' + escHtml(c.name) + '</span>' +
                    '<span style="font-size:12px;color:#9ca3af;">(ID: ' + c.id + ')</span>' +
                '</label>';
            });
            html += '</div>';
            var secondaryIds = group.contacts.map(function(c) { return c.id; });
            document.getElementById('crm-merge-confirm-btn').dataset.secondaryIds = JSON.stringify(secondaryIds);
            body.innerHTML = html;
            crmOpenModal('crm-merge-modal');
        }

        document.getElementById('crm-merge-confirm-btn').addEventListener('click', function() {
            var primaryInput = document.querySelector('input[name="crm-merge-primary"]:checked');
            if (!primaryInput) { crmToast('Select a primary contact.', 'error'); return; }
            var primaryId    = primaryInput.value;
            var allIds       = JSON.parse(this.dataset.secondaryIds || '[]');
            var secondaryIds = allIds.filter(function(id) { return String(id) !== String(primaryId); });
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_merge_contacts', { primary_id: primaryId, secondary_ids: secondaryIds }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Merge failed.', 'error'); return; }
                crmToast('Contacts merged.', 'success');
                crmCloseModal('crm-merge-modal');
                document.getElementById('crm-duplicates-result').innerHTML = '';
            });
        });

        document.getElementById('crm-export-all-btn').addEventListener('click', function() {
            window.location.href = ajaxurl + '?action=bntm_crm_export_all_data&nonce=' + bntm_crm_nonce;
        });

        document.getElementById('crm-reset-module-btn').addEventListener('click', function() {
            var confirm1 = confirm('WARNING: This will permanently delete ALL CRM data. This cannot be undone. Are you sure?');
            if (!confirm1) return;
            var confirm2 = confirm('Final confirmation: Type OK to proceed with the complete CRM reset.');
            if (!confirm2) return;
            var btn = this;
            btn.disabled = true;
            crmAjax('bntm_crm_save_settings', { action_type: 'reset_module', confirmed: 1 }, function(err, res) {
                btn.disabled = false;
                if (err || !res.success) { crmToast(res ? res.data.message : 'Reset failed.', 'error'); return; }
                crmToast('Module reset complete.', 'success');
                setTimeout(function() { window.location.reload(); }, 1500);
            });
        });

        // ---- Audit log ----
        function loadAuditLog() {
            var tbody = document.getElementById('crm-audit-tbody');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;">Loading…</td></tr>';
            crmAjax('bntm_crm_get_audit_log', {
                action_filter: document.getElementById('crm-audit-action-filter').value,
                user_id:       document.getElementById('crm-audit-user-filter').value,
                date_from:     document.getElementById('crm-audit-date-from').value,
                date_to:       document.getElementById('crm-audit-date-to').value,
                page:          auditPage,
                per_page:      auditPerPage,
            }, function(err, res) {
                if (err || !res.success) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:#dc2626;">Failed to load audit log.</td></tr>';
                    return;
                }
                renderAuditLog(res.data.logs, res.data.total);
            });
        }

        function renderAuditLog(logs, total) {
            var tbody = document.getElementById('crm-audit-tbody');
            if (!logs || !logs.length) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:#9ca3af;">No audit log entries found.</td></tr>';
                renderAuditPagination(0);
                return;
            }
            var actionColors = {
                created: '#d1fae5', updated: '#dbeafe', deleted: '#fee2e2',
                imported: '#fef9c3', exported: '#ede9fe', merged: '#f3f4f6',
                stage_changed: '#fff7ed', status_changed: '#fce7f3',
            };
            var html = '';
            logs.forEach(function(l) {
                var bg = actionColors[l.action] || '#f3f4f6';
                var fields = '';
                try {
                    var cf = JSON.parse(l.changed_fields || '[]');
                    if (Array.isArray(cf)) fields = cf.join(', ');
                } catch(e) { fields = l.changed_fields || '—'; }
                html += '<tr>' +
                    '<td style="font-size:13px;"><strong style="color:#374151;">' + escHtml(ucFirst(l.object_type)) + '</strong><div style="font-size:11px;color:#9ca3af;">ID: ' + l.object_id + '</div></td>' +
                    '<td><span style="display:inline-block;padding:3px 8px;border-radius:5px;font-size:12px;font-weight:600;background:' + bg + ';color:#374151;">' + escHtml(ucFirst(l.action.replace('_',' '))) + '</span></td>' +
                    '<td style="font-size:13px;">' + escHtml(l.changed_by_name || '—') + '</td>' +
                    '<td style="font-size:12px;color:#6b7280;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + escHtml(fields) + '">' + escHtml(fields || '—') + '</td>' +
                    '<td style="font-size:12px;color:#9ca3af;">' + escHtml(l.ip_address || '—') + '</td>' +
                    '<td style="font-size:12px;color:#374151;white-space:nowrap;">' + escHtml(l.created_at ? l.created_at.substring(0,16).replace('T',' ') : '') + '</td>' +
                '</tr>';
            });
            tbody.innerHTML = html;
            renderAuditPagination(total);
        }

        function renderAuditPagination(total) {
            var pages = Math.ceil(total / auditPerPage);
            var el    = document.getElementById('crm-audit-pagination');
            if (pages <= 1) { el.innerHTML = '<span>' + total + ' entr' + (total !== 1 ? 'ies' : 'y') + '</span>'; return; }
            var btns = '<button ' + (auditPage === 1 ? 'disabled' : '') + ' id="crm-audit-prev">&larr;</button>';
            for (var p = 1; p <= pages; p++) {
                btns += '<button class="' + (p === auditPage ? 'active' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            btns += '<button ' + (auditPage === pages ? 'disabled' : '') + ' id="crm-audit-next">&rarr;</button>';
            el.innerHTML = '<span>' + total + ' entr' + (total !== 1 ? 'ies' : 'y') + '</span><div class="crm-pagination-btns">' + btns + '</div>';
            el.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() { auditPage = parseInt(this.dataset.page); loadAuditLog(); });
            });
            var prev = document.getElementById('crm-audit-prev');
            var next = document.getElementById('crm-audit-next');
            if (prev) prev.addEventListener('click', function() { auditPage--; loadAuditLog(); });
            if (next) next.addEventListener('click', function() { auditPage++; loadAuditLog(); });
        }

        document.getElementById('crm-audit-apply-filters').addEventListener('click', function() { auditPage = 1; loadAuditLog(); });

        function escHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''; }
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX HANDLERS — DASHBOARD
// =============================================================================

function bntm_ajax_crm_get_summary_cards() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table  = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table     = $wpdb->prefix . 'bntm_crm_deals';
    $tasks_table     = $wpdb->prefix . 'bntm_crm_tasks';

    $month_start = date('Y-m-01 00:00:00');
    $month_end   = date('Y-m-t 23:59:59');

    wp_send_json_success([
        'total_contacts'  => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$contacts_table} WHERE deleted_at IS NULL"),
        'open_deals'      => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL"),
        'pipeline_value'  => (float) $wpdb->get_var("SELECT COALESCE(SUM(value),0) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL"),
        'tasks_due'       => (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$tasks_table} WHERE status != 'done' AND due_date <= %s",
            date('Y-m-d 23:59:59', strtotime('+7 days'))
        )),
        'won_this_month'  => (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'won' AND updated_at BETWEEN %s AND %s",
            $month_start, $month_end
        )),
        'lost_this_month' => (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'lost' AND updated_at BETWEEN %s AND %s",
            $month_start, $month_end
        )),
    ]);
}

function bntm_ajax_crm_get_activity_feed() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $limit            = intval($_POST['limit'] ?? 10);
    $limit            = min(max($limit, 1), 50);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT a.*, CONCAT(c.first_name,' ',c.last_name) AS contact_name, c.id AS contact_id,
                u.display_name AS logged_by_name
         FROM {$activities_table} a
         LEFT JOIN {$contacts_table} c ON a.contact_id = c.id
         LEFT JOIN {$wpdb->users} u ON a.logged_by = u.ID
         ORDER BY a.logged_at DESC LIMIT %d",
        $limit
    ));

    wp_send_json_success(['activities' => $rows]);
}

function bntm_ajax_crm_get_pipeline_chart() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $stages_table = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $deals_table  = $wpdb->prefix . 'bntm_crm_deals';

    $pipeline_id = intval($_POST['pipeline_id'] ?? 0);
    $where       = $pipeline_id ? $wpdb->prepare('AND ps.pipeline_id = %d', $pipeline_id) : '';

    $rows = $wpdb->get_results(
        "SELECT ps.name, ps.colour, COUNT(d.id) AS deal_count,
                COALESCE(SUM(d.value),0) AS stage_value
         FROM {$stages_table} ps
         LEFT JOIN {$deals_table} d ON d.stage_id = ps.id AND d.status = 'open' AND d.deleted_at IS NULL
         WHERE 1=1 {$where}
         GROUP BY ps.id ORDER BY ps.sort_order ASC"
    );

    wp_send_json_success(['stages' => $rows]);
}

function bntm_ajax_crm_get_upcoming_tasks() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table    = $wpdb->prefix . 'bntm_crm_tasks';
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $days           = intval($_POST['days'] ?? 7);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, CONCAT(c.first_name,' ',c.last_name) AS contact_name, u.display_name AS assigned_name
         FROM {$tasks_table} t
         LEFT JOIN {$contacts_table} c ON t.contact_id = c.id
         LEFT JOIN {$wpdb->users} u ON t.assigned_user_id = u.ID
         WHERE t.status != 'done' AND t.due_date BETWEEN %s AND %s
         ORDER BY t.due_date ASC LIMIT 10",
        current_time('mysql'),
        date('Y-m-d 23:59:59', strtotime("+{$days} days"))
    ));

    wp_send_json_success(['tasks' => $rows]);
}

function bntm_ajax_crm_get_bar_graph_data() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';
    $period         = sanitize_text_field($_POST['period'] ?? 'monthly');
    $labels         = [];
    $contacts       = [];
    $deals          = [];
    $won            = [];

    if ($period === 'weekly') {
        for ($i = 5; $i >= 0; $i--) {
            $start    = date('Y-m-d 00:00:00', strtotime("-{$i} weeks monday"));
            $end      = date('Y-m-d 23:59:59', strtotime("-{$i} weeks sunday"));
            $labels[] = date('M j', strtotime($start));
            $contacts[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$contacts_table} WHERE created_at BETWEEN %s AND %s AND deleted_at IS NULL", $start, $end
            ));
            $deals[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$deals_table} WHERE created_at BETWEEN %s AND %s AND deleted_at IS NULL", $start, $end
            ));
            $won[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'won' AND updated_at BETWEEN %s AND %s", $start, $end
            ));
        }
    } else {
        for ($i = 5; $i >= 0; $i--) {
            $month_ts = strtotime("-{$i} months");
            $start    = date('Y-m-01 00:00:00', $month_ts);
            $end      = date('Y-m-t 23:59:59', $month_ts);
            $labels[] = date('M', $month_ts);
            $contacts[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$contacts_table} WHERE created_at BETWEEN %s AND %s AND deleted_at IS NULL", $start, $end
            ));
            $deals[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$deals_table} WHERE created_at BETWEEN %s AND %s AND deleted_at IS NULL", $start, $end
            ));
            $won[] = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$deals_table} WHERE status = 'won' AND updated_at BETWEEN %s AND %s", $start, $end
            ));
        }
    }

    wp_send_json_success(['labels' => $labels, 'contacts' => $contacts, 'deals' => $deals, 'won' => $won]);
}

// =============================================================================
// AJAX HANDLERS — CONTACTS
// =============================================================================

function bntm_ajax_crm_get_contacts() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $ctags_table    = $wpdb->prefix . 'bntm_crm_contact_tags';
    $tags_table     = $wpdb->prefix . 'bntm_crm_tags';

    $search   = sanitize_text_field($_POST['search']   ?? '');
    $type     = sanitize_text_field($_POST['type']     ?? '');
    $status   = sanitize_text_field($_POST['status']   ?? '');
    $assigned = intval($_POST['assigned']              ?? 0);
    $tag_id   = intval($_POST['tag']                   ?? 0);
    $page     = max(1, intval($_POST['page']           ?? 1));
    $per_page = min(100, max(1, intval($_POST['per_page'] ?? 20)));
    $sort_col = in_array($_POST['sort_col'] ?? '', ['first_name','last_name','type','status','created_at','email']) ? sanitize_text_field($_POST['sort_col']) : 'created_at';
    $sort_dir = strtoupper($_POST['sort_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $offset   = ($page - 1) * $per_page;

    $where  = ['c.deleted_at IS NULL'];
    $params = [];

    if ($search) {
        $like      = '%' . $wpdb->esc_like($search) . '%';
        $where[]   = '(c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s OR c.company LIKE %s)';
        $params[]  = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($type)     { $where[] = 'c.type = %s';              $params[] = $type; }
    if ($status)   { $where[] = 'c.status = %s';            $params[] = $status; }
    if ($assigned) { $where[] = 'c.assigned_user_id = %d';  $params[] = $assigned; }
    if ($tag_id)   { $where[] = 'ct.tag_id = %d';           $params[] = $tag_id; }

    $join      = $tag_id ? "INNER JOIN {$ctags_table} ct ON ct.contact_id = c.id" : '';
    $where_sql = 'WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(DISTINCT c.id) FROM {$contacts_table} c {$join} {$where_sql}";
    $total     = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, ...$params)) : $wpdb->get_var($count_sql));

    $data_params   = array_merge($params, [$per_page, $offset]);
    $data_sql      = "SELECT DISTINCT c.*, u.display_name AS assigned_name
                      FROM {$contacts_table} c
                      {$join}
                      LEFT JOIN {$wpdb->users} u ON c.assigned_user_id = u.ID
                      {$where_sql}
                      ORDER BY c.{$sort_col} {$sort_dir}
                      LIMIT %d OFFSET %d";
    $contacts      = $wpdb->get_results($wpdb->prepare($data_sql, ...$data_params));

    foreach ($contacts as &$contact) {
        $contact->tags = $wpdb->get_results($wpdb->prepare(
            "SELECT t.* FROM {$tags_table} t
             INNER JOIN {$ctags_table} ct ON ct.tag_id = t.id
             WHERE ct.contact_id = %d",
            $contact->id
        ));
    }
    unset($contact);

    wp_send_json_success(['contacts' => $contacts, 'total' => $total]);
}

function bntm_ajax_crm_save_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $ctags_table    = $wpdb->prefix . 'bntm_crm_contact_tags';

    $contact_id      = intval($_POST['contact_id'] ?? 0);
    $allowed_statuses = ['lead', 'active', 'churned', 'archived'];
    $allowed_types    = ['person', 'organisation'];

    $data = [
        'type'             => in_array($_POST['type'] ?? '', $allowed_types) ? sanitize_text_field($_POST['type']) : 'person',
        'first_name'       => sanitize_text_field($_POST['first_name']    ?? ''),
        'last_name'        => sanitize_text_field($_POST['last_name']     ?? ''),
        'email'            => sanitize_email($_POST['email']              ?? ''),
        'phone'            => sanitize_text_field($_POST['phone']         ?? ''),
        'mobile'           => sanitize_text_field($_POST['mobile']        ?? ''),
        'company'          => sanitize_text_field($_POST['company']       ?? ''),
        'job_title'        => sanitize_text_field($_POST['job_title']     ?? ''),
        'address_line_1'   => sanitize_text_field($_POST['address_line_1'] ?? ''),
        'address_line_2'   => sanitize_text_field($_POST['address_line_2'] ?? ''),
        'city'             => sanitize_text_field($_POST['city']          ?? ''),
        'state'            => sanitize_text_field($_POST['state']         ?? ''),
        'postcode'         => sanitize_text_field($_POST['postcode']      ?? ''),
        'country'          => sanitize_text_field($_POST['country']       ?? ''),
        'website'          => esc_url_raw($_POST['website']               ?? ''),
        'source'           => sanitize_text_field($_POST['source']        ?? ''),
        'status'           => in_array($_POST['status'] ?? '', $allowed_statuses) ? sanitize_text_field($_POST['status']) : 'lead',
        'assigned_user_id' => intval($_POST['assigned_user_id']           ?? 0) ?: null,
    ];

    $formats = ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'];

    $wpdb->query('START TRANSACTION');

    if ($contact_id > 0) {
        $old = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$contacts_table} WHERE id = %d", $contact_id));
        if (!$old) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Contact not found.']); }
        $data['updated_at'] = current_time('mysql');
        $formats[]          = '%s';
        $result = $wpdb->update($contacts_table, $data, ['id' => $contact_id], $formats, ['%d']);
        if ($result === false) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Failed to update contact.']); }
        crm_audit_log('contact', $contact_id, 'updated', array_keys($data), (array) $old, $data);
    } else {
        $data['created_by']        = get_current_user_id();
        $data['unsubscribe_token'] = crm_generate_token();
        $formats[]                 = '%d';
        $formats[]                 = '%s';
        $result = $wpdb->insert($contacts_table, $data, $formats);
        if (!$result) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Failed to create contact.']); }
        $contact_id = $wpdb->insert_id;
        crm_audit_log('contact', $contact_id, 'created', array_keys($data), [], $data);
    }

    $tag_ids = array_map('intval', (array) ($_POST['tag_ids'] ?? []));
    $wpdb->delete($ctags_table, ['contact_id' => $contact_id], ['%d']);
    foreach ($tag_ids as $tag_id) {
        if ($tag_id > 0) {
            $wpdb->insert($ctags_table, ['contact_id' => $contact_id, 'tag_id' => $tag_id], ['%d', '%d']);
        }
    }

    $wpdb->query('COMMIT');
    wp_send_json_success(['message' => 'Contact saved successfully.', 'contact_id' => $contact_id]);
}

function bntm_ajax_crm_delete_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $contact_id     = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $result = $wpdb->update(
        $contacts_table,
        ['deleted_at' => current_time('mysql')],
        ['id' => $contact_id],
        ['%s'],
        ['%d']
    );

    if ($result === false) wp_send_json_error(['message' => 'Failed to delete contact.']);

    crm_audit_log('contact', $contact_id, 'deleted', [], [], []);
    wp_send_json_success(['message' => 'Contact deleted.']);
}

function bntm_ajax_crm_bulk_action_contacts() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $ctags_table    = $wpdb->prefix . 'bntm_crm_contact_tags';

    $action_type = sanitize_text_field($_POST['action_type'] ?? '');
    $contact_ids = array_map('intval', (array) ($_POST['contact_ids'] ?? []));
    $contact_ids = array_filter($contact_ids);

    if (empty($contact_ids)) wp_send_json_error(['message' => 'No contacts selected.']);

    $placeholders = implode(',', array_fill(0, count($contact_ids), '%d'));

    $wpdb->query('START TRANSACTION');

    switch ($action_type) {
        case 'assign':
            $assign_user_id = intval($_POST['assign_user_id'] ?? 0);
            $wpdb->query($wpdb->prepare(
                "UPDATE {$contacts_table} SET assigned_user_id = %d WHERE id IN ({$placeholders})",
                array_merge([$assign_user_id], $contact_ids)
            ));
            break;

        case 'tag':
            $tag_id = intval($_POST['tag_id'] ?? 0);
            if (!$tag_id) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Invalid tag.']); }
            foreach ($contact_ids as $cid) {
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$ctags_table} WHERE contact_id = %d AND tag_id = %d", $cid, $tag_id
                ));
                if (!$exists) {
                    $wpdb->insert($ctags_table, ['contact_id' => $cid, 'tag_id' => $tag_id], ['%d', '%d']);
                }
            }
            break;

        case 'delete':
            $wpdb->query($wpdb->prepare(
                "UPDATE {$contacts_table} SET deleted_at = %s WHERE id IN ({$placeholders})",
                array_merge([current_time('mysql')], $contact_ids)
            ));
            break;

        case 'export':
            $wpdb->query('ROLLBACK');
            $contacts = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$contacts_table} WHERE id IN ({$placeholders}) AND deleted_at IS NULL",
                ...$contact_ids
            ));
            crm_output_contacts_csv($contacts, 'selected-contacts-export.csv');
            exit;

        default:
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => 'Unknown bulk action.']);
    }

    $wpdb->query('COMMIT');
    wp_send_json_success(['message' => 'Bulk action completed for ' . count($contact_ids) . ' contact(s).']);
}

function bntm_ajax_crm_import_contacts() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    if (empty($_FILES['csv_file'])) wp_send_json_error(['message' => 'No file uploaded.']);

    $file    = $_FILES['csv_file'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') wp_send_json_error(['message' => 'Only CSV files are supported.']);

    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) wp_send_json_error(['message' => 'Could not read file.']);

    global $wpdb;
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $allowed_statuses = ['lead', 'active', 'churned', 'archived'];
    $header           = fgetcsv($handle);
    if (!$header) { fclose($handle); wp_send_json_error(['message' => 'Empty or invalid CSV.']); }

    $header      = array_map('trim', $header);
    $imported    = 0;
    $skipped     = 0;
    $current_uid = get_current_user_id();

    $wpdb->query('START TRANSACTION');

    while (($row = fgetcsv($handle)) !== false) {
        $data_row = array_combine($header, $row);
        if (!$data_row) { $skipped++; continue; }

        $email = sanitize_email($data_row['email'] ?? '');
        if ($email) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$contacts_table} WHERE email = %s AND deleted_at IS NULL", $email
            ));
            if ($exists) { $skipped++; continue; }
        }

        $status = in_array($data_row['status'] ?? '', $allowed_statuses) ? sanitize_text_field($data_row['status']) : 'lead';

        $result = $wpdb->insert($contacts_table, [
            'first_name'       => sanitize_text_field($data_row['first_name'] ?? ''),
            'last_name'        => sanitize_text_field($data_row['last_name']  ?? ''),
            'email'            => $email,
            'phone'            => sanitize_text_field($data_row['phone']      ?? ''),
            'company'          => sanitize_text_field($data_row['company']    ?? ''),
            'status'           => $status,
            'source'           => sanitize_text_field($data_row['source']     ?? ''),
            'created_by'       => $current_uid,
            'unsubscribe_token' => crm_generate_token(),
        ], ['%s','%s','%s','%s','%s','%s','%s','%d','%s']);

        if ($result) { $imported++; } else { $skipped++; }
    }

    fclose($handle);
    $wpdb->query('COMMIT');
    crm_audit_log('contact', 0, 'imported', [], [], ['imported' => $imported, 'skipped' => $skipped]);
    wp_send_json_success(['message' => "Import complete: {$imported} imported, {$skipped} skipped."]);
}

function bntm_ajax_crm_export_contacts() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';

    $where  = ['deleted_at IS NULL'];
    $params = [];

    $search = sanitize_text_field($_GET['search'] ?? $_POST['search'] ?? '');
    $status = sanitize_text_field($_GET['status'] ?? $_POST['status'] ?? '');
    $type   = sanitize_text_field($_GET['type']   ?? $_POST['type']   ?? '');

    if ($search) {
        $like    = '%' . $wpdb->esc_like($search) . '%';
        $where[] = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ($status) { $where[] = 'status = %s'; $params[] = $status; }
    if ($type)   { $where[] = 'type = %s';   $params[] = $type; }

    $where_sql = 'WHERE ' . implode(' AND ', $where);
    $sql       = "SELECT * FROM {$contacts_table} {$where_sql} ORDER BY created_at DESC";
    $contacts  = $params ? $wpdb->get_results($wpdb->prepare($sql, ...$params)) : $wpdb->get_results($sql);

    crm_audit_log('contact', 0, 'exported', [], [], ['count' => count($contacts)]);
    crm_output_contacts_csv($contacts, 'contacts-export-' . date('Y-m-d') . '.csv');
    exit;
}

function bntm_ajax_crm_get_contact_detail() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $ctags_table    = $wpdb->prefix . 'bntm_crm_contact_tags';
    $tags_table     = $wpdb->prefix . 'bntm_crm_tags';
    $contact_id     = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE id = %d AND deleted_at IS NULL", $contact_id
    ));

    if (!$contact) wp_send_json_error(['message' => 'Contact not found.']);

    $contact->tags = $wpdb->get_results($wpdb->prepare(
        "SELECT t.* FROM {$tags_table} t
         INNER JOIN {$ctags_table} ct ON ct.tag_id = t.id
         WHERE ct.contact_id = %d",
        $contact_id
    ));

    wp_send_json_success($contact);
}

function bntm_ajax_crm_save_tags() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tags_table  = $wpdb->prefix . 'bntm_crm_tags';
    $ctags_table = $wpdb->prefix . 'bntm_crm_contact_tags';
    $tag_id      = intval($_POST['tag_id'] ?? 0);
    $delete_tag  = intval($_POST['delete_tag'] ?? 0);

    if ($delete_tag && $tag_id) {
        $wpdb->delete($ctags_table, ['tag_id' => $tag_id], ['%d']);
        $wpdb->delete($tags_table,  ['id'     => $tag_id], ['%d']);
        wp_send_json_success(['message' => 'Tag deleted.']);
    }

    $name   = sanitize_text_field($_POST['tag_name']   ?? '');
    $colour = sanitize_text_field($_POST['tag_colour'] ?? '#6c757d');

    if (!$name) wp_send_json_error(['message' => 'Tag name is required.']);

    if ($tag_id) {
        $wpdb->update($tags_table, ['name' => $name, 'colour' => $colour], ['id' => $tag_id], ['%s','%s'], ['%d']);
        wp_send_json_success(['message' => 'Tag updated.', 'tag_id' => $tag_id]);
    } else {
        $result = $wpdb->insert($tags_table, [
            'name'       => $name,
            'colour'     => $colour,
            'created_by' => get_current_user_id(),
        ], ['%s','%s','%d']);
        if (!$result) wp_send_json_error(['message' => 'Failed to create tag. Name may already exist.']);
        wp_send_json_success(['message' => 'Tag created.', 'tag_id' => $wpdb->insert_id]);
    }
}

function bntm_ajax_crm_get_contact_profile() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contact_id = intval($_POST['contact_id'] ?? 0);
    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT c.*, u.display_name AS assigned_name
         FROM {$wpdb->prefix}bntm_crm_contacts c
         LEFT JOIN {$wpdb->users} u ON c.assigned_user_id = u.ID
         WHERE c.id = %d AND c.deleted_at IS NULL",
        $contact_id
    ));

    if (!$contact) wp_send_json_error(['message' => 'Contact not found.']);
    wp_send_json_success($contact);
}

function bntm_ajax_crm_get_contact_timeline() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $contact_id       = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT a.*, u.display_name AS logged_by_name
         FROM {$activities_table} a
         LEFT JOIN {$wpdb->users} u ON a.logged_by = u.ID
         WHERE a.contact_id = %d
         ORDER BY a.logged_at DESC LIMIT 50",
        $contact_id
    ));

    wp_send_json_success($rows);
}

function bntm_ajax_crm_get_contact_tasks() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table = $wpdb->prefix . 'bntm_crm_tasks';
    $contact_id  = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, u.display_name AS assigned_name
         FROM {$tasks_table} t
         LEFT JOIN {$wpdb->users} u ON t.assigned_user_id = u.ID
         WHERE t.contact_id = %d AND t.status != 'done'
         ORDER BY t.due_date ASC",
        $contact_id
    ));

    wp_send_json_success($rows);
}

function bntm_ajax_crm_get_contact_files() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $files_table = $wpdb->prefix . 'bntm_crm_files';
    $contact_id  = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact ID.']);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT f.*, u.display_name AS uploader_name
         FROM {$files_table} f
         LEFT JOIN {$wpdb->users} u ON f.uploaded_by = u.ID
         WHERE f.contact_id = %d
         ORDER BY f.created_at DESC",
        $contact_id
    ));

    foreach ($rows as &$row) {
        $row->file_url = file_exists($row->file_path) ? str_replace(ABSPATH, site_url('/'), $row->file_path) : '';
    }
    unset($row);

    wp_send_json_success($rows);
}

function bntm_ajax_crm_upload_contact_file() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $files_table = $wpdb->prefix . 'bntm_crm_files';
    $contact_id  = intval($_POST['contact_id'] ?? 0);

    if (!$contact_id)             wp_send_json_error(['message' => 'Invalid contact ID.']);
    if (empty($_FILES['files']))  wp_send_json_error(['message' => 'No files uploaded.']);

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $upload_dir  = wp_upload_dir();
    $crm_dir     = $upload_dir['basedir'] . '/bntm-crm/contacts/' . $contact_id . '/';
    wp_mkdir_p($crm_dir);

    $allowed_ext = ['pdf','doc','docx','xls','xlsx','png','jpg','jpeg','gif','zip','csv','txt'];
    $uploaded    = 0;
    $files       = $_FILES['files'];

    $file_count = is_array($files['name']) ? count($files['name']) : 1;

    for ($i = 0; $i < $file_count; $i++) {
        $name     = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
        $tmp      = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $size     = is_array($files['size'])     ? $files['size'][$i]     : $files['size'];
        $mime     = is_array($files['type'])     ? $files['type'][$i]     : $files['type'];
        $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) continue;

        $safe_name  = sanitize_file_name($name);
        $dest       = $crm_dir . time() . '_' . $safe_name;
        if (!move_uploaded_file($tmp, $dest)) continue;

        $wpdb->insert($files_table, [
            'contact_id'  => $contact_id,
            'file_name'   => $safe_name,
            'file_path'   => $dest,
            'file_type'   => sanitize_text_field($mime),
            'file_size'   => intval($size),
            'uploaded_by' => get_current_user_id(),
        ], ['%d','%s','%s','%s','%d','%d']);

        $uploaded++;
    }

    if (!$uploaded) wp_send_json_error(['message' => 'No valid files were uploaded.']);
    wp_send_json_success(['message' => $uploaded . ' file(s) uploaded successfully.']);
}

function bntm_ajax_crm_delete_contact_file() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $files_table = $wpdb->prefix . 'bntm_crm_files';
    $file_id     = intval($_POST['file_id'] ?? 0);

    if (!$file_id) wp_send_json_error(['message' => 'Invalid file ID.']);

    $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$files_table} WHERE id = %d", $file_id));
    if (!$file) wp_send_json_error(['message' => 'File not found.']);

    if (file_exists($file->file_path)) @unlink($file->file_path);

    $wpdb->delete($files_table, ['id' => $file_id], ['%d']);
    wp_send_json_success(['message' => 'File deleted.']);
}

// =============================================================================
// AJAX HANDLERS — PIPELINE
// =============================================================================

function bntm_ajax_crm_get_pipeline_board() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $stages_table   = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';

    $pipeline_id = intval($_POST['pipeline_id'] ?? 0);
    $owner_id    = intval($_POST['owner']        ?? 0);
    $date_from   = sanitize_text_field($_POST['date_from'] ?? '');
    $date_to     = sanitize_text_field($_POST['date_to']   ?? '');

    if (!$pipeline_id) {
        $pipeline_id = (int) $wpdb->get_var(
            "SELECT id FROM {$wpdb->prefix}bntm_crm_pipelines WHERE is_default = 1 LIMIT 1"
        );
        if (!$pipeline_id) {
            $pipeline_id = (int) $wpdb->get_var(
                "SELECT id FROM {$wpdb->prefix}bntm_crm_pipelines ORDER BY sort_order ASC LIMIT 1"
            );
        }
    }

    $stages = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$stages_table} WHERE pipeline_id = %d ORDER BY sort_order ASC",
        $pipeline_id
    ));

    foreach ($stages as &$stage) {
        $deal_where  = ['d.stage_id = %d', 'd.status = \'open\'', 'd.deleted_at IS NULL'];
        $deal_params = [$stage->id];

        if ($owner_id)  { $deal_where[] = 'd.assigned_user_id = %d'; $deal_params[] = $owner_id; }
        if ($date_from) { $deal_where[] = 'd.close_date >= %s';      $deal_params[] = $date_from; }
        if ($date_to)   { $deal_where[] = 'd.close_date <= %s';      $deal_params[] = $date_to; }

        $where_sql   = 'WHERE ' . implode(' AND ', $deal_where);

        $stage->deals = $wpdb->get_results($wpdb->prepare(
            "SELECT d.*,
                    CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                    u.display_name AS assigned_name
             FROM {$deals_table} d
             LEFT JOIN {$contacts_table} c ON d.contact_id = c.id
             LEFT JOIN {$wpdb->users} u ON d.assigned_user_id = u.ID
             {$where_sql}
             ORDER BY d.created_at ASC",
            ...$deal_params
        ));
    }
    unset($stage);

    wp_send_json_success(['stages' => $stages, 'pipeline_id' => $pipeline_id]);
}

function bntm_ajax_crm_move_deal_stage() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $deals_table  = $wpdb->prefix . 'bntm_crm_deals';
    $stages_table = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $deal_id      = intval($_POST['deal_id']  ?? 0);
    $stage_id     = intval($_POST['stage_id'] ?? 0);

    if (!$deal_id || !$stage_id) wp_send_json_error(['message' => 'Invalid parameters.']);

    $old_deal = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$deals_table} WHERE id = %d AND deleted_at IS NULL", $deal_id));
    if (!$old_deal) wp_send_json_error(['message' => 'Deal not found.']);

    $stage = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$stages_table} WHERE id = %d", $stage_id));
    if (!$stage) wp_send_json_error(['message' => 'Stage not found.']);

    $result = $wpdb->update(
        $deals_table,
        ['stage_id' => $stage_id, 'probability' => $stage->probability, 'updated_at' => current_time('mysql')],
        ['id' => $deal_id],
        ['%d','%d','%s'],
        ['%d']
    );

    if ($result === false) wp_send_json_error(['message' => 'Failed to move deal.']);

    crm_log_activity('stage_change', $old_deal->contact_id, $deal_id,
        'Stage changed', "Moved from stage ID {$old_deal->stage_id} to {$stage_id} ({$stage->name})", '', 0, 1);
    crm_audit_log('deal', $deal_id, 'stage_changed', ['stage_id'], ['stage_id' => $old_deal->stage_id], ['stage_id' => $stage_id]);

    wp_send_json_success(['message' => 'Deal moved successfully.']);
}

function bntm_ajax_crm_save_deal() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $deals_table     = $wpdb->prefix . 'bntm_crm_deals';
    $line_items_table = $wpdb->prefix . 'bntm_crm_deal_line_items';

    $deal_id    = intval($_POST['deal_id']    ?? 0);
    $contact_id = intval($_POST['contact_id'] ?? 0);
    $pipeline_id = intval($_POST['pipeline_id'] ?? 0);
    $stage_id   = intval($_POST['stage_id']   ?? 0);

    if (!$contact_id || !$pipeline_id || !$stage_id) {
        wp_send_json_error(['message' => 'Contact, pipeline, and stage are required.']);
    }

    $title = sanitize_text_field($_POST['title'] ?? '');
    if (!$title) wp_send_json_error(['message' => 'Deal title is required.']);

    $allowed_currencies = ['USD','EUR','GBP','PHP','AED','AUD','CAD','SGD'];
    $currency           = in_array($_POST['currency'] ?? '', $allowed_currencies) ? sanitize_text_field($_POST['currency']) : 'USD';

    $data = [
        'title'            => $title,
        'contact_id'       => $contact_id,
        'pipeline_id'      => $pipeline_id,
        'stage_id'         => $stage_id,
        'value'            => floatval($_POST['value']       ?? 0),
        'currency'         => $currency,
        'probability'      => min(100, max(0, intval($_POST['probability'] ?? 0))),
        'close_date'       => sanitize_text_field($_POST['close_date'] ?? '') ?: null,
        'source'           => sanitize_text_field($_POST['source']     ?? ''),
        'assigned_user_id' => intval($_POST['assigned_user_id'] ?? 0) ?: null,
        'updated_at'       => current_time('mysql'),
    ];

    $formats = ['%s','%d','%d','%d','%f','%s','%d','%s','%s','%s','%s'];

    $wpdb->query('START TRANSACTION');

    if ($deal_id > 0) {
        $old    = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$deals_table} WHERE id = %d AND deleted_at IS NULL", $deal_id));
        if (!$old) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Deal not found.']); }
        $result = $wpdb->update($deals_table, $data, ['id' => $deal_id], $formats, ['%d']);
        if ($result === false) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Failed to update deal.']); }
        crm_audit_log('deal', $deal_id, 'updated', array_keys($data), (array) $old, $data);
    } else {
        $data['created_by']    = get_current_user_id();
        $data['status']        = 'open';
        $data['access_token']  = crm_generate_token();
        $formats[]             = '%d';
        $formats[]             = '%s';
        $formats[]             = '%s';
        $result = $wpdb->insert($deals_table, $data, $formats);
        if (!$result) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Failed to create deal.']); }
        $deal_id = $wpdb->insert_id;
        crm_audit_log('deal', $deal_id, 'created', array_keys($data), [], $data);
    }

    $wpdb->delete($line_items_table, ['deal_id' => $deal_id], ['%d']);
    $line_items_raw = json_decode(stripslashes($_POST['line_items'] ?? '[]'), true);
    if (is_array($line_items_raw)) {
        foreach ($line_items_raw as $idx => $li) {
            $desc = sanitize_text_field($li['description'] ?? '');
            if (!$desc) continue;
            $wpdb->insert($line_items_table, [
                'deal_id'     => $deal_id,
                'description' => $desc,
                'quantity'    => floatval($li['quantity']   ?? 1),
                'unit_price'  => floatval($li['unit_price'] ?? 0),
                'sort_order'  => intval($li['sort_order']   ?? $idx),
            ], ['%d','%s','%f','%f','%d']);
        }
    }

    $wpdb->query('COMMIT');
    wp_send_json_success(['message' => 'Deal saved successfully.', 'deal_id' => $deal_id]);
}

function bntm_ajax_crm_delete_deal() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $deals_table = $wpdb->prefix . 'bntm_crm_deals';
    $deal_id     = intval($_POST['deal_id'] ?? 0);

    if (!$deal_id) wp_send_json_error(['message' => 'Invalid deal ID.']);

    $result = $wpdb->update(
        $deals_table,
        ['deleted_at' => current_time('mysql')],
        ['id' => $deal_id],
        ['%s'],
        ['%d']
    );

    if ($result === false) wp_send_json_error(['message' => 'Failed to delete deal.']);

    crm_audit_log('deal', $deal_id, 'deleted', [], [], []);
    wp_send_json_success(['message' => 'Deal deleted.']);
}

function bntm_ajax_crm_close_deal() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';
    $deal_id        = intval($_POST['deal_id'] ?? 0);
    $outcome        = sanitize_text_field($_POST['outcome'] ?? '');
    $lost_reason    = sanitize_text_field($_POST['lost_reason'] ?? '');

    if (!$deal_id || !in_array($outcome, ['won','lost'])) {
        wp_send_json_error(['message' => 'Invalid parameters.']);
    }

    $old = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$deals_table} WHERE id = %d AND deleted_at IS NULL", $deal_id));
    if (!$old) wp_send_json_error(['message' => 'Deal not found.']);

    $update = ['status' => $outcome, 'updated_at' => current_time('mysql')];
    if ($outcome === 'lost' && $lost_reason) $update['lost_reason'] = $lost_reason;

    $result = $wpdb->update($deals_table, $update, ['id' => $deal_id], ['%s','%s'], ['%d']);
    if ($result === false) wp_send_json_error(['message' => 'Failed to update deal status.']);

    crm_log_activity($outcome === 'won' ? 'stage_change' : 'stage_change', $old->contact_id, $deal_id,
        'Deal marked as ' . $outcome,
        $outcome === 'lost' && $lost_reason ? 'Lost reason: ' . $lost_reason : '',
        $outcome, 0, 1);
    crm_audit_log('deal', $deal_id, 'status_changed', ['status'], ['status' => $old->status], ['status' => $outcome]);

    wp_send_json_success(['message' => 'Deal marked as ' . $outcome . '.']);
}

function bntm_ajax_crm_get_deal_detail() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $deals_table      = $wpdb->prefix . 'bntm_crm_deals';
    $stages_table     = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $line_items_table = $wpdb->prefix . 'bntm_crm_deal_line_items';
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $files_table      = $wpdb->prefix . 'bntm_crm_files';

    $deal_id    = intval($_POST['deal_id'] ?? 0);
    $token      = sanitize_text_field($_POST['token'] ?? '');

    if ($deal_id) {
        $deal = $wpdb->get_row($wpdb->prepare(
            "SELECT d.*, ps.name AS stage_name, ps.colour AS stage_colour,
                    CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                    u.display_name AS assigned_name
             FROM {$deals_table} d
             LEFT JOIN {$stages_table} ps ON ps.id = d.stage_id
             LEFT JOIN {$contacts_table} c ON c.id = d.contact_id
             LEFT JOIN {$wpdb->users} u ON d.assigned_user_id = u.ID
             WHERE d.id = %d AND d.deleted_at IS NULL",
            $deal_id
        ));
    } elseif ($token) {
        $deal = $wpdb->get_row($wpdb->prepare(
            "SELECT d.*, ps.name AS stage_name,
                    CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                    u.display_name AS assigned_name
             FROM {$deals_table} d
             LEFT JOIN {$stages_table} ps ON ps.id = d.stage_id
             LEFT JOIN {$contacts_table} c ON c.id = d.contact_id
             LEFT JOIN {$wpdb->users} u ON d.assigned_user_id = u.ID
             WHERE d.access_token = %s AND d.deleted_at IS NULL",
            $token
        ));
    }

    if (empty($deal)) wp_send_json_error(['message' => 'Deal not found.']);

    $deal->line_items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$line_items_table} WHERE deal_id = %d ORDER BY sort_order ASC",
        $deal->id
    ));

    $deal->activities = $wpdb->get_results($wpdb->prepare(
        "SELECT a.*, u.display_name AS logged_by_name
         FROM {$activities_table} a
         LEFT JOIN {$wpdb->users} u ON a.logged_by = u.ID
         WHERE a.deal_id = %d ORDER BY a.logged_at DESC LIMIT 10",
        $deal->id
    ));

    $deal->files = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$files_table} WHERE deal_id = %d ORDER BY created_at DESC",
        $deal->id
    ));
    foreach ($deal->files as &$f) {
        $f->file_url = file_exists($f->file_path) ? str_replace(ABSPATH, site_url('/'), $f->file_path) : '';
    }
    unset($f);

    wp_send_json_success($deal);
}

function bntm_ajax_crm_get_pipelines() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $pipelines = $wpdb->get_results(
        "SELECT p.*, GROUP_CONCAT(ps.name ORDER BY ps.sort_order SEPARATOR ',') AS stage_names
         FROM {$wpdb->prefix}bntm_crm_pipelines p
         LEFT JOIN {$wpdb->prefix}bntm_crm_pipeline_stages ps ON ps.pipeline_id = p.id
         GROUP BY p.id ORDER BY p.sort_order ASC"
    );
    wp_send_json_success(['pipelines' => $pipelines]);
}

function bntm_ajax_crm_save_pipeline() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $pipelines_table = $wpdb->prefix . 'bntm_crm_pipelines';
    $pipeline_id     = intval($_POST['pipeline_id'] ?? 0);
    $name            = sanitize_text_field($_POST['name'] ?? '');
    $description     = sanitize_textarea_field($_POST['description'] ?? '');
    $is_default      = intval($_POST['is_default'] ?? 0);

    if (!$name) wp_send_json_error(['message' => 'Pipeline name is required.']);

    $wpdb->query('START TRANSACTION');

    if ($is_default) {
        $wpdb->update($pipelines_table, ['is_default' => 0], ['is_default' => 1], ['%d'], ['%d']);
    }

    $data    = ['name' => $name, 'description' => $description, 'is_default' => $is_default];
    $formats = ['%s','%s','%d'];

    if ($pipeline_id > 0) {
        $result = $wpdb->update($pipelines_table, $data, ['id' => $pipeline_id], $formats, ['%d']);
    } else {
        $data['created_by'] = get_current_user_id();
        $formats[]          = '%d';
        $result             = $wpdb->insert($pipelines_table, $data, $formats);
        $pipeline_id        = $wpdb->insert_id;
    }

    if ($result === false) { $wpdb->query('ROLLBACK'); wp_send_json_error(['message' => 'Failed to save pipeline.']); }

    $wpdb->query('COMMIT');
    wp_send_json_success(['message' => 'Pipeline saved.', 'pipeline_id' => $pipeline_id]);
}

// =============================================================================
// AJAX HANDLERS — ACTIVITIES
// =============================================================================

function bntm_ajax_crm_get_activities() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table      = $wpdb->prefix . 'bntm_crm_deals';

    $activity_id = intval($_POST['activity_id'] ?? 0);
    if ($activity_id) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, u.display_name AS logged_by_name,
                    CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                    d.title AS deal_title
             FROM {$activities_table} a
             LEFT JOIN {$wpdb->users} u ON a.logged_by = u.ID
             LEFT JOIN {$contacts_table} c ON a.contact_id = c.id
             LEFT JOIN {$deals_table} d ON a.deal_id = d.id
             WHERE a.id = %d",
            $activity_id
        ));
        wp_send_json_success(['activities' => $row ? [$row] : [], 'total' => $row ? 1 : 0, 'overdue_count' => 0]);
    }

    $allowed_types = ['call','email','meeting','note','task_completion','callback_request','deal_view','stage_change','unsubscribe'];
    $type          = sanitize_text_field($_POST['type']       ?? '');
    $user_id       = intval($_POST['user_id']                 ?? 0);
    $contact_id    = intval($_POST['contact_id']              ?? 0);
    $date_from     = sanitize_text_field($_POST['date_from']  ?? '');
    $date_to       = sanitize_text_field($_POST['date_to']    ?? '');
    $upcoming      = intval($_POST['upcoming']                ?? 0);
    $page          = max(1, intval($_POST['page']             ?? 1));
    $per_page      = min(100, max(1, intval($_POST['per_page'] ?? 20)));
    $offset        = ($page - 1) * $per_page;

    $where  = ['1=1'];
    $params = [];

    if ($type && in_array($type, $allowed_types)) { $where[] = 'a.type = %s'; $params[] = $type; }
    if ($user_id)    { $where[] = 'a.logged_by = %d';   $params[] = $user_id; }
    if ($contact_id) { $where[] = 'a.contact_id = %d';  $params[] = $contact_id; }
    if ($date_from)  { $where[] = 'a.logged_at >= %s';  $params[] = $date_from . ' 00:00:00'; }
    if ($date_to)    { $where[] = 'a.logged_at <= %s';  $params[] = $date_to   . ' 23:59:59'; }
    if ($upcoming)   { $where[] = 'a.scheduled_at >= %s'; $params[] = current_time('mysql'); }

    $where_sql  = 'WHERE ' . implode(' AND ', $where);
    $order      = $upcoming ? 'a.scheduled_at ASC' : 'a.logged_at DESC';

    $count_sql  = "SELECT COUNT(*) FROM {$activities_table} a {$where_sql}";
    $total      = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, ...$params)) : $wpdb->get_var($count_sql));

    $overdue_count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$activities_table} WHERE scheduled_at < %s AND scheduled_at IS NOT NULL",
        current_time('mysql')
    ));

    $data_params = array_merge($params, [$per_page, $offset]);
    $data_sql    = "SELECT a.*,
                          u.display_name AS logged_by_name,
                          CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                          d.title AS deal_title
                   FROM {$activities_table} a
                   LEFT JOIN {$wpdb->users} u ON a.logged_by = u.ID
                   LEFT JOIN {$contacts_table} c ON a.contact_id = c.id
                   LEFT JOIN {$deals_table} d ON a.deal_id = d.id
                   {$where_sql}
                   ORDER BY {$order}
                   LIMIT %d OFFSET %d";

    $activities = $wpdb->get_results($wpdb->prepare($data_sql, ...$data_params));

    wp_send_json_success(['activities' => $activities, 'total' => $total, 'overdue_count' => $overdue_count]);
}

function bntm_ajax_crm_save_activity() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $activity_id      = intval($_POST['activity_id'] ?? 0);
    $allowed_types    = ['call','email','meeting','note','task_completion','callback_request','stage_change','deal_view','unsubscribe'];
    $type             = sanitize_text_field($_POST['type'] ?? '');

    if (!in_array($type, $allowed_types)) wp_send_json_error(['message' => 'Invalid activity type.']);

    $scheduled_raw = sanitize_text_field($_POST['scheduled_at'] ?? '');
    $scheduled_at  = $scheduled_raw ? date('Y-m-d H:i:s', strtotime($scheduled_raw)) : null;

    $data = [
        'type'                => $type,
        'contact_id'          => intval($_POST['contact_id'] ?? 0) ?: null,
        'deal_id'             => intval($_POST['deal_id']    ?? 0) ?: null,
        'subject'             => sanitize_text_field($_POST['subject']          ?? ''),
        'body'                => sanitize_textarea_field($_POST['body']         ?? ''),
        'outcome'             => sanitize_text_field($_POST['outcome']          ?? ''),
        'duration_minutes'    => intval($_POST['duration_minutes']              ?? 0) ?: null,
        'scheduled_at'        => $scheduled_at,
        'visible_to_customer' => intval($_POST['visible_to_customer']           ?? 0),
        'logged_by'           => get_current_user_id(),
        'updated_at'          => current_time('mysql'),
    ];

    $formats = ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%s'];

    if ($activity_id > 0) {
        $result = $wpdb->update($activities_table, $data, ['id' => $activity_id], $formats, ['%d']);
        if ($result === false) wp_send_json_error(['message' => 'Failed to update activity.']);
    } else {
        $data['logged_at'] = current_time('mysql');
        $formats[]         = '%s';
        $result = $wpdb->insert($activities_table, $data, $formats);
        if (!$result) wp_send_json_error(['message' => 'Failed to log activity.']);
        $activity_id = $wpdb->insert_id;
    }

    wp_send_json_success(['message' => 'Activity saved.', 'activity_id' => $activity_id]);
}

function bntm_ajax_crm_delete_activity() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $activity_id = intval($_POST['activity_id'] ?? 0);
    if (!$activity_id) wp_send_json_error(['message' => 'Invalid activity ID.']);

    $result = $wpdb->delete($wpdb->prefix . 'bntm_crm_activities', ['id' => $activity_id], ['%d']);
    if (!$result) wp_send_json_error(['message' => 'Failed to delete activity.']);

    wp_send_json_success(['message' => 'Activity deleted.']);
}

function bntm_ajax_crm_get_activity_types() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    wp_send_json_success(['types' => [
        'call'             => 'Call',
        'email'            => 'Email',
        'meeting'          => 'Meeting',
        'note'             => 'Note',
        'task_completion'  => 'Task Completion',
        'callback_request' => 'Callback Request',
        'stage_change'     => 'Stage Change',
        'deal_view'        => 'Deal View',
        'unsubscribe'      => 'Unsubscribe',
    ]]);
}

// =============================================================================
// AJAX HANDLERS — TASKS
// =============================================================================

function bntm_ajax_crm_get_tasks() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table    = $wpdb->prefix . 'bntm_crm_tasks';
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';

    $task_id = intval($_POST['task_id'] ?? 0);
    if ($task_id) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, u.display_name AS assigned_name,
                    CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                    d.title AS deal_title
             FROM {$tasks_table} t
             LEFT JOIN {$wpdb->users} u ON t.assigned_user_id = u.ID
             LEFT JOIN {$contacts_table} c ON t.contact_id = c.id
             LEFT JOIN {$deals_table} d ON t.deal_id = d.id
             WHERE t.id = %d",
            $task_id
        ));
        wp_send_json_success(['tasks' => $row ? [$row] : [], 'total' => $row ? 1 : 0]);
    }

    $scope       = sanitize_text_field($_POST['scope']       ?? 'all');
    $search      = sanitize_text_field($_POST['search']      ?? '');
    $status      = sanitize_text_field($_POST['status']      ?? '');
    $priority    = sanitize_text_field($_POST['priority']    ?? '');
    $assigned_id = intval($_POST['assigned_id']              ?? 0);
    $due_from    = sanitize_text_field($_POST['due_from']    ?? '');
    $due_to      = sanitize_text_field($_POST['due_to']      ?? '');
    $page        = max(1, intval($_POST['page']              ?? 1));
    $per_page    = min(100, max(1, intval($_POST['per_page'] ?? 20)));
    $offset      = ($page - 1) * $per_page;

    $where  = ['1=1'];
    $params = [];

    if ($scope === 'my') { $where[] = 't.assigned_user_id = %d'; $params[] = get_current_user_id(); }
    if ($search) {
        $like    = '%' . $wpdb->esc_like($search) . '%';
        $where[] = 't.title LIKE %s'; $params[] = $like;
    }
    if ($status)   { $where[] = 't.status = %s';      $params[] = $status; }
    if ($priority) { $where[] = 't.priority = %s';    $params[] = $priority; }
    if ($assigned_id) { $where[] = 't.assigned_user_id = %d'; $params[] = $assigned_id; }
    if ($due_from) { $where[] = 't.due_date >= %s';   $params[] = $due_from . ' 00:00:00'; }
    if ($due_to)   { $where[] = 't.due_date <= %s';   $params[] = $due_to   . ' 23:59:59'; }

    $where_sql = 'WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) FROM {$tasks_table} t {$where_sql}";
    $total     = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, ...$params)) : $wpdb->get_var($count_sql));

    $data_params = array_merge($params, [$per_page, $offset]);
    $data_sql    = "SELECT t.*,
                          u.display_name AS assigned_name,
                          CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                          d.title AS deal_title
                   FROM {$tasks_table} t
                   LEFT JOIN {$wpdb->users} u ON t.assigned_user_id = u.ID
                   LEFT JOIN {$contacts_table} c ON t.contact_id = c.id
                   LEFT JOIN {$deals_table} d ON t.deal_id = d.id
                   {$where_sql}
                   ORDER BY FIELD(t.priority,'urgent','high','medium','low'), t.due_date ASC
                   LIMIT %d OFFSET %d";

    $tasks = $wpdb->get_results($wpdb->prepare($data_sql, ...$data_params));

    wp_send_json_success(['tasks' => $tasks, 'total' => $total]);
}

function bntm_ajax_crm_save_task() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table      = $wpdb->prefix . 'bntm_crm_tasks';
    $task_id          = intval($_POST['task_id'] ?? 0);
    $title            = sanitize_text_field($_POST['title'] ?? '');
    if (!$title) wp_send_json_error(['message' => 'Task title is required.']);

    $allowed_priorities = ['low','medium','high','urgent'];
    $allowed_statuses   = ['open','in_progress','done'];
    $due_raw            = sanitize_text_field($_POST['due_date'] ?? '');
    $due_date           = $due_raw ? date('Y-m-d H:i:s', strtotime($due_raw)) : null;

    $data = [
        'title'            => $title,
        'description'      => sanitize_textarea_field($_POST['description'] ?? ''),
        'contact_id'       => intval($_POST['contact_id']       ?? 0) ?: null,
        'deal_id'          => intval($_POST['deal_id']          ?? 0) ?: null,
        'assigned_user_id' => intval($_POST['assigned_user_id'] ?? 0) ?: null,
        'priority'         => in_array($_POST['priority'] ?? '', $allowed_priorities) ? sanitize_text_field($_POST['priority']) : 'medium',
        'status'           => in_array($_POST['status']   ?? '', $allowed_statuses)   ? sanitize_text_field($_POST['status'])   : 'open',
        'due_date'         => $due_date,
        'updated_at'       => current_time('mysql'),
    ];

    $formats = ['%s','%s','%s','%s','%s','%s','%s','%s','%s'];

    if ($task_id > 0) {
        $result = $wpdb->update($tasks_table, $data, ['id' => $task_id], $formats, ['%d']);
        if ($result === false) wp_send_json_error(['message' => 'Failed to update task.']);
    } else {
        $data['created_by'] = get_current_user_id();
        $formats[]          = '%d';
        $result = $wpdb->insert($tasks_table, $data, $formats);
        if (!$result) wp_send_json_error(['message' => 'Failed to create task.']);
        $task_id = $wpdb->insert_id;
    }

    wp_send_json_success(['message' => 'Task saved successfully.', 'task_id' => $task_id]);
}

function bntm_ajax_crm_delete_task() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $task_id = intval($_POST['task_id'] ?? 0);
    if (!$task_id) wp_send_json_error(['message' => 'Invalid task ID.']);

    $result = $wpdb->delete($wpdb->prefix . 'bntm_crm_tasks', ['id' => $task_id], ['%d']);
    if (!$result) wp_send_json_error(['message' => 'Failed to delete task.']);

    wp_send_json_success(['message' => 'Task deleted.']);
}

function bntm_ajax_crm_complete_task() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table = $wpdb->prefix . 'bntm_crm_tasks';
    $task_id     = intval($_POST['task_id'] ?? 0);
    if (!$task_id) wp_send_json_error(['message' => 'Invalid task ID.']);

    $task = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tasks_table} WHERE id = %d", $task_id));
    if (!$task) wp_send_json_error(['message' => 'Task not found.']);

    $new_status = $task->status === 'done' ? 'open' : 'done';
    $update     = ['status' => $new_status, 'updated_at' => current_time('mysql')];
    if ($new_status === 'done') {
        $update['completed_at'] = current_time('mysql');
        $update['completed_by'] = get_current_user_id();
        if ($task->contact_id) {
            crm_log_activity('task_completion', $task->contact_id, $task->deal_id,
                'Task completed: ' . $task->title, '', '', 0, 1);
        }
    } else {
        $update['completed_at'] = null;
        $update['completed_by'] = null;
    }

    $wpdb->update($tasks_table, $update, ['id' => $task_id]);
    wp_send_json_success(['message' => 'Task updated.', 'new_status' => $new_status]);
}

function bntm_ajax_crm_bulk_action_tasks() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $tasks_table = $wpdb->prefix . 'bntm_crm_tasks';
    $action_type = sanitize_text_field($_POST['action_type'] ?? '');
    $task_ids    = array_map('intval', (array) ($_POST['task_ids'] ?? []));
    $task_ids    = array_filter($task_ids);

    if (empty($task_ids)) wp_send_json_error(['message' => 'No tasks selected.']);

    $placeholders = implode(',', array_fill(0, count($task_ids), '%d'));

    switch ($action_type) {
        case 'complete':
            $wpdb->query($wpdb->prepare(
                "UPDATE {$tasks_table} SET status = 'done', completed_at = %s, completed_by = %d
                 WHERE id IN ({$placeholders})",
                array_merge([current_time('mysql'), get_current_user_id()], $task_ids)
            ));
            break;
        case 'delete':
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$tasks_table} WHERE id IN ({$placeholders})",
                ...$task_ids
            ));
            break;
        default:
            wp_send_json_error(['message' => 'Unknown bulk action.']);
    }

    wp_send_json_success(['message' => 'Bulk action applied to ' . count($task_ids) . ' task(s).']);
}

// =============================================================================
// AJAX HANDLERS — SETTINGS
// =============================================================================

function bntm_ajax_crm_get_settings() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $settings_table = $wpdb->prefix . 'bntm_crm_settings';
    $rows           = $wpdb->get_results("SELECT setting_key, setting_value FROM {$settings_table}");
    $settings       = [];
    foreach ($rows as $row) {
        $settings[$row->setting_key] = $row->setting_value;
    }
    wp_send_json_success(['settings' => $settings]);
}

function bntm_ajax_crm_save_settings() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Insufficient permissions.']);

    global $wpdb;
    $settings_table = $wpdb->prefix . 'bntm_crm_settings';
    $action_type    = sanitize_text_field($_POST['action_type'] ?? '');

    if ($action_type === 'reset_module') {
        if (!intval($_POST['confirmed'] ?? 0)) wp_send_json_error(['message' => 'Reset not confirmed.']);
        $tables = [
            'bntm_crm_activities', 'bntm_crm_tasks', 'bntm_crm_files',
            'bntm_crm_deal_line_items', 'bntm_crm_deals', 'bntm_crm_pipeline_stages',
            'bntm_crm_pipelines', 'bntm_crm_contact_tags', 'bntm_crm_contact_meta',
            'bntm_crm_contacts', 'bntm_crm_tags', 'bntm_crm_custom_fields',
            'bntm_crm_audit_log', 'bntm_crm_settings',
        ];
        foreach ($tables as $tbl) {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$tbl}");
        }
        wp_send_json_success(['message' => 'Module reset complete.']);
    }

    $allowed_keys = [
        'crm_currency', 'crm_default_assigned_user', 'crm_enable_recaptcha',
        'crm_enable_gdpr', 'crm_enable_autoresponder', 'crm_admin_notify_new_lead',
        'crm_contact_form_success_msg', 'crm_email_from_name', 'crm_email_from_email',
        'crm_contact_sources', 'crm_lost_reasons', 'crm_unsubscribe_confirm_msg',
        'crm_brand_logo', 'crm_brand_colour',
    ];

    $saved  = 0;
    $uid    = get_current_user_id();

    foreach ($allowed_keys as $key) {
        if (!isset($_POST[$key])) continue;
        $value = sanitize_textarea_field($_POST[$key]);
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$settings_table} WHERE setting_key = %s", $key
        ));
        if ($exists) {
            $wpdb->update($settings_table,
                ['setting_value' => $value, 'updated_by' => $uid],
                ['setting_key' => $key],
                ['%s','%d'], ['%s']
            );
        } else {
            $wpdb->insert($settings_table,
                ['setting_key' => $key, 'setting_value' => $value, 'updated_by' => $uid],
                ['%s','%s','%d']
            );
        }
        $saved++;
    }

    wp_send_json_success(['message' => $saved . ' setting(s) saved.']);
}

function bntm_ajax_crm_save_pipeline_config() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $stages_table    = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $pipelines_table = $wpdb->prefix . 'bntm_crm_pipelines';
    $action_type     = sanitize_text_field($_POST['action_type'] ?? '');

    switch ($action_type) {
        case 'add_stage':
            $pipeline_id = intval($_POST['pipeline_id'] ?? 0);
            $stage_name  = sanitize_text_field($_POST['stage_name']   ?? '');
            $colour      = sanitize_text_field($_POST['stage_colour'] ?? '#0d6efd');
            $probability = min(100, max(0, intval($_POST['probability'] ?? 0)));
            if (!$pipeline_id || !$stage_name) wp_send_json_error(['message' => 'Pipeline and stage name required.']);
            $max_order = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(MAX(sort_order),0) FROM {$stages_table} WHERE pipeline_id = %d", $pipeline_id
            ));
            $wpdb->insert($stages_table, [
                'pipeline_id' => $pipeline_id,
                'name'        => $stage_name,
                'colour'      => $colour,
                'probability' => $probability,
                'sort_order'  => $max_order + 1,
            ], ['%d','%s','%s','%d','%d']);
            wp_send_json_success(['message' => 'Stage added.', 'stage_id' => $wpdb->insert_id]);

        case 'delete_stage':
            $stage_id = intval($_POST['stage_id'] ?? 0);
            if (!$stage_id) wp_send_json_error(['message' => 'Invalid stage ID.']);
            $wpdb->update(
                $wpdb->prefix . 'bntm_crm_deals',
                ['stage_id' => null],
                ['stage_id' => $stage_id],
                ['%s'], ['%d']
            );
            $wpdb->delete($stages_table, ['id' => $stage_id], ['%d']);
            wp_send_json_success(['message' => 'Stage deleted.']);

        case 'delete_pipeline':
            $pipeline_id = intval($_POST['pipeline_id'] ?? 0);
            if (!$pipeline_id) wp_send_json_error(['message' => 'Invalid pipeline ID.']);
            $wpdb->query('START TRANSACTION');
            $wpdb->delete($stages_table,    ['pipeline_id' => $pipeline_id], ['%d']);
            $wpdb->delete($pipelines_table, ['id'          => $pipeline_id], ['%d']);
            $wpdb->query('COMMIT');
            wp_send_json_success(['message' => 'Pipeline deleted.']);

        default:
            wp_send_json_error(['message' => 'Unknown action.']);
    }
}

function bntm_ajax_crm_save_custom_fields() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Insufficient permissions.']);

    global $wpdb;
    $cf_table    = $wpdb->prefix . 'bntm_crm_custom_fields';
    $action_type = sanitize_text_field($_POST['action_type'] ?? '');

    if ($action_type === 'delete') {
        $field_id = intval($_POST['field_id'] ?? 0);
        if (!$field_id) wp_send_json_error(['message' => 'Invalid field ID.']);
        $wpdb->delete($cf_table, ['id' => $field_id], ['%d']);
        wp_send_json_success(['message' => 'Custom field deleted.']);
    }

    $allowed_obj_types  = ['contact','deal','activity'];
    $allowed_field_types = ['text','textarea','number','select','checkbox','date','url','email'];
    $object_type = in_array($_POST['object_type'] ?? '', $allowed_obj_types)  ? sanitize_text_field($_POST['object_type']) : 'contact';
    $field_type  = in_array($_POST['field_type']  ?? '', $allowed_field_types) ? sanitize_text_field($_POST['field_type'])  : 'text';
    $field_label = sanitize_text_field($_POST['field_label']   ?? '');
    $field_key   = preg_replace('/[^a-z0-9_]/', '_', strtolower(sanitize_text_field($_POST['field_key'] ?? '')));
    $is_required = intval($_POST['is_required']                ?? 0);
    $field_opts  = sanitize_textarea_field($_POST['field_options'] ?? '');

    if (!$field_label || !$field_key) wp_send_json_error(['message' => 'Label and key are required.']);

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$cf_table} WHERE object_type = %s AND field_key = %s", $object_type, $field_key
    ));
    if ($exists) wp_send_json_error(['message' => 'A field with this key already exists for this object type.']);

    $max_order = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(MAX(sort_order),0) FROM {$cf_table} WHERE object_type = %s", $object_type
    ));

    $result = $wpdb->insert($cf_table, [
        'object_type'   => $object_type,
        'field_key'     => $field_key,
        'field_label'   => $field_label,
        'field_type'    => $field_type,
        'field_options' => $field_opts ?: null,
        'is_required'   => $is_required,
        'sort_order'    => $max_order + 1,
        'created_by'    => get_current_user_id(),
    ], ['%s','%s','%s','%s','%s','%d','%d','%d']);

    if (!$result) wp_send_json_error(['message' => 'Failed to create custom field.']);
    wp_send_json_success(['message' => 'Custom field created.', 'field_id' => $wpdb->insert_id]);
}

function bntm_ajax_crm_detect_duplicates() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';

    $dupes = $wpdb->get_results(
        "SELECT email, COUNT(*) AS cnt FROM {$contacts_table}
         WHERE email != '' AND email IS NOT NULL AND deleted_at IS NULL
         GROUP BY email HAVING cnt > 1"
    );

    $groups = [];
    foreach ($dupes as $d) {
        $contacts = $wpdb->get_results($wpdb->prepare(
            "SELECT id, CONCAT(first_name,' ',last_name) AS name, email, created_at
             FROM {$contacts_table} WHERE email = %s AND deleted_at IS NULL ORDER BY created_at ASC",
            $d->email
        ));
        $groups[] = ['email' => $d->email, 'contacts' => $contacts];
    }

    wp_send_json_success(['groups' => $groups, 'total_groups' => count($groups)]);
}

function bntm_ajax_crm_merge_contacts() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $ctags_table      = $wpdb->prefix . 'bntm_crm_contact_tags';
    $deals_table      = $wpdb->prefix . 'bntm_crm_deals';
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $tasks_table      = $wpdb->prefix . 'bntm_crm_tasks';
    $files_table      = $wpdb->prefix . 'bntm_crm_files';

    $primary_id    = intval($_POST['primary_id'] ?? 0);
    $secondary_ids = array_map('intval', (array) ($_POST['secondary_ids'] ?? []));
    $secondary_ids = array_filter($secondary_ids, function($id) use ($primary_id) { return $id !== $primary_id; });

    if (!$primary_id || empty($secondary_ids)) wp_send_json_error(['message' => 'Invalid merge parameters.']);

    $wpdb->query('START TRANSACTION');

    $placeholders = implode(',', array_fill(0, count($secondary_ids), '%d'));

    $wpdb->query($wpdb->prepare("UPDATE {$deals_table}      SET contact_id = %d WHERE contact_id IN ({$placeholders})", array_merge([$primary_id], $secondary_ids)));
    $wpdb->query($wpdb->prepare("UPDATE {$activities_table} SET contact_id = %d WHERE contact_id IN ({$placeholders})", array_merge([$primary_id], $secondary_ids)));
    $wpdb->query($wpdb->prepare("UPDATE {$tasks_table}      SET contact_id = %d WHERE contact_id IN ({$placeholders})", array_merge([$primary_id], $secondary_ids)));
    $wpdb->query($wpdb->prepare("UPDATE {$files_table}      SET contact_id = %d WHERE contact_id IN ({$placeholders})", array_merge([$primary_id], $secondary_ids)));

    foreach ($secondary_ids as $sid) {
        $sec_tags = $wpdb->get_col($wpdb->prepare("SELECT tag_id FROM {$ctags_table} WHERE contact_id = %d", $sid));
        foreach ($sec_tags as $tag_id) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$ctags_table} WHERE contact_id = %d AND tag_id = %d", $primary_id, $tag_id
            ));
            if (!$exists) $wpdb->insert($ctags_table, ['contact_id' => $primary_id, 'tag_id' => intval($tag_id)], ['%d','%d']);
        }
    }

    $wpdb->query($wpdb->prepare(
        "UPDATE {$contacts_table} SET deleted_at = %s WHERE id IN ({$placeholders})",
        array_merge([current_time('mysql')], $secondary_ids)
    ));

    $wpdb->query('COMMIT');
    crm_audit_log('contact', $primary_id, 'merged', [], ['secondary_ids' => $secondary_ids], ['primary_id' => $primary_id]);
    wp_send_json_success(['message' => 'Contacts merged successfully.']);
}

function bntm_ajax_crm_export_all_data() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Insufficient permissions.']);

    global $wpdb;
    $upload_dir = wp_upload_dir();
    $export_dir = $upload_dir['basedir'] . '/bntm-crm/exports/';
    wp_mkdir_p($export_dir);

    $timestamp   = date('Y-m-d-His');
    $files_added = [];

    $exports = [
        'contacts'   => "SELECT * FROM {$wpdb->prefix}bntm_crm_contacts WHERE deleted_at IS NULL",
        'deals'      => "SELECT * FROM {$wpdb->prefix}bntm_crm_deals WHERE deleted_at IS NULL",
        'activities' => "SELECT * FROM {$wpdb->prefix}bntm_crm_activities",
        'tasks'      => "SELECT * FROM {$wpdb->prefix}bntm_crm_tasks",
    ];

    foreach ($exports as $name => $sql) {
        $rows    = $wpdb->get_results($sql, ARRAY_A);
        $path    = $export_dir . $name . '-' . $timestamp . '.csv';
        $handle  = fopen($path, 'w');
        if (!empty($rows)) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) fputcsv($handle, $row);
        }
        fclose($handle);
        $files_added[] = $path;
    }

    $zip_path = $export_dir . 'crm-export-' . $timestamp . '.zip';
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) === true) {
            foreach ($files_added as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            foreach ($files_added as $file) @unlink($file);
        }
    } else {
        $zip_path = $files_added[0];
    }

    crm_audit_log('system', 0, 'exported', [], [], ['file' => basename($zip_path)]);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
    header('Content-Length: ' . filesize($zip_path));
    readfile($zip_path);
    @unlink($zip_path);
    exit;
}

function bntm_ajax_crm_get_audit_log() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) wp_send_json_error(['message' => 'Unauthorized']);

    global $wpdb;
    $audit_table    = $wpdb->prefix . 'bntm_crm_audit_log';
    $allowed_actions = ['created','updated','deleted','imported','exported','merged','stage_changed','status_changed'];

    $action_filter = sanitize_text_field($_POST['action_filter'] ?? '');
    $user_id       = intval($_POST['user_id']                    ?? 0);
    $date_from     = sanitize_text_field($_POST['date_from']     ?? '');
    $date_to       = sanitize_text_field($_POST['date_to']       ?? '');
    $page          = max(1, intval($_POST['page']                ?? 1));
    $per_page      = min(100, max(1, intval($_POST['per_page']   ?? 25)));
    $offset        = ($page - 1) * $per_page;

    $where  = ['1=1'];
    $params = [];

    if ($action_filter && in_array($action_filter, $allowed_actions)) {
        $where[] = 'a.action = %s'; $params[] = $action_filter;
    }
    if ($user_id)  { $where[] = 'a.changed_by = %d';     $params[] = $user_id; }
    if ($date_from){ $where[] = 'a.created_at >= %s';     $params[] = $date_from . ' 00:00:00'; }
    if ($date_to)  { $where[] = 'a.created_at <= %s';     $params[] = $date_to   . ' 23:59:59'; }

    $where_sql = 'WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) FROM {$audit_table} a {$where_sql}";
    $total     = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, ...$params)) : $wpdb->get_var($count_sql));

    $data_params = array_merge($params, [$per_page, $offset]);
    $data_sql    = "SELECT a.*, u.display_name AS changed_by_name
                   FROM {$audit_table} a
                   LEFT JOIN {$wpdb->users} u ON a.changed_by = u.ID
                   {$where_sql}
                   ORDER BY a.created_at DESC
                   LIMIT %d OFFSET %d";

    $logs = $wpdb->get_results($wpdb->prepare($data_sql, ...$data_params));

    wp_send_json_success(['logs' => $logs, 'total' => $total]);
}

// =============================================================================
// FRONTEND SHORTCODE — CONTACT FORM
// =============================================================================

function bntm_shortcode_crm_contact_form() {
    global $wpdb;
    $settings_table = $wpdb->prefix . 'bntm_crm_settings';

    $get_setting = function($key, $default = '') use ($wpdb, $settings_table) {
        $val = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", $key));
        return $val !== null ? $val : $default;
    };

    $enable_recaptcha   = $get_setting('crm_enable_recaptcha',   '0') === '1';
    $enable_gdpr        = $get_setting('crm_enable_gdpr',        '0') === '1';
    $enable_autorespond = $get_setting('crm_enable_autoresponder','0') === '1';
    $success_msg        = $get_setting('crm_contact_form_success_msg', 'Thank you! We will be in touch shortly.');
    $recaptcha_site_key = $get_setting('crm_recaptcha_site_key', '');

    $nonce = wp_create_nonce('crm_contact_form_nonce');

    ob_start();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php if ($enable_recaptcha && $recaptcha_site_key): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php endif; ?>
    </head>
    <body>
    <div class="crm-cf-wrapper">
        <div class="crm-cf-card" id="crm-cf-form-area">
            <div class="crm-cf-header">
                <h2>Get In Touch</h2>
                <p>Fill in your details below and we'll be in touch soon.</p>
            </div>

            <div id="crm-cf-success" style="display:none;">
                <div class="crm-cf-success-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3><?php echo esc_html($success_msg); ?></h3>
            </div>

            <div id="crm-cf-error-banner" style="display:none;" class="crm-cf-error-banner"></div>

            <form id="crm-contact-form" novalidate>
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                <!-- Honeypot -->
                <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;">
                    <input type="text" name="crm_hp_field" value="" autocomplete="off" tabindex="-1">
                </div>

                <div class="crm-cf-row crm-cf-grid-2">
                    <div class="crm-cf-field">
                        <label for="crm-cf-first-name">First Name <span class="crm-cf-req">*</span></label>
                        <input type="text" id="crm-cf-first-name" name="first_name" placeholder="John" required autocomplete="given-name">
                        <span class="crm-cf-field-error" id="err-first-name"></span>
                    </div>
                    <div class="crm-cf-field">
                        <label for="crm-cf-last-name">Last Name <span class="crm-cf-req">*</span></label>
                        <input type="text" id="crm-cf-last-name" name="last_name" placeholder="Smith" required autocomplete="family-name">
                        <span class="crm-cf-field-error" id="err-last-name"></span>
                    </div>
                </div>

                <div class="crm-cf-row">
                    <div class="crm-cf-field">
                        <label for="crm-cf-email">Email Address <span class="crm-cf-req">*</span></label>
                        <input type="email" id="crm-cf-email" name="email" placeholder="john@example.com" required autocomplete="email">
                        <span class="crm-cf-field-error" id="err-email"></span>
                    </div>
                </div>

                <div class="crm-cf-row crm-cf-grid-2">
                    <div class="crm-cf-field">
                        <label for="crm-cf-phone">Phone Number</label>
                        <input type="tel" id="crm-cf-phone" name="phone" placeholder="+1 000 000 0000" autocomplete="tel">
                    </div>
                    <div class="crm-cf-field">
                        <label for="crm-cf-company">Company</label>
                        <input type="text" id="crm-cf-company" name="company" placeholder="Your company" autocomplete="organization">
                    </div>
                </div>

                <div class="crm-cf-row">
                    <div class="crm-cf-field">
                        <label for="crm-cf-message">Message</label>
                        <textarea id="crm-cf-message" name="message" rows="4" placeholder="How can we help you?"></textarea>
                    </div>
                </div>

                <?php if ($enable_gdpr): ?>
                <div class="crm-cf-row">
                    <div class="crm-cf-field crm-cf-checkbox-field">
                        <label class="crm-cf-checkbox-label">
                            <input type="checkbox" name="gdpr_consent" id="crm-cf-gdpr" required>
                            <span>I consent to my data being stored and used to contact me. <span class="crm-cf-req">*</span></span>
                        </label>
                        <span class="crm-cf-field-error" id="err-gdpr"></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($enable_recaptcha && $recaptcha_site_key): ?>
                <div class="crm-cf-row">
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($recaptcha_site_key); ?>"></div>
                </div>
                <?php endif; ?>

                <div class="crm-cf-row">
                    <button type="submit" class="crm-cf-submit-btn" id="crm-cf-submit-btn">
                        <span id="crm-cf-btn-label">Send Message</span>
                        <span id="crm-cf-btn-spinner" style="display:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="animation:crm-spin 1s linear infinite;vertical-align:middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                            </svg>
                            Sending…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
    .crm-cf-wrapper { width: 100%; max-width: 560px; margin: 0 auto; }
    .crm-cf-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); overflow: hidden; }
    .crm-cf-header { padding: 32px 36px 24px; border-bottom: 1px solid #f3f4f6; }
    .crm-cf-header h2 { font-size: 22px; font-weight: 700; color: #111827; margin-bottom: 6px; }
    .crm-cf-header p  { font-size: 14px; color: #6b7280; }
    form { padding: 28px 36px 32px; }
    .crm-cf-row { margin-bottom: 18px; }
    .crm-cf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .crm-cf-field { display: flex; flex-direction: column; }
    .crm-cf-field label { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
    .crm-cf-req { color: #dc2626; }
    .crm-cf-field input,
    .crm-cf-field select,
    .crm-cf-field textarea {
        width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px;
        font-size: 14px; color: #111827; background: #fff; transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }
    .crm-cf-field input:focus,
    .crm-cf-field select:focus,
    .crm-cf-field textarea:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.10); }
    .crm-cf-field input.crm-cf-invalid { border-color: #dc2626; }
    .crm-cf-field-error { font-size: 12px; color: #dc2626; margin-top: 4px; min-height: 16px; }
    .crm-cf-checkbox-field { flex-direction: row; align-items: flex-start; }
    .crm-cf-checkbox-label { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #374151; cursor: pointer; font-weight: 400; }
    .crm-cf-checkbox-label input { width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; cursor: pointer; }
    .crm-cf-submit-btn {
        width: 100%; padding: 13px; background: #6366f1; color: #fff; border: none;
        border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }
    .crm-cf-submit-btn:hover   { background: #4f46e5; }
    .crm-cf-submit-btn:active  { transform: scale(0.98); }
    .crm-cf-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .crm-cf-error-banner { padding: 12px 16px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; color: #dc2626; font-size: 13px; margin-bottom: 18px; }
    .crm-cf-success-icon { text-align: center; margin: 32px 0 16px; color: #059669; }
    #crm-cf-success { padding: 32px 36px; text-align: center; }
    #crm-cf-success h3 { font-size: 18px; font-weight: 600; color: #111827; margin-top: 8px; line-height: 1.5; }
    @keyframes crm-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @media (max-width: 480px) {
        body { padding: 12px; align-items: flex-start; }
        .crm-cf-header, form { padding: 20px; }
        .crm-cf-grid-2 { grid-template-columns: 1fr; }
    }
    </style>

    <script>
    (function() {
        var form    = document.getElementById('crm-contact-form');
        var btn     = document.getElementById('crm-cf-submit-btn');
        var label   = document.getElementById('crm-cf-btn-label');
        var spinner = document.getElementById('crm-cf-btn-spinner');

        function setLoading(loading) {
            btn.disabled   = loading;
            label.style.display   = loading ? 'none' : '';
            spinner.style.display = loading ? '' : 'none';
        }

        function showError(fieldId, message) {
            var el = document.getElementById('err-' + fieldId);
            var input = document.getElementById('crm-cf-' + fieldId);
            if (el) el.textContent = message;
            if (input) input.classList.add('crm-cf-invalid');
        }

        function clearErrors() {
            document.querySelectorAll('.crm-cf-field-error').forEach(function(el) { el.textContent = ''; });
            document.querySelectorAll('.crm-cf-invalid').forEach(function(el) { el.classList.remove('crm-cf-invalid'); });
            var banner = document.getElementById('crm-cf-error-banner');
            banner.style.display  = 'none';
            banner.textContent    = '';
        }

        function validate() {
            var valid = true;
            var firstName = document.getElementById('crm-cf-first-name').value.trim();
            var lastName  = document.getElementById('crm-cf-last-name').value.trim();
            var email     = document.getElementById('crm-cf-email').value.trim();
            var gdprEl    = document.getElementById('crm-cf-gdpr');

            if (!firstName) { showError('first-name', 'First name is required.'); valid = false; }
            if (!lastName)  { showError('last-name',  'Last name is required.');  valid = false; }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email', 'A valid email address is required.'); valid = false;
            }
            if (gdprEl && !gdprEl.checked) {
                showError('gdpr', 'You must accept the consent checkbox.'); valid = false;
            }
            return valid;
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors();
            if (!validate()) return;
            setLoading(true);

            var fd = new FormData(form);
            fd.append('action', 'bntm_crm_submit_contact_form');

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    setLoading(false);
                    if (res.success) {
                        form.style.display = 'none';
                        document.getElementById('crm-cf-success').style.display = '';
                    } else {
                        var banner = document.getElementById('crm-cf-error-banner');
                        banner.textContent    = res.data ? res.data.message : 'Submission failed. Please try again.';
                        banner.style.display  = '';
                    }
                })
                .catch(function() {
                    setLoading(false);
                    var banner = document.getElementById('crm-cf-error-banner');
                    banner.textContent   = 'A network error occurred. Please try again.';
                    banner.style.display = '';
                });
        });

        document.querySelectorAll('.crm-cf-field input, .crm-cf-field textarea').forEach(function(el) {
            el.addEventListener('input', function() {
                this.classList.remove('crm-cf-invalid');
            });
        });
    })();
    </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX HANDLER — CONTACT FORM SUBMISSION (PUBLIC)
// =============================================================================

function bntm_ajax_crm_submit_contact_form() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_contact_form_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    if (!empty($_POST['crm_hp_field'])) {
        wp_send_json_success(['message' => 'Thank you!']);
    }

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $settings_table = $wpdb->prefix . 'bntm_crm_settings';

    $get_setting = function($key, $default = '') use ($wpdb, $settings_table) {
        $val = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", $key));
        return $val !== null ? $val : $default;
    };

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name']  ?? '');
    $email      = sanitize_email($_POST['email']           ?? '');
    $phone      = sanitize_text_field($_POST['phone']      ?? '');
    $company    = sanitize_text_field($_POST['company']    ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');

    if (!$first_name || !$last_name) wp_send_json_error(['message' => 'First and last name are required.']);
    if (!is_email($email))           wp_send_json_error(['message' => 'A valid email address is required.']);

    $default_user = intval($get_setting('crm_default_assigned_user', 0));
    $enable_admin_notify   = $get_setting('crm_admin_notify_new_lead',  '1') === '1';
    $enable_autoresponder  = $get_setting('crm_enable_autoresponder',   '0') === '1';
    $from_name  = $get_setting('crm_email_from_name',  get_bloginfo('name'));
    $from_email = $get_setting('crm_email_from_email', get_option('admin_email'));

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$contacts_table} WHERE email = %s AND deleted_at IS NULL", $email
    ));

    if ($existing) {
        if ($message) {
            crm_log_activity('note', $existing, null, 'New web form submission', $message, '', 0, 0);
        }
        wp_send_json_success(['message' => 'Thank you! We have received your message.']);
    }

    $contact_id = null;
    $result = $wpdb->insert($contacts_table, [
        'first_name'        => $first_name,
        'last_name'         => $last_name,
        'email'             => $email,
        'phone'             => $phone,
        'company'           => $company,
        'status'            => 'lead',
        'source'            => 'Web Form',
        'assigned_user_id'  => $default_user ?: null,
        'unsubscribe_token' => crm_generate_token(),
        'created_by'        => 0,
    ], ['%s','%s','%s','%s','%s','%s','%s','%s','%s','%d']);

    if (!$result) wp_send_json_error(['message' => 'Failed to save your details. Please try again.']);
    $contact_id = $wpdb->insert_id;

    if ($message) {
        crm_log_activity('note', $contact_id, null, 'Web form message', $message, '', 1, 0);
    }

    crm_audit_log('contact', $contact_id, 'created', ['source'], [], ['source' => 'Web Form']);

    if ($enable_admin_notify) {
        $admin_email   = get_option('admin_email');
        $subject       = 'New CRM Lead: ' . $first_name . ' ' . $last_name;
        $body          = "A new lead has been submitted via the contact form.\n\n";
        $body         .= "Name:    {$first_name} {$last_name}\n";
        $body         .= "Email:   {$email}\n";
        $body         .= "Phone:   {$phone}\n";
        $body         .= "Company: {$company}\n";
        if ($message) $body .= "Message: {$message}\n";
        wp_mail($admin_email, $subject, $body, ["From: {$from_name} <{$from_email}>"]);
    }

    if ($enable_autoresponder && $email) {
        $auto_subject = 'Thank you for contacting ' . get_bloginfo('name');
        $auto_body    = "Dear {$first_name},\n\nThank you for getting in touch. We have received your message and will respond shortly.\n\nBest regards,\n" . get_bloginfo('name');
        wp_mail($email, $auto_subject, $auto_body, ["From: {$from_name} <{$from_email}>"]);
    }

    wp_send_json_success(['message' => 'Thank you! We will be in touch shortly.', 'contact_id' => $contact_id]);
}

// =============================================================================
// FRONTEND SHORTCODE — CUSTOMER PORTAL
// =============================================================================

function bntm_shortcode_crm_customer_portal() {
    if (!is_user_logged_in()) {
        return '<div style="max-width:480px;margin:60px auto;text-align:center;font-family:sans-serif;">
            <p style="color:#374151;font-size:15px;">Please <a href="' . esc_url(wp_login_url(get_permalink())) . '" style="color:#6366f1;">log in</a> to access your customer portal.</p>
        </div>';
    }

    global $wpdb;
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table      = $wpdb->prefix . 'bntm_crm_deals';
    $stages_table     = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $activities_table = $wpdb->prefix . 'bntm_crm_activities';
    $files_table      = $wpdb->prefix . 'bntm_crm_files';

    $wp_user_id = get_current_user_id();
    $wp_user    = wp_get_current_user();

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE wp_user_id = %d AND deleted_at IS NULL LIMIT 1",
        $wp_user_id
    ));

    if (!$contact) {
        $contact = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$contacts_table} WHERE email = %s AND deleted_at IS NULL LIMIT 1",
            $wp_user->user_email
        ));
        if ($contact) {
            $wpdb->update($contacts_table, ['wp_user_id' => $wp_user_id], ['id' => $contact->id], ['%d'], ['%d']);
        }
    }

    $nonce = wp_create_nonce('crm_portal_nonce');

    ob_start();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Portal</title>
    </head>
    <body>
    <div class="crm-portal-wrapper">
        <div class="crm-portal-header">
            <div class="crm-portal-avatar">
                <?php echo esc_html(strtoupper(substr($wp_user->display_name, 0, 1))); ?>
            </div>
            <div>
                <h1>Welcome, <?php echo esc_html($wp_user->display_name); ?></h1>
                <p><?php echo esc_html($wp_user->user_email); ?></p>
            </div>
            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="crm-portal-logout">Sign Out</a>
        </div>

        <?php if (!$contact): ?>
        <div class="crm-portal-card" style="text-align:center;padding:48px;">
            <svg width="48" height="48" fill="none" stroke="#9ca3af" viewBox="0 0 24 24" style="margin-bottom:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <h3 style="color:#374151;margin-bottom:8px;">No CRM profile found</h3>
            <p style="color:#9ca3af;font-size:14px;">Your account is not yet linked to a CRM record. Please contact us for assistance.</p>
        </div>
        <?php else: ?>

        <!-- Portal sub-tabs -->
        <div class="crm-portal-tabs">
            <button class="crm-portal-tab active" data-tab="overview">Overview</button>
            <button class="crm-portal-tab" data-tab="deals">My Deals</button>
            <button class="crm-portal-tab" data-tab="activity">Activity</button>
            <button class="crm-portal-tab" data-tab="documents">Documents</button>
            <button class="crm-portal-tab" data-tab="update">Update Details</button>
        </div>

        <!-- OVERVIEW -->
        <div class="crm-portal-panel" id="crm-portal-tab-overview">
            <div class="crm-portal-card">
                <h3>Your Contact Details</h3>
                <div class="crm-portal-details-grid">
                    <?php
                    $detail_map = [
                        'Email'     => $contact->email,
                        'Phone'     => $contact->phone,
                        'Mobile'    => $contact->mobile,
                        'Company'   => $contact->company,
                        'Job Title' => $contact->job_title,
                        'Address'   => implode(', ', array_filter([
                            $contact->address_line_1,
                            $contact->city,
                            $contact->state,
                            $contact->country,
                        ])),
                    ];
                    foreach ($detail_map as $label => $value):
                        if (!$value) continue;
                    ?>
                    <div class="crm-portal-detail-item">
                        <span class="crm-portal-detail-label"><?php echo esc_html($label); ?></span>
                        <span class="crm-portal-detail-value"><?php echo esc_html($value); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Callback request -->
            <div class="crm-portal-card">
                <h3>Request a Callback</h3>
                <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Would you like us to give you a call? Let us know a good time.</p>
                <div id="crm-portal-callback-success" style="display:none;color:#059669;font-size:14px;font-weight:600;padding:10px 0;">Callback request sent. We will be in touch!</div>
                <form id="crm-portal-callback-form">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="contact_id" value="<?php echo intval($contact->id); ?>">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div>
                            <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Preferred Date</label>
                            <input type="date" name="preferred_date" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Preferred Time</label>
                            <input type="time" name="preferred_time" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Additional Notes</label>
                        <textarea name="notes" rows="2" placeholder="Any specific topics to discuss…" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;resize:vertical;font-family:inherit;"></textarea>
                    </div>
                    <button type="submit" class="crm-portal-btn-primary">Request Callback</button>
                </form>
            </div>
        </div>

        <!-- DEALS -->
        <div class="crm-portal-panel" id="crm-portal-tab-deals" style="display:none;">
            <?php
            $deals = $wpdb->get_results($wpdb->prepare(
                "SELECT d.*, ps.name AS stage_name, ps.colour AS stage_colour
                 FROM {$deals_table} d
                 LEFT JOIN {$stages_table} ps ON ps.id = d.stage_id
                 WHERE d.contact_id = %d AND d.deleted_at IS NULL
                 ORDER BY d.created_at DESC",
                $contact->id
            ));
            ?>
            <?php if (!empty($deals)): ?>
            <?php foreach ($deals as $deal): ?>
            <div class="crm-portal-card crm-portal-deal-card">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h4 style="font-size:16px;font-weight:600;color:#111827;margin-bottom:6px;"><?php echo esc_html($deal->title); ?></h4>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:<?php echo esc_attr($deal->stage_colour ?? '#e5e7eb'); ?>;color:#fff;">
                                <?php echo esc_html($deal->stage_name ?? ''); ?>
                            </span>
                            <span style="font-size:12px;padding:3px 9px;border-radius:20px;background:#f3f4f6;color:#374151;font-weight:500;">
                                <?php echo esc_html(ucfirst($deal->status)); ?>
                            </span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:20px;font-weight:700;color:#059669;"><?php echo crm_format_price($deal->value); ?></div>
                        <?php if ($deal->close_date): ?>
                        <div style="font-size:12px;color:#9ca3af;margin-top:3px;">Close: <?php echo esc_html(date('M j, Y', strtotime($deal->close_date))); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="crm-portal-card" style="text-align:center;padding:40px;">
                <p style="color:#9ca3af;font-size:14px;">No deals found for your account.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- ACTIVITY -->
        <div class="crm-portal-panel" id="crm-portal-tab-activity" style="display:none;">
            <div class="crm-portal-card">
                <h3>Activity History</h3>
                <?php
                $activities = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$activities_table}
                     WHERE contact_id = %d AND visible_to_customer = 1
                     ORDER BY logged_at DESC LIMIT 30",
                    $contact->id
                ));
                ?>
                <?php if (!empty($activities)): ?>
                <ul style="list-style:none;margin:0;padding:0;">
                    <?php foreach ($activities as $act): ?>
                    <li style="display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f3f4f6;">
                        <div style="width:36px;height:36px;border-radius:50%;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#6366f1;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px;font-weight:500;color:#111827;"><?php echo esc_html(ucfirst(str_replace('_',' ',$act->type))); ?><?php if ($act->subject): ?> — <?php echo esc_html($act->subject); ?><?php endif; ?></div>
                            <?php if ($act->body): ?>
                            <div style="font-size:13px;color:#6b7280;margin-top:3px;"><?php echo esc_html($act->body); ?></div>
                            <?php endif; ?>
                            <div style="font-size:12px;color:#9ca3af;margin-top:4px;"><?php echo esc_html(date('M j, Y g:i A', strtotime($act->logged_at))); ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div style="text-align:center;padding:30px 0;color:#9ca3af;font-size:14px;">No activity to display.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DOCUMENTS -->
        <div class="crm-portal-panel" id="crm-portal-tab-documents" style="display:none;">
            <div class="crm-portal-card">
                <h3>Your Documents</h3>
                <?php
                $files = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$files_table} WHERE contact_id = %d ORDER BY created_at DESC",
                    $contact->id
                ));
                ?>
                <?php if (!empty($files)): ?>
                <div>
                    <?php foreach ($files as $file):
                        $file_url = file_exists($file->file_path) ? str_replace(ABSPATH, site_url('/'), $file->file_path) : '';
                    ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f3f4f6;">
                        <div style="width:38px;height:38px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#6b7280;flex-shrink:0;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px;font-weight:500;color:#111827;"><?php echo esc_html($file->file_name); ?></div>
                            <div style="font-size:12px;color:#9ca3af;"><?php echo esc_html(date('M j, Y', strtotime($file->created_at))); ?></div>
                        </div>
                        <?php if ($file_url): ?>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank" class="crm-portal-btn-secondary" style="text-decoration:none;">Download</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:30px 0;color:#9ca3af;font-size:14px;">No documents shared with you yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- UPDATE DETAILS -->
        <div class="crm-portal-panel" id="crm-portal-tab-update" style="display:none;">
            <div class="crm-portal-card">
                <h3>Update My Details</h3>
                <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">Submit a change request. Our team will review and update your profile.</p>
                <div id="crm-portal-update-success" style="display:none;color:#059669;font-size:14px;font-weight:600;padding:12px 0;">Your update request has been submitted for review. Thank you!</div>
                <form id="crm-portal-update-form">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                    <input type="hidden" name="contact_id" value="<?php echo intval($contact->id); ?>">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                        <div>
                            <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">First Name</label>
                            <input type="text" name="first_name" value="<?php echo esc_attr($contact->first_name); ?>" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo esc_attr($contact->last_name); ?>" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Phone</label>
                        <input type="tel" name="phone" value="<?php echo esc_attr($contact->phone); ?>" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Company</label>
                        <input type="text" name="company" value="<?php echo esc_attr($contact->company); ?>" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;">
                    </div>
                    <div style="margin-bottom:18px;">
                        <label style="font-size:13px;font-weight:500;color:#374151;display:block;margin-bottom:5px;">Additional Notes</label>
                        <textarea name="notes" rows="3" placeholder="Any other changes to request…" style="width:100%;padding:9px 12px;border:1px solid #e5e7eb;border-radius:7px;font-size:14px;resize:vertical;font-family:inherit;"></textarea>
                    </div>
                    <button type="submit" class="crm-portal-btn-primary">Submit Update Request</button>
                </form>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; padding: 24px; }
    .crm-portal-wrapper { max-width: 780px; margin: 0 auto; }
    .crm-portal-header {
        background: #fff; border-radius: 14px; padding: 24px 28px;
        display: flex; align-items: center; gap: 18px; margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }
    .crm-portal-avatar {
        width: 52px; height: 52px; border-radius: 50%; background: #6366f1;
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 700; flex-shrink: 0;
    }
    .crm-portal-header h1 { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 3px; }
    .crm-portal-header p  { font-size: 13px; color: #9ca3af; }
    .crm-portal-logout {
        margin-left: auto; font-size: 13px; color: #6b7280; text-decoration: none;
        padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 6px; white-space: nowrap;
        transition: background 0.15s;
    }
    .crm-portal-logout:hover { background: #f3f4f6; }
    .crm-portal-tabs {
        display: flex; background: #fff; border-radius: 10px; padding: 4px;
        gap: 2px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        overflow-x: auto;
    }
    .crm-portal-tab {
        padding: 8px 18px; border: none; background: none; border-radius: 7px;
        font-size: 13px; font-weight: 500; color: #6b7280; cursor: pointer; white-space: nowrap;
        transition: all 0.15s;
    }
    .crm-portal-tab.active { background: #6366f1; color: #fff; }
    .crm-portal-tab:not(.active):hover { background: #f3f4f6; }
    .crm-portal-card {
        background: #fff; border-radius: 12px; padding: 24px 28px; margin-bottom: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }
    .crm-portal-card h3 { font-size: 15px; font-weight: 600; color: #111827; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; }
    .crm-portal-details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .crm-portal-detail-item { display: flex; flex-direction: column; gap: 3px; }
    .crm-portal-detail-label { font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; }
    .crm-portal-detail-value { font-size: 14px; color: #111827; font-weight: 500; }
    .crm-portal-deal-card { margin-bottom: 12px; border: 1px solid #f3f4f6; }
    .crm-portal-btn-primary {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 10px 22px; background: #6366f1; color: #fff; border: none;
        border-radius: 7px; font-size: 14px; font-weight: 600; cursor: pointer;
        transition: background 0.2s;
    }
    .crm-portal-btn-primary:hover { background: #4f46e5; }
    .crm-portal-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
    .crm-portal-btn-secondary {
        display: inline-flex; align-items: center; padding: 6px 14px;
        border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px;
        color: #374151; background: #fff; cursor: pointer; transition: background 0.15s;
    }
    .crm-portal-btn-secondary:hover { background: #f3f4f6; }
    @media (max-width: 600px) {
        body { padding: 12px; }
        .crm-portal-details-grid { grid-template-columns: 1fr; }
        .crm-portal-card { padding: 18px; }
        .crm-portal-header { flex-wrap: wrap; }
        .crm-portal-logout { margin-left: 0; }
    }
    </style>

    <script>
    (function() {
        document.querySelectorAll('.crm-portal-tab').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.crm-portal-tab').forEach(function(b) { b.classList.remove('active'); });
                document.querySelectorAll('.crm-portal-panel').forEach(function(p) { p.style.display = 'none'; });
                this.classList.add('active');
                var panel = document.getElementById('crm-portal-tab-' + this.dataset.tab);
                if (panel) panel.style.display = '';
            });
        });

        var callbackForm = document.getElementById('crm-portal-callback-form');
        if (callbackForm) {
            callbackForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = callbackForm.querySelector('button[type="submit"]');
                btn.disabled = true;
                var fd = new FormData(callbackForm);
                fd.append('action', 'bntm_crm_portal_callback_request');
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btn.disabled = false;
                        if (res.success) {
                            callbackForm.style.display = 'none';
                            document.getElementById('crm-portal-callback-success').style.display = '';
                        }
                    })
                    .catch(function() { btn.disabled = false; });
            });
        }

        var updateForm = document.getElementById('crm-portal-update-form');
        if (updateForm) {
            updateForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = updateForm.querySelector('button[type="submit"]');
                btn.disabled = true;
                var fd = new FormData(updateForm);
                fd.append('action', 'bntm_crm_portal_update_details');
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        btn.disabled = false;
                        if (res.success) {
                            updateForm.style.display = 'none';
                            document.getElementById('crm-portal-update-success').style.display = '';
                        }
                    })
                    .catch(function() { btn.disabled = false; });
            });
        }
    })();
    </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX — PORTAL CALLBACK REQUEST
// =============================================================================

function bntm_ajax_crm_portal_callback_request() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_portal_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    global $wpdb;
    $contact_id = intval($_POST['contact_id'] ?? 0);
    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact.']);

    $date  = sanitize_text_field($_POST['preferred_date'] ?? '');
    $time  = sanitize_text_field($_POST['preferred_time'] ?? '');
    $notes = sanitize_textarea_field($_POST['notes']      ?? '');

    $body = 'Callback requested.';
    if ($date) $body .= ' Preferred date: ' . $date;
    if ($time) $body .= ' at ' . $time . '.';
    if ($notes) $body .= ' Notes: ' . $notes;

    crm_log_activity('callback_request', $contact_id, null, 'Callback Requested via Portal', $body, '', 1, 0);
    wp_send_json_success(['message' => 'Callback request logged.']);
}

// =============================================================================
// AJAX — PORTAL UPDATE DETAILS REQUEST
// =============================================================================

function bntm_ajax_crm_portal_update_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_portal_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    global $wpdb;
    $contact_id = intval($_POST['contact_id'] ?? 0);
    if (!$contact_id) wp_send_json_error(['message' => 'Invalid contact.']);

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name']  ?? '');
    $phone      = sanitize_text_field($_POST['phone']      ?? '');
    $company    = sanitize_text_field($_POST['company']    ?? '');
    $notes      = sanitize_textarea_field($_POST['notes']  ?? '');

    $body  = "Update request from customer portal.\n";
    $body .= "First Name: {$first_name}\nLast Name: {$last_name}\nPhone: {$phone}\nCompany: {$company}";
    if ($notes) $body .= "\nNotes: {$notes}";

    crm_log_activity('note', $contact_id, null, 'Update Details Request (Pending Review)', $body, '', 0, 0);
    wp_send_json_success(['message' => 'Update request submitted.']);
}

// =============================================================================
// FRONTEND SHORTCODE — DEAL VIEW
// =============================================================================

function bntm_shortcode_crm_deal_view() {
    $token = sanitize_text_field($_GET['token'] ?? '');

    if (!$token && is_user_logged_in()) {
        $token = sanitize_text_field($_GET['t'] ?? '');
    }

    if (!$token) {
        return '<div style="max-width:480px;margin:60px auto;text-align:center;font-family:sans-serif;color:#374151;font-size:15px;">Invalid or missing deal token.</div>';
    }

    global $wpdb;
    $deals_table      = $wpdb->prefix . 'bntm_crm_deals';
    $stages_table     = $wpdb->prefix . 'bntm_crm_pipeline_stages';
    $contacts_table   = $wpdb->prefix . 'bntm_crm_contacts';
    $line_items_table = $wpdb->prefix . 'bntm_crm_deal_line_items';
    $settings_table   = $wpdb->prefix . 'bntm_crm_settings';

    $get_setting = function($key, $default = '') use ($wpdb, $settings_table) {
        $val = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", $key));
        return $val !== null ? $val : $default;
    };

    $deal = $wpdb->get_row($wpdb->prepare(
        "SELECT d.*, ps.name AS stage_name,
                CONCAT(c.first_name,' ',c.last_name) AS contact_name,
                c.email AS contact_email,
                u.display_name AS rep_name, u.user_email AS rep_email
         FROM {$deals_table} d
         LEFT JOIN {$stages_table} ps ON ps.id = d.stage_id
         LEFT JOIN {$contacts_table} c ON c.id = d.contact_id
         LEFT JOIN {$wpdb->users} u ON d.assigned_user_id = u.ID
         WHERE d.access_token = %s AND d.deleted_at IS NULL",
        $token
    ));

    if (!$deal) {
        return '<div style="max-width:480px;margin:60px auto;text-align:center;font-family:sans-serif;color:#374151;font-size:15px;">Deal not found or link is invalid.</div>';
    }

    if ($deal->token_expires_at && strtotime($deal->token_expires_at) < time()) {
        return '<div style="max-width:560px;margin:60px auto;text-align:center;font-family:sans-serif;">
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:40px;">
                <h2 style="color:#dc2626;font-size:18px;margin-bottom:10px;">This Proposal Has Expired</h2>
                <p style="color:#6b7280;font-size:14px;">The link for this proposal is no longer valid. Please contact us for an updated proposal.</p>
            </div>
        </div>';
    }

    $line_items  = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$line_items_table} WHERE deal_id = %d ORDER BY sort_order ASC",
        $deal->id
    ));

    $brand_colour = $get_setting('crm_brand_colour', '#6366f1');
    $brand_logo   = $get_setting('crm_brand_logo',   '');
    $site_name    = get_bloginfo('name');

    $total = array_reduce((array) $line_items, function($carry, $item) {
        return $carry + (floatval($item->quantity) * floatval($item->unit_price));
    }, 0.0);

    crm_log_activity('deal_view', $deal->contact_id, $deal->id, 'Proposal viewed', 'Viewed via token link', '', 0, 0);

    $nonce = wp_create_nonce('crm_deal_view_nonce');

    ob_start();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html($deal->title); ?> — <?php echo esc_html($site_name); ?></title>
        <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; padding: 32px 16px; min-height: 100vh; }
        .crm-dv-wrapper { max-width: 720px; margin: 0 auto; }
        .crm-dv-header {
            background: <?php echo esc_attr($brand_colour); ?>;
            border-radius: 14px 14px 0 0;
            padding: 36px 40px;
            color: #fff;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
        }
        .crm-dv-logo { max-height: 48px; max-width: 180px; object-fit: contain; }
        .crm-dv-logo-text { font-size: 20px; font-weight: 700; color: #fff; }
        .crm-dv-header-right { text-align: right; }
        .crm-dv-header-right h1 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .crm-dv-header-right p  { font-size: 13px; opacity: 0.85; }
        .crm-dv-body { background: #fff; padding: 36px 40px; }
        .crm-dv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid #f3f4f6; }
        .crm-dv-meta-item label { font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
        .crm-dv-meta-item span  { font-size: 15px; font-weight: 500; color: #111827; }
        .crm-dv-section-title { font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 14px; }
        .crm-dv-line-items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .crm-dv-line-items thead th { text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; padding: 8px 12px; border-bottom: 2px solid #f3f4f6; }
        .crm-dv-line-items tbody td { padding: 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #374151; }
        .crm-dv-line-items tfoot td { padding: 14px 12px; font-weight: 700; font-size: 16px; color: #111827; border-top: 2px solid #e5e7eb; }
        .crm-dv-total-value { color: #059669; font-size: 20px; }
        .crm-dv-actions { display: flex; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1px solid #f3f4f6; flex-wrap: wrap; }
        .crm-dv-btn-accept {
            flex: 1; padding: 14px; background: #059669; color: #fff; border: none;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s; min-width: 140px;
        }
        .crm-dv-btn-accept:hover { background: #047857; }
        .crm-dv-btn-decline {
            flex: 1; padding: 14px; background: #fff; color: #dc2626; border: 2px solid #dc2626;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; min-width: 140px;
        }
        .crm-dv-btn-decline:hover { background: #fee2e2; }
        .crm-dv-btn-print {
            padding: 14px 20px; background: #f3f4f6; color: #374151; border: none;
            border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;
            transition: background 0.2s;
        }
        .crm-dv-btn-print:hover { background: #e5e7eb; }
        .crm-dv-footer { background: #f9fafb; border-radius: 0 0 14px 14px; padding: 20px 40px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
        .crm-dv-footer p { font-size: 12px; color: #9ca3af; }
        .crm-dv-status-banner { padding: 16px 20px; border-radius: 8px; text-align: center; font-size: 15px; font-weight: 600; margin-top: 24px; }
        .crm-dv-status-accepted { background: #d1fae5; color: #065f46; }
        .crm-dv-status-declined  { background: #fee2e2; color: #991b1b; }
        .crm-dv-decline-reason { display: none; margin-top: 12px; }
        .crm-dv-decline-reason textarea { width: 100%; padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 7px; font-size: 14px; font-family: inherit; resize: vertical; }
        @media print {
            .crm-dv-actions, .crm-dv-decline-reason { display: none !important; }
            body { background: #fff; padding: 0; }
            .crm-dv-wrapper { max-width: 100%; }
        }
        @media (max-width: 560px) {
            .crm-dv-header, .crm-dv-body, .crm-dv-footer { padding: 24px 20px; }
            .crm-dv-meta { grid-template-columns: 1fr; }
        }
        </style>
    </head>
    <body>
    <div class="crm-dv-wrapper">
        <div class="crm-dv-header">
            <div>
                <?php if ($brand_logo): ?>
                <img src="<?php echo esc_url($brand_logo); ?>" alt="<?php echo esc_attr($site_name); ?>" class="crm-dv-logo">
                <?php else: ?>
                <div class="crm-dv-logo-text"><?php echo esc_html($site_name); ?></div>
                <?php endif; ?>
            </div>
            <div class="crm-dv-header-right">
                <h1><?php echo esc_html($deal->title); ?></h1>
                <p>Prepared for <?php echo esc_html($deal->contact_name); ?></p>
                <?php if ($deal->close_date): ?>
                <p>Valid until <?php echo esc_html(date('M j, Y', strtotime($deal->close_date))); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="crm-dv-body">
            <div class="crm-dv-meta">
                <div class="crm-dv-meta-item">
                    <label>Prepared For</label>
                    <span><?php echo esc_html($deal->contact_name); ?></span>
                </div>
                <div class="crm-dv-meta-item">
                    <label>Your Representative</label>
                    <span><?php echo esc_html($deal->rep_name ?? $site_name); ?></span>
                </div>
                <div class="crm-dv-meta-item">
                    <label>Proposal Date</label>
                    <span><?php echo esc_html(date('M j, Y', strtotime($deal->created_at))); ?></span>
                </div>
                <?php if ($deal->close_date): ?>
                <div class="crm-dv-meta-item">
                    <label>Expiry Date</label>
                    <span><?php echo esc_html(date('M j, Y', strtotime($deal->close_date))); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($line_items)): ?>
            <div class="crm-dv-section-title">Proposal Summary</div>
            <table class="crm-dv-line-items">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Unit Price</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($line_items as $item):
                        $line_total = floatval($item->quantity) * floatval($item->unit_price);
                    ?>
                    <tr>
                        <td><?php echo esc_html($item->description); ?></td>
                        <td style="text-align:right;"><?php echo esc_html(number_format(floatval($item->quantity), 2)); ?></td>
                        <td style="text-align:right;"><?php echo crm_format_price($item->unit_price); ?></td>
                        <td style="text-align:right;font-weight:600;"><?php echo crm_format_price($line_total); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;font-size:14px;">Total Amount</td>
                        <td style="text-align:right;" class="crm-dv-total-value"><?php echo crm_format_price($total); ?></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <div class="crm-dv-meta-item" style="margin-bottom:24px;">
                <label>Deal Value</label>
                <span class="crm-dv-total-value" style="font-size:22px;font-weight:700;"><?php echo crm_format_price($deal->value); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($deal->status === 'open'): ?>
            <div class="crm-dv-actions">
                <button class="crm-dv-btn-accept" id="crm-dv-accept-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Accept Proposal
                </button>
                <button class="crm-dv-btn-decline" id="crm-dv-decline-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Decline
                </button>
                <button class="crm-dv-btn-print" onclick="window.print()">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:middle;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print / PDF
                </button>
            </div>
            <div class="crm-dv-decline-reason" id="crm-dv-decline-reason">
                <textarea id="crm-dv-decline-text" rows="3" placeholder="Please tell us why you're declining (optional)…"></textarea>
                <div style="display:flex;gap:10px;margin-top:10px;">
                    <button class="crm-dv-btn-decline" id="crm-dv-decline-confirm-btn" style="flex:none;padding:10px 20px;font-size:14px;">Confirm Decline</button>
                    <button onclick="document.getElementById('crm-dv-decline-reason').style.display='none';" style="padding:10px 16px;background:#f3f4f6;border:none;border-radius:7px;cursor:pointer;font-size:14px;">Cancel</button>
                </div>
            </div>
            <?php elseif ($deal->status === 'won'): ?>
            <div class="crm-dv-status-banner crm-dv-status-accepted">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                You have accepted this proposal. Thank you!
            </div>
            <?php elseif ($deal->status === 'lost'): ?>
            <div class="crm-dv-status-banner crm-dv-status-declined">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                This proposal has been declined.
            </div>
            <?php endif; ?>

            <div id="crm-dv-action-result"></div>
        </div>

        <div class="crm-dv-footer">
            <p><?php echo esc_html($site_name); ?> &mdash; <?php echo esc_html(date('Y')); ?></p>
            <?php if ($deal->rep_name): ?>
            <p>Your rep: <?php echo esc_html($deal->rep_name); ?><?php if ($deal->rep_email): ?> &lt;<?php echo esc_html($deal->rep_email); ?>&gt;<?php endif; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function() {
        var token  = <?php echo json_encode($token); ?>;
        var nonce  = <?php echo json_encode($nonce); ?>;
        var ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;

        function sendAction(outcome, reason) {
            var fd = new FormData();
            fd.append('action',  'bntm_crm_deal_action');
            fd.append('nonce',   nonce);
            fd.append('token',   token);
            fd.append('outcome', outcome);
            if (reason) fd.append('reason', reason);
            return fetch(ajaxUrl, { method: 'POST', body: fd }).then(function(r) { return r.json(); });
        }

        var acceptBtn = document.getElementById('crm-dv-accept-btn');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to accept this proposal?')) return;
                acceptBtn.disabled = true;
                sendAction('won', '').then(function(res) {
                    var resultEl = document.getElementById('crm-dv-action-result');
                    if (res.success) {
                        document.querySelector('.crm-dv-actions').style.display = 'none';
                        resultEl.innerHTML = '<div class="crm-dv-status-banner crm-dv-status-accepted" style="margin-top:24px;">Thank you! You have accepted this proposal. We will be in touch shortly.</div>';
                    } else {
                        acceptBtn.disabled = false;
                        resultEl.innerHTML = '<p style="color:#dc2626;font-size:13px;margin-top:10px;">' + (res.data ? res.data.message : 'An error occurred.') + '</p>';
                    }
                });
            });
        }

        var declineBtn = document.getElementById('crm-dv-decline-btn');
        if (declineBtn) {
            declineBtn.addEventListener('click', function() {
                document.getElementById('crm-dv-decline-reason').style.display = '';
            });
        }

        var declineConfirmBtn = document.getElementById('crm-dv-decline-confirm-btn');
        if (declineConfirmBtn) {
            declineConfirmBtn.addEventListener('click', function() {
                var reason = document.getElementById('crm-dv-decline-text').value;
                declineConfirmBtn.disabled = true;
                sendAction('lost', reason).then(function(res) {
                    var resultEl = document.getElementById('crm-dv-action-result');
                    if (res.success) {
                        document.querySelector('.crm-dv-actions').style.display         = 'none';
                        document.getElementById('crm-dv-decline-reason').style.display  = 'none';
                        resultEl.innerHTML = '<div class="crm-dv-status-banner crm-dv-status-declined" style="margin-top:24px;">You have declined this proposal. Thank you for letting us know.</div>';
                    } else {
                        declineConfirmBtn.disabled = false;
                        resultEl.innerHTML = '<p style="color:#dc2626;font-size:13px;margin-top:10px;">' + (res.data ? res.data.message : 'An error occurred.') + '</p>';
                    }
                });
            });
        }
    })();
    </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX — DEAL VIEW ACTION (ACCEPT / DECLINE)
// =============================================================================

function bntm_ajax_crm_deal_action() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_deal_view_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    global $wpdb;
    $deals_table = $wpdb->prefix . 'bntm_crm_deals';
    $token       = sanitize_text_field($_POST['token']   ?? '');
    $outcome     = sanitize_text_field($_POST['outcome'] ?? '');
    $reason      = sanitize_textarea_field($_POST['reason'] ?? '');

    if (!$token || !in_array($outcome, ['won','lost'])) wp_send_json_error(['message' => 'Invalid parameters.']);

    $deal = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$deals_table} WHERE access_token = %s AND deleted_at IS NULL", $token
    ));
    if (!$deal) wp_send_json_error(['message' => 'Deal not found.']);
    if ($deal->status !== 'open') wp_send_json_error(['message' => 'This proposal has already been actioned.']);

    $update = ['status' => $outcome, 'updated_at' => current_time('mysql')];
    if ($outcome === 'lost' && $reason) $update['lost_reason'] = $reason;

    $result = $wpdb->update($deals_table, $update, ['id' => $deal->id], ['%s','%s'], ['%d']);
    if ($result === false) wp_send_json_error(['message' => 'Failed to update deal.']);

    $body = $outcome === 'won' ? 'Contact accepted the proposal via deal view page.' : 'Contact declined the proposal via deal view page.' . ($reason ? ' Reason: ' . $reason : '');
    crm_log_activity('stage_change', $deal->contact_id, $deal->id, 'Proposal ' . $outcome, $body, $outcome, 1, 0);
    crm_audit_log('deal', $deal->id, 'status_changed', ['status'], ['status' => 'open'], ['status' => $outcome]);

    wp_send_json_success(['message' => 'Proposal status updated.', 'outcome' => $outcome]);
}

// =============================================================================
// AJAX — LOG DEAL VIEW
// =============================================================================

function bntm_ajax_crm_log_deal_view() {
    $token = sanitize_text_field($_POST['token'] ?? '');
    if (!$token) wp_send_json_error(['message' => 'No token.']);

    global $wpdb;
    $deal = $wpdb->get_row($wpdb->prepare(
        "SELECT id, contact_id FROM {$wpdb->prefix}bntm_crm_deals WHERE access_token = %s AND deleted_at IS NULL",
        $token
    ));

    if ($deal) {
        crm_log_activity('deal_view', $deal->contact_id, $deal->id, 'Deal page viewed', '', '', 0, 0);
    }

    wp_send_json_success();
}

// =============================================================================
// FRONTEND SHORTCODE — UNSUBSCRIBE PAGE
// =============================================================================

function bntm_shortcode_crm_unsubscribe() {
    $token = sanitize_text_field($_GET['token'] ?? '');

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $settings_table = $wpdb->prefix . 'bntm_crm_settings';

    $confirm_msg = $wpdb->get_var($wpdb->prepare(
        "SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", 'crm_unsubscribe_confirm_msg'
    )) ?: 'You have been successfully unsubscribed from our communications.';

    if (!$token) {
        return '<div style="max-width:480px;margin:60px auto;text-align:center;font-family:sans-serif;color:#374151;">Invalid unsubscribe link.</div>';
    }

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE unsubscribe_token = %s AND deleted_at IS NULL", $token
    ));

    if (!$contact) {
        return '<div style="max-width:480px;margin:60px auto;text-align:center;font-family:sans-serif;color:#374151;font-size:15px;">This unsubscribe link is invalid or has already been processed.</div>';
    }

    $nonce = wp_create_nonce('crm_unsubscribe_nonce');

    ob_start();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Unsubscribe — <?php bloginfo('name'); ?></title>
        <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .crm-unsub-card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); max-width: 480px; width: 100%; padding: 40px; text-align: center; }
        .crm-unsub-icon { margin-bottom: 20px; }
        .crm-unsub-card h2 { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 10px; }
        .crm-unsub-card p  { font-size: 14px; color: #6b7280; line-height: 1.6; margin-bottom: 20px; }
        .crm-unsub-email { font-weight: 600; color: #374151; }
        .crm-unsub-btn-confirm {
            width: 100%; padding: 13px; background: #dc2626; color: #fff; border: none;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
            transition: background 0.2s; margin-bottom: 10px;
        }
        .crm-unsub-btn-confirm:hover    { background: #b91c1c; }
        .crm-unsub-btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
        .crm-unsub-btn-resub {
            width: 100%; padding: 12px; background: #fff; color: #059669; border: 2px solid #059669;
            border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; margin-top: 6px;
        }
        .crm-unsub-btn-resub:hover { background: #d1fae5; }
        .crm-unsub-reason-select { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; margin-bottom: 14px; font-family: inherit; }
        .crm-unsub-result { padding: 16px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-top: 16px; }
        .crm-unsub-result-success { background: #d1fae5; color: #065f46; }
        .crm-unsub-result-resub   { background: #dbeafe; color: #1e40af; }
        .crm-unsub-result-error   { background: #fee2e2; color: #991b1b; }
        </style>
    </head>
    <body>
    <div class="crm-unsub-card" id="crm-unsub-main">
        <div class="crm-unsub-icon">
            <svg width="52" height="52" fill="none" stroke="#dc2626" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <h2>Unsubscribe from Communications</h2>
        <p>You are about to unsubscribe <span class="crm-unsub-email"><?php echo esc_html($contact->email); ?></span> from all future communications from <strong><?php bloginfo('name'); ?></strong>.</p>

        <div id="crm-unsub-form-area">
            <select class="crm-unsub-reason-select" id="crm-unsub-reason">
                <option value="">Select a reason (optional)</option>
                <option value="Too many emails">Too many emails</option>
                <option value="Content not relevant">Content not relevant</option>
                <option value="Never signed up">Never signed up</option>
                <option value="Other">Other</option>
            </select>
            <button class="crm-unsub-btn-confirm" id="crm-unsub-confirm-btn">Confirm Unsubscribe</button>
            <div id="crm-unsub-result"></div>
        </div>

        <div id="crm-resub-area" style="display:none;">
            <p style="color:#059669;font-weight:600;margin-bottom:16px;"><?php echo esc_html($confirm_msg); ?></p>
            <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">Changed your mind? You can re-subscribe below.</p>
            <button class="crm-unsub-btn-resub" id="crm-resub-btn">Re-subscribe</button>
            <div id="crm-resub-result"></div>
        </div>
    </div>

    <script>
    (function() {
        var token   = <?php echo json_encode($token); ?>;
        var nonce   = <?php echo json_encode($nonce); ?>;
        var ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;

        document.getElementById('crm-unsub-confirm-btn').addEventListener('click', function() {
            var btn    = this;
            var reason = document.getElementById('crm-unsub-reason').value;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'bntm_crm_unsubscribe_action');
            fd.append('nonce',  nonce);
            fd.append('token',  token);
            fd.append('reason', reason);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    if (res.success) {
                        document.getElementById('crm-unsub-form-area').style.display = 'none';
                        document.getElementById('crm-resub-area').style.display      = '';
                    } else {
                        var el = document.getElementById('crm-unsub-result');
                        el.className   = 'crm-unsub-result crm-unsub-result-error';
                        el.textContent = res.data ? res.data.message : 'An error occurred.';
                    }
                })
                .catch(function() { btn.disabled = false; });
        });

        document.getElementById('crm-resub-btn').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'bntm_crm_resubscribe_action');
            fd.append('nonce',  nonce);
            fd.append('token',  token);
            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    var el = document.getElementById('crm-resub-result');
                    if (res.success) {
                        el.className   = 'crm-unsub-result crm-unsub-result-resub';
                        el.textContent = 'You have been re-subscribed. Welcome back!';
                        btn.style.display = 'none';
                    } else {
                        el.className   = 'crm-unsub-result crm-unsub-result-error';
                        el.textContent = res.data ? res.data.message : 'An error occurred.';
                    }
                })
                .catch(function() { btn.disabled = false; });
        });
    })();
    </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX — UNSUBSCRIBE ACTION
// =============================================================================

function bntm_ajax_crm_unsubscribe_action() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_unsubscribe_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $token          = sanitize_text_field($_POST['token']  ?? '');
    $reason         = sanitize_text_field($_POST['reason'] ?? '');

    if (!$token) wp_send_json_error(['message' => 'Invalid token.']);

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE unsubscribe_token = %s AND deleted_at IS NULL", $token
    ));
    if (!$contact) wp_send_json_error(['message' => 'Contact not found.']);

    $wpdb->update($contacts_table, ['email_opt_out' => 1], ['id' => $contact->id], ['%d'], ['%d']);

    $body = 'Contact unsubscribed via email link.' . ($reason ? ' Reason: ' . $reason : '');
    crm_log_activity('unsubscribe', $contact->id, null, 'Unsubscribed', $body, '', 0, 0);
    crm_audit_log('contact', $contact->id, 'status_changed', ['email_opt_out'], ['email_opt_out' => 0], ['email_opt_out' => 1]);

    $settings_table  = $wpdb->prefix . 'bntm_crm_settings';
    $notify_admin    = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$settings_table} WHERE setting_key = %s", 'crm_admin_notify_new_lead'));
    if ($notify_admin === '1') {
        $admin_email = get_option('admin_email');
        wp_mail($admin_email, 'CRM: Contact Unsubscribed — ' . $contact->email,
            "Contact {$contact->first_name} {$contact->last_name} ({$contact->email}) has unsubscribed." . ($reason ? "\nReason: {$reason}" : ''));
    }

    wp_send_json_success(['message' => 'Unsubscribed successfully.']);
}

// =============================================================================
// AJAX — RESUBSCRIBE ACTION
// =============================================================================

function bntm_ajax_crm_resubscribe_action() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'crm_unsubscribe_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $token          = sanitize_text_field($_POST['token'] ?? '');

    if (!$token) wp_send_json_error(['message' => 'Invalid token.']);

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE unsubscribe_token = %s AND deleted_at IS NULL", $token
    ));
    if (!$contact) wp_send_json_error(['message' => 'Contact not found.']);

    $wpdb->update($contacts_table, ['email_opt_out' => 0], ['id' => $contact->id], ['%d'], ['%d']);

    crm_log_activity('note', $contact->id, null, 'Re-subscribed', 'Contact re-subscribed via unsubscribe page.', '', 0, 0);

    wp_send_json_success(['message' => 'Re-subscribed successfully.']);
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

function crm_format_price($amount) {
    global $wpdb;
    $currency = $wpdb->get_var(
        "SELECT setting_value FROM {$wpdb->prefix}bntm_crm_settings WHERE setting_key = 'crm_currency' LIMIT 1"
    ) ?: 'USD';
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'PHP' => '₱', 'AED' => 'AED ', 'AUD' => 'A$', 'CAD' => 'CA$', 'SGD' => 'S$'];
    $symbol  = $symbols[$currency] ?? '$';
    return $symbol . number_format((float) $amount, 2);
}

function crm_get_stats($business_id) {
    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $deals_table    = $wpdb->prefix . 'bntm_crm_deals';
    $tasks_table    = $wpdb->prefix . 'bntm_crm_tasks';

    return [
        'total_contacts' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$contacts_table} WHERE deleted_at IS NULL"),
        'open_deals'     => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL"),
        'pipeline_value' => (float) $wpdb->get_var("SELECT COALESCE(SUM(value),0) FROM {$deals_table} WHERE status = 'open' AND deleted_at IS NULL"),
        'open_tasks'     => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$tasks_table} WHERE status != 'done'"),
        'overdue_tasks'  => (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$tasks_table} WHERE status != 'done' AND due_date < %s",
            current_time('mysql')
        )),
    ];
}

function crm_check_dependencies() {
    $required = [];
    $missing  = [];
    foreach ($required as $mod) {
        if (!bntm_is_module_enabled($mod)) $missing[] = $mod;
    }
    return empty($missing) ? true : $missing;
}

function crm_get_default_pipeline_id() {
    global $wpdb;
    $pipelines_table = $wpdb->prefix . 'bntm_crm_pipelines';
    $id = $wpdb->get_var("SELECT id FROM {$pipelines_table} WHERE is_default = 1 LIMIT 1");
    if (!$id) {
        $id = $wpdb->get_var("SELECT id FROM {$pipelines_table} ORDER BY sort_order ASC LIMIT 1");
    }
    return intval($id);
}

function crm_log_activity($type, $contact_id, $deal_id, $subject, $body, $outcome, $visible_to_customer, $logged_by_override) {
    global $wpdb;
    $allowed_types = ['call','email','meeting','note','task_completion','callback_request','deal_view','stage_change','unsubscribe'];
    if (!in_array($type, $allowed_types)) return false;

    $logged_by = $logged_by_override ? intval($logged_by_override) : get_current_user_id();

    return $wpdb->insert(
        $wpdb->prefix . 'bntm_crm_activities',
        [
            'type'                => $type,
            'contact_id'          => $contact_id ? intval($contact_id) : null,
            'deal_id'             => $deal_id    ? intval($deal_id)    : null,
            'subject'             => sanitize_text_field($subject),
            'body'                => sanitize_textarea_field($body),
            'outcome'             => sanitize_text_field($outcome),
            'visible_to_customer' => intval($visible_to_customer),
            'logged_by'           => $logged_by,
            'logged_at'           => current_time('mysql'),
        ],
        ['%s','%s','%s','%s','%s','%s','%d','%d','%s']
    );
}

function crm_get_contact_by_wp_user($wp_user_id) {
    global $wpdb;
    $contacts_table = $wpdb->prefix . 'bntm_crm_contacts';
    $wp_user_id     = intval($wp_user_id);

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$contacts_table} WHERE wp_user_id = %d AND deleted_at IS NULL LIMIT 1",
        $wp_user_id
    ));

    if (!$contact) {
        $user    = get_user_by('ID', $wp_user_id);
        if ($user) {
            $contact = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$contacts_table} WHERE email = %s AND deleted_at IS NULL LIMIT 1",
                $user->user_email
            ));
            if ($contact) {
                $wpdb->update($contacts_table, ['wp_user_id' => $wp_user_id], ['id' => $contact->id], ['%d'], ['%d']);
            }
        }
    }

    return $contact;
}

function crm_generate_token() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(32));
    }
    return bin2hex(openssl_random_pseudo_bytes(32));
}

function crm_audit_log($object_type, $object_id, $action, $changed_fields, $old_values, $new_values) {
    global $wpdb;
    $allowed_actions = ['created','updated','deleted','imported','exported','merged','stage_changed','status_changed'];
    if (!in_array($action, $allowed_actions)) return false;

    return $wpdb->insert(
        $wpdb->prefix . 'bntm_crm_audit_log',
        [
            'object_type'    => sanitize_text_field($object_type),
            'object_id'      => intval($object_id),
            'action'         => $action,
            'changed_by'     => get_current_user_id(),
            'changed_fields' => is_array($changed_fields) ? json_encode($changed_fields) : $changed_fields,
            'old_values'     => is_array($old_values)     ? json_encode($old_values)     : $old_values,
            'new_values'     => is_array($new_values)     ? json_encode($new_values)     : $new_values,
            'ip_address'     => sanitize_text_field($_SERVER['REMOTE_ADDR']     ?? ''),
            'user_agent'     => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 499)),
            'created_at'     => current_time('mysql'),
        ],
        ['%s','%d','%s','%d','%s','%s','%s','%s','%s','%s']
    );
}

function crm_get_activity_icon($type) {
    $icons = [
        'call'             => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>',
        'email'            => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'meeting'          => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'note'             => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
        'task_completion'  => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'stage_change'     => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>',
        'deal_view'        => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
        'unsubscribe'      => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>',
        'callback_request' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
    ];
    return $icons[$type] ?? $icons['note'];
}

function crm_output_contacts_csv($contacts, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    $headers = ['ID','First Name','Last Name','Email','Phone','Mobile','Company','Job Title','Status','Source','Type','Address','City','State','Postcode','Country','Website','Created At'];
    fputcsv($output, $headers);
    foreach ((array) $contacts as $c) {
        fputcsv($output, [
            $c->id,
            $c->first_name,
            $c->last_name,
            $c->email,
            $c->phone,
            $c->mobile,
            $c->company,
            $c->job_title,
            $c->status,
            $c->source,
            $c->type,
            trim(($c->address_line_1 ?? '') . ' ' . ($c->address_line_2 ?? '')),
            $c->city,
            $c->state,
            $c->postcode,
            $c->country,
            $c->website,
            $c->created_at,
        ]);
    }
    fclose($output);
}