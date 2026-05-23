<?php
/**
 * Module Name: Simple CRM
 * Module Slug: crm
 * Description: A HubSpot-inspired internal CRM for managing contacts, companies,
 *              deals, tasks, and activity history. Supports B2B and individual
 *              customers with an owner-first dashboard, pipeline management,
 *              and basic admin customization via custom properties.
 * Version: 1.0.0
 * Author: BNTM
 * Icon: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BNTM_CRM_PATH', dirname( __FILE__ ) . '/' );
define( 'BNTM_CRM_URL',  plugin_dir_url( __FILE__ ) );

// =============================================================================
// MODULE CONFIGURATION
// =============================================================================

function bntm_crm_get_pages() {
    return [
        'CRM Dashboard' => '[crm_dashboard]',
    ];
}

function bntm_crm_get_tables() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $prefix  = $wpdb->prefix;

    return [
        'crm_contacts' => "CREATE TABLE {$prefix}crm_contacts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            owner_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            company_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            first_name VARCHAR(100) NOT NULL DEFAULT '',
            last_name VARCHAR(100) NOT NULL DEFAULT '',
            email VARCHAR(191) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            lifecycle_status VARCHAR(50) NOT NULL DEFAULT 'lead',
            tags TEXT DEFAULT NULL,
            custom_properties LONGTEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_owner (owner_id),
            INDEX idx_company (company_id),
            INDEX idx_lifecycle (lifecycle_status)
        ) {$charset};",

        'crm_companies' => "CREATE TABLE {$prefix}crm_companies (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            owner_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            name VARCHAR(191) NOT NULL DEFAULT '',
            industry VARCHAR(100) NOT NULL DEFAULT '',
            website VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            tags TEXT DEFAULT NULL,
            custom_properties LONGTEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_owner (owner_id)
        ) {$charset};",

        'crm_deals' => "CREATE TABLE {$prefix}crm_deals (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            owner_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            contact_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            company_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            pipeline_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
            stage_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            name VARCHAR(191) NOT NULL DEFAULT '',
            amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            expected_close_date DATE DEFAULT NULL,
            custom_properties LONGTEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'open',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_owner (owner_id),
            INDEX idx_stage (stage_id),
            INDEX idx_contact (contact_id),
            INDEX idx_company (company_id),
            INDEX idx_status (status)
        ) {$charset};",

        'crm_tasks' => "CREATE TABLE {$prefix}crm_tasks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            assignee_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            linked_type VARCHAR(50) NOT NULL DEFAULT '',
            linked_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(255) NOT NULL DEFAULT '',
            due_date DATE DEFAULT NULL,
            reminder_days INT UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_assignee (assignee_id),
            INDEX idx_linked (linked_type, linked_id),
            INDEX idx_due (due_date)
        ) {$charset};",

        'crm_activities' => "CREATE TABLE {$prefix}crm_activities (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            author_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            linked_type VARCHAR(50) NOT NULL DEFAULT '',
            linked_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            activity_type VARCHAR(50) NOT NULL DEFAULT 'note',
            body TEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_linked (linked_type, linked_id),
            INDEX idx_author (author_id)
        ) {$charset};",

        'crm_pipeline_stages' => "CREATE TABLE {$prefix}crm_pipeline_stages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            pipeline_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
            name VARCHAR(100) NOT NULL DEFAULT '',
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            color VARCHAR(20) NOT NULL DEFAULT '#6366f1',
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_pipeline (pipeline_id),
            INDEX idx_sort (sort_order)
        ) {$charset};",

        'crm_custom_properties' => "CREATE TABLE {$prefix}crm_custom_properties (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            object_type VARCHAR(50) NOT NULL DEFAULT 'contact',
            field_name VARCHAR(100) NOT NULL DEFAULT '',
            field_label VARCHAR(191) NOT NULL DEFAULT '',
            field_type VARCHAR(50) NOT NULL DEFAULT 'text',
            field_options TEXT DEFAULT NULL,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_object (object_type)
        ) {$charset};",
    ];
}

function bntm_crm_get_shortcodes() {
    return [
        'crm_dashboard' => 'bntm_shortcode_crm',
    ];
}

function bntm_crm_create_tables() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $tables = bntm_crm_get_tables();
    foreach ( $tables as $sql ) {
        dbDelta( $sql );
    }
    crm_seed_default_pipeline_stages();
    return count( $tables );
}

// =============================================================================
// AJAX ACTION HOOKS
// =============================================================================

// Dashboard
add_action( 'wp_ajax_crm_get_dashboard_stats',   'bntm_ajax_crm_get_dashboard_stats' );
add_action( 'wp_ajax_crm_get_recent_activity',   'bntm_ajax_crm_get_recent_activity' );

// Contacts
add_action( 'wp_ajax_crm_get_contacts',          'bntm_ajax_crm_get_contacts' );
add_action( 'wp_ajax_crm_create_contact',        'bntm_ajax_crm_create_contact' );
add_action( 'wp_ajax_crm_update_contact',        'bntm_ajax_crm_update_contact' );
add_action( 'wp_ajax_crm_delete_contact',        'bntm_ajax_crm_delete_contact' );

// Companies
add_action( 'wp_ajax_crm_get_companies',         'bntm_ajax_crm_get_companies' );
add_action( 'wp_ajax_crm_create_company',        'bntm_ajax_crm_create_company' );
add_action( 'wp_ajax_crm_update_company',        'bntm_ajax_crm_update_company' );
add_action( 'wp_ajax_crm_delete_company',        'bntm_ajax_crm_delete_company' );

// Deals
add_action( 'wp_ajax_crm_get_deals',             'bntm_ajax_crm_get_deals' );
add_action( 'wp_ajax_crm_create_deal',           'bntm_ajax_crm_create_deal' );
add_action( 'wp_ajax_crm_update_deal',           'bntm_ajax_crm_update_deal' );
add_action( 'wp_ajax_crm_delete_deal',           'bntm_ajax_crm_delete_deal' );
add_action( 'wp_ajax_crm_move_deal_stage',       'bntm_ajax_crm_move_deal_stage' );

// Tasks
add_action( 'wp_ajax_crm_get_tasks',             'bntm_ajax_crm_get_tasks' );
add_action( 'wp_ajax_crm_create_task',           'bntm_ajax_crm_create_task' );
add_action( 'wp_ajax_crm_update_task',           'bntm_ajax_crm_update_task' );
add_action( 'wp_ajax_crm_delete_task',           'bntm_ajax_crm_delete_task' );
add_action( 'wp_ajax_crm_complete_task',         'bntm_ajax_crm_complete_task' );

// Settings — Pipeline Stages
add_action( 'wp_ajax_crm_get_stages',            'bntm_ajax_crm_get_stages' );
add_action( 'wp_ajax_crm_save_stage',            'bntm_ajax_crm_save_stage' );
add_action( 'wp_ajax_crm_delete_stage',          'bntm_ajax_crm_delete_stage' );
add_action( 'wp_ajax_crm_reorder_stages',        'bntm_ajax_crm_reorder_stages' );

// Settings — Custom Properties
add_action( 'wp_ajax_crm_get_custom_properties', 'bntm_ajax_crm_get_custom_properties' );
add_action( 'wp_ajax_crm_save_custom_property',  'bntm_ajax_crm_save_custom_property' );
add_action( 'wp_ajax_crm_delete_custom_property','bntm_ajax_crm_delete_custom_property' );

// =============================================================================
// MAIN DASHBOARD SHORTCODE
// =============================================================================

function bntm_shortcode_crm() {
    if ( ! is_user_logged_in() ) {
        return '<div class="bntm-notice">Please log in to access the CRM.</div>';
    }

    $current_user = wp_get_current_user();
    $business_id  = $current_user->ID;
    $is_admin     = current_user_can( 'manage_options' );
    $active_tab   = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';

    ob_start();
    ?>
    <script>
    var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
    var crm_nonce = '<?php echo wp_create_nonce( 'crm_nonce' ); ?>';
    var crm_is_admin = <?php echo $is_admin ? 'true' : 'false'; ?>;
    var crm_user_id = <?php echo intval( $business_id ); ?>;
    </script>

    <div class="bntm-crm-container">

        <div class="bntm-tabs">
            <a href="?tab=dashboard" class="bntm-tab <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="?tab=contacts" class="bntm-tab <?php echo $active_tab === 'contacts' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Contacts
            </a>
            <a href="?tab=companies" class="bntm-tab <?php echo $active_tab === 'companies' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Companies
            </a>
            <a href="?tab=deals" class="bntm-tab <?php echo $active_tab === 'deals' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Deals
            </a>
            <a href="?tab=tasks" class="bntm-tab <?php echo $active_tab === 'tasks' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Tasks
            </a>
            <?php if ( $is_admin ) : ?>
            <a href="?tab=settings" class="bntm-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
            <?php endif; ?>
        </div>

        <div class="bntm-tab-content">
            <?php if ( $active_tab === 'dashboard' ) : ?>
                <?php echo crm_dashboard_tab( $business_id ); ?>
            <?php elseif ( $active_tab === 'contacts' ) : ?>
                <?php echo crm_contacts_tab( $business_id ); ?>
            <?php elseif ( $active_tab === 'companies' ) : ?>
                <?php echo crm_companies_tab( $business_id ); ?>
            <?php elseif ( $active_tab === 'deals' ) : ?>
                <?php echo crm_deals_tab( $business_id ); ?>
            <?php elseif ( $active_tab === 'tasks' ) : ?>
                <?php echo crm_tasks_tab( $business_id ); ?>
            <?php elseif ( $active_tab === 'settings' && $is_admin ) : ?>
                <?php echo crm_settings_tab( $business_id ); ?>
            <?php endif; ?>
        </div>
    </div>

    <style>
    .bntm-crm-container { width: 100%; }

    /* ── Tab Navigation ── */
    .bntm-crm-container .bntm-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 24px;
        padding-bottom: 0;
    }
    .bntm-crm-container .bntm-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: color .2s, border-color .2s;
        white-space: nowrap;
    }
    .bntm-crm-container .bntm-tab:hover { color: var(--bntm-primary); }
    .bntm-crm-container .bntm-tab.active {
        color: var(--bntm-primary);
        border-bottom-color: var(--bntm-primary);
    }

    /* ── Stat Cards ── */
    .bntm-stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .bntm-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .bntm-stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--bntm-primary);
    }
    .bntm-stat-card .stat-content h3 {
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        margin: 0 0 4px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .bntm-stat-card .stat-number {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 2px;
        line-height: 1;
    }
    .bntm-stat-card .stat-label {
        font-size: 11px;
        color: #9ca3af;
    }

    /* ── Form Sections ── */
    .bntm-form-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }
    .bntm-form-section h3 {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin: 0 0 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* ── Two-column layout for record detail feel ── */
    .crm-two-col {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .crm-two-col { grid-template-columns: 1fr; }
    }

    /* ── Table ── */
    .bntm-table-wrapper {
        width: 100%;
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }
    .bntm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .bntm-table thead tr {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .bntm-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }
    .bntm-table td {
        padding: 12px 16px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .bntm-table tbody tr:last-child td { border-bottom: none; }
    .bntm-table tbody tr:hover { background: #f9fafb; }

    /* ── Badges ── */
    .crm-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .crm-badge-lead       { background: #eff6ff; color: #2563eb; }
    .crm-badge-prospect   { background: #f0fdf4; color: #16a34a; }
    .crm-badge-customer   { background: #fdf4ff; color: #9333ea; }
    .crm-badge-churned    { background: #fef2f2; color: #dc2626; }
    .crm-badge-open       { background: #eff6ff; color: #2563eb; }
    .crm-badge-closed     { background: #f0fdf4; color: #16a34a; }
    .crm-badge-pending    { background: #fffbeb; color: #d97706; }
    .crm-badge-complete   { background: #f0fdf4; color: #16a34a; }
    .crm-badge-overdue    { background: #fef2f2; color: #dc2626; }

    /* ── Search / Filter Row ── */
    .crm-filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 16px;
    }
    .crm-filter-row input,
    .crm-filter-row select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background: #fff;
        outline: none;
        transition: border-color .2s;
    }
    .crm-filter-row input:focus,
    .crm-filter-row select:focus { border-color: var(--bntm-primary); }
    .crm-filter-row input { min-width: 220px; }

    /* ── Modal ── */
    .crm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 99990;
        align-items: center;
        justify-content: center;
    }
    .crm-modal-overlay.open { display: flex; }
    .crm-modal {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        animation: crmModalIn .2s ease;
    }
    @keyframes crmModalIn {
        from { opacity: 0; transform: translateY(-16px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .crm-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    .crm-modal-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .crm-modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: #9ca3af;
        padding: 4px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        transition: color .2s, background .2s;
    }
    .crm-modal-close:hover { color: #374151; background: #f3f4f6; }
    .crm-modal-body { padding: 20px 24px; }
    .crm-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* ── Form fields inside modals ── */
    .crm-field-group {
        margin-bottom: 16px;
    }
    .crm-field-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .crm-field-group input,
    .crm-field-group select,
    .crm-field-group textarea {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background: #fff;
        box-sizing: border-box;
        outline: none;
        transition: border-color .2s;
    }
    .crm-field-group input:focus,
    .crm-field-group select:focus,
    .crm-field-group textarea:focus { border-color: var(--bntm-primary); }
    .crm-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 480px) { .crm-field-row { grid-template-columns: 1fr; } }

    /* ── Toast ── */
    #crm-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        gap: 8px;
        pointer-events: none;
    }
    .crm-toast-item {
        background: #1f2937;
        color: #fff;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 16px rgba(0,0,0,.2);
        animation: crmToastIn .25s ease;
        pointer-events: all;
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 240px;
    }
    .crm-toast-item.success { border-left: 4px solid #22c55e; }
    .crm-toast-item.error   { border-left: 4px solid #ef4444; }
    @keyframes crmToastIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Pipeline board ── */
    .crm-board {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 12px;
        align-items: flex-start;
    }
    .crm-board-col {
        min-width: 230px;
        max-width: 230px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        flex-shrink: 0;
    }
    .crm-board-col-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .crm-board-col-header h4 {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .crm-stage-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .crm-board-col-count {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        background: #e5e7eb;
        padding: 2px 7px;
        border-radius: 20px;
    }
    .crm-deal-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        padding: 12px;
        margin-bottom: 8px;
        cursor: grab;
        transition: box-shadow .2s, transform .15s;
    }
    .crm-deal-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        transform: translateY(-1px);
    }
    .crm-deal-card.dragging {
        opacity: .5;
        cursor: grabbing;
    }
    .crm-board-col.drag-over { background: #eff6ff; border-color: var(--bntm-primary); }
    .crm-deal-card-name {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }
    .crm-deal-card-amount {
        font-size: 14px;
        font-weight: 700;
        color: var(--bntm-primary);
        margin-bottom: 4px;
    }
    .crm-deal-card-meta {
        font-size: 11px;
        color: #9ca3af;
    }

    /* ── Activity timeline ── */
    .crm-timeline { padding: 4px 0; }
    .crm-timeline-item {
        display: flex;
        gap: 12px;
        padding-bottom: 20px;
        position: relative;
    }
    .crm-timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .crm-timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid #e5e7eb;
    }
    .crm-timeline-content { flex: 1; }
    .crm-timeline-meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Empty state ── */
    .crm-empty {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
    }
    .crm-empty svg { margin: 0 auto 12px; display: block; opacity: .4; }
    .crm-empty p { font-size: 14px; }

    /* ── Overdue highlight ── */
    .crm-overdue-section { border-left: 3px solid #ef4444; }
    .crm-upcoming-section { border-left: 3px solid #22c55e; }

    /* ── Settings: sortable stages ── */
    .crm-stage-list { list-style: none; margin: 0; padding: 0; }
    .crm-stage-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 8px;
        cursor: grab;
    }
    .crm-stage-item.drag-over { background: #eff6ff; border-color: var(--bntm-primary); }
    .crm-stage-item .crm-stage-drag-handle { color: #d1d5db; cursor: grab; flex-shrink: 0; }
    .crm-stage-item .crm-stage-name { flex: 1; font-size: 14px; font-weight: 500; color: #111827; }
    .crm-stage-item .crm-stage-color-dot {
        width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0;
    }

    /* ── Custom props table ── */
    .crm-prop-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: #f3f4f6;
        color: #374151;
        text-transform: uppercase;
    }

    @media (max-width: 640px) {
        .bntm-stats-row { grid-template-columns: 1fr 1fr; }
        .crm-filter-row input { min-width: 100%; }
    }
    </style>

    <div id="crm-toast"></div>

    <script>
    (function() {
        // ── Toast utility ──
        window.crmToast = function(msg, type) {
            type = type || 'success';
            var container = document.getElementById('crm-toast');
            var item = document.createElement('div');
            item.className = 'crm-toast-item ' + type;
            var icon = type === 'success'
                ? '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            item.innerHTML = icon + msg;
            container.appendChild(item);
            setTimeout(function() {
                item.style.opacity = '0';
                item.style.transition = 'opacity .3s';
                setTimeout(function() { item.remove(); }, 300);
            }, 3200);
        };

        // ── Modal open/close utility ──
        window.crmOpenModal = function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('open');
        };
        window.crmCloseModal = function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.classList.remove('open');
                var form = el.querySelector('form');
                if (form) form.reset();
                var hiddenId = el.querySelector('[name="record_id"]');
                if (hiddenId) hiddenId.value = '';
                var title = el.querySelector('.crm-modal-header h3');
                if (title && title.dataset.default) title.textContent = title.dataset.default;
            }
        };

        // ── Close modal on overlay click ──
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('crm-modal-overlay')) {
                e.target.classList.remove('open');
            }
        });

        // ── Generic AJAX POST utility ──
        window.crmPost = function(action, data, onSuccess, onError) {
            data.action = action;
            data.nonce  = crm_nonce;
            var body = new URLSearchParams(data);
            return fetch(ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    if (onSuccess) onSuccess(res.data);
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : 'An error occurred.';
                    crmToast(msg, 'error');
                    if (onError) onError(res.data);
                }
            })
            .catch(function() {
                crmToast('Network error. Please try again.', 'error');
                if (onError) onError();
            });
        };

        // ── Confirm dialog utility ──
        window.crmConfirm = function(msg, onConfirm) {
            if (window.confirm(msg)) onConfirm();
        };

        // ── Format currency (client-side) ──
        window.crmFormatCurrency = function(amount) {
            return '&#8369;' + parseFloat(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            });
        };

        // ── Escape HTML ──
        window.crmEsc = function(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        };
    })();
    </script>
    <?php
    $content = ob_get_clean();
    return bntm_universal_container( 'CRM', $content );
}

// =============================================================================
// TAB 1 — DASHBOARD
// =============================================================================

function crm_dashboard_tab( $business_id ) {
    global $wpdb;

    $today = current_time( 'Y-m-d' );

    $total_contacts = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
         WHERE business_id = %d AND status = 'active'",
        $business_id
    ) );

    $total_companies = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_companies
         WHERE business_id = %d AND status = 'active'",
        $business_id
    ) );

    $total_open_deals = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE business_id = %d AND status = 'open'",
        $business_id
    ) );

    $pipeline_value = (float) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}crm_deals
         WHERE business_id = %d AND status = 'open'",
        $business_id
    ) );

    $overdue_tasks = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
         WHERE business_id = %d AND status = 'pending' AND due_date < %s",
        $business_id, $today
    ) );

    $stage_breakdown = $wpdb->get_results( $wpdb->prepare(
        "SELECT ps.name, ps.color, COUNT(d.id) AS deal_count,
                COALESCE(SUM(d.amount), 0) AS stage_value
         FROM {$wpdb->prefix}crm_pipeline_stages ps
         LEFT JOIN {$wpdb->prefix}crm_deals d
           ON d.stage_id = ps.id AND d.business_id = %d AND d.status = 'open'
         WHERE ps.business_id = %d AND ps.status = 'active'
         GROUP BY ps.id, ps.name, ps.color, ps.sort_order
         ORDER BY ps.sort_order ASC",
        $business_id, $business_id
    ) );

    $recent_activities = $wpdb->get_results( $wpdb->prepare(
        "SELECT a.*, u.display_name AS author_name
         FROM {$wpdb->prefix}crm_activities a
         LEFT JOIN {$wpdb->users} u ON u.ID = a.author_id
         WHERE a.business_id = %d AND a.status = 'active'
         ORDER BY a.created_at DESC
         LIMIT 10",
        $business_id
    ) );

    $overdue_task_list = $wpdb->get_results( $wpdb->prepare(
        "SELECT t.*, u.display_name AS assignee_name
         FROM {$wpdb->prefix}crm_tasks t
         LEFT JOIN {$wpdb->users} u ON u.ID = t.assignee_id
         WHERE t.business_id = %d AND t.status = 'pending' AND t.due_date < %s
         ORDER BY t.due_date ASC
         LIMIT 5",
        $business_id, $today
    ) );

    ob_start();
    ?>
    <div class="bntm-stats-row">

        <div class="bntm-stat-card">
            <div class="stat-icon" style="background: var(--bntm-primary);">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Contacts</h3>
                <p class="stat-number"><?php echo number_format( $total_contacts ); ?></p>
                <span class="stat-label">Total active</span>
            </div>
        </div>

        <div class="bntm-stat-card">
            <div class="stat-icon" style="background: #0ea5e9;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Companies</h3>
                <p class="stat-number"><?php echo number_format( $total_companies ); ?></p>
                <span class="stat-label">Total active</span>
            </div>
        </div>

        <div class="bntm-stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Open Deals</h3>
                <p class="stat-number"><?php echo number_format( $total_open_deals ); ?></p>
                <span class="stat-label">In pipeline</span>
            </div>
        </div>

        <div class="bntm-stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Pipeline Value</h3>
                <p class="stat-number" style="font-size:20px;"><?php echo crm_format_currency( $pipeline_value ); ?></p>
                <span class="stat-label">Open deals total</span>
            </div>
        </div>

        <div class="bntm-stat-card">
            <div class="stat-icon" style="background: #ef4444;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Overdue Tasks</h3>
                <p class="stat-number"><?php echo number_format( $overdue_tasks ); ?></p>
                <span class="stat-label">Need attention</span>
            </div>
        </div>

    </div>

    <div class="crm-two-col">

        <div>
            <!-- Pipeline Stage Breakdown -->
            <div class="bntm-form-section">
                <h3>Pipeline Stage Breakdown</h3>
                <?php if ( empty( $stage_breakdown ) ) : ?>
                    <div class="crm-empty">
                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>No pipeline stages configured yet.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ( $stage_breakdown as $stage ) :
                        $pct = $pipeline_value > 0
                            ? round( ( $stage->stage_value / $pipeline_value ) * 100 )
                            : 0;
                    ?>
                    <div class="crm-stage-breakdown-row">
                        <div class="crm-stage-breakdown-label">
                            <span class="crm-stage-dot" style="background:<?php echo esc_attr( $stage->color ); ?>;"></span>
                            <span class="crm-stage-breakdown-name"><?php echo esc_html( $stage->name ); ?></span>
                            <span class="crm-stage-breakdown-count"><?php echo intval( $stage->deal_count ); ?> deal<?php echo $stage->deal_count == 1 ? '' : 's'; ?></span>
                        </div>
                        <div class="crm-stage-bar-wrap">
                            <div class="crm-stage-bar-fill" style="width:<?php echo $pct; ?>%;background:<?php echo esc_attr( $stage->color ); ?>;"></div>
                        </div>
                        <div class="crm-stage-breakdown-value">
                            <?php echo crm_format_currency( $stage->stage_value ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="bntm-form-section">
                <h3>Recent Activity</h3>
                <?php if ( empty( $recent_activities ) ) : ?>
                    <div class="crm-empty">
                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <p>No activity recorded yet.</p>
                    </div>
                <?php else : ?>
                    <div class="crm-timeline">
                        <?php foreach ( $recent_activities as $act ) :
                            $icon_map = [
                                'note'             => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>',
                                'task_created'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                                'task_completed'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 9l2 2 4-4"/>',
                                'deal_moved'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>',
                                'status_changed'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
                            ];
                            $svg_path = $icon_map[ $act->activity_type ] ?? $icon_map['note'];
                            $time_diff = crm_time_ago( $act->created_at );
                        ?>
                        <div class="crm-timeline-item">
                            <div class="crm-timeline-icon">
                                <svg width="14" height="14" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                    <?php echo $svg_path; ?>
                                </svg>
                            </div>
                            <div class="crm-timeline-content">
                                <div style="font-size:13px;font-weight:500;color:#111827;">
                                    <?php echo esc_html( crm_activity_label( $act->activity_type ) ); ?>
                                    <span style="font-weight:400;color:#374151;">
                                        — <?php echo esc_html( $act->body ? wp_trim_words( $act->body, 12 ) : '' ); ?>
                                    </span>
                                </div>
                                <div class="crm-timeline-meta">
                                    <?php echo esc_html( $act->author_name ); ?>
                                    &middot; <?php echo esc_html( $time_diff ); ?>
                                    &middot; <?php echo esc_html( ucfirst( $act->linked_type ) ); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right column -->
        <div>
            <!-- Overdue Tasks -->
            <div class="bntm-form-section crm-overdue-section">
                <h3 style="color:#dc2626;">
                    <svg width="16" height="16" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Overdue Tasks
                </h3>
                <?php if ( empty( $overdue_task_list ) ) : ?>
                    <p style="font-size:13px;color:#9ca3af;text-align:center;padding:12px 0;">
                        No overdue tasks. All caught up.
                    </p>
                <?php else : ?>
                    <?php foreach ( $overdue_task_list as $task ) :
                        $days_over = (int) ( ( strtotime( $today ) - strtotime( $task->due_date ) ) / DAY_IN_SECONDS );
                    ?>
                    <div class="crm-overdue-task-row">
                        <div class="crm-overdue-task-title">
                            <?php echo esc_html( $task->title ); ?>
                        </div>
                        <div class="crm-overdue-task-meta">
                            <span class="crm-badge crm-badge-overdue">
                                <?php echo $days_over; ?> day<?php echo $days_over == 1 ? '' : 's'; ?> overdue
                            </span>
                            &nbsp;
                            <span style="font-size:11px;color:#9ca3af;">
                                <?php echo esc_html( $task->assignee_name ); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ( $overdue_tasks > 5 ) : ?>
                        <a href="?tab=tasks" style="display:block;text-align:center;font-size:13px;color:var(--bntm-primary);margin-top:12px;text-decoration:none;">
                            View all <?php echo intval( $overdue_tasks ); ?> overdue tasks &rarr;
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Quick Stats Summary -->
            <div class="bntm-form-section">
                <h3>Quick Summary</h3>
                <?php
                $won_deals = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
                     WHERE business_id = %d AND status = 'closed'
                     AND stage_id IN (
                         SELECT id FROM {$wpdb->prefix}crm_pipeline_stages
                         WHERE business_id = %d AND name LIKE %s
                     )",
                    $business_id, $business_id, '%Won%'
                ) );
                $this_month_contacts = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
                     WHERE business_id = %d AND status = 'active'
                     AND MONTH(created_at) = MONTH(%s) AND YEAR(created_at) = YEAR(%s)",
                    $business_id, $today, $today
                ) );
                $pending_tasks_count = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
                     WHERE business_id = %d AND status = 'pending' AND due_date >= %s",
                    $business_id, $today
                ) );
                ?>
                <div class="crm-quick-summary-list">
                    <div class="crm-quick-summary-row">
                        <span class="crm-quick-summary-label">New contacts this month</span>
                        <span class="crm-quick-summary-value"><?php echo number_format( $this_month_contacts ); ?></span>
                    </div>
                    <div class="crm-quick-summary-row">
                        <span class="crm-quick-summary-label">Deals won</span>
                        <span class="crm-quick-summary-value" style="color:#16a34a;"><?php echo number_format( $won_deals ); ?></span>
                    </div>
                    <div class="crm-quick-summary-row">
                        <span class="crm-quick-summary-label">Upcoming tasks</span>
                        <span class="crm-quick-summary-value"><?php echo number_format( $pending_tasks_count ); ?></span>
                    </div>
                    <div class="crm-quick-summary-row">
                        <span class="crm-quick-summary-label">Open pipeline value</span>
                        <span class="crm-quick-summary-value" style="color:var(--bntm-primary);">
                            <?php echo crm_format_currency( $pipeline_value ); ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
    .crm-stage-breakdown-row {
        display: grid;
        grid-template-columns: 1fr 120px 100px;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .crm-stage-breakdown-label {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .crm-stage-breakdown-name {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .crm-stage-breakdown-count {
        font-size: 11px;
        color: #9ca3af;
        white-space: nowrap;
    }
    .crm-stage-bar-wrap {
        height: 8px;
        background: #f3f4f6;
        border-radius: 4px;
        overflow: hidden;
    }
    .crm-stage-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width .4s ease;
        min-width: 2px;
    }
    .crm-stage-breakdown-value {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        text-align: right;
        white-space: nowrap;
    }
    .crm-overdue-task-row {
        padding: 10px 0;
        border-bottom: 1px solid #fef2f2;
    }
    .crm-overdue-task-row:last-child { border-bottom: none; }
    .crm-overdue-task-title {
        font-size: 13px;
        font-weight: 500;
        color: #111827;
        margin-bottom: 4px;
    }
    .crm-overdue-task-meta {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .crm-quick-summary-list { padding: 4px 0; }
    .crm-quick-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 9px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .crm-quick-summary-row:last-child { border-bottom: none; }
    .crm-quick-summary-label {
        font-size: 13px;
        color: #6b7280;
    }
    .crm-quick-summary-value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }
    @media (max-width: 640px) {
        .crm-stage-breakdown-row {
            grid-template-columns: 1fr 80px;
        }
        .crm-stage-breakdown-value { display: none; }
    }
    </style>

    <script>
    (function() {
        // Dashboard tab has no interactive AJAX — data is server-rendered on load.
        // Refresh button could be added in a future iteration.
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 2 — CONTACTS
// =============================================================================

function crm_contacts_tab( $business_id ) {
    global $wpdb;

    $companies = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}crm_companies
         WHERE business_id = %d AND status = 'active'
         ORDER BY name ASC",
        $business_id
    ) );

    $users = get_users( [ 'fields' => [ 'ID', 'display_name' ] ] );

    $custom_props = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND object_type = 'contact' AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    ob_start();
    ?>

    <!-- Filter Row -->
    <div class="crm-filter-row">
        <input type="text" id="crm-contact-search"
               placeholder="Search name, email, phone..."
               style="flex:1;min-width:200px;">
        <select id="crm-contact-filter-status">
            <option value="">All Statuses</option>
            <option value="lead">Lead</option>
            <option value="prospect">Prospect</option>
            <option value="customer">Customer</option>
            <option value="churned">Churned</option>
        </select>
        <select id="crm-contact-filter-owner">
            <option value="">All Owners</option>
            <?php foreach ( $users as $u ) : ?>
                <option value="<?php echo intval( $u->ID ); ?>">
                    <?php echo esc_html( $u->display_name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="bntm-btn-primary" id="crm-contact-add-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Contact
        </button>
    </div>

    <!-- Contacts Table -->
    <div class="bntm-form-section" style="padding:0;overflow:hidden;">
        <div id="crm-contacts-table-wrap">
            <div class="crm-empty" style="padding:48px;">
                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p>Loading contacts...</p>
            </div>
        </div>
        <div id="crm-contacts-pagination" style="padding:16px 20px;border-top:1px solid #f3f4f6;display:flex;align-items:center;gap:8px;flex-wrap:wrap;"></div>
    </div>

    <!-- Add / Edit Contact Modal -->
    <div class="crm-modal-overlay" id="crm-contact-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3 data-default="Add Contact">Add Contact</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-contact-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-contact-id" name="record_id" value="">
                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>First Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="crm-contact-first-name" placeholder="First name">
                    </div>
                    <div class="crm-field-group">
                        <label>Last Name</label>
                        <input type="text" id="crm-contact-last-name" placeholder="Last name">
                    </div>
                </div>
                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Email</label>
                        <input type="email" id="crm-contact-email" placeholder="email@example.com">
                    </div>
                    <div class="crm-field-group">
                        <label>Phone</label>
                        <input type="text" id="crm-contact-phone" placeholder="+1 555 000 0000">
                    </div>
                </div>
                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Lifecycle Status</label>
                        <select id="crm-contact-lifecycle">
                            <option value="lead">Lead</option>
                            <option value="prospect">Prospect</option>
                            <option value="customer">Customer</option>
                            <option value="churned">Churned</option>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Owner</label>
                        <select id="crm-contact-owner">
                            <option value="">Unassigned</option>
                            <?php foreach ( $users as $u ) : ?>
                                <option value="<?php echo intval( $u->ID ); ?>">
                                    <?php echo esc_html( $u->display_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="crm-field-group">
                    <label>Company</label>
                    <select id="crm-contact-company">
                        <option value="0">No company (individual)</option>
                        <?php foreach ( $companies as $co ) : ?>
                            <option value="<?php echo intval( $co->id ); ?>">
                                <?php echo esc_html( $co->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="crm-field-group">
                    <label>Tags <span style="font-weight:400;color:#9ca3af;">(comma separated)</span></label>
                    <input type="text" id="crm-contact-tags" placeholder="e.g. vip, referral, partner">
                </div>
                <?php if ( ! empty( $custom_props ) ) : ?>
                    <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:4px;">
                        <p style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                            Custom Properties
                        </p>
                        <?php foreach ( $custom_props as $prop ) : ?>
                            <div class="crm-field-group">
                                <label><?php echo esc_html( $prop->field_label ); ?></label>
                                <?php if ( $prop->field_type === 'textarea' ) : ?>
                                    <textarea id="crm-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                              data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                              class="crm-custom-prop-field" rows="3"></textarea>
                                <?php elseif ( $prop->field_type === 'select' ) :
                                    $opts = json_decode( $prop->field_options, true ) ?: [];
                                ?>
                                    <select id="crm-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                            data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                            class="crm-custom-prop-field">
                                        <option value="">Select...</option>
                                        <?php foreach ( $opts as $opt ) : ?>
                                            <option value="<?php echo esc_attr( $opt ); ?>">
                                                <?php echo esc_html( $opt ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else : ?>
                                    <input type="<?php echo $prop->field_type === 'number' ? 'number' : ( $prop->field_type === 'date' ? 'date' : 'text' ); ?>"
                                           id="crm-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                           data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                           class="crm-custom-prop-field">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-contact-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-contact-save-btn">Save Contact</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var page        = 1;
        var perPage     = 20;
        var totalPages  = 1;
        var searchTimer = null;

        function loadContacts() {
            var search     = document.getElementById('crm-contact-search').value;
            var status     = document.getElementById('crm-contact-filter-status').value;
            var owner      = document.getElementById('crm-contact-filter-owner').value;
            var wrap       = document.getElementById('crm-contacts-table-wrap');

            wrap.innerHTML = '<div class="crm-empty" style="padding:48px;"><p>Loading...</p></div>';

            crmPost( 'crm_get_contacts', {
                search: search,
                lifecycle_status: status,
                owner_id: owner,
                page: page,
                per_page: perPage
            }, function(data) {
                totalPages = data.total_pages || 1;
                renderContactsTable( data.contacts || [] );
                renderPagination( data.total || 0 );
            });
        }

        function renderContactsTable(contacts) {
            var wrap = document.getElementById('crm-contacts-table-wrap');
            if ( contacts.length === 0 ) {
                wrap.innerHTML = '<div class="crm-empty" style="padding:48px;">'
                    + '<svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
                    + '<p>No contacts found.</p></div>';
                return;
            }

            var lifecycle_labels = {
                lead: 'Lead', prospect: 'Prospect',
                customer: 'Customer', churned: 'Churned'
            };

            var html = '<div class="bntm-table-wrapper" style="border:none;border-radius:0;">'
                + '<table class="bntm-table"><thead><tr>'
                + '<th>Name</th><th>Email</th><th>Phone</th>'
                + '<th>Status</th><th>Owner</th><th>Company</th>'
                + '<th>Tags</th><th>Created</th><th>Actions</th>'
                + '</tr></thead><tbody>';

            contacts.forEach(function(c) {
                var statusClass = 'crm-badge-' + (c.lifecycle_status || 'lead');
                var label       = lifecycle_labels[c.lifecycle_status] || c.lifecycle_status;
                var tags        = c.tags
                    ? c.tags.split(',').map(function(t){
                        return '<span class="crm-tag">' + crmEsc(t.trim()) + '</span>';
                      }).join('')
                    : '—';
                var created = c.created_at ? c.created_at.substring(0,10) : '—';

                html += '<tr>'
                    + '<td><span style="font-weight:600;color:#111827;">'
                    + crmEsc(c.first_name) + ' ' + crmEsc(c.last_name)
                    + '</span></td>'
                    + '<td>' + (c.email ? crmEsc(c.email) : '—') + '</td>'
                    + '<td>' + (c.phone ? crmEsc(c.phone) : '—') + '</td>'
                    + '<td><span class="crm-badge ' + statusClass + '">' + crmEsc(label) + '</span></td>'
                    + '<td>' + (c.owner_name ? crmEsc(c.owner_name) : '—') + '</td>'
                    + '<td>' + (c.company_name ? crmEsc(c.company_name) : '<span style="color:#9ca3af;">Individual</span>') + '</td>'
                    + '<td><div class="crm-tags-wrap">' + tags + '</div></td>'
                    + '<td style="white-space:nowrap;">' + crmEsc(created) + '</td>'
                    + '<td style="white-space:nowrap;">'
                    + '<button class="bntm-btn-secondary bntm-btn-small crm-contact-edit-btn" data-id="' + c.id + '">Edit</button> '
                    + '<button class="bntm-btn-danger bntm-btn-small crm-contact-delete-btn" data-id="' + c.id + '">Delete</button>'
                    + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table></div>';
            wrap.innerHTML = html;

            wrap.querySelectorAll('.crm-contact-edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openEditContact( this.dataset.id, contacts );
                });
            });
            wrap.querySelectorAll('.crm-contact-delete-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deleteContact( this.dataset.id );
                });
            });
        }

        function renderPagination(total) {
            var pag = document.getElementById('crm-contacts-pagination');
            if ( totalPages <= 1 ) { pag.innerHTML = '<span style="font-size:13px;color:#9ca3af;">Showing ' + total + ' contact(s)</span>'; return; }

            var html = '<span style="font-size:13px;color:#6b7280;margin-right:8px;">Page ' + page + ' of ' + totalPages + ' &nbsp;|&nbsp; ' + total + ' total</span>';

            if ( page > 1 ) {
                html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-contact-prev">Prev</button> ';
            }
            if ( page < totalPages ) {
                html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-contact-next">Next</button>';
            }
            pag.innerHTML = html;

            var prevBtn = document.getElementById('crm-contact-prev');
            var nextBtn = document.getElementById('crm-contact-next');
            if ( prevBtn ) prevBtn.addEventListener('click', function() { page--; loadContacts(); });
            if ( nextBtn ) nextBtn.addEventListener('click', function() { page++; loadContacts(); });
        }

        function openEditContact(id, contacts) {
            var c = contacts.find(function(x){ return x.id == id; });
            if (!c) return;

            document.getElementById('crm-contact-id').value          = c.id;
            document.getElementById('crm-contact-first-name').value  = c.first_name || '';
            document.getElementById('crm-contact-last-name').value   = c.last_name  || '';
            document.getElementById('crm-contact-email').value       = c.email      || '';
            document.getElementById('crm-contact-phone').value       = c.phone      || '';
            document.getElementById('crm-contact-lifecycle').value   = c.lifecycle_status || 'lead';
            document.getElementById('crm-contact-owner').value       = c.owner_id   || '';
            document.getElementById('crm-contact-company').value     = c.company_id || 0;
            document.getElementById('crm-contact-tags').value        = c.tags       || '';

            var title = document.querySelector('#crm-contact-modal .crm-modal-header h3');
            title.textContent = 'Edit Contact';

            var customProps = {};
            try { customProps = JSON.parse(c.custom_properties || '{}'); } catch(e){}
            document.querySelectorAll('.crm-custom-prop-field').forEach(function(el) {
                var prop = el.dataset.prop;
                if ( prop && customProps[prop] !== undefined ) {
                    el.value = customProps[prop];
                }
            });

            crmOpenModal('crm-contact-modal');
        }

        function deleteContact(id) {
            crmConfirm('Delete this contact? This cannot be undone.', function() {
                crmPost( 'crm_delete_contact', { contact_id: id }, function(data) {
                    crmToast(data.message || 'Contact deleted.', 'success');
                    loadContacts();
                });
            });
        }

        // Add button
        document.getElementById('crm-contact-add-btn').addEventListener('click', function() {
            document.getElementById('crm-contact-id').value = '';
            var title = document.querySelector('#crm-contact-modal .crm-modal-header h3');
            title.textContent = 'Add Contact';
            crmOpenModal('crm-contact-modal');
        });

        // Save button
        document.getElementById('crm-contact-save-btn').addEventListener('click', function() {
            var btn        = this;
            var contactId  = document.getElementById('crm-contact-id').value;
            var firstName  = document.getElementById('crm-contact-first-name').value.trim();

            if ( ! firstName ) {
                crmToast('First name is required.', 'error');
                return;
            }

            var customProps = {};
            document.querySelectorAll('.crm-custom-prop-field').forEach(function(el) {
                if (el.dataset.prop) customProps[el.dataset.prop] = el.value;
            });

            var action = contactId ? 'crm_update_contact' : 'crm_create_contact';
            btn.disabled = true;
            btn.textContent = 'Saving...';

            crmPost( action, {
                contact_id:        contactId,
                first_name:        firstName,
                last_name:         document.getElementById('crm-contact-last-name').value.trim(),
                email:             document.getElementById('crm-contact-email').value.trim(),
                phone:             document.getElementById('crm-contact-phone').value.trim(),
                lifecycle_status:  document.getElementById('crm-contact-lifecycle').value,
                owner_id:          document.getElementById('crm-contact-owner').value,
                company_id:        document.getElementById('crm-contact-company').value,
                tags:              document.getElementById('crm-contact-tags').value.trim(),
                custom_properties: JSON.stringify(customProps)
            }, function(data) {
                crmToast(data.message || 'Saved.', 'success');
                crmCloseModal('crm-contact-modal');
                loadContacts();
                btn.disabled = false;
                btn.textContent = 'Save Contact';
            }, function() {
                btn.disabled = false;
                btn.textContent = 'Save Contact';
            });
        });

        // Search with debounce
        document.getElementById('crm-contact-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { page = 1; loadContacts(); }, 380);
        });

        document.getElementById('crm-contact-filter-status').addEventListener('change', function() {
            page = 1; loadContacts();
        });
        document.getElementById('crm-contact-filter-owner').addEventListener('change', function() {
            page = 1; loadContacts();
        });

        // Initial load
        loadContacts();
    })();
    </script>

    <style>
    .crm-tag {
        display: inline-block;
        background: #f3f4f6;
        color: #374151;
        font-size: 11px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 20px;
        margin: 1px 2px;
    }
    .crm-tags-wrap { display: flex; flex-wrap: wrap; gap: 2px; }
    </style>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 3 — COMPANIES
// =============================================================================

function crm_companies_tab( $business_id ) {
    global $wpdb;

    $users = get_users( [ 'fields' => [ 'ID', 'display_name' ] ] );

    $custom_props = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND object_type = 'company' AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    ob_start();
    ?>

    <!-- Filter Row -->
    <div class="crm-filter-row">
        <input type="text" id="crm-company-search"
               placeholder="Search name, industry, website..."
               style="flex:1;min-width:200px;">
        <select id="crm-company-filter-owner">
            <option value="">All Owners</option>
            <?php foreach ( $users as $u ) : ?>
                <option value="<?php echo intval( $u->ID ); ?>">
                    <?php echo esc_html( $u->display_name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="bntm-btn-primary" id="crm-company-add-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Company
        </button>
    </div>

    <!-- Companies Table -->
    <div class="bntm-form-section" style="padding:0;overflow:hidden;">
        <div id="crm-companies-table-wrap">
            <div class="crm-empty" style="padding:48px;">
                <p>Loading companies...</p>
            </div>
        </div>
        <div id="crm-companies-pagination"
             style="padding:16px 20px;border-top:1px solid #f3f4f6;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        </div>
    </div>

    <!-- Add / Edit Company Modal -->
    <div class="crm-modal-overlay" id="crm-company-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3 data-default="Add Company">Add Company</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-company-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-company-id" name="record_id" value="">

                <div class="crm-field-group">
                    <label>Company Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="crm-company-name" placeholder="Acme Corporation">
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Industry</label>
                        <select id="crm-company-industry">
                            <option value="">Select industry...</option>
                            <option value="Technology">Technology</option>
                            <option value="Finance">Finance</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Retail">Retail</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Real Estate">Real Estate</option>
                            <option value="Education">Education</option>
                            <option value="Hospitality">Hospitality</option>
                            <option value="Construction">Construction</option>
                            <option value="Legal">Legal</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Logistics">Logistics</option>
                            <option value="Non-Profit">Non-Profit</option>
                            <option value="Government">Government</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Phone</label>
                        <input type="text" id="crm-company-phone" placeholder="+1 555 000 0000">
                    </div>
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Website</label>
                        <input type="text" id="crm-company-website" placeholder="https://example.com">
                    </div>
                    <div class="crm-field-group">
                        <label>Owner</label>
                        <select id="crm-company-owner">
                            <option value="">Unassigned</option>
                            <?php foreach ( $users as $u ) : ?>
                                <option value="<?php echo intval( $u->ID ); ?>">
                                    <?php echo esc_html( $u->display_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="crm-field-group">
                    <label>Tags <span style="font-weight:400;color:#9ca3af;">(comma separated)</span></label>
                    <input type="text" id="crm-company-tags" placeholder="e.g. enterprise, partner, key-account">
                </div>

                <?php if ( ! empty( $custom_props ) ) : ?>
                    <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:4px;">
                        <p style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;
                                  letter-spacing:.5px;margin-bottom:12px;">
                            Custom Properties
                        </p>
                        <?php foreach ( $custom_props as $prop ) : ?>
                            <div class="crm-field-group">
                                <label><?php echo esc_html( $prop->field_label ); ?></label>
                                <?php if ( $prop->field_type === 'textarea' ) : ?>
                                    <textarea id="crm-co-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                              data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                              class="crm-company-custom-prop-field"
                                              rows="3"></textarea>
                                <?php elseif ( $prop->field_type === 'select' ) :
                                    $opts = json_decode( $prop->field_options, true ) ?: [];
                                ?>
                                    <select id="crm-co-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                            data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                            class="crm-company-custom-prop-field">
                                        <option value="">Select...</option>
                                        <?php foreach ( $opts as $opt ) : ?>
                                            <option value="<?php echo esc_attr( $opt ); ?>">
                                                <?php echo esc_html( $opt ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else : ?>
                                    <input type="<?php echo $prop->field_type === 'number'
                                                        ? 'number'
                                                        : ( $prop->field_type === 'date' ? 'date' : 'text' ); ?>"
                                           id="crm-co-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                           data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                           class="crm-company-custom-prop-field">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-company-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-company-save-btn">Save Company</button>
            </div>
        </div>
    </div>

    <!-- Linked Contacts / Deals Drawer -->
    <div class="crm-modal-overlay" id="crm-company-detail-modal">
        <div class="crm-modal" style="max-width:680px;">
            <div class="crm-modal-header">
                <h3 id="crm-company-detail-title">Company Details</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-company-detail-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body" id="crm-company-detail-body">
                <p style="color:#9ca3af;text-align:center;padding:24px 0;">Loading...</p>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-company-detail-modal')">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var page        = 1;
        var perPage     = 20;
        var totalPages  = 1;
        var searchTimer = null;
        var cachedList  = [];

        function loadCompanies() {
            var search = document.getElementById('crm-company-search').value;
            var owner  = document.getElementById('crm-company-filter-owner').value;
            var wrap   = document.getElementById('crm-companies-table-wrap');

            wrap.innerHTML = '<div class="crm-empty" style="padding:48px;"><p>Loading...</p></div>';

            crmPost( 'crm_get_companies', {
                search:   search,
                owner_id: owner,
                page:     page,
                per_page: perPage
            }, function(data) {
                cachedList = data.companies || [];
                totalPages = data.total_pages || 1;
                renderCompaniesTable( cachedList );
                renderPagination( data.total || 0 );
            });
        }

        function renderCompaniesTable( companies ) {
            var wrap = document.getElementById('crm-companies-table-wrap');

            if ( companies.length === 0 ) {
                wrap.innerHTML = '<div class="crm-empty" style="padding:48px;">'
                    + '<svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                    + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" '
                    + 'd="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 '
                    + '0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'
                    + '</svg><p>No companies found.</p></div>';
                return;
            }

            var html = '<div class="bntm-table-wrapper" style="border:none;border-radius:0;">'
                + '<table class="bntm-table"><thead><tr>'
                + '<th>Company</th><th>Industry</th><th>Website</th>'
                + '<th>Phone</th><th>Owner</th><th>Tags</th>'
                + '<th>Contacts</th><th>Deals</th><th>Actions</th>'
                + '</tr></thead><tbody>';

            companies.forEach(function(co) {
                var tags = co.tags
                    ? co.tags.split(',').map(function(t) {
                        return '<span class="crm-tag">' + crmEsc(t.trim()) + '</span>';
                      }).join('')
                    : '—';

                var website = co.website
                    ? '<a href="' + crmEsc(co.website) + '" target="_blank" '
                      + 'style="color:var(--bntm-primary);text-decoration:none;">'
                      + crmEsc(co.website.replace(/^https?:\/\//, '').replace(/\/$/, ''))
                      + '</a>'
                    : '—';

                html += '<tr>'
                    + '<td>'
                    + '<button class="crm-company-view-btn" data-id="' + co.id + '" '
                    + 'style="background:none;border:none;cursor:pointer;font-weight:600;'
                    + 'color:#111827;font-size:14px;padding:0;text-align:left;">'
                    + crmEsc(co.name)
                    + '</button></td>'
                    + '<td>' + ( co.industry ? crmEsc(co.industry) : '—' ) + '</td>'
                    + '<td>' + website + '</td>'
                    + '<td>' + ( co.phone ? crmEsc(co.phone) : '—' ) + '</td>'
                    + '<td>' + ( co.owner_name ? crmEsc(co.owner_name) : '—' ) + '</td>'
                    + '<td><div class="crm-tags-wrap">' + tags + '</div></td>'
                    + '<td style="text-align:center;">'
                    + '<span class="crm-count-pill">' + parseInt(co.contact_count || 0) + '</span>'
                    + '</td>'
                    + '<td style="text-align:center;">'
                    + '<span class="crm-count-pill">' + parseInt(co.deal_count || 0) + '</span>'
                    + '</td>'
                    + '<td style="white-space:nowrap;">'
                    + '<button class="bntm-btn-secondary bntm-btn-small crm-company-edit-btn" '
                    + 'data-id="' + co.id + '">Edit</button> '
                    + '<button class="bntm-btn-danger bntm-btn-small crm-company-delete-btn" '
                    + 'data-id="' + co.id + '">Delete</button>'
                    + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table></div>';
            wrap.innerHTML = html;

            wrap.querySelectorAll('.crm-company-view-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openCompanyDetail( this.dataset.id );
                });
            });
            wrap.querySelectorAll('.crm-company-edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openEditCompany( this.dataset.id );
                });
            });
            wrap.querySelectorAll('.crm-company-delete-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deleteCompany( this.dataset.id );
                });
            });
        }

        function renderPagination(total) {
            var pag = document.getElementById('crm-companies-pagination');
            if ( totalPages <= 1 ) {
                pag.innerHTML = '<span style="font-size:13px;color:#9ca3af;">'
                    + 'Showing ' + total + ' compan' + (total === 1 ? 'y' : 'ies') + '</span>';
                return;
            }
            var html = '<span style="font-size:13px;color:#6b7280;margin-right:8px;">'
                + 'Page ' + page + ' of ' + totalPages + ' &nbsp;|&nbsp; ' + total + ' total</span>';
            if ( page > 1 ) {
                html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-co-prev">Prev</button> ';
            }
            if ( page < totalPages ) {
                html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-co-next">Next</button>';
            }
            pag.innerHTML = html;

            var prevBtn = document.getElementById('crm-co-prev');
            var nextBtn = document.getElementById('crm-co-next');
            if ( prevBtn ) prevBtn.addEventListener('click', function() { page--; loadCompanies(); });
            if ( nextBtn ) nextBtn.addEventListener('click', function() { page++; loadCompanies(); });
        }

        function openEditCompany(id) {
            var co = cachedList.find(function(x) { return x.id == id; });
            if ( ! co ) return;

            document.getElementById('crm-company-id').value       = co.id;
            document.getElementById('crm-company-name').value     = co.name        || '';
            document.getElementById('crm-company-industry').value = co.industry    || '';
            document.getElementById('crm-company-phone').value    = co.phone       || '';
            document.getElementById('crm-company-website').value  = co.website     || '';
            document.getElementById('crm-company-owner').value    = co.owner_id    || '';
            document.getElementById('crm-company-tags').value     = co.tags        || '';

            var customProps = {};
            try { customProps = JSON.parse(co.custom_properties || '{}'); } catch(e) {}
            document.querySelectorAll('.crm-company-custom-prop-field').forEach(function(el) {
                var prop = el.dataset.prop;
                if ( prop && customProps[prop] !== undefined ) {
                    el.value = customProps[prop];
                }
            });

            var title = document.querySelector('#crm-company-modal .crm-modal-header h3');
            title.textContent = 'Edit Company';
            crmOpenModal('crm-company-modal');
        }

        function openCompanyDetail(id) {
            var titleEl = document.getElementById('crm-company-detail-title');
            var bodyEl  = document.getElementById('crm-company-detail-body');
            var co      = cachedList.find(function(x) { return x.id == id; });

            titleEl.textContent = co ? co.name : 'Company Details';
            bodyEl.innerHTML    = '<p style="color:#9ca3af;text-align:center;padding:24px 0;">Loading...</p>';
            crmOpenModal('crm-company-detail-modal');

            crmPost( 'crm_get_companies', {
                company_id: id,
                detail:     '1',
                page:       1,
                per_page:   999
            }, function(data) {
                var detail   = data.detail   || {};
                var contacts = data.contacts || [];
                var deals    = data.deals    || [];

                var html = '';

                // Meta strip
                html += '<div class="crm-detail-meta-strip">';
                if (detail.industry) html += '<span><strong>Industry:</strong> ' + crmEsc(detail.industry) + '</span>';
                if (detail.phone)    html += '<span><strong>Phone:</strong> '    + crmEsc(detail.phone)    + '</span>';
                if (detail.website)  html += '<span><strong>Website:</strong> <a href="'
                    + crmEsc(detail.website) + '" target="_blank" style="color:var(--bntm-primary);">'
                    + crmEsc(detail.website) + '</a></span>';
                html += '</div>';

                // Linked contacts
                html += '<div style="margin-top:20px;">';
                html += '<h4 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 10px;">'
                    + 'Linked Contacts (' + contacts.length + ')</h4>';

                if ( contacts.length === 0 ) {
                    html += '<p style="font-size:13px;color:#9ca3af;">No contacts linked to this company.</p>';
                } else {
                    html += '<div class="bntm-table-wrapper"><table class="bntm-table">'
                        + '<thead><tr><th>Name</th><th>Email</th><th>Status</th></tr></thead><tbody>';
                    contacts.forEach(function(c) {
                        var lc = c.lifecycle_status || 'lead';
                        html += '<tr>'
                            + '<td style="font-weight:500;">' + crmEsc(c.first_name) + ' ' + crmEsc(c.last_name) + '</td>'
                            + '<td>' + ( c.email ? crmEsc(c.email) : '—' ) + '</td>'
                            + '<td><span class="crm-badge crm-badge-' + lc + '">' + crmEsc(lc) + '</span></td>'
                            + '</tr>';
                    });
                    html += '</tbody></table></div>';
                }
                html += '</div>';

                // Linked deals
                html += '<div style="margin-top:20px;">';
                html += '<h4 style="font-size:14px;font-weight:700;color:#111827;margin:0 0 10px;">'
                    + 'Linked Deals (' + deals.length + ')</h4>';

                if ( deals.length === 0 ) {
                    html += '<p style="font-size:13px;color:#9ca3af;">No deals linked to this company.</p>';
                } else {
                    html += '<div class="bntm-table-wrapper"><table class="bntm-table">'
                        + '<thead><tr><th>Deal</th><th>Amount</th><th>Stage</th><th>Status</th></tr></thead><tbody>';
                    deals.forEach(function(d) {
                        var st = d.status || 'open';
                        html += '<tr>'
                            + '<td style="font-weight:500;">' + crmEsc(d.name) + '</td>'
                            + '<td>' + crmFormatCurrency(d.amount) + '</td>'
                            + '<td>' + ( d.stage_name ? crmEsc(d.stage_name) : '—' ) + '</td>'
                            + '<td><span class="crm-badge crm-badge-' + st + '">' + crmEsc(st) + '</span></td>'
                            + '</tr>';
                    });
                    html += '</tbody></table></div>';
                }
                html += '</div>';

                bodyEl.innerHTML = html;
            });
        }

        function deleteCompany(id) {
            crmConfirm(
                'Delete this company? Contacts will be unlinked. '
                + 'Companies with open deals cannot be deleted.',
                function() {
                    crmPost( 'crm_delete_company', { company_id: id }, function(data) {
                        crmToast(data.message || 'Company deleted.', 'success');
                        loadCompanies();
                    });
                }
            );
        }

        // Add button
        document.getElementById('crm-company-add-btn').addEventListener('click', function() {
            document.getElementById('crm-company-id').value = '';
            var title = document.querySelector('#crm-company-modal .crm-modal-header h3');
            title.textContent = 'Add Company';
            document.querySelectorAll('.crm-company-custom-prop-field').forEach(function(el) {
                el.value = '';
            });
            crmOpenModal('crm-company-modal');
        });

        // Save button
        document.getElementById('crm-company-save-btn').addEventListener('click', function() {
            var btn       = this;
            var companyId = document.getElementById('crm-company-id').value;
            var name      = document.getElementById('crm-company-name').value.trim();

            if ( ! name ) {
                crmToast('Company name is required.', 'error');
                return;
            }

            var customProps = {};
            document.querySelectorAll('.crm-company-custom-prop-field').forEach(function(el) {
                if ( el.dataset.prop ) customProps[el.dataset.prop] = el.value;
            });

            var action   = companyId ? 'crm_update_company' : 'crm_create_company';
            btn.disabled = true;
            btn.textContent = 'Saving...';

            crmPost( action, {
                company_id:        companyId,
                name:              name,
                industry:          document.getElementById('crm-company-industry').value,
                phone:             document.getElementById('crm-company-phone').value.trim(),
                website:           document.getElementById('crm-company-website').value.trim(),
                owner_id:          document.getElementById('crm-company-owner').value,
                tags:              document.getElementById('crm-company-tags').value.trim(),
                custom_properties: JSON.stringify(customProps)
            }, function(data) {
                crmToast(data.message || 'Saved.', 'success');
                crmCloseModal('crm-company-modal');
                loadCompanies();
                btn.disabled    = false;
                btn.textContent = 'Save Company';
            }, function() {
                btn.disabled    = false;
                btn.textContent = 'Save Company';
            });
        });

        // Filters
        document.getElementById('crm-company-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { page = 1; loadCompanies(); }, 380);
        });
        document.getElementById('crm-company-filter-owner').addEventListener('change', function() {
            page = 1; loadCompanies();
        });

        loadCompanies();
    })();
    </script>

    <style>
    .crm-count-pill {
        display: inline-block;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        min-width: 28px;
        text-align: center;
    }
    .crm-detail-meta-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #374151;
    }
    .crm-detail-meta-strip span { display: flex; gap: 4px; align-items: center; }
    </style>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 4 — DEALS
// =============================================================================

function crm_deals_tab( $business_id ) {
    global $wpdb;

    $users = get_users( [ 'fields' => [ 'ID', 'display_name' ] ] );

    $stages = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    $contacts = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, first_name, last_name FROM {$wpdb->prefix}crm_contacts
         WHERE business_id = %d AND status = 'active'
         ORDER BY first_name ASC",
        $business_id
    ) );

    $companies = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}crm_companies
         WHERE business_id = %d AND status = 'active'
         ORDER BY name ASC",
        $business_id
    ) );

    $custom_props = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND object_type = 'deal' AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    ob_start();
    ?>

    <!-- View Toggle + Filter Row -->
    <div class="crm-filter-row" style="justify-content:space-between;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex:1;">
            <input type="text" id="crm-deal-search"
                   placeholder="Search deals..."
                   style="min-width:200px;flex:1;">
            <select id="crm-deal-filter-stage">
                <option value="">All Stages</option>
                <?php foreach ( $stages as $stage ) : ?>
                    <option value="<?php echo intval( $stage->id ); ?>">
                        <?php echo esc_html( $stage->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="crm-deal-filter-status">
                <option value="">All Statuses</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>
            <select id="crm-deal-filter-owner">
                <option value="">All Owners</option>
                <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo intval( $u->ID ); ?>">
                        <?php echo esc_html( $u->display_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
            <div class="crm-view-toggle">
                <button class="crm-view-btn active" id="crm-deal-view-list" data-view="list"
                        title="List View">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
                <button class="crm-view-btn" id="crm-deal-view-board" data-view="board"
                        title="Board View">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                </button>
            </div>
            <button class="bntm-btn-primary" id="crm-deal-add-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Deal
            </button>
        </div>
    </div>

    <!-- List View -->
    <div id="crm-deals-list-view">
        <div class="bntm-form-section" style="padding:0;overflow:hidden;">
            <div id="crm-deals-table-wrap">
                <div class="crm-empty" style="padding:48px;">
                    <p>Loading deals...</p>
                </div>
            </div>
            <div id="crm-deals-pagination"
                 style="padding:16px 20px;border-top:1px solid #f3f4f6;
                        display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            </div>
        </div>
    </div>

    <!-- Board View -->
    <div id="crm-deals-board-view" style="display:none;">
        <div id="crm-deals-board-wrap">
            <div class="crm-empty" style="padding:48px;">
                <p>Loading board...</p>
            </div>
        </div>
    </div>

    <!-- Add / Edit Deal Modal -->
    <div class="crm-modal-overlay" id="crm-deal-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3 data-default="Add Deal">Add Deal</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-deal-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-deal-id" name="record_id" value="">

                <div class="crm-field-group">
                    <label>Deal Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="crm-deal-name" placeholder="e.g. Website Redesign Project">
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Amount</label>
                        <input type="number" id="crm-deal-amount" placeholder="0.00" min="0" step="0.01">
                    </div>
                    <div class="crm-field-group">
                        <label>Expected Close Date</label>
                        <input type="date" id="crm-deal-close-date">
                    </div>
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Stage <span style="color:#ef4444;">*</span></label>
                        <select id="crm-deal-stage">
                            <option value="">Select stage...</option>
                            <?php foreach ( $stages as $stage ) : ?>
                                <option value="<?php echo intval( $stage->id ); ?>">
                                    <?php echo esc_html( $stage->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Owner</label>
                        <select id="crm-deal-owner">
                            <option value="">Unassigned</option>
                            <?php foreach ( $users as $u ) : ?>
                                <option value="<?php echo intval( $u->ID ); ?>">
                                    <?php echo esc_html( $u->display_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Contact <span style="color:#ef4444;">*</span></label>
                        <select id="crm-deal-contact">
                            <option value="">Select contact...</option>
                            <?php foreach ( $contacts as $ct ) : ?>
                                <option value="<?php echo intval( $ct->id ); ?>">
                                    <?php echo esc_html( trim( $ct->first_name . ' ' . $ct->last_name ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Company <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                        <select id="crm-deal-company">
                            <option value="0">No company</option>
                            <?php foreach ( $companies as $co ) : ?>
                                <option value="<?php echo intval( $co->id ); ?>">
                                    <?php echo esc_html( $co->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ( ! empty( $custom_props ) ) : ?>
                    <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:4px;">
                        <p style="font-size:12px;font-weight:600;color:#6b7280;
                                  text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                            Custom Properties
                        </p>
                        <?php foreach ( $custom_props as $prop ) : ?>
                            <div class="crm-field-group">
                                <label><?php echo esc_html( $prop->field_label ); ?></label>
                                <?php if ( $prop->field_type === 'textarea' ) : ?>
                                    <textarea id="crm-dl-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                              data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                              class="crm-deal-custom-prop-field"
                                              rows="3"></textarea>
                                <?php elseif ( $prop->field_type === 'select' ) :
                                    $opts = json_decode( $prop->field_options, true ) ?: [];
                                ?>
                                    <select id="crm-dl-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                            data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                            class="crm-deal-custom-prop-field">
                                        <option value="">Select...</option>
                                        <?php foreach ( $opts as $opt ) : ?>
                                            <option value="<?php echo esc_attr( $opt ); ?>">
                                                <?php echo esc_html( $opt ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else : ?>
                                    <input type="<?php echo $prop->field_type === 'number'
                                                        ? 'number'
                                                        : ( $prop->field_type === 'date' ? 'date' : 'text' ); ?>"
                                           id="crm-dl-cprop-<?php echo esc_attr( $prop->field_name ); ?>"
                                           data-prop="<?php echo esc_attr( $prop->field_name ); ?>"
                                           class="crm-deal-custom-prop-field">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-deal-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="crm-deal-save-btn">Save Deal</button>
            </div>
        </div>
    </div>

    <style>
    .crm-view-toggle {
        display: flex;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }
    .crm-view-btn {
        background: #fff;
        border: none;
        padding: 8px 12px;
        cursor: pointer;
        color: #6b7280;
        display: flex;
        align-items: center;
        transition: background .15s, color .15s;
    }
    .crm-view-btn:hover  { background: #f9fafb; color: #374151; }
    .crm-view-btn.active { background: var(--bntm-primary); color: #fff; }
    .crm-deal-amount-col {
        font-weight: 700;
        color: var(--bntm-primary);
    }
    .crm-close-date-warn { color: #ef4444; font-weight: 600; }
    </style>

    <script>
    (function() {
        var currentView = 'list';
        var page        = 1;
        var perPage     = 20;
        var totalPages  = 1;
        var searchTimer = null;
        var cachedDeals = [];
        var stagesData  = <?php echo wp_json_encode(
            array_map( function($s) {
                return [ 'id' => (int)$s->id, 'name' => $s->name, 'color' => $s->color ];
            }, $stages )
        ); ?>;

        // ── View toggle ──
        document.getElementById('crm-deal-view-list').addEventListener('click', function() {
            setView('list');
        });
        document.getElementById('crm-deal-view-board').addEventListener('click', function() {
            setView('board');
        });

        function setView(view) {
            currentView = view;
            document.getElementById('crm-deals-list-view').style.display  = view === 'list'  ? 'block' : 'none';
            document.getElementById('crm-deals-board-view').style.display = view === 'board' ? 'block' : 'none';
            document.getElementById('crm-deal-view-list').classList.toggle('active',  view === 'list');
            document.getElementById('crm-deal-view-board').classList.toggle('active', view === 'board');
            if ( view === 'list' )  loadDealsList();
            if ( view === 'board' ) loadDealsBoard();
        }

        // ── List View ──
        function loadDealsList() {
            var wrap = document.getElementById('crm-deals-table-wrap');
            wrap.innerHTML = '<div class="crm-empty" style="padding:48px;"><p>Loading...</p></div>';

            crmPost( 'crm_get_deals', {
                search:   document.getElementById('crm-deal-search').value,
                stage_id: document.getElementById('crm-deal-filter-stage').value,
                status:   document.getElementById('crm-deal-filter-status').value,
                owner_id: document.getElementById('crm-deal-filter-owner').value,
                page:     page,
                per_page: perPage
            }, function(data) {
                cachedDeals = data.deals || [];
                totalPages  = data.total_pages || 1;
                renderDealsTable( cachedDeals );
                renderPagination( data.total || 0 );
            });
        }

        function renderDealsTable(deals) {
            var wrap = document.getElementById('crm-deals-table-wrap');

            if ( deals.length === 0 ) {
                wrap.innerHTML = '<div class="crm-empty" style="padding:48px;">'
                    + '<svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                    + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" '
                    + 'd="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 '
                    + '2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 '
                    + '0 11-18 0 9 9 0 0118 0z"/></svg>'
                    + '<p>No deals found.</p></div>';
                return;
            }

            var today = new Date();
            today.setHours(0,0,0,0);

            var html = '<div class="bntm-table-wrapper" style="border:none;border-radius:0;">'
                + '<table class="bntm-table"><thead><tr>'
                + '<th>Deal</th><th>Amount</th><th>Stage</th>'
                + '<th>Close Date</th><th>Owner</th>'
                + '<th>Contact</th><th>Company</th>'
                + '<th>Status</th><th>Actions</th>'
                + '</tr></thead><tbody>';

            deals.forEach(function(d) {
                var stObj     = stagesData.find(function(s){ return s.id == d.stage_id; });
                var stName    = stObj ? stObj.name  : '—';
                var stColor   = stObj ? stObj.color : '#9ca3af';
                var statusCls = 'crm-badge-' + (d.status || 'open');

                var closeDateHtml = '—';
                if ( d.expected_close_date ) {
                    var cd = new Date(d.expected_close_date);
                    cd.setHours(0,0,0,0);
                    var isPast = cd < today && d.status === 'open';
                    closeDateHtml = '<span class="' + (isPast ? 'crm-close-date-warn' : '') + '">'
                        + crmEsc(d.expected_close_date) + '</span>';
                }

                html += '<tr>'
                    + '<td style="font-weight:600;color:#111827;max-width:180px;white-space:nowrap;'
                    + 'overflow:hidden;text-overflow:ellipsis;">' + crmEsc(d.name) + '</td>'
                    + '<td class="crm-deal-amount-col">' + crmFormatCurrency(d.amount) + '</td>'
                    + '<td>'
                    + '<span style="display:inline-flex;align-items:center;gap:5px;">'
                    + '<span class="crm-stage-dot" style="background:' + crmEsc(stColor) + ';"></span>'
                    + crmEsc(stName)
                    + '</span></td>'
                    + '<td>' + closeDateHtml + '</td>'
                    + '<td>' + ( d.owner_name   ? crmEsc(d.owner_name)   : '—' ) + '</td>'
                    + '<td>' + ( d.contact_name ? crmEsc(d.contact_name) : '—' ) + '</td>'
                    + '<td>' + ( d.company_name ? crmEsc(d.company_name) : '—' ) + '</td>'
                    + '<td><span class="crm-badge ' + statusCls + '">'
                    + crmEsc(d.status || 'open') + '</span></td>'
                    + '<td style="white-space:nowrap;">'
                    + '<button class="bntm-btn-secondary bntm-btn-small crm-deal-edit-btn" '
                    + 'data-id="' + d.id + '">Edit</button> '
                    + '<button class="bntm-btn-danger bntm-btn-small crm-deal-delete-btn" '
                    + 'data-id="' + d.id + '">Delete</button>'
                    + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table></div>';
            wrap.innerHTML = html;

            wrap.querySelectorAll('.crm-deal-edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { openEditDeal(this.dataset.id); });
            });
            wrap.querySelectorAll('.crm-deal-delete-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { deleteDeal(this.dataset.id); });
            });
        }

        function renderPagination(total) {
            var pag = document.getElementById('crm-deals-pagination');
            if ( totalPages <= 1 ) {
                pag.innerHTML = '<span style="font-size:13px;color:#9ca3af;">Showing '
                    + total + ' deal' + (total === 1 ? '' : 's') + '</span>';
                return;
            }
            var html = '<span style="font-size:13px;color:#6b7280;margin-right:8px;">'
                + 'Page ' + page + ' of ' + totalPages + ' &nbsp;|&nbsp; ' + total + ' total</span>';
            if ( page > 1 )          html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-deal-prev">Prev</button> ';
            if ( page < totalPages ) html += '<button class="bntm-btn-secondary bntm-btn-small" id="crm-deal-next">Next</button>';
            pag.innerHTML = html;

            var p = document.getElementById('crm-deal-prev');
            var n = document.getElementById('crm-deal-next');
            if (p) p.addEventListener('click', function() { page--; loadDealsList(); });
            if (n) n.addEventListener('click', function() { page++; loadDealsList(); });
        }

        // ── Board View ──
        function loadDealsBoard() {
            var wrap = document.getElementById('crm-deals-board-wrap');
            wrap.innerHTML = '<div class="crm-empty" style="padding:48px;"><p>Loading board...</p></div>';

            crmPost( 'crm_get_deals', {
                page:     1,
                per_page: 500,
                status:   'open'
            }, function(data) {
                var allDeals = data.deals || [];
                renderBoard(allDeals);
            });
        }

        function renderBoard(deals) {
            var wrap = document.getElementById('crm-deals-board-wrap');

            if ( stagesData.length === 0 ) {
                wrap.innerHTML = '<div class="crm-empty" style="padding:48px;">'
                    + '<p>No pipeline stages configured. Go to Settings to add stages.</p></div>';
                return;
            }

            var html = '<div class="crm-board">';

            stagesData.forEach(function(stage) {
                var stageDeals = deals.filter(function(d) { return d.stage_id == stage.id; });
                var stageVal   = stageDeals.reduce(function(sum, d) { return sum + parseFloat(d.amount || 0); }, 0);

                html += '<div class="crm-board-col" data-stage-id="' + stage.id + '">';
                html += '<div class="crm-board-col-header">';
                html += '<h4>'
                    + '<span class="crm-stage-dot" style="background:' + crmEsc(stage.color) + ';"></span>'
                    + crmEsc(stage.name)
                    + '</h4>';
                html += '<span class="crm-board-col-count">' + stageDeals.length + '</span>';
                html += '</div>';
                html += '<div style="font-size:11px;color:#9ca3af;margin-bottom:10px;">'
                    + crmFormatCurrency(stageVal) + '</div>';

                if ( stageDeals.length === 0 ) {
                    html += '<div class="crm-board-empty-col">'
                        + '<p style="font-size:12px;color:#d1d5db;text-align:center;padding:16px 0;">'
                        + 'No deals</p></div>';
                }

                stageDeals.forEach(function(d) {
                    var today = new Date(); today.setHours(0,0,0,0);
                    var isOverdue = false;
                    if ( d.expected_close_date ) {
                        var cd = new Date(d.expected_close_date); cd.setHours(0,0,0,0);
                        isOverdue = cd < today;
                    }
                    html += '<div class="crm-deal-card" draggable="true" '
                        + 'data-deal-id="' + d.id + '" data-stage-id="' + stage.id + '">';
                    html += '<div class="crm-deal-card-name">' + crmEsc(d.name) + '</div>';
                    html += '<div class="crm-deal-card-amount">' + crmFormatCurrency(d.amount) + '</div>';
                    if ( d.contact_name ) {
                        html += '<div class="crm-deal-card-meta">'
                            + '<svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" '
                            + 'style="display:inline;vertical-align:middle;">'
                            + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" '
                            + 'd="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'
                            + '</svg> ' + crmEsc(d.contact_name) + '</div>';
                    }
                    if ( d.expected_close_date ) {
                        html += '<div class="crm-deal-card-meta" style="margin-top:4px;'
                            + (isOverdue ? 'color:#ef4444;font-weight:600;' : '') + '">'
                            + '<svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" '
                            + 'style="display:inline;vertical-align:middle;">'
                            + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" '
                            + 'd="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'
                            + '</svg> ' + crmEsc(d.expected_close_date) + '</div>';
                    }
                    html += '<div style="display:flex;gap:6px;margin-top:8px;">'
                        + '<button class="bntm-btn-secondary bntm-btn-small crm-board-edit-btn" '
                        + 'data-id="' + d.id + '" style="flex:1;">Edit</button>'
                        + '</div>';
                    html += '</div>';
                });

                html += '</div>';
            });

            html += '</div>';
            wrap.innerHTML = html;

            initBoardDragDrop();

            wrap.querySelectorAll('.crm-board-edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openEditDealById(this.dataset.id);
                });
            });
        }

        function initBoardDragDrop() {
            var draggingCard = null;

            document.querySelectorAll('.crm-deal-card').forEach(function(card) {
                card.addEventListener('dragstart', function(e) {
                    draggingCard = card;
                    card.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });
                card.addEventListener('dragend', function() {
                    card.classList.remove('dragging');
                    document.querySelectorAll('.crm-board-col').forEach(function(col) {
                        col.classList.remove('drag-over');
                    });
                    draggingCard = null;
                });
            });

            document.querySelectorAll('.crm-board-col').forEach(function(col) {
                col.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    col.classList.add('drag-over');
                });
                col.addEventListener('dragleave', function() {
                    col.classList.remove('drag-over');
                });
                col.addEventListener('drop', function(e) {
                    e.preventDefault();
                    col.classList.remove('drag-over');

                    if ( ! draggingCard ) return;

                    var dealId      = draggingCard.dataset.dealId;
                    var fromStageId = draggingCard.dataset.stageId;
                    var toStageId   = col.dataset.stageId;

                    if ( fromStageId === toStageId ) return;

                    crmPost( 'crm_move_deal_stage', {
                        deal_id:  dealId,
                        stage_id: toStageId
                    }, function(data) {
                        crmToast(data.message || 'Deal moved.', 'success');
                        loadDealsBoard();
                    });
                });
            });
        }

        // ── Open Edit Deal ──
        function openEditDeal(id) {
            var d = cachedDeals.find(function(x) { return x.id == id; });
            if ( ! d ) { openEditDealById(id); return; }
            populateDealModal(d);
        }

        function openEditDealById(id) {
            crmPost( 'crm_get_deals', {
                deal_id:  id,
                page:     1,
                per_page: 1
            }, function(data) {
                var deals = data.deals || [];
                if ( deals.length > 0 ) populateDealModal(deals[0]);
            });
        }

        function populateDealModal(d) {
            document.getElementById('crm-deal-id').value         = d.id;
            document.getElementById('crm-deal-name').value       = d.name              || '';
            document.getElementById('crm-deal-amount').value     = d.amount            || '';
            document.getElementById('crm-deal-close-date').value = d.expected_close_date || '';
            document.getElementById('crm-deal-stage').value      = d.stage_id          || '';
            document.getElementById('crm-deal-owner').value      = d.owner_id          || '';
            document.getElementById('crm-deal-contact').value    = d.contact_id        || '';
            document.getElementById('crm-deal-company').value    = d.company_id        || 0;

            var customProps = {};
            try { customProps = JSON.parse(d.custom_properties || '{}'); } catch(e) {}
            document.querySelectorAll('.crm-deal-custom-prop-field').forEach(function(el) {
                var prop = el.dataset.prop;
                if ( prop && customProps[prop] !== undefined ) el.value = customProps[prop];
            });

            document.querySelector('#crm-deal-modal .crm-modal-header h3').textContent = 'Edit Deal';
            crmOpenModal('crm-deal-modal');
        }

        function deleteDeal(id) {
            crmConfirm('Delete this deal? This cannot be undone.', function() {
                crmPost( 'crm_delete_deal', { deal_id: id }, function(data) {
                    crmToast(data.message || 'Deal deleted.', 'success');
                    if ( currentView === 'list' )  loadDealsList();
                    if ( currentView === 'board' ) loadDealsBoard();
                });
            });
        }

        // ── Add button ──
        document.getElementById('crm-deal-add-btn').addEventListener('click', function() {
            document.getElementById('crm-deal-id').value = '';
            document.querySelector('#crm-deal-modal .crm-modal-header h3').textContent = 'Add Deal';
            crmOpenModal('crm-deal-modal');
        });

        // ── Save button ──
        document.getElementById('crm-deal-save-btn').addEventListener('click', function() {
            var btn     = this;
            var dealId  = document.getElementById('crm-deal-id').value;
            var name    = document.getElementById('crm-deal-name').value.trim();
            var stageId = document.getElementById('crm-deal-stage').value;
            var contact = document.getElementById('crm-deal-contact').value;

            if ( ! name )    { crmToast('Deal name is required.',    'error'); return; }
            if ( ! stageId ) { crmToast('Please select a stage.',    'error'); return; }
            if ( ! contact ) { crmToast('Please select a contact.', 'error'); return; }

            var customProps = {};
            document.querySelectorAll('.crm-deal-custom-prop-field').forEach(function(el) {
                if ( el.dataset.prop ) customProps[el.dataset.prop] = el.value;
            });

            var action      = dealId ? 'crm_update_deal' : 'crm_create_deal';
            btn.disabled    = true;
            btn.textContent = 'Saving...';

            crmPost( action, {
                deal_id:              dealId,
                name:                 name,
                amount:               document.getElementById('crm-deal-amount').value     || 0,
                expected_close_date:  document.getElementById('crm-deal-close-date').value || '',
                stage_id:             stageId,
                owner_id:             document.getElementById('crm-deal-owner').value      || '',
                contact_id:           contact,
                company_id:           document.getElementById('crm-deal-company').value    || 0,
                custom_properties:    JSON.stringify(customProps)
            }, function(data) {
                crmToast(data.message || 'Saved.', 'success');
                crmCloseModal('crm-deal-modal');
                if ( currentView === 'list' )  loadDealsList();
                if ( currentView === 'board' ) loadDealsBoard();
                btn.disabled    = false;
                btn.textContent = 'Save Deal';
            }, function() {
                btn.disabled    = false;
                btn.textContent = 'Save Deal';
            });
        });

        // ── Filters ──
        document.getElementById('crm-deal-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { page = 1; loadDealsList(); }, 380);
        });
        ['crm-deal-filter-stage','crm-deal-filter-status','crm-deal-filter-owner'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', function() {
                page = 1;
                if ( currentView === 'list' )  loadDealsList();
                if ( currentView === 'board' ) loadDealsBoard();
            });
        });

        // ── Initial load ──
        loadDealsList();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// TAB 5 — TASKS
// =============================================================================

function crm_tasks_tab( $business_id ) {
    global $wpdb;

    $users = get_users( [ 'fields' => [ 'ID', 'display_name' ] ] );

    $contacts = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, first_name, last_name FROM {$wpdb->prefix}crm_contacts
         WHERE business_id = %d AND status = 'active'
         ORDER BY first_name ASC",
        $business_id
    ) );

    $companies = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}crm_companies
         WHERE business_id = %d AND status = 'active'
         ORDER BY name ASC",
        $business_id
    ) );

    $deals = $wpdb->get_results( $wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}crm_deals
         WHERE business_id = %d AND status = 'open'
         ORDER BY name ASC",
        $business_id
    ) );

    $today = current_time( 'Y-m-d' );

    $overdue_tasks = $wpdb->get_results( $wpdb->prepare(
        "SELECT t.*,
                u.display_name AS assignee_name,
                DATEDIFF(%s, t.due_date) AS days_overdue
         FROM {$wpdb->prefix}crm_tasks t
         LEFT JOIN {$wpdb->users} u ON u.ID = t.assignee_id
         WHERE t.business_id = %d
           AND t.status = 'pending'
           AND t.due_date < %s
         ORDER BY t.due_date ASC",
        $today, $business_id, $today
    ) );

    $upcoming_tasks = $wpdb->get_results( $wpdb->prepare(
        "SELECT t.*,
                u.display_name AS assignee_name,
                DATEDIFF(t.due_date, %s) AS days_until
         FROM {$wpdb->prefix}crm_tasks t
         LEFT JOIN {$wpdb->users} u ON u.ID = t.assignee_id
         WHERE t.business_id = %d
           AND t.status = 'pending'
           AND t.due_date >= %s
         ORDER BY t.due_date ASC
         LIMIT 50",
        $today, $business_id, $today
    ) );

    ob_start();
    ?>

    <!-- Filter Row -->
    <div class="crm-filter-row" style="justify-content:space-between;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;flex:1;">
            <input type="text" id="crm-task-search"
                   placeholder="Search tasks..."
                   style="min-width:200px;flex:1;">
            <select id="crm-task-filter-assignee">
                <option value="">All Assignees</option>
                <?php foreach ( $users as $u ) : ?>
                    <option value="<?php echo intval( $u->ID ); ?>">
                        <?php echo esc_html( $u->display_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="crm-task-filter-linked-type">
                <option value="">All Record Types</option>
                <option value="contact">Contact</option>
                <option value="company">Company</option>
                <option value="deal">Deal</option>
            </select>
        </div>
        <button class="bntm-btn-primary" id="crm-task-add-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Task
        </button>
    </div>

    <!-- Summary Strip -->
    <div class="crm-task-summary-strip">
        <div class="crm-task-summary-item crm-task-summary-overdue">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><?php echo count( $overdue_tasks ); ?> Overdue</span>
        </div>
        <div class="crm-task-summary-item crm-task-summary-upcoming">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                         M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span><?php echo count( $upcoming_tasks ); ?> Upcoming</span>
        </div>
    </div>

    <div id="crm-tasks-wrap">

        <!-- Overdue Section -->
        <?php if ( ! empty( $overdue_tasks ) ) : ?>
        <div class="bntm-form-section crm-overdue-section" id="crm-overdue-section">
            <h3 style="color:#dc2626;display:flex;align-items:center;gap:8px;">
                <svg width="18" height="18" fill="none" stroke="#dc2626" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Overdue Tasks
                <span class="crm-task-section-count" style="background:#fef2f2;color:#dc2626;">
                    <?php echo count( $overdue_tasks ); ?>
                </span>
            </h3>
            <div class="bntm-table-wrapper">
                <table class="bntm-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Linked To</th>
                            <th>Assignee</th>
                            <th>Due Date</th>
                            <th>Overdue By</th>
                            <th>Reminder</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="crm-overdue-tbody">
                        <?php foreach ( $overdue_tasks as $task ) :
                            $linked_label = crm_get_linked_record_label(
                                $task->linked_type,
                                $task->linked_id,
                                $business_id
                            );
                            $days_over = max( 0, intval( $task->days_overdue ) );
                        ?>
                        <tr data-task-id="<?php echo intval( $task->id ); ?>">
                            <td>
                                <span style="font-weight:600;color:#111827;">
                                    <?php echo esc_html( $task->title ); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( $task->linked_type && $task->linked_id ) : ?>
                                    <span class="crm-linked-type-badge crm-linked-<?php echo esc_attr( $task->linked_type ); ?>">
                                        <?php echo esc_html( ucfirst( $task->linked_type ) ); ?>
                                    </span>
                                    <span style="font-size:12px;color:#6b7280;margin-left:4px;">
                                        <?php echo esc_html( $linked_label ); ?>
                                    </span>
                                <?php else : ?>
                                    <span style="color:#9ca3af;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html( $task->assignee_name ?: '—' ); ?></td>
                            <td>
                                <span style="color:#dc2626;font-weight:600;">
                                    <?php echo esc_html( $task->due_date ); ?>
                                </span>
                            </td>
                            <td>
                                <span class="crm-badge crm-badge-overdue">
                                    <?php echo $days_over; ?> day<?php echo $days_over === 1 ? '' : 's'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ( $task->reminder_days > 0 ) : ?>
                                    <span class="crm-reminder-chip">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002
                                                     6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388
                                                     6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3
                                                     0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <?php echo intval( $task->reminder_days ); ?>d reminder
                                    </span>
                                <?php else : ?>
                                    <span style="color:#9ca3af;font-size:12px;">None</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <button class="bntm-btn-primary bntm-btn-small crm-task-complete-btn"
                                        data-id="<?php echo intval( $task->id ); ?>">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Done
                                </button>
                                <button class="bntm-btn-secondary bntm-btn-small crm-task-edit-btn"
                                        data-id="<?php echo intval( $task->id ); ?>"
                                        data-task='<?php echo esc_attr( wp_json_encode( $task ) ); ?>'>
                                    Edit
                                </button>
                                <button class="bntm-btn-danger bntm-btn-small crm-task-delete-btn"
                                        data-id="<?php echo intval( $task->id ); ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else : ?>
        <div class="bntm-form-section" style="border-left:3px solid #22c55e;padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" fill="none" stroke="#22c55e" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="font-size:14px;font-weight:500;color:#15803d;">
                    No overdue tasks — you are all caught up.
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Section -->
        <div class="bntm-form-section crm-upcoming-section" id="crm-upcoming-section">
            <h3 style="display:flex;align-items:center;gap:8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5
                             a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Upcoming Tasks
                <span class="crm-task-section-count">
                    <?php echo count( $upcoming_tasks ); ?>
                </span>
            </h3>

            <?php if ( empty( $upcoming_tasks ) ) : ?>
                <div class="crm-empty" style="padding:32px 0;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                                 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>No upcoming tasks. Add a task to get started.</p>
                </div>
            <?php else : ?>
                <div class="bntm-table-wrapper">
                    <table class="bntm-table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Linked To</th>
                                <th>Assignee</th>
                                <th>Due Date</th>
                                <th>Due In</th>
                                <th>Reminder</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="crm-upcoming-tbody">
                            <?php foreach ( $upcoming_tasks as $task ) :
                                $linked_label = crm_get_linked_record_label(
                                    $task->linked_type,
                                    $task->linked_id,
                                    $business_id
                                );
                                $days_until = max( 0, intval( $task->days_until ) );
                                $is_today   = $days_until === 0;
                                $is_soon    = $days_until <= 3 && ! $is_today;
                            ?>
                            <tr data-task-id="<?php echo intval( $task->id ); ?>">
                                <td>
                                    <span style="font-weight:600;color:#111827;">
                                        <?php echo esc_html( $task->title ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $task->linked_type && $task->linked_id ) : ?>
                                        <span class="crm-linked-type-badge crm-linked-<?php echo esc_attr( $task->linked_type ); ?>">
                                            <?php echo esc_html( ucfirst( $task->linked_type ) ); ?>
                                        </span>
                                        <span style="font-size:12px;color:#6b7280;margin-left:4px;">
                                            <?php echo esc_html( $linked_label ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="color:#9ca3af;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $task->assignee_name ?: '—' ); ?></td>
                                <td>
                                    <span style="<?php echo $is_today ? 'color:#d97706;font-weight:600;' : ''; ?>">
                                        <?php echo esc_html( $task->due_date ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ( $is_today ) : ?>
                                        <span class="crm-badge crm-badge-pending">Today</span>
                                    <?php elseif ( $is_soon ) : ?>
                                        <span class="crm-badge crm-badge-pending">
                                            <?php echo $days_until; ?> day<?php echo $days_until === 1 ? '' : 's'; ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="font-size:13px;color:#6b7280;">
                                            <?php echo $days_until; ?> day<?php echo $days_until === 1 ? '' : 's'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( $task->reminder_days > 0 ) : ?>
                                        <?php
                                        $remind_on     = date( 'Y-m-d', strtotime( $task->due_date . ' -' . $task->reminder_days . ' days' ) );
                                        $remind_active = $remind_on <= $today && $task->due_date >= $today;
                                        ?>
                                        <span class="crm-reminder-chip <?php echo $remind_active ? 'crm-reminder-active' : ''; ?>">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118
                                                         14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0
                                                         10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0
                                                         .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3
                                                         0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                            <?php echo intval( $task->reminder_days ); ?>d
                                            <?php if ( $remind_active ) : ?>
                                                <span style="color:#d97706;font-size:10px;font-weight:700;">
                                                    &bull; Now
                                                </span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="color:#9ca3af;font-size:12px;">None</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <button class="bntm-btn-primary bntm-btn-small crm-task-complete-btn"
                                            data-id="<?php echo intval( $task->id ); ?>">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Done
                                    </button>
                                    <button class="bntm-btn-secondary bntm-btn-small crm-task-edit-btn"
                                            data-id="<?php echo intval( $task->id ); ?>"
                                            data-task='<?php echo esc_attr( wp_json_encode( $task ) ); ?>'>
                                        Edit
                                    </button>
                                    <button class="bntm-btn-danger bntm-btn-small crm-task-delete-btn"
                                            data-id="<?php echo intval( $task->id ); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /#crm-tasks-wrap -->

    <!-- Add / Edit Task Modal -->
    <div class="crm-modal-overlay" id="crm-task-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3 data-default="Add Task">Add Task</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-task-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-task-id" name="record_id" value="">

                <div class="crm-field-group">
                    <label>Task Title <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="crm-task-title"
                           placeholder="e.g. Follow up with client">
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Assignee</label>
                        <select id="crm-task-assignee">
                            <option value="">Unassigned</option>
                            <?php foreach ( $users as $u ) : ?>
                                <option value="<?php echo intval( $u->ID ); ?>">
                                    <?php echo esc_html( $u->display_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Due Date</label>
                        <input type="date" id="crm-task-due-date">
                    </div>
                </div>

                <div class="crm-field-group">
                    <label>
                        Remind Me In
                        <span style="font-weight:400;color:#9ca3af;">(days before due date)</span>
                    </label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="number" id="crm-task-reminder-days"
                               placeholder="0" min="0" max="365"
                               style="width:120px;">
                        <span style="font-size:13px;color:#6b7280;">
                            days before due date
                            <span id="crm-task-reminder-preview"
                                  style="color:var(--bntm-primary);font-weight:600;"></span>
                        </span>
                    </div>
                </div>

                <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:4px;">
                    <p style="font-size:12px;font-weight:600;color:#6b7280;
                              text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                        Link to Record
                        <span style="font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span>
                    </p>
                    <div class="crm-field-row">
                        <div class="crm-field-group">
                            <label>Record Type</label>
                            <select id="crm-task-linked-type">
                                <option value="">None</option>
                                <option value="contact">Contact</option>
                                <option value="company">Company</option>
                                <option value="deal">Deal</option>
                            </select>
                        </div>
                        <div class="crm-field-group">
                            <label>Record</label>
                            <select id="crm-task-linked-id" disabled>
                                <option value="">Select type first...</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-task-modal')">
                    Cancel
                </button>
                <button class="bntm-btn-primary" id="crm-task-save-btn">Save Task</button>
            </div>
        </div>
    </div>

    <style>
    .crm-task-summary-strip {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .crm-task-summary-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .crm-task-summary-overdue {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .crm-task-summary-upcoming {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .crm-task-section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        min-width: 24px;
        height: 24px;
        padding: 0 7px;
        border-radius: 20px;
    }
    .crm-linked-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .crm-linked-contact { background: #eff6ff; color: #2563eb; }
    .crm-linked-company { background: #f0fdf4; color: #16a34a; }
    .crm-linked-deal    { background: #fdf4ff; color: #9333ea; }
    .crm-reminder-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 12px;
        color: #374151;
        font-weight: 500;
    }
    .crm-reminder-active {
        background: #fffbeb;
        border-color: #fde68a;
        color: #d97706;
    }
    </style>

    <script>
    (function() {
        var contactsData  = <?php echo wp_json_encode(
            array_map( function($c) {
                return [
                    'id'   => (int) $c->id,
                    'name' => trim( $c->first_name . ' ' . $c->last_name ),
                ];
            }, $contacts )
        ); ?>;
        var companiesData = <?php echo wp_json_encode(
            array_map( function($co) {
                return [ 'id' => (int) $co->id, 'name' => $co->name ];
            }, $companies )
        ); ?>;
        var dealsData     = <?php echo wp_json_encode(
            array_map( function($d) {
                return [ 'id' => (int) $d->id, 'name' => $d->name ];
            }, $deals )
        ); ?>;

        // ── Linked type → populate linked id select ──
        var linkedTypeEl = document.getElementById('crm-task-linked-type');
        var linkedIdEl   = document.getElementById('crm-task-linked-id');

        linkedTypeEl.addEventListener('change', function() {
            populateLinkedIdSelect( this.value, 0 );
        });

        function populateLinkedIdSelect(type, selectedId) {
            linkedIdEl.innerHTML = '';
            if ( ! type ) {
                linkedIdEl.disabled = true;
                linkedIdEl.innerHTML = '<option value="">Select type first...</option>';
                return;
            }

            linkedIdEl.disabled = false;
            var opt = document.createElement('option');
            opt.value = ''; opt.textContent = 'Select...';
            linkedIdEl.appendChild(opt);

            var records = [];
            if ( type === 'contact' ) records = contactsData;
            if ( type === 'company' ) records = companiesData;
            if ( type === 'deal' )    records = dealsData;

            records.forEach(function(r) {
                var o = document.createElement('option');
                o.value       = r.id;
                o.textContent = r.name;
                if ( r.id == selectedId ) o.selected = true;
                linkedIdEl.appendChild(o);
            });
        }

        // ── Reminder preview ──
        function updateReminderPreview() {
            var dueDate     = document.getElementById('crm-task-due-date').value;
            var remindDays  = parseInt( document.getElementById('crm-task-reminder-days').value ) || 0;
            var previewEl   = document.getElementById('crm-task-reminder-preview');

            if ( dueDate && remindDays > 0 ) {
                var remindDate = new Date(dueDate);
                remindDate.setDate( remindDate.getDate() - remindDays );
                var formatted = remindDate.toISOString().substring(0,10);
                previewEl.textContent = '(remind on ' + formatted + ')';
            } else {
                previewEl.textContent = '';
            }
        }

        document.getElementById('crm-task-due-date').addEventListener('change', updateReminderPreview);
        document.getElementById('crm-task-reminder-days').addEventListener('input', updateReminderPreview);

        // ── Add button ──
        document.getElementById('crm-task-add-btn').addEventListener('click', function() {
            document.getElementById('crm-task-id').value            = '';
            document.getElementById('crm-task-title').value         = '';
            document.getElementById('crm-task-assignee').value      = '';
            document.getElementById('crm-task-due-date').value      = '';
            document.getElementById('crm-task-reminder-days').value = '';
            document.getElementById('crm-task-reminder-preview').textContent = '';
            linkedTypeEl.value = '';
            populateLinkedIdSelect('', 0);
            document.querySelector('#crm-task-modal .crm-modal-header h3').textContent = 'Add Task';
            crmOpenModal('crm-task-modal');
        });

        // ── Edit button ──
        document.addEventListener('click', function(e) {
            var editBtn = e.target.closest('.crm-task-edit-btn');
            if ( ! editBtn ) return;

            var task = {};
            try { task = JSON.parse( editBtn.dataset.task || '{}' ); } catch(err) {}

            document.getElementById('crm-task-id').value            = task.id            || '';
            document.getElementById('crm-task-title').value         = task.title         || '';
            document.getElementById('crm-task-assignee').value      = task.assignee_id   || '';
            document.getElementById('crm-task-due-date').value      = task.due_date      || '';
            document.getElementById('crm-task-reminder-days').value = task.reminder_days || 0;

            linkedTypeEl.value = task.linked_type || '';
            populateLinkedIdSelect( task.linked_type || '', task.linked_id || 0 );

            updateReminderPreview();
            document.querySelector('#crm-task-modal .crm-modal-header h3').textContent = 'Edit Task';
            crmOpenModal('crm-task-modal');
        });

        // ── Complete button ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-task-complete-btn');
            if ( ! btn ) return;

            var taskId    = btn.dataset.id;
            btn.disabled  = true;

            crmPost( 'crm_complete_task', { task_id: taskId }, function(data) {
                crmToast(data.message || 'Task marked complete.', 'success');
                var row = document.querySelector('tr[data-task-id="' + taskId + '"]');
                if ( row ) {
                    row.style.transition = 'opacity .3s';
                    row.style.opacity    = '0';
                    setTimeout(function() { row.remove(); }, 300);
                }
            }, function() {
                btn.disabled = false;
            });
        });

        // ── Delete button ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-task-delete-btn');
            if ( ! btn ) return;

            var taskId = btn.dataset.id;
            crmConfirm('Delete this task? This cannot be undone.', function() {
                crmPost( 'crm_delete_task', { task_id: taskId }, function(data) {
                    crmToast(data.message || 'Task deleted.', 'success');
                    var row = document.querySelector('tr[data-task-id="' + taskId + '"]');
                    if ( row ) {
                        row.style.transition = 'opacity .3s';
                        row.style.opacity    = '0';
                        setTimeout(function() { row.remove(); }, 300);
                    }
                });
            });
        });

        // ── Save button ──
        document.getElementById('crm-task-save-btn').addEventListener('click', function() {
            var btn    = this;
            var taskId = document.getElementById('crm-task-id').value;
            var title  = document.getElementById('crm-task-title').value.trim();

            if ( ! title ) {
                crmToast('Task title is required.', 'error');
                return;
            }

            var action      = taskId ? 'crm_update_task' : 'crm_create_task';
            btn.disabled    = true;
            btn.textContent = 'Saving...';

            crmPost( action, {
                task_id:       taskId,
                title:         title,
                assignee_id:   document.getElementById('crm-task-assignee').value      || '',
                due_date:      document.getElementById('crm-task-due-date').value       || '',
                reminder_days: document.getElementById('crm-task-reminder-days').value  || 0,
                linked_type:   linkedTypeEl.value                                        || '',
                linked_id:     linkedIdEl.value                                          || 0
            }, function(data) {
                crmToast(data.message || 'Saved.', 'success');
                crmCloseModal('crm-task-modal');
                btn.disabled    = false;
                btn.textContent = 'Save Task';
                setTimeout(function() { window.location.reload(); }, 800);
            }, function() {
                btn.disabled    = false;
                btn.textContent = 'Save Task';
            });
        });

        // ── Client-side search filter ──
        var searchTimer = null;
        document.getElementById('crm-task-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            var q = this.value.toLowerCase();
            searchTimer = setTimeout(function() {
                document.querySelectorAll('#crm-overdue-tbody tr, #crm-upcoming-tbody tr')
                    .forEach(function(row) {
                        var text = row.textContent.toLowerCase();
                        row.style.display = text.indexOf(q) !== -1 ? '' : 'none';
                    });
            }, 250);
        });

        document.getElementById('crm-task-filter-assignee').addEventListener('change', function() {
            var val = this.value;
            document.querySelectorAll('#crm-overdue-tbody tr, #crm-upcoming-tbody tr')
                .forEach(function(row) {
                    if ( ! val ) { row.style.display = ''; return; }
                    var editBtn = row.querySelector('.crm-task-edit-btn');
                    if ( ! editBtn ) { row.style.display = ''; return; }
                    var task = {};
                    try { task = JSON.parse( editBtn.dataset.task || '{}' ); } catch(e) {}
                    row.style.display = ( String(task.assignee_id) === String(val) ) ? '' : 'none';
                });
        });

        document.getElementById('crm-task-filter-linked-type').addEventListener('change', function() {
            var val = this.value;
            document.querySelectorAll('#crm-overdue-tbody tr, #crm-upcoming-tbody tr')
                .forEach(function(row) {
                    if ( ! val ) { row.style.display = ''; return; }
                    var editBtn = row.querySelector('.crm-task-edit-btn');
                    if ( ! editBtn ) { row.style.display = ''; return; }
                    var task = {};
                    try { task = JSON.parse( editBtn.dataset.task || '{}' ); } catch(e) {}
                    row.style.display = ( task.linked_type === val ) ? '' : 'none';
                });
        });

    })();
    </script>
    <?php
    return ob_get_clean();
}

//Last marker: Tab 5. Continue to Tab 6 in claude Ayano
// =============================================================================
// TAB 6 — SETTINGS
// =============================================================================

function crm_settings_tab( $business_id ) {
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        return '<div class="bntm-notice bntm-notice-error">You do not have permission to access settings.</div>';
    }

    $stages = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    $custom_props = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND status = 'active'
         ORDER BY object_type ASC, sort_order ASC",
        $business_id
    ) );

    $lifecycle_statuses = [
        'lead'     => 'Lead',
        'prospect' => 'Prospect',
        'customer' => 'Customer',
        'churned'  => 'Churned',
    ];

    ob_start();
    ?>

    <div class="crm-settings-grid">

        <!-- LEFT COLUMN -->
        <div class="crm-settings-col">

            <!-- Pipeline Stages -->
            <div class="bntm-form-section">
                <div style="display:flex;align-items:center;justify-content:space-between;
                            margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
                    <div>
                        <h3 style="margin:0;padding:0;border:none;">Pipeline Stages</h3>
                        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                            Drag to reorder. Changes are saved automatically.
                        </p>
                    </div>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-stage-add-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Stage
                    </button>
                </div>

                <ul class="crm-stage-list" id="crm-stage-sortable">
                    <?php if ( empty( $stages ) ) : ?>
                        <li id="crm-stage-empty-msg"
                            style="text-align:center;padding:24px;color:#9ca3af;font-size:13px;list-style:none;">
                            No stages yet. Add your first pipeline stage.
                        </li>
                    <?php else : ?>
                        <?php foreach ( $stages as $stage ) : ?>
                        <li class="crm-stage-item"
                            data-id="<?php echo intval( $stage->id ); ?>"
                            draggable="true">
                            <span class="crm-stage-drag-handle">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 8h16M4 16h16"/>
                                </svg>
                            </span>
                            <span class="crm-stage-color-dot"
                                  style="background:<?php echo esc_attr( $stage->color ); ?>;"></span>
                            <span class="crm-stage-name">
                                <?php echo esc_html( $stage->name ); ?>
                            </span>
                            <button class="bntm-btn-secondary bntm-btn-small crm-stage-edit-btn"
                                    data-id="<?php echo intval( $stage->id ); ?>"
                                    data-name="<?php echo esc_attr( $stage->name ); ?>"
                                    data-color="<?php echo esc_attr( $stage->color ); ?>">
                                Edit
                            </button>
                            <button class="bntm-btn-danger bntm-btn-small crm-stage-delete-btn"
                                    data-id="<?php echo intval( $stage->id ); ?>"
                                    data-name="<?php echo esc_attr( $stage->name ); ?>">
                                Delete
                            </button>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Lifecycle Status Labels -->
            <div class="bntm-form-section">
                <h3>Lifecycle Status Labels</h3>
                <p style="font-size:13px;color:#6b7280;margin:-8px 0 16px;">
                    These are the default contact lifecycle stages used across the CRM.
                </p>
                <div class="bntm-table-wrapper">
                    <table class="bntm-table">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Label</th>
                                <th>Badge Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $lifecycle_statuses as $key => $label ) : ?>
                            <tr>
                                <td>
                                    <code style="background:#f3f4f6;padding:2px 7px;
                                                 border-radius:4px;font-size:12px;">
                                        <?php echo esc_html( $key ); ?>
                                    </code>
                                </td>
                                <td style="font-weight:500;"><?php echo esc_html( $label ); ?></td>
                                <td>
                                    <span class="crm-badge crm-badge-<?php echo esc_attr( $key ); ?>">
                                        <?php echo esc_html( $label ); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="font-size:12px;color:#9ca3af;margin-top:12px;">
                    Lifecycle status labels are fixed in v1. Custom label editing will be available in a future update.
                </p>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="crm-settings-col">

            <!-- Custom Properties -->
            <div class="bntm-form-section">
                <div style="display:flex;align-items:center;justify-content:space-between;
                            margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
                    <div>
                        <h3 style="margin:0;padding:0;border:none;">Custom Properties</h3>
                        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                            Add extra fields to contacts, companies, or deals.
                        </p>
                    </div>
                    <button class="bntm-btn-primary bntm-btn-small" id="crm-prop-add-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Property
                    </button>
                </div>

                <!-- Props grouped by object type -->
                <?php
                $grouped = [ 'contact' => [], 'company' => [], 'deal' => [] ];
                foreach ( $custom_props as $prop ) {
                    if ( isset( $grouped[ $prop->object_type ] ) ) {
                        $grouped[ $prop->object_type ][] = $prop;
                    }
                }
                $group_labels = [
                    'contact' => 'Contact Properties',
                    'company' => 'Company Properties',
                    'deal'    => 'Deal Properties',
                ];
                $group_colors = [
                    'contact' => '#eff6ff',
                    'company' => '#f0fdf4',
                    'deal'    => '#fdf4ff',
                ];
                $group_text = [
                    'contact' => '#2563eb',
                    'company' => '#16a34a',
                    'deal'    => '#9333ea',
                ];
                ?>

                <?php foreach ( $grouped as $type => $props ) : ?>
                <div style="margin-bottom:20px;" id="crm-props-group-<?php echo esc_attr( $type ); ?>">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <span style="display:inline-block;background:<?php echo $group_colors[$type]; ?>;
                                     color:<?php echo $group_text[$type]; ?>;font-size:11px;
                                     font-weight:700;text-transform:uppercase;letter-spacing:.5px;
                                     padding:3px 10px;border-radius:20px;">
                            <?php echo esc_html( $group_labels[$type] ); ?>
                        </span>
                        <span style="font-size:12px;color:#9ca3af;">
                            <?php echo count($props); ?> propert<?php echo count($props) === 1 ? 'y' : 'ies'; ?>
                        </span>
                    </div>

                    <?php if ( empty( $props ) ) : ?>
                        <p style="font-size:13px;color:#9ca3af;padding:8px 0;">
                            No custom properties for <?php echo esc_html( $type ); ?>s yet.
                        </p>
                    <?php else : ?>
                        <div class="bntm-table-wrapper">
                            <table class="bntm-table">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Field Name</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="crm-props-tbody-<?php echo esc_attr( $type ); ?>">
                                    <?php foreach ( $props as $prop ) : ?>
                                    <tr data-prop-id="<?php echo intval( $prop->id ); ?>">
                                        <td style="font-weight:500;">
                                            <?php echo esc_html( $prop->field_label ); ?>
                                        </td>
                                        <td>
                                            <code style="background:#f3f4f6;padding:2px 7px;
                                                         border-radius:4px;font-size:12px;">
                                                <?php echo esc_html( $prop->field_name ); ?>
                                            </code>
                                        </td>
                                        <td>
                                            <span class="crm-prop-type-badge">
                                                <?php echo esc_html( $prop->field_type ); ?>
                                            </span>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <button class="bntm-btn-secondary bntm-btn-small
                                                           crm-prop-edit-btn"
                                                    data-id="<?php echo intval( $prop->id ); ?>"
                                                    data-label="<?php echo esc_attr( $prop->field_label ); ?>"
                                                    data-name="<?php echo esc_attr( $prop->field_name ); ?>"
                                                    data-type="<?php echo esc_attr( $prop->field_type ); ?>"
                                                    data-object="<?php echo esc_attr( $prop->object_type ); ?>"
                                                    data-options="<?php echo esc_attr( $prop->field_options ?: '' ); ?>">
                                                Edit
                                            </button>
                                            <button class="bntm-btn-danger bntm-btn-small
                                                           crm-prop-delete-btn"
                                                    data-id="<?php echo intval( $prop->id ); ?>"
                                                    data-label="<?php echo esc_attr( $prop->field_label ); ?>">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

            </div>

        </div>
    </div>

    <!-- Add / Edit Stage Modal -->
    <div class="crm-modal-overlay" id="crm-stage-modal">
        <div class="crm-modal" style="max-width:420px;">
            <div class="crm-modal-header">
                <h3 data-default="Add Stage">Add Stage</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-stage-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-stage-id" value="">
                <div class="crm-field-group">
                    <label>Stage Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="crm-stage-name" placeholder="e.g. Qualified">
                </div>
                <div class="crm-field-group">
                    <label>Color</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <input type="color" id="crm-stage-color" value="#6366f1"
                               style="width:48px;height:36px;padding:2px;border:1px solid #d1d5db;
                                      border-radius:8px;cursor:pointer;">
                        <div class="crm-stage-color-swatches">
                            <?php
                            $swatches = [
                                '#6366f1','#8b5cf6','#ec4899','#ef4444',
                                '#f59e0b','#10b981','#06b6d4','#3b82f6',
                                '#64748b','#1f2937',
                            ];
                            foreach ( $swatches as $sw ) :
                            ?>
                                <button class="crm-color-swatch"
                                        data-color="<?php echo esc_attr( $sw ); ?>"
                                        style="background:<?php echo esc_attr( $sw ); ?>;"
                                        title="<?php echo esc_attr( $sw ); ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-stage-modal')">
                    Cancel
                </button>
                <button class="bntm-btn-primary" id="crm-stage-save-btn">Save Stage</button>
            </div>
        </div>
    </div>

    <!-- Add / Edit Custom Property Modal -->
    <div class="crm-modal-overlay" id="crm-prop-modal">
        <div class="crm-modal" style="max-width:480px;">
            <div class="crm-modal-header">
                <h3 data-default="Add Custom Property">Add Custom Property</h3>
                <button class="crm-modal-close" onclick="crmCloseModal('crm-prop-modal')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="crm-prop-id" value="">

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Field Label <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="crm-prop-label"
                               placeholder="e.g. LinkedIn URL">
                    </div>
                    <div class="crm-field-group">
                        <label>
                            Field Name
                            <span style="font-weight:400;color:#9ca3af;">(auto)</span>
                        </label>
                        <input type="text" id="crm-prop-name"
                               placeholder="e.g. linkedin_url"
                               style="font-family:monospace;font-size:13px;">
                    </div>
                </div>

                <div class="crm-field-row">
                    <div class="crm-field-group">
                        <label>Object Type <span style="color:#ef4444;">*</span></label>
                        <select id="crm-prop-object">
                            <option value="contact">Contact</option>
                            <option value="company">Company</option>
                            <option value="deal">Deal</option>
                        </select>
                    </div>
                    <div class="crm-field-group">
                        <label>Field Type <span style="color:#ef4444;">*</span></label>
                        <select id="crm-prop-type">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select (dropdown)</option>
                        </select>
                    </div>
                </div>

                <div class="crm-field-group" id="crm-prop-options-group" style="display:none;">
                    <label>
                        Dropdown Options
                        <span style="font-weight:400;color:#9ca3af;">(one per line)</span>
                    </label>
                    <textarea id="crm-prop-options" rows="4"
                              placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                </div>

                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;
                            padding:12px;margin-top:4px;">
                    <p style="font-size:12px;color:#6b7280;margin:0;">
                        <svg width="14" height="14" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:4px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Custom property values are stored as JSON on each record and displayed
                        in the create/edit form for the selected object type.
                    </p>
                </div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('crm-prop-modal')">
                    Cancel
                </button>
                <button class="bntm-btn-primary" id="crm-prop-save-btn">Save Property</button>
            </div>
        </div>
    </div>

    <style>
    .crm-settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .crm-settings-grid { grid-template-columns: 1fr; }
    }
    .crm-settings-col { display: flex; flex-direction: column; gap: 20px; }
    .crm-stage-color-swatches {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .crm-color-swatch {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        transition: transform .15s, border-color .15s;
        padding: 0;
    }
    .crm-color-swatch:hover        { transform: scale(1.2); }
    .crm-color-swatch.selected     { border-color: #111827; transform: scale(1.15); }
    </style>

    <script>
    (function() {

        // ====================================================================
        // PIPELINE STAGES
        // ====================================================================

        var stageList    = document.getElementById('crm-stage-sortable');
        var draggedStage = null;

        // ── Stage drag-and-drop reorder ──
        stageList.addEventListener('dragstart', function(e) {
            var li = e.target.closest('.crm-stage-item');
            if ( ! li ) return;
            draggedStage = li;
            li.style.opacity = '.45';
            e.dataTransfer.effectAllowed = 'move';
        });

        stageList.addEventListener('dragend', function(e) {
            var li = e.target.closest('.crm-stage-item');
            if ( li ) li.style.opacity = '1';
            document.querySelectorAll('.crm-stage-item').forEach(function(el) {
                el.classList.remove('drag-over');
            });
            draggedStage = null;
            saveStageOrder();
        });

        stageList.addEventListener('dragover', function(e) {
            e.preventDefault();
            var li = e.target.closest('.crm-stage-item');
            if ( ! li || li === draggedStage ) return;
            document.querySelectorAll('.crm-stage-item').forEach(function(el) {
                el.classList.remove('drag-over');
            });
            li.classList.add('drag-over');
            var rect     = li.getBoundingClientRect();
            var midpoint = rect.top + rect.height / 2;
            if ( e.clientY < midpoint ) {
                stageList.insertBefore( draggedStage, li );
            } else {
                stageList.insertBefore( draggedStage, li.nextSibling );
            }
        });

        stageList.addEventListener('dragleave', function(e) {
            var li = e.target.closest('.crm-stage-item');
            if ( li ) li.classList.remove('drag-over');
        });

        function saveStageOrder() {
            var ids = [];
            stageList.querySelectorAll('.crm-stage-item[data-id]').forEach(function(li) {
                ids.push( li.dataset.id );
            });
            if ( ids.length === 0 ) return;
            crmPost( 'crm_reorder_stages', { stage_ids: ids.join(',') }, function() {
                crmToast('Stage order saved.', 'success');
            });
        }

        // ── Add stage button ──
        document.getElementById('crm-stage-add-btn').addEventListener('click', function() {
            document.getElementById('crm-stage-id').value   = '';
            document.getElementById('crm-stage-name').value = '';
            document.getElementById('crm-stage-color').value = '#6366f1';
            document.querySelectorAll('.crm-color-swatch').forEach(function(s) {
                s.classList.remove('selected');
            });
            document.querySelector('#crm-stage-modal .crm-modal-header h3').textContent = 'Add Stage';
            crmOpenModal('crm-stage-modal');
        });

        // ── Edit stage buttons ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-stage-edit-btn');
            if ( ! btn ) return;
            document.getElementById('crm-stage-id').value    = btn.dataset.id;
            document.getElementById('crm-stage-name').value  = btn.dataset.name;
            document.getElementById('crm-stage-color').value = btn.dataset.color;
            document.querySelectorAll('.crm-color-swatch').forEach(function(s) {
                s.classList.toggle('selected', s.dataset.color === btn.dataset.color);
            });
            document.querySelector('#crm-stage-modal .crm-modal-header h3').textContent = 'Edit Stage';
            crmOpenModal('crm-stage-modal');
        });

        // ── Delete stage buttons ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-stage-delete-btn');
            if ( ! btn ) return;
            crmConfirm(
                'Delete stage "' + btn.dataset.name + '"? Deals in this stage will need reassignment.',
                function() {
                    crmPost( 'crm_delete_stage', { stage_id: btn.dataset.id }, function(data) {
                        crmToast(data.message || 'Stage deleted.', 'success');
                        var li = stageList.querySelector('.crm-stage-item[data-id="' + btn.dataset.id + '"]');
                        if ( li ) {
                            li.style.transition = 'opacity .3s';
                            li.style.opacity    = '0';
                            setTimeout(function() { li.remove(); }, 300);
                        }
                    });
                }
            );
        });

        // ── Color swatches ──
        document.querySelectorAll('.crm-color-swatch').forEach(function(swatch) {
            swatch.addEventListener('click', function() {
                document.querySelectorAll('.crm-color-swatch').forEach(function(s) {
                    s.classList.remove('selected');
                });
                this.classList.add('selected');
                document.getElementById('crm-stage-color').value = this.dataset.color;
            });
        });

        // ── Save stage ──
        document.getElementById('crm-stage-save-btn').addEventListener('click', function() {
            var btn     = this;
            var stageId = document.getElementById('crm-stage-id').value;
            var name    = document.getElementById('crm-stage-name').value.trim();
            var color   = document.getElementById('crm-stage-color').value;

            if ( ! name ) { crmToast('Stage name is required.', 'error'); return; }

            var action      = stageId ? 'crm_save_stage' : 'crm_save_stage';
            btn.disabled    = true;
            btn.textContent = 'Saving...';

            crmPost( action, {
                stage_id: stageId,
                name:     name,
                color:    color
            }, function(data) {
                crmToast(data.message || 'Stage saved.', 'success');
                crmCloseModal('crm-stage-modal');
                btn.disabled    = false;
                btn.textContent = 'Save Stage';

                if ( stageId ) {
                    var li     = stageList.querySelector('.crm-stage-item[data-id="' + stageId + '"]');
                    var dotEl  = li  ? li.querySelector('.crm-stage-color-dot') : null;
                    var nameEl = li  ? li.querySelector('.crm-stage-name')      : null;
                    var editBtn = li ? li.querySelector('.crm-stage-edit-btn')  : null;
                    if ( dotEl )  dotEl.style.background = color;
                    if ( nameEl ) nameEl.textContent     = name;
                    if ( editBtn ) {
                        editBtn.dataset.name  = name;
                        editBtn.dataset.color = color;
                    }
                } else {
                    var newId   = data.stage_id || 0;
                    var emptyMsg = document.getElementById('crm-stage-empty-msg');
                    if ( emptyMsg ) emptyMsg.remove();

                    var li = document.createElement('li');
                    li.className   = 'crm-stage-item';
                    li.dataset.id  = newId;
                    li.draggable   = true;
                    li.innerHTML   =
                        '<span class="crm-stage-drag-handle">'
                        + '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                        + '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>'
                        + '</svg></span>'
                        + '<span class="crm-stage-color-dot" style="background:' + crmEsc(color) + ';"></span>'
                        + '<span class="crm-stage-name">' + crmEsc(name) + '</span>'
                        + '<button class="bntm-btn-secondary bntm-btn-small crm-stage-edit-btn" '
                        + 'data-id="' + newId + '" data-name="' + crmEsc(name) + '" '
                        + 'data-color="' + crmEsc(color) + '">Edit</button>'
                        + '<button class="bntm-btn-danger bntm-btn-small crm-stage-delete-btn" '
                        + 'data-id="' + newId + '" data-name="' + crmEsc(name) + '">Delete</button>';
                    stageList.appendChild(li);
                }
            }, function() {
                btn.disabled    = false;
                btn.textContent = 'Save Stage';
            });
        });

        // ====================================================================
        // CUSTOM PROPERTIES
        // ====================================================================

        var propTypeEl    = document.getElementById('crm-prop-type');
        var propOptGroup  = document.getElementById('crm-prop-options-group');
        var propLabelEl   = document.getElementById('crm-prop-label');
        var propNameEl    = document.getElementById('crm-prop-name');

        // ── Show/hide options textarea based on type ──
        propTypeEl.addEventListener('change', function() {
            propOptGroup.style.display = this.value === 'select' ? 'block' : 'none';
        });

        // ── Auto-generate field name from label ──
        propLabelEl.addEventListener('input', function() {
            if ( document.getElementById('crm-prop-id').value ) return;
            propNameEl.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s_]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 60);
        });

        // ── Add property button ──
        document.getElementById('crm-prop-add-btn').addEventListener('click', function() {
            document.getElementById('crm-prop-id').value      = '';
            propLabelEl.value                                  = '';
            propNameEl.value                                   = '';
            document.getElementById('crm-prop-object').value  = 'contact';
            propTypeEl.value                                   = 'text';
            document.getElementById('crm-prop-options').value = '';
            propOptGroup.style.display                         = 'none';
            document.querySelector('#crm-prop-modal .crm-modal-header h3').textContent = 'Add Custom Property';
            crmOpenModal('crm-prop-modal');
        });

        // ── Edit property buttons ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-prop-edit-btn');
            if ( ! btn ) return;

            document.getElementById('crm-prop-id').value     = btn.dataset.id;
            propLabelEl.value                                  = btn.dataset.label  || '';
            propNameEl.value                                   = btn.dataset.name   || '';
            document.getElementById('crm-prop-object').value  = btn.dataset.object || 'contact';
            propTypeEl.value                                   = btn.dataset.type   || 'text';
            propOptGroup.style.display                         = btn.dataset.type === 'select' ? 'block' : 'none';

            var rawOpts = btn.dataset.options || '';
            var opts    = [];
            try { opts = JSON.parse(rawOpts) || []; } catch(e) {}
            document.getElementById('crm-prop-options').value = opts.join('\n');

            document.querySelector('#crm-prop-modal .crm-modal-header h3').textContent = 'Edit Custom Property';
            crmOpenModal('crm-prop-modal');
        });

        // ── Delete property buttons ──
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.crm-prop-delete-btn');
            if ( ! btn ) return;
            crmConfirm(
                'Delete property "' + btn.dataset.label + '"? '
                + 'Existing values stored on records will no longer display.',
                function() {
                    crmPost( 'crm_delete_custom_property', { prop_id: btn.dataset.id }, function(data) {
                        crmToast(data.message || 'Property deleted.', 'success');
                        var row = document.querySelector('tr[data-prop-id="' + btn.dataset.id + '"]');
                        if ( row ) {
                            row.style.transition = 'opacity .3s';
                            row.style.opacity    = '0';
                            setTimeout(function() { row.remove(); }, 300);
                        }
                    });
                }
            );
        });

        // ── Save property ──
        document.getElementById('crm-prop-save-btn').addEventListener('click', function() {
            var btn    = this;
            var propId = document.getElementById('crm-prop-id').value;
            var label  = propLabelEl.value.trim();
            var name   = propNameEl.value.trim();
            var object = document.getElementById('crm-prop-object').value;
            var type   = propTypeEl.value;

            if ( ! label )  { crmToast('Field label is required.',  'error'); return; }
            if ( ! name )   { crmToast('Field name is required.',   'error'); return; }
            if ( ! /^[a-z0-9_]+$/.test(name) ) {
                crmToast('Field name must be lowercase letters, numbers, or underscores only.', 'error');
                return;
            }

            var options = '';
            if ( type === 'select' ) {
                var rawLines = document.getElementById('crm-prop-options').value
                    .split('\n')
                    .map(function(l){ return l.trim(); })
                    .filter(function(l){ return l.length > 0; });
                if ( rawLines.length === 0 ) {
                    crmToast('Please add at least one dropdown option.', 'error');
                    return;
                }
                options = JSON.stringify(rawLines);
            }

            btn.disabled    = true;
            btn.textContent = 'Saving...';

            crmPost( 'crm_save_custom_property', {
                prop_id:      propId,
                field_label:  label,
                field_name:   name,
                object_type:  object,
                field_type:   type,
                field_options: options
            }, function(data) {
                crmToast(data.message || 'Property saved.', 'success');
                crmCloseModal('crm-prop-modal');
                btn.disabled    = false;
                btn.textContent = 'Save Property';
                setTimeout(function() { window.location.reload(); }, 900);
            }, function() {
                btn.disabled    = false;
                btn.textContent = 'Save Property';
            });
        });

    })();
    </script>
    <?php
    return ob_get_clean();
}

// =============================================================================
// AJAX HANDLERS — DASHBOARD
// =============================================================================

function bntm_ajax_crm_get_dashboard_stats() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $today       = current_time( 'Y-m-d' );

    $total_contacts = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
         WHERE business_id = %d AND status = 'active'",
        $business_id
    ) );

    $total_companies = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_companies
         WHERE business_id = %d AND status = 'active'",
        $business_id
    ) );

    $total_open_deals = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE business_id = %d AND status = 'open'",
        $business_id
    ) );

    $pipeline_value = (float) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}crm_deals
         WHERE business_id = %d AND status = 'open'",
        $business_id
    ) );

    $overdue_tasks = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
         WHERE business_id = %d AND status = 'pending' AND due_date < %s",
        $business_id, $today
    ) );

    $this_month_contacts = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
         WHERE business_id = %d AND status = 'active'
           AND MONTH(created_at) = MONTH(%s)
           AND YEAR(created_at)  = YEAR(%s)",
        $business_id, $today, $today
    ) );

    $stage_breakdown = $wpdb->get_results( $wpdb->prepare(
        "SELECT ps.id, ps.name, ps.color,
                COUNT(d.id)                   AS deal_count,
                COALESCE(SUM(d.amount), 0)    AS stage_value
         FROM {$wpdb->prefix}crm_pipeline_stages ps
         LEFT JOIN {$wpdb->prefix}crm_deals d
           ON d.stage_id    = ps.id
           AND d.business_id = %d
           AND d.status      = 'open'
         WHERE ps.business_id = %d
           AND ps.pipeline_id = 1
           AND ps.status      = 'active'
         GROUP BY ps.id, ps.name, ps.color, ps.sort_order
         ORDER BY ps.sort_order ASC",
        $business_id, $business_id
    ) );

    wp_send_json_success( [
        'total_contacts'      => $total_contacts,
        'total_companies'     => $total_companies,
        'total_open_deals'    => $total_open_deals,
        'pipeline_value'      => $pipeline_value,
        'overdue_tasks'       => $overdue_tasks,
        'this_month_contacts' => $this_month_contacts,
        'stage_breakdown'     => $stage_breakdown,
    ] );
}

function bntm_ajax_crm_get_recent_activity() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $limit       = min( intval( $_POST['limit'] ?? 10 ), 50 );

    $activities = $wpdb->get_results( $wpdb->prepare(
        "SELECT a.*, u.display_name AS author_name
         FROM {$wpdb->prefix}crm_activities a
         LEFT JOIN {$wpdb->users} u ON u.ID = a.author_id
         WHERE a.business_id = %d AND a.status = 'active'
         ORDER BY a.created_at DESC
         LIMIT %d",
        $business_id, $limit
    ) );

    wp_send_json_success( [ 'activities' => $activities ] );
}

// =============================================================================
// AJAX HANDLERS — CONTACTS
// =============================================================================

function bntm_ajax_crm_get_contacts() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id     = get_current_user_id();
    $search          = sanitize_text_field( $_POST['search']           ?? '' );
    $lifecycle       = sanitize_text_field( $_POST['lifecycle_status'] ?? '' );
    $owner_id        = intval( $_POST['owner_id']                      ?? 0 );
    $page            = max( 1, intval( $_POST['page']                  ?? 1 ) );
    $per_page        = min( intval( $_POST['per_page']                 ?? 20 ), 100 );
    $offset          = ( $page - 1 ) * $per_page;

    $where  = [ $wpdb->prepare( 'c.business_id = %d', $business_id ) ];
    $where[] = "c.status = 'active'";

    if ( $search !== '' ) {
        $like    = '%' . $wpdb->esc_like( $search ) . '%';
        $where[] = $wpdb->prepare(
            "(c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s OR c.phone LIKE %s)",
            $like, $like, $like, $like
        );
    }
    if ( $lifecycle !== '' ) {
        $where[] = $wpdb->prepare( 'c.lifecycle_status = %s', $lifecycle );
    }
    if ( $owner_id > 0 ) {
        $where[] = $wpdb->prepare( 'c.owner_id = %d', $owner_id );
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    $total = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts c {$where_sql}"
    );

    $contacts = $wpdb->get_results( $wpdb->prepare(
        "SELECT c.*,
                u.display_name  AS owner_name,
                co.name         AS company_name
         FROM {$wpdb->prefix}crm_contacts c
         LEFT JOIN {$wpdb->users} u          ON u.ID   = c.owner_id
         LEFT JOIN {$wpdb->prefix}crm_companies co ON co.id  = c.company_id
         {$where_sql}
         ORDER BY c.created_at DESC
         LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );

    wp_send_json_success( [
        'contacts'    => $contacts,
        'total'       => $total,
        'total_pages' => $per_page > 0 ? ceil( $total / $per_page ) : 1,
    ] );
}

function bntm_ajax_crm_create_contact() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();

    $first_name       = sanitize_text_field( $_POST['first_name']        ?? '' );
    $last_name        = sanitize_text_field( $_POST['last_name']         ?? '' );
    $email            = sanitize_email(      $_POST['email']             ?? '' );
    $phone            = sanitize_text_field( $_POST['phone']             ?? '' );
    $lifecycle_status = sanitize_text_field( $_POST['lifecycle_status']  ?? 'lead' );
    $owner_id         = intval(              $_POST['owner_id']          ?? 0 );
    $company_id       = intval(              $_POST['company_id']        ?? 0 );
    $tags             = sanitize_text_field( $_POST['tags']              ?? '' );
    $custom_raw       = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $first_name === '' ) {
        wp_send_json_error( [ 'message' => 'First name is required.' ] );
    }

    $allowed_statuses = [ 'lead', 'prospect', 'customer', 'churned' ];
    if ( ! in_array( $lifecycle_status, $allowed_statuses, true ) ) {
        $lifecycle_status = 'lead';
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $wpdb->query( 'START TRANSACTION' );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_contacts',
        [
            'rand_id'           => bntm_rand_id(),
            'business_id'       => $business_id,
            'owner_id'          => $owner_id,
            'company_id'        => $company_id,
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $email,
            'phone'             => $phone,
            'lifecycle_status'  => $lifecycle_status,
            'tags'              => $tags,
            'custom_properties' => $custom_props,
            'status'            => 'active',
        ],
        [ '%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s' ]
    );

    if ( ! $inserted ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to create contact. Please try again.' ] );
    }

    $contact_id = $wpdb->insert_id;

    crm_log_activity(
        $business_id,
        $business_id,
        'contact',
        $contact_id,
        'note',
        'Contact created: ' . trim( $first_name . ' ' . $last_name )
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [
        'message'    => 'Contact created successfully.',
        'contact_id' => $contact_id,
    ] );
}

function bntm_ajax_crm_update_contact() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();

    $contact_id       = intval(              $_POST['contact_id']       ?? 0 );
    $first_name       = sanitize_text_field( $_POST['first_name']       ?? '' );
    $last_name        = sanitize_text_field( $_POST['last_name']        ?? '' );
    $email            = sanitize_email(      $_POST['email']            ?? '' );
    $phone            = sanitize_text_field( $_POST['phone']            ?? '' );
    $lifecycle_status = sanitize_text_field( $_POST['lifecycle_status'] ?? 'lead' );
    $owner_id         = intval(              $_POST['owner_id']         ?? 0 );
    $company_id       = intval(              $_POST['company_id']       ?? 0 );
    $tags             = sanitize_text_field( $_POST['tags']             ?? '' );
    $custom_raw       = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $contact_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid contact.' ] );
    }
    if ( $first_name === '' ) {
        wp_send_json_error( [ 'message' => 'First name is required.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
         WHERE id = %d AND business_id = %d",
        $contact_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Contact not found.' ] );
    }

    $allowed_statuses = [ 'lead', 'prospect', 'customer', 'churned' ];
    if ( ! in_array( $lifecycle_status, $allowed_statuses, true ) ) {
        $lifecycle_status = 'lead';
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $wpdb->query( 'START TRANSACTION' );

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_contacts',
        [
            'owner_id'          => $owner_id,
            'company_id'        => $company_id,
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $email,
            'phone'             => $phone,
            'lifecycle_status'  => $lifecycle_status,
            'tags'              => $tags,
            'custom_properties' => $custom_props,
        ],
        [ 'id' => $contact_id, 'business_id' => $business_id ],
        [ '%d','%d','%s','%s','%s','%s','%s','%s','%s' ],
        [ '%d','%d' ]
    );

    if ( $updated === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to update contact. Please try again.' ] );
    }

    crm_log_activity(
        $business_id,
        $business_id,
        'contact',
        $contact_id,
        'status_changed',
        'Contact updated: ' . trim( $first_name . ' ' . $last_name )
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Contact updated successfully.' ] );
}

function bntm_ajax_crm_delete_contact() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $contact_id  = intval( $_POST['contact_id'] ?? 0 );

    if ( $contact_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid contact.' ] );
    }

    $open_deals = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE contact_id = %d AND business_id = %d AND status = 'open'",
        $contact_id, $business_id
    ) );

    if ( $open_deals > 0 ) {
        wp_send_json_error( [
            'message' => 'Cannot delete contact with ' . $open_deals . ' open deal(s). Close or reassign deals first.',
        ] );
    }

    $wpdb->query( 'START TRANSACTION' );

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_contacts',
        [ 'status' => 'deleted' ],
        [ 'id' => $contact_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to delete contact. Please try again.' ] );
    }

    $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [ 'status' => 'deleted' ],
        [ 'linked_type' => 'contact', 'linked_id' => $contact_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%s', '%d', '%d' ]
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Contact deleted successfully.' ] );
}

// =============================================================================
// AJAX HANDLERS — COMPANIES
// =============================================================================

function bntm_ajax_crm_get_companies() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $search      = sanitize_text_field( $_POST['search']     ?? '' );
    $owner_id    = intval(              $_POST['owner_id']   ?? 0 );
    $company_id  = intval(              $_POST['company_id'] ?? 0 );
    $detail      = sanitize_text_field( $_POST['detail']     ?? '' );
    $page        = max( 1, intval( $_POST['page']     ?? 1 ) );
    $per_page    = min( intval( $_POST['per_page']    ?? 20 ), 999 );
    $offset      = ( $page - 1 ) * $per_page;

    if ( $detail === '1' && $company_id > 0 ) {
        $detail_row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}crm_companies
             WHERE id = %d AND business_id = %d",
            $company_id, $business_id
        ) );

        $contacts = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, first_name, last_name, email, lifecycle_status
             FROM {$wpdb->prefix}crm_contacts
             WHERE company_id = %d AND business_id = %d AND status = 'active'
             ORDER BY first_name ASC",
            $company_id, $business_id
        ) );

        $deals = $wpdb->get_results( $wpdb->prepare(
            "SELECT d.*, ps.name AS stage_name
             FROM {$wpdb->prefix}crm_deals d
             LEFT JOIN {$wpdb->prefix}crm_pipeline_stages ps ON ps.id = d.stage_id
             WHERE d.company_id = %d AND d.business_id = %d
             ORDER BY d.created_at DESC",
            $company_id, $business_id
        ) );

        wp_send_json_success( [
            'detail'   => $detail_row,
            'contacts' => $contacts,
            'deals'    => $deals,
        ] );
    }

    $where   = [ $wpdb->prepare( 'co.business_id = %d', $business_id ) ];
    $where[] = "co.status = 'active'";

    if ( $search !== '' ) {
        $like    = '%' . $wpdb->esc_like( $search ) . '%';
        $where[] = $wpdb->prepare(
            "(co.name LIKE %s OR co.industry LIKE %s OR co.website LIKE %s)",
            $like, $like, $like
        );
    }
    if ( $owner_id > 0 ) {
        $where[] = $wpdb->prepare( 'co.owner_id = %d', $owner_id );
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    $total = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_companies co {$where_sql}"
    );

    $companies = $wpdb->get_results( $wpdb->prepare(
        "SELECT co.*,
                u.display_name AS owner_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts c
                 WHERE c.company_id = co.id AND c.status = 'active') AS contact_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals d
                 WHERE d.company_id = co.id) AS deal_count
         FROM {$wpdb->prefix}crm_companies co
         LEFT JOIN {$wpdb->users} u ON u.ID = co.owner_id
         {$where_sql}
         ORDER BY co.created_at DESC
         LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );

    wp_send_json_success( [
        'companies'   => $companies,
        'total'       => $total,
        'total_pages' => $per_page > 0 ? ceil( $total / $per_page ) : 1,
    ] );
}

function bntm_ajax_crm_create_company() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();

    $name       = sanitize_text_field( $_POST['name']     ?? '' );
    $industry   = sanitize_text_field( $_POST['industry'] ?? '' );
    $phone      = sanitize_text_field( $_POST['phone']    ?? '' );
    $website    = esc_url_raw(         $_POST['website']  ?? '' );
    $owner_id   = intval(              $_POST['owner_id'] ?? 0 );
    $tags       = sanitize_text_field( $_POST['tags']     ?? '' );
    $custom_raw = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Company name is required.' ] );
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $wpdb->query( 'START TRANSACTION' );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_companies',
        [
            'rand_id'           => bntm_rand_id(),
            'business_id'       => $business_id,
            'owner_id'          => $owner_id,
            'name'              => $name,
            'industry'          => $industry,
            'phone'             => $phone,
            'website'           => $website,
            'tags'              => $tags,
            'custom_properties' => $custom_props,
            'status'            => 'active',
        ],
        [ '%s','%d','%d','%s','%s','%s','%s','%s','%s','%s' ]
    );

    if ( ! $inserted ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to create company. Please try again.' ] );
    }

    $company_id = $wpdb->insert_id;

    crm_log_activity(
        $business_id,
        $business_id,
        'company',
        $company_id,
        'note',
        'Company created: ' . $name
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [
        'message'    => 'Company created successfully.',
        'company_id' => $company_id,
    ] );
}

function bntm_ajax_crm_update_company() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();

    $company_id = intval(              $_POST['company_id'] ?? 0 );
    $name       = sanitize_text_field( $_POST['name']       ?? '' );
    $industry   = sanitize_text_field( $_POST['industry']   ?? '' );
    $phone      = sanitize_text_field( $_POST['phone']      ?? '' );
    $website    = esc_url_raw(         $_POST['website']    ?? '' );
    $owner_id   = intval(              $_POST['owner_id']   ?? 0 );
    $tags       = sanitize_text_field( $_POST['tags']       ?? '' );
    $custom_raw = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $company_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid company.' ] );
    }
    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Company name is required.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_companies
         WHERE id = %d AND business_id = %d",
        $company_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Company not found.' ] );
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $wpdb->query( 'START TRANSACTION' );

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_companies',
        [
            'owner_id'          => $owner_id,
            'name'              => $name,
            'industry'          => $industry,
            'phone'             => $phone,
            'website'           => $website,
            'tags'              => $tags,
            'custom_properties' => $custom_props,
        ],
        [ 'id' => $company_id, 'business_id' => $business_id ],
        [ '%d','%s','%s','%s','%s','%s','%s' ],
        [ '%d','%d' ]
    );

    if ( $updated === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to update company. Please try again.' ] );
    }

    crm_log_activity(
        $business_id,
        $business_id,
        'company',
        $company_id,
        'status_changed',
        'Company updated: ' . $name
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Company updated successfully.' ] );
}

function bntm_ajax_crm_delete_company() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $company_id  = intval( $_POST['company_id'] ?? 0 );

    if ( $company_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid company.' ] );
    }

    $open_deals = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE company_id = %d AND business_id = %d AND status = 'open'",
        $company_id, $business_id
    ) );

    if ( $open_deals > 0 ) {
        wp_send_json_error( [
            'message' => 'Cannot delete company with ' . $open_deals . ' open deal(s). Close or reassign deals first.',
        ] );
    }

    $wpdb->query( 'START TRANSACTION' );

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_companies',
        [ 'status' => 'deleted' ],
        [ 'id' => $company_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to delete company. Please try again.' ] );
    }

    $wpdb->update(
        $wpdb->prefix . 'crm_contacts',
        [ 'company_id' => 0 ],
        [ 'company_id' => $company_id, 'business_id' => $business_id ],
        [ '%d' ],
        [ '%d', '%d' ]
    );

    $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [ 'status' => 'deleted' ],
        [ 'linked_type' => 'company', 'linked_id' => $company_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%s', '%d', '%d' ]
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Company deleted successfully.' ] );
}

// =============================================================================
// AJAX HANDLERS — DEALS
// =============================================================================

function bntm_ajax_crm_get_deals() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $search      = sanitize_text_field( $_POST['search']   ?? '' );
    $stage_id    = intval(              $_POST['stage_id'] ?? 0 );
    $owner_id    = intval(              $_POST['owner_id'] ?? 0 );
    $deal_id     = intval(              $_POST['deal_id']  ?? 0 );
    $status      = sanitize_text_field( $_POST['status']   ?? '' );
    $page        = max( 1, intval( $_POST['page']          ?? 1 ) );
    $per_page    = min( intval( $_POST['per_page']         ?? 20 ), 500 );
    $offset      = ( $page - 1 ) * $per_page;

    $where   = [ $wpdb->prepare( 'd.business_id = %d', $business_id ) ];

    if ( $deal_id > 0 ) {
        $where[] = $wpdb->prepare( 'd.id = %d', $deal_id );
    }
    if ( $search !== '' ) {
        $like    = '%' . $wpdb->esc_like( $search ) . '%';
        $where[] = $wpdb->prepare( 'd.name LIKE %s', $like );
    }
    if ( $stage_id > 0 ) {
        $where[] = $wpdb->prepare( 'd.stage_id = %d', $stage_id );
    }
    if ( $owner_id > 0 ) {
        $where[] = $wpdb->prepare( 'd.owner_id = %d', $owner_id );
    }
    if ( $status !== '' ) {
        $allowed_statuses = [ 'open', 'closed' ];
        if ( in_array( $status, $allowed_statuses, true ) ) {
            $where[] = $wpdb->prepare( 'd.status = %s', $status );
        }
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    $total = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals d {$where_sql}"
    );

    $deals = $wpdb->get_results( $wpdb->prepare(
        "SELECT d.*,
                ps.name            AS stage_name,
                ps.color           AS stage_color,
                u.display_name     AS owner_name,
                CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                co.name            AS company_name
         FROM {$wpdb->prefix}crm_deals d
         LEFT JOIN {$wpdb->prefix}crm_pipeline_stages ps ON ps.id  = d.stage_id
         LEFT JOIN {$wpdb->users}                     u  ON u.ID   = d.owner_id
         LEFT JOIN {$wpdb->prefix}crm_contacts        c  ON c.id   = d.contact_id
         LEFT JOIN {$wpdb->prefix}crm_companies       co ON co.id  = d.company_id
         {$where_sql}
         ORDER BY d.created_at DESC
         LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );

    wp_send_json_success( [
        'deals'       => $deals,
        'total'       => $total,
        'total_pages' => $per_page > 0 ? ceil( $total / $per_page ) : 1,
    ] );
}

function bntm_ajax_crm_create_deal() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id        = get_current_user_id();
    $name               = sanitize_text_field( $_POST['name']                ?? '' );
    $amount             = floatval(            $_POST['amount']               ?? 0 );
    $expected_close_date = sanitize_text_field( $_POST['expected_close_date'] ?? '' );
    $stage_id           = intval(              $_POST['stage_id']             ?? 0 );
    $owner_id           = intval(              $_POST['owner_id']             ?? 0 );
    $contact_id         = intval(              $_POST['contact_id']           ?? 0 );
    $company_id         = intval(              $_POST['company_id']           ?? 0 );
    $custom_raw         = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Deal name is required.' ] );
    }
    if ( $stage_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'A pipeline stage is required.' ] );
    }
    if ( $contact_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'A contact is required.' ] );
    }
    if ( $amount < 0 ) {
        wp_send_json_error( [ 'message' => 'Amount cannot be negative.' ] );
    }

    $stage_exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE id = %d AND business_id = %d AND status = 'active'",
        $stage_id, $business_id
    ) );
    if ( ! $stage_exists ) {
        wp_send_json_error( [ 'message' => 'Selected pipeline stage does not exist.' ] );
    }

    $contact_exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
         WHERE id = %d AND business_id = %d AND status = 'active'",
        $contact_id, $business_id
    ) );
    if ( ! $contact_exists ) {
        wp_send_json_error( [ 'message' => 'Selected contact does not exist.' ] );
    }

    $close_date = '';
    if ( $expected_close_date !== '' ) {
        $parsed = date_create( $expected_close_date );
        $close_date = $parsed ? date_format( $parsed, 'Y-m-d' ) : '';
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $wpdb->query( 'START TRANSACTION' );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_deals',
        [
            'rand_id'              => bntm_rand_id(),
            'business_id'          => $business_id,
            'owner_id'             => $owner_id,
            'contact_id'           => $contact_id,
            'company_id'           => $company_id,
            'pipeline_id'          => 1,
            'stage_id'             => $stage_id,
            'name'                 => $name,
            'amount'               => $amount,
            'expected_close_date'  => $close_date !== '' ? $close_date : null,
            'custom_properties'    => $custom_props,
            'status'               => 'open',
        ],
        [ '%s','%d','%d','%d','%d','%d','%d','%s','%f','%s','%s','%s' ]
    );

    if ( ! $inserted ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to create deal. Please try again.' ] );
    }

    $deal_id = $wpdb->insert_id;

    $stage_name = $wpdb->get_var( $wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}crm_pipeline_stages WHERE id = %d",
        $stage_id
    ) );

    crm_log_activity(
        $business_id,
        $business_id,
        'deal',
        $deal_id,
        'note',
        'Deal created: ' . $name . ' — Stage: ' . $stage_name
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [
        'message' => 'Deal created successfully.',
        'deal_id' => $deal_id,
    ] );
}

function bntm_ajax_crm_update_deal() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id         = get_current_user_id();
    $deal_id             = intval(              $_POST['deal_id']             ?? 0 );
    $name                = sanitize_text_field( $_POST['name']                ?? '' );
    $amount              = floatval(            $_POST['amount']               ?? 0 );
    $expected_close_date = sanitize_text_field( $_POST['expected_close_date'] ?? '' );
    $stage_id            = intval(              $_POST['stage_id']             ?? 0 );
    $owner_id            = intval(              $_POST['owner_id']             ?? 0 );
    $contact_id          = intval(              $_POST['contact_id']           ?? 0 );
    $company_id          = intval(              $_POST['company_id']           ?? 0 );
    $custom_raw          = sanitize_textarea_field( $_POST['custom_properties'] ?? '' );

    if ( $deal_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid deal.' ] );
    }
    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Deal name is required.' ] );
    }
    if ( $stage_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'A pipeline stage is required.' ] );
    }
    if ( $contact_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'A contact is required.' ] );
    }

    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, stage_id, name FROM {$wpdb->prefix}crm_deals
         WHERE id = %d AND business_id = %d",
        $deal_id, $business_id
    ) );

    if ( ! $existing ) {
        wp_send_json_error( [ 'message' => 'Deal not found.' ] );
    }

    $stage_exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE id = %d AND business_id = %d AND status = 'active'",
        $stage_id, $business_id
    ) );
    if ( ! $stage_exists ) {
        wp_send_json_error( [ 'message' => 'Selected pipeline stage does not exist.' ] );
    }

    $close_date = null;
    if ( $expected_close_date !== '' ) {
        $parsed     = date_create( $expected_close_date );
        $close_date = $parsed ? date_format( $parsed, 'Y-m-d' ) : null;
    }

    $custom_props = '{}';
    if ( $custom_raw !== '' ) {
        $decoded = json_decode( $custom_raw, true );
        if ( is_array( $decoded ) ) {
            $sanitized = [];
            foreach ( $decoded as $k => $v ) {
                $sanitized[ sanitize_key( $k ) ] = sanitize_text_field( $v );
            }
            $custom_props = wp_json_encode( $sanitized );
        }
    }

    $stage_changed = ( (int) $existing->stage_id !== $stage_id );

    $wpdb->query( 'START TRANSACTION' );

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_deals',
        [
            'owner_id'            => $owner_id,
            'contact_id'          => $contact_id,
            'company_id'          => $company_id,
            'stage_id'            => $stage_id,
            'name'                => $name,
            'amount'              => $amount,
            'expected_close_date' => $close_date,
            'custom_properties'   => $custom_props,
        ],
        [ 'id' => $deal_id, 'business_id' => $business_id ],
        [ '%d','%d','%d','%d','%s','%f','%s','%s' ],
        [ '%d','%d' ]
    );

    if ( $updated === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to update deal. Please try again.' ] );
    }

    $activity_body = 'Deal updated: ' . $name;
    $activity_type = 'status_changed';

    if ( $stage_changed ) {
        $new_stage_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}crm_pipeline_stages WHERE id = %d",
            $stage_id
        ) );
        $old_stage_name = $wpdb->get_var( $wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}crm_pipeline_stages WHERE id = %d",
            $existing->stage_id
        ) );
        $activity_body  = 'Deal moved from "' . $old_stage_name . '" to "' . $new_stage_name . '"';
        $activity_type  = 'deal_moved';
    }

    crm_log_activity(
        $business_id,
        $business_id,
        'deal',
        $deal_id,
        $activity_type,
        $activity_body
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Deal updated successfully.' ] );
}

function bntm_ajax_crm_delete_deal() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $deal_id     = intval( $_POST['deal_id'] ?? 0 );

    if ( $deal_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid deal.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE id = %d AND business_id = %d",
        $deal_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Deal not found.' ] );
    }

    $wpdb->query( 'START TRANSACTION' );

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_deals',
        [ 'status' => 'deleted' ],
        [ 'id' => $deal_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to delete deal. Please try again.' ] );
    }

    $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [ 'status' => 'deleted' ],
        [ 'linked_type' => 'deal', 'linked_id' => $deal_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%s', '%d', '%d' ]
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Deal deleted successfully.' ] );
}

function bntm_ajax_crm_move_deal_stage() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $deal_id     = intval( $_POST['deal_id']  ?? 0 );
    $stage_id    = intval( $_POST['stage_id'] ?? 0 );

    if ( $deal_id <= 0 || $stage_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid deal or stage.' ] );
    }

    $deal = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, name, stage_id FROM {$wpdb->prefix}crm_deals
         WHERE id = %d AND business_id = %d",
        $deal_id, $business_id
    ) );

    if ( ! $deal ) {
        wp_send_json_error( [ 'message' => 'Deal not found.' ] );
    }

    $stage = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, name FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE id = %d AND business_id = %d AND status = 'active'",
        $stage_id, $business_id
    ) );

    if ( ! $stage ) {
        wp_send_json_error( [ 'message' => 'Stage not found.' ] );
    }

    if ( (int) $deal->stage_id === $stage_id ) {
        wp_send_json_success( [ 'message' => 'Deal already in this stage.' ] );
    }

    $old_stage_name = $wpdb->get_var( $wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}crm_pipeline_stages WHERE id = %d",
        $deal->stage_id
    ) );

    $closed_keywords = [ 'won', 'lost' ];
    $stage_name_lower = strtolower( $stage->name );
    $is_closing_stage = false;
    foreach ( $closed_keywords as $kw ) {
        if ( strpos( $stage_name_lower, $kw ) !== false ) {
            $is_closing_stage = true;
            break;
        }
    }
    $new_deal_status = $is_closing_stage ? 'closed' : 'open';

    $wpdb->query( 'START TRANSACTION' );

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_deals',
        [
            'stage_id' => $stage_id,
            'status'   => $new_deal_status,
        ],
        [ 'id' => $deal_id, 'business_id' => $business_id ],
        [ '%d', '%s' ],
        [ '%d', '%d' ]
    );

    if ( $updated === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to move deal. Please try again.' ] );
    }

    crm_log_activity(
        $business_id,
        $business_id,
        'deal',
        $deal_id,
        'deal_moved',
        'Deal "' . $deal->name . '" moved from "' . $old_stage_name . '" to "' . $stage->name . '"'
    );

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [
        'message'    => 'Deal moved to ' . $stage->name . '.',
        'new_status' => $new_deal_status,
    ] );
}

// =============================================================================
// AJAX HANDLERS — TASKS
// =============================================================================

function bntm_ajax_crm_get_tasks() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id  = get_current_user_id();
    $assignee_id  = intval(              $_POST['assignee_id']  ?? 0 );
    $linked_type  = sanitize_text_field( $_POST['linked_type']  ?? '' );
    $linked_id    = intval(              $_POST['linked_id']     ?? 0 );
    $status       = sanitize_text_field( $_POST['status']        ?? '' );
    $today        = current_time( 'Y-m-d' );

    $where   = [ $wpdb->prepare( 't.business_id = %d', $business_id ) ];
    $where[] = "t.status != 'deleted'";

    if ( $assignee_id > 0 ) {
        $where[] = $wpdb->prepare( 't.assignee_id = %d', $assignee_id );
    }
    if ( $linked_type !== '' ) {
        $allowed_types = [ 'contact', 'company', 'deal' ];
        if ( in_array( $linked_type, $allowed_types, true ) ) {
            $where[] = $wpdb->prepare( 't.linked_type = %s', $linked_type );
        }
    }
    if ( $linked_id > 0 ) {
        $where[] = $wpdb->prepare( 't.linked_id = %d', $linked_id );
    }
    if ( $status !== '' ) {
        $allowed_statuses = [ 'pending', 'complete' ];
        if ( in_array( $status, $allowed_statuses, true ) ) {
            $where[] = $wpdb->prepare( 't.status = %s', $status );
        }
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    $tasks = $wpdb->get_results(
        "SELECT t.*,
                u.display_name AS assignee_name,
                DATEDIFF(t.due_date, '{$today}') AS days_until,
                DATEDIFF('{$today}', t.due_date) AS days_overdue
         FROM {$wpdb->prefix}crm_tasks t
         LEFT JOIN {$wpdb->users} u ON u.ID = t.assignee_id
         {$where_sql}
         ORDER BY t.due_date ASC"
    );

    $overdue  = [];
    $upcoming = [];

    foreach ( $tasks as $task ) {
        if ( $task->status === 'pending' && $task->due_date < $today ) {
            $overdue[] = $task;
        } else {
            $upcoming[] = $task;
        }
    }

    wp_send_json_success( [
        'tasks'    => $tasks,
        'overdue'  => $overdue,
        'upcoming' => $upcoming,
        'total'    => count( $tasks ),
    ] );
}

function bntm_ajax_crm_create_task() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id   = get_current_user_id();
    $title         = sanitize_text_field( $_POST['title']         ?? '' );
    $assignee_id   = intval(              $_POST['assignee_id']   ?? 0 );
    $due_date      = sanitize_text_field( $_POST['due_date']      ?? '' );
    $reminder_days = intval(              $_POST['reminder_days'] ?? 0 );
    $linked_type   = sanitize_text_field( $_POST['linked_type']   ?? '' );
    $linked_id     = intval(              $_POST['linked_id']      ?? 0 );

    if ( $title === '' ) {
        wp_send_json_error( [ 'message' => 'Task title is required.' ] );
    }

    $allowed_types = [ '', 'contact', 'company', 'deal' ];
    if ( ! in_array( $linked_type, $allowed_types, true ) ) {
        $linked_type = '';
    }

    $parsed_date = '';
    if ( $due_date !== '' ) {
        $parsed      = date_create( $due_date );
        $parsed_date = $parsed ? date_format( $parsed, 'Y-m-d' ) : '';
    }

    if ( $reminder_days < 0 ) {
        $reminder_days = 0;
    }

    $wpdb->query( 'START TRANSACTION' );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_tasks',
        [
            'rand_id'       => bntm_rand_id(),
            'business_id'   => $business_id,
            'assignee_id'   => $assignee_id,
            'linked_type'   => $linked_type,
            'linked_id'     => $linked_id,
            'title'         => $title,
            'due_date'      => $parsed_date !== '' ? $parsed_date : null,
            'reminder_days' => $reminder_days,
            'status'        => 'pending',
        ],
        [ '%s','%d','%d','%s','%d','%s','%s','%d','%s' ]
    );

    if ( ! $inserted ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to create task. Please try again.' ] );
    }

    $task_id = $wpdb->insert_id;

    if ( $linked_type !== '' && $linked_id > 0 ) {
        crm_log_activity(
            $business_id,
            $business_id,
            $linked_type,
            $linked_id,
            'task_created',
            'Task created: ' . $title . ( $parsed_date ? ' — Due: ' . $parsed_date : '' )
        );
    }

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [
        'message' => 'Task created successfully.',
        'task_id' => $task_id,
    ] );
}

function bntm_ajax_crm_update_task() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id   = get_current_user_id();
    $task_id       = intval(              $_POST['task_id']       ?? 0 );
    $title         = sanitize_text_field( $_POST['title']         ?? '' );
    $assignee_id   = intval(              $_POST['assignee_id']   ?? 0 );
    $due_date      = sanitize_text_field( $_POST['due_date']      ?? '' );
    $reminder_days = intval(              $_POST['reminder_days'] ?? 0 );
    $linked_type   = sanitize_text_field( $_POST['linked_type']   ?? '' );
    $linked_id     = intval(              $_POST['linked_id']      ?? 0 );

    if ( $task_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid task.' ] );
    }
    if ( $title === '' ) {
        wp_send_json_error( [ 'message' => 'Task title is required.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
         WHERE id = %d AND business_id = %d",
        $task_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Task not found.' ] );
    }

    $allowed_types = [ '', 'contact', 'company', 'deal' ];
    if ( ! in_array( $linked_type, $allowed_types, true ) ) {
        $linked_type = '';
    }

    $parsed_date = null;
    if ( $due_date !== '' ) {
        $parsed      = date_create( $due_date );
        $parsed_date = $parsed ? date_format( $parsed, 'Y-m-d' ) : null;
    }

    if ( $reminder_days < 0 ) {
        $reminder_days = 0;
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [
            'assignee_id'   => $assignee_id,
            'linked_type'   => $linked_type,
            'linked_id'     => $linked_id,
            'title'         => $title,
            'due_date'      => $parsed_date,
            'reminder_days' => $reminder_days,
        ],
        [ 'id' => $task_id, 'business_id' => $business_id ],
        [ '%d','%s','%d','%s','%s','%d' ],
        [ '%d','%d' ]
    );

    if ( $updated === false ) {
        wp_send_json_error( [ 'message' => 'Failed to update task. Please try again.' ] );
    }

    wp_send_json_success( [ 'message' => 'Task updated successfully.' ] );
}

function bntm_ajax_crm_delete_task() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $task_id     = intval( $_POST['task_id'] ?? 0 );

    if ( $task_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid task.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
         WHERE id = %d AND business_id = %d",
        $task_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Task not found.' ] );
    }

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [ 'status' => 'deleted' ],
        [ 'id' => $task_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        wp_send_json_error( [ 'message' => 'Failed to delete task. Please try again.' ] );
    }

    wp_send_json_success( [ 'message' => 'Task deleted successfully.' ] );
}

function bntm_ajax_crm_complete_task() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $task_id     = intval( $_POST['task_id'] ?? 0 );

    if ( $task_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid task.' ] );
    }

    $task = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_tasks
         WHERE id = %d AND business_id = %d",
        $task_id, $business_id
    ) );

    if ( ! $task ) {
        wp_send_json_error( [ 'message' => 'Task not found.' ] );
    }

    if ( $task->status === 'complete' ) {
        wp_send_json_success( [ 'message' => 'Task already marked complete.' ] );
    }

    $wpdb->query( 'START TRANSACTION' );

    $updated = $wpdb->update(
        $wpdb->prefix . 'crm_tasks',
        [ 'status' => 'complete' ],
        [ 'id' => $task_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $updated === false ) {
        $wpdb->query( 'ROLLBACK' );
        wp_send_json_error( [ 'message' => 'Failed to complete task. Please try again.' ] );
    }

    if ( $task->linked_type !== '' && $task->linked_id > 0 ) {
        crm_log_activity(
            $business_id,
            $business_id,
            $task->linked_type,
            (int) $task->linked_id,
            'task_completed',
            'Task completed: ' . $task->title
        );
    }

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Task marked as complete.' ] );
}

// =============================================================================
// AJAX HANDLERS — SETTINGS: PIPELINE STAGES
// =============================================================================

function bntm_ajax_crm_get_stages() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();

    $stages = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'
         ORDER BY sort_order ASC",
        $business_id
    ) );

    wp_send_json_success( [ 'stages' => $stages ] );
}

function bntm_ajax_crm_save_stage() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $stage_id    = intval(              $_POST['stage_id'] ?? 0 );
    $name        = sanitize_text_field( $_POST['name']     ?? '' );
    $color       = sanitize_text_field( $_POST['color']    ?? '#6366f1' );

    if ( $name === '' ) {
        wp_send_json_error( [ 'message' => 'Stage name is required.' ] );
    }

    if ( ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $color ) ) {
        $color = '#6366f1';
    }

    if ( $stage_id > 0 ) {
        $exists = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
             WHERE id = %d AND business_id = %d",
            $stage_id, $business_id
        ) );

        if ( ! $exists ) {
            wp_send_json_error( [ 'message' => 'Stage not found.' ] );
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'crm_pipeline_stages',
            [
                'name'  => $name,
                'color' => $color,
            ],
            [ 'id' => $stage_id, 'business_id' => $business_id ],
            [ '%s', '%s' ],
            [ '%d', '%d' ]
        );

        if ( $updated === false ) {
            wp_send_json_error( [ 'message' => 'Failed to update stage. Please try again.' ] );
        }

        wp_send_json_success( [
            'message'  => 'Stage updated successfully.',
            'stage_id' => $stage_id,
        ] );
    }

    $duplicate = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'
           AND name = %s",
        $business_id, $name
    ) );

    if ( $duplicate > 0 ) {
        wp_send_json_error( [ 'message' => 'A stage with this name already exists.' ] );
    }

    $max_order = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(MAX(sort_order), 0)
         FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'",
        $business_id
    ) );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_pipeline_stages',
        [
            'rand_id'     => bntm_rand_id(),
            'business_id' => $business_id,
            'pipeline_id' => 1,
            'name'        => $name,
            'sort_order'  => $max_order + 1,
            'color'       => $color,
            'status'      => 'active',
        ],
        [ '%s', '%d', '%d', '%s', '%d', '%s', '%s' ]
    );

    if ( ! $inserted ) {
        wp_send_json_error( [ 'message' => 'Failed to create stage. Please try again.' ] );
    }

    wp_send_json_success( [
        'message'  => 'Stage created successfully.',
        'stage_id' => $wpdb->insert_id,
    ] );
}

function bntm_ajax_crm_delete_stage() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $stage_id    = intval( $_POST['stage_id'] ?? 0 );

    if ( $stage_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid stage.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE id = %d AND business_id = %d",
        $stage_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Stage not found.' ] );
    }

    $active_stage_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE business_id = %d AND pipeline_id = 1 AND status = 'active'",
        $business_id
    ) );

    if ( $active_stage_count <= 1 ) {
        wp_send_json_error( [ 'message' => 'You must have at least one pipeline stage. Add another stage before deleting this one.' ] );
    }

    $deals_in_stage = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
         WHERE stage_id = %d AND business_id = %d AND status = 'open'",
        $stage_id, $business_id
    ) );

    if ( $deals_in_stage > 0 ) {
        wp_send_json_error( [
            'message' => 'Cannot delete stage with ' . $deals_in_stage . ' open deal(s). Move or close those deals first.',
        ] );
    }

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_pipeline_stages',
        [ 'status' => 'deleted' ],
        [ 'id' => $stage_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        wp_send_json_error( [ 'message' => 'Failed to delete stage. Please try again.' ] );
    }

    wp_send_json_success( [ 'message' => 'Stage deleted successfully.' ] );
}

function bntm_ajax_crm_reorder_stages() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $stage_ids   = sanitize_text_field( $_POST['stage_ids'] ?? '' );

    if ( $stage_ids === '' ) {
        wp_send_json_error( [ 'message' => 'No stage order provided.' ] );
    }

    $ids = array_map( 'intval', explode( ',', $stage_ids ) );
    $ids = array_filter( $ids, function( $id ) { return $id > 0; } );
    $ids = array_values( $ids );

    if ( empty( $ids ) ) {
        wp_send_json_error( [ 'message' => 'Invalid stage order data.' ] );
    }

    $wpdb->query( 'START TRANSACTION' );

    foreach ( $ids as $index => $id ) {
        $updated = $wpdb->update(
            $wpdb->prefix . 'crm_pipeline_stages',
            [ 'sort_order' => $index + 1 ],
            [ 'id' => $id, 'business_id' => $business_id ],
            [ '%d' ],
            [ '%d', '%d' ]
        );

        if ( $updated === false ) {
            $wpdb->query( 'ROLLBACK' );
            wp_send_json_error( [ 'message' => 'Failed to save stage order. Please try again.' ] );
        }
    }

    $wpdb->query( 'COMMIT' );

    wp_send_json_success( [ 'message' => 'Stage order saved.' ] );
}

// =============================================================================
// AJAX HANDLERS — SETTINGS: CUSTOM PROPERTIES
// =============================================================================

function bntm_ajax_crm_get_custom_properties() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $object_type = sanitize_text_field( $_POST['object_type'] ?? '' );

    $where   = [ $wpdb->prepare( 'business_id = %d', $business_id ) ];
    $where[] = "status = 'active'";

    $allowed_types = [ 'contact', 'company', 'deal' ];
    if ( $object_type !== '' && in_array( $object_type, $allowed_types, true ) ) {
        $where[] = $wpdb->prepare( 'object_type = %s', $object_type );
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    $props = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}crm_custom_properties
         {$where_sql}
         ORDER BY object_type ASC, sort_order ASC"
    );

    wp_send_json_success( [ 'properties' => $props ] );
}

function bntm_ajax_crm_save_custom_property() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id   = get_current_user_id();
    $prop_id       = intval(              $_POST['prop_id']       ?? 0 );
    $field_label   = sanitize_text_field( $_POST['field_label']   ?? '' );
    $field_name    = sanitize_key(        $_POST['field_name']     ?? '' );
    $object_type   = sanitize_text_field( $_POST['object_type']   ?? '' );
    $field_type    = sanitize_text_field( $_POST['field_type']    ?? 'text' );
    $field_options = sanitize_textarea_field( $_POST['field_options'] ?? '' );

    if ( $field_label === '' ) {
        wp_send_json_error( [ 'message' => 'Field label is required.' ] );
    }
    if ( $field_name === '' ) {
        wp_send_json_error( [ 'message' => 'Field name is required.' ] );
    }
    if ( ! preg_match( '/^[a-z0-9_]+$/', $field_name ) ) {
        wp_send_json_error( [ 'message' => 'Field name must contain only lowercase letters, numbers, and underscores.' ] );
    }

    $allowed_object_types = [ 'contact', 'company', 'deal' ];
    if ( ! in_array( $object_type, $allowed_object_types, true ) ) {
        wp_send_json_error( [ 'message' => 'Invalid object type.' ] );
    }

    $allowed_field_types = [ 'text', 'number', 'date', 'textarea', 'select' ];
    if ( ! in_array( $field_type, $allowed_field_types, true ) ) {
        $field_type = 'text';
    }

    $sanitized_options = null;
    if ( $field_type === 'select' ) {
        if ( $field_options === '' ) {
            wp_send_json_error( [ 'message' => 'Dropdown options are required for select fields.' ] );
        }
        $options_array = array_filter(
            array_map( 'sanitize_text_field', explode( "\n", $field_options ) ),
            function( $o ) { return $o !== ''; }
        );
        if ( empty( $options_array ) ) {
            wp_send_json_error( [ 'message' => 'Please provide at least one dropdown option.' ] );
        }
        $sanitized_options = wp_json_encode( array_values( $options_array ) );
    }

    if ( $prop_id > 0 ) {
        $exists = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_custom_properties
             WHERE id = %d AND business_id = %d",
            $prop_id, $business_id
        ) );

        if ( ! $exists ) {
            wp_send_json_error( [ 'message' => 'Property not found.' ] );
        }

        $duplicate = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_custom_properties
             WHERE business_id = %d AND object_type = %s
               AND field_name = %s AND status = 'active' AND id != %d",
            $business_id, $object_type, $field_name, $prop_id
        ) );

        if ( $duplicate > 0 ) {
            wp_send_json_error( [ 'message' => 'A property with this field name already exists for this object type.' ] );
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'crm_custom_properties',
            [
                'field_label'   => $field_label,
                'field_name'    => $field_name,
                'object_type'   => $object_type,
                'field_type'    => $field_type,
                'field_options' => $sanitized_options,
            ],
            [ 'id' => $prop_id, 'business_id' => $business_id ],
            [ '%s', '%s', '%s', '%s', '%s' ],
            [ '%d', '%d' ]
        );

        if ( $updated === false ) {
            wp_send_json_error( [ 'message' => 'Failed to update property. Please try again.' ] );
        }

        wp_send_json_success( [
            'message' => 'Property updated successfully.',
            'prop_id' => $prop_id,
        ] );
    }

    $duplicate = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND object_type = %s
           AND field_name = %s AND status = 'active'",
        $business_id, $object_type, $field_name
    ) );

    if ( $duplicate > 0 ) {
        wp_send_json_error( [ 'message' => 'A property with this field name already exists for this object type.' ] );
    }

    $max_order = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(MAX(sort_order), 0)
         FROM {$wpdb->prefix}crm_custom_properties
         WHERE business_id = %d AND object_type = %s AND status = 'active'",
        $business_id, $object_type
    ) );

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'crm_custom_properties',
        [
            'rand_id'       => bntm_rand_id(),
            'business_id'   => $business_id,
            'object_type'   => $object_type,
            'field_name'    => $field_name,
            'field_label'   => $field_label,
            'field_type'    => $field_type,
            'field_options' => $sanitized_options,
            'sort_order'    => $max_order + 1,
            'status'        => 'active',
        ],
        [ '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
    );

    if ( ! $inserted ) {
        wp_send_json_error( [ 'message' => 'Failed to create property. Please try again.' ] );
    }

    wp_send_json_success( [
        'message' => 'Property created successfully.',
        'prop_id' => $wpdb->insert_id,
    ] );
}

function bntm_ajax_crm_delete_custom_property() {
    check_ajax_referer( 'crm_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    global $wpdb;
    $business_id = get_current_user_id();
    $prop_id     = intval( $_POST['prop_id'] ?? 0 );

    if ( $prop_id <= 0 ) {
        wp_send_json_error( [ 'message' => 'Invalid property.' ] );
    }

    $exists = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_custom_properties
         WHERE id = %d AND business_id = %d",
        $prop_id, $business_id
    ) );

    if ( ! $exists ) {
        wp_send_json_error( [ 'message' => 'Property not found.' ] );
    }

    $deleted = $wpdb->update(
        $wpdb->prefix . 'crm_custom_properties',
        [ 'status' => 'deleted' ],
        [ 'id' => $prop_id, 'business_id' => $business_id ],
        [ '%s' ],
        [ '%d', '%d' ]
    );

    if ( $deleted === false ) {
        wp_send_json_error( [ 'message' => 'Failed to delete property. Please try again.' ] );
    }

    wp_send_json_success( [ 'message' => 'Property deleted successfully.' ] );
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

function crm_format_currency( $amount ) {
    $currency = bntm_get_setting( 'crm_currency', 'PHP' );
    $symbols  = [
        'USD' => '$',
        'EUR' => '&euro;',
        'GBP' => '&pound;',
        'PHP' => '&#8369;',
    ];
    $symbol = $symbols[ $currency ] ?? '&#8369;';
    return $symbol . number_format( (float) $amount, 2 );
}

function crm_log_activity(
    $business_id,
    $author_id,
    $linked_type,
    $linked_id,
    $activity_type,
    $body
) {
    global $wpdb;

    $allowed_types = [ 'note', 'task_created', 'task_completed', 'deal_moved', 'status_changed' ];
    if ( ! in_array( $activity_type, $allowed_types, true ) ) {
        $activity_type = 'note';
    }

    $allowed_linked = [ 'contact', 'company', 'deal' ];
    if ( ! in_array( $linked_type, $allowed_linked, true ) ) {
        return false;
    }

    return $wpdb->insert(
        $wpdb->prefix . 'crm_activities',
        [
            'rand_id'       => bntm_rand_id(),
            'business_id'   => intval( $business_id ),
            'author_id'     => intval( $author_id ),
            'linked_type'   => sanitize_text_field( $linked_type ),
            'linked_id'     => intval( $linked_id ),
            'activity_type' => $activity_type,
            'body'          => sanitize_textarea_field( $body ),
            'status'        => 'active',
        ],
        [ '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s' ]
    );
}

function crm_activity_label( $type ) {
    $labels = [
        'note'           => 'Note added',
        'task_created'   => 'Task created',
        'task_completed' => 'Task completed',
        'deal_moved'     => 'Deal stage changed',
        'status_changed' => 'Record updated',
    ];
    return $labels[ $type ] ?? ucfirst( str_replace( '_', ' ', $type ) );
}

function crm_time_ago( $datetime ) {
    $now  = current_time( 'timestamp' );
    $then = strtotime( $datetime );
    $diff = max( 0, $now - $then );

    if ( $diff < 60 )         return 'just now';
    if ( $diff < 3600 )       return floor( $diff / 60 ) . 'm ago';
    if ( $diff < 86400 )      return floor( $diff / 3600 ) . 'h ago';
    if ( $diff < 604800 )     return floor( $diff / 86400 ) . 'd ago';
    if ( $diff < 2592000 )    return floor( $diff / 604800 ) . 'w ago';
    if ( $diff < 31536000 )   return floor( $diff / 2592000 ) . 'mo ago';
    return floor( $diff / 31536000 ) . 'y ago';
}

function crm_get_linked_record_label( $linked_type, $linked_id, $business_id ) {
    global $wpdb;

    if ( ! $linked_type || ! $linked_id ) {
        return '';
    }

    switch ( $linked_type ) {
        case 'contact':
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT first_name, last_name
                 FROM {$wpdb->prefix}crm_contacts
                 WHERE id = %d AND business_id = %d",
                $linked_id, $business_id
            ) );
            return $row ? trim( $row->first_name . ' ' . $row->last_name ) : '—';

        case 'company':
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}crm_companies
                 WHERE id = %d AND business_id = %d",
                $linked_id, $business_id
            ) );
            return $row ? $row->name : '—';

        case 'deal':
            $row = $wpdb->get_row( $wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}crm_deals
                 WHERE id = %d AND business_id = %d",
                $linked_id, $business_id
            ) );
            return $row ? $row->name : '—';

        default:
            return '—';
    }
}

function crm_get_stats( $business_id ) {
    global $wpdb;
    $today = current_time( 'Y-m-d' );

    return [
        'total_contacts'  => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_contacts
             WHERE business_id = %d AND status = 'active'",
            $business_id
        ) ),
        'total_companies' => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_companies
             WHERE business_id = %d AND status = 'active'",
            $business_id
        ) ),
        'open_deals'      => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_deals
             WHERE business_id = %d AND status = 'open'",
            $business_id
        ) ),
        'pipeline_value'  => (float) $wpdb->get_var( $wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}crm_deals
             WHERE business_id = %d AND status = 'open'",
            $business_id
        ) ),
        'overdue_tasks'   => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
             WHERE business_id = %d AND status = 'pending' AND due_date < %s",
            $business_id, $today
        ) ),
        'pending_tasks'   => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}crm_tasks
             WHERE business_id = %d AND status = 'pending' AND due_date >= %s",
            $business_id, $today
        ) ),
    ];
}

function crm_seed_default_pipeline_stages() {
    global $wpdb;

    $existing = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}crm_pipeline_stages
         WHERE pipeline_id = 1"
    );

    if ( $existing > 0 ) {
        return;
    }

    $default_stages = [
        [ 'name' => 'Lead In',        'color' => '#6366f1', 'sort_order' => 1 ],
        [ 'name' => 'Qualified',       'color' => '#3b82f6', 'sort_order' => 2 ],
        [ 'name' => 'Proposal Sent',   'color' => '#f59e0b', 'sort_order' => 3 ],
        [ 'name' => 'Negotiation',     'color' => '#ec4899', 'sort_order' => 4 ],
        [ 'name' => 'Closed Won',      'color' => '#10b981', 'sort_order' => 5 ],
        [ 'name' => 'Closed Lost',     'color' => '#ef4444', 'sort_order' => 6 ],
    ];

    foreach ( $default_stages as $stage ) {
        $wpdb->insert(
            $wpdb->prefix . 'crm_pipeline_stages',
            [
                'rand_id'     => bntm_rand_id(),
                'business_id' => 0,
                'pipeline_id' => 1,
                'name'        => $stage['name'],
                'sort_order'  => $stage['sort_order'],
                'color'       => $stage['color'],
                'status'      => 'active',
            ],
            [ '%s', '%d', '%d', '%s', '%d', '%s', '%s' ]
        );
    }
}

function crm_check_dependencies() {
    return true;
}

// =============================================================================
// SHORTCODE REGISTRATION BOOTSTRAP
// =============================================================================

function bntm_crm_register_shortcodes() {
    $shortcodes = bntm_crm_get_shortcodes();
    foreach ( $shortcodes as $tag => $callback ) {
        if ( function_exists( $callback ) ) {
            add_shortcode( $tag, $callback );
        }
    }
}
add_action( 'init', 'bntm_crm_register_shortcodes' );

// =============================================================================
// PAGE AUTO-CREATION ON ACTIVATION
// =============================================================================

function bntm_crm_create_pages() {
    $pages = bntm_crm_get_pages();

    foreach ( $pages as $title => $shortcode ) {
        $slug = sanitize_title( $title );

        $existing = get_page_by_path( $slug );
        if ( $existing ) {
            continue;
        }

        wp_insert_post( [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $shortcode,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id(),
        ] );
    }
}
register_activation_hook( BNTM_CRM_PATH . 'crm.php', 'bntm_crm_create_pages' );
register_activation_hook( BNTM_CRM_PATH . 'crm.php', 'bntm_crm_create_tables' );
