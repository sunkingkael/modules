<?php
/**
 * Module Name: CRM
 * Module Slug: crm
 * Description: Allows businesses to manage their customer relationships. Business owners can track contacts, manage leads through a pipeline, log interactions, and follow up on deals. All activity is managed from an internal dashboard.
 * Version: 1.0.0
 * Author: BNTM
 * Icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Module constants
define('BNTM_CRM_PATH', dirname(__FILE__) . '/');
define('BNTM_CRM_URL', plugin_dir_url(__FILE__));

// ============================================================
// MODULE CONFIGURATION FUNCTIONS
// ============================================================

function bntm_crm_get_pages() {
    return [
        'CRM Dashboard'  => '[crm_dashboard]',
        'Contact Form'   => '[crm_contact_form]',
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
            first_name VARCHAR(100) NOT NULL DEFAULT '',
            last_name VARCHAR(100) NOT NULL DEFAULT '',
            email VARCHAR(255) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            company VARCHAR(255) NOT NULL DEFAULT '',
            notes TEXT,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_status (status)
        ) {$charset};",

        'crm_leads' => "CREATE TABLE {$prefix}crm_leads (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            contact_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(255) NOT NULL DEFAULT '',
            value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            pipeline_type VARCHAR(50) NOT NULL DEFAULT 'subscription',
            product_type VARCHAR(50) NOT NULL DEFAULT '',
            service_type VARCHAR(100) NOT NULL DEFAULT '',
            lead_source VARCHAR(100) NOT NULL DEFAULT '',
            sales_owner BIGINT UNSIGNED NOT NULL DEFAULT 0,
            motm_uploaded TINYINT(1) NOT NULL DEFAULT 0,
            ended_reason VARCHAR(50) NOT NULL DEFAULT '',
            stage VARCHAR(100) NOT NULL DEFAULT 'new',
            priority VARCHAR(20) NOT NULL DEFAULT 'medium',
            expected_close DATE NULL,
            notes TEXT,
            status VARCHAR(50) NOT NULL DEFAULT 'open',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_contact (contact_id),
            INDEX idx_stage (stage),
            INDEX idx_pipeline_type (pipeline_type),
            INDEX idx_product_type (product_type),
            INDEX idx_service_type (service_type),
            INDEX idx_lead_source (lead_source),
            INDEX idx_sales_owner (sales_owner)
        ) {$charset};",

        'crm_interactions' => "CREATE TABLE {$prefix}crm_interactions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            rand_id VARCHAR(20) UNIQUE NOT NULL,
            business_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            contact_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            type VARCHAR(50) NOT NULL DEFAULT 'note',
            subject VARCHAR(255) NOT NULL DEFAULT '',
            details TEXT,
            interaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(50) NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_business (business_id),
            INDEX idx_contact (contact_id)
        ) {$charset};",
    ];
}

function bntm_crm_get_shortcodes() {
    return [
        'crm_dashboard'    => 'bntm_shortcode_crm',
        'crm_contact_form' => 'bntm_shortcode_crm_contact_form',
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

// ============================================================
// AJAX ACTION HOOKS
// ============================================================

// Contacts
add_action('wp_ajax_crm_add_contact',        'bntm_ajax_crm_add_contact');
add_action('wp_ajax_crm_edit_contact',       'bntm_ajax_crm_edit_contact');
add_action('wp_ajax_crm_delete_contact',     'bntm_ajax_crm_delete_contact');
add_action('wp_ajax_crm_get_contact',        'bntm_ajax_crm_get_contact');

// Leads
add_action('wp_ajax_crm_add_lead',           'bntm_ajax_crm_add_lead');
add_action('wp_ajax_crm_edit_lead',          'bntm_ajax_crm_edit_lead');
add_action('wp_ajax_crm_delete_lead',        'bntm_ajax_crm_delete_lead');
add_action('wp_ajax_crm_update_lead_stage',  'bntm_ajax_crm_update_lead_stage');

// Interactions
add_action('wp_ajax_crm_add_interaction',    'bntm_ajax_crm_add_interaction');
add_action('wp_ajax_crm_delete_interaction', 'bntm_ajax_crm_delete_interaction');

// Settings
add_action('wp_ajax_crm_save_settings',      'bntm_ajax_crm_save_settings');

// Public: contact form submission
add_action('wp_ajax_crm_submit_contact_form',        'bntm_ajax_crm_submit_contact_form');
add_action('wp_ajax_nopriv_crm_submit_contact_form', 'bntm_ajax_crm_submit_contact_form');

// ============================================================
// MAIN DASHBOARD SHORTCODE
// ============================================================

function bntm_shortcode_crm() {
    if (!is_user_logged_in()) {
        return '<div class="bntm-notice">Please log in to access the CRM.</div>';
    }

    $current_user = wp_get_current_user();
    $business_id  = $current_user->ID;
    $active_tab   = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

    ob_start();
    ?>
    <script>
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var crm_nonce = '<?php echo wp_create_nonce('crm_nonce'); ?>';
    </script>

    <div class="bntm-crm-container">
        <!-- Tab Navigation -->
        <div class="bntm-tabs">
            <a href="?tab=overview" class="bntm-tab <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="3" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="3" y="14" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="14" y="14" width="7" height="7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Overview
            </a>
            <a href="?tab=contacts" class="bntm-tab <?php echo $active_tab === 'contacts' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                Contacts
            </a>
            <a href="?tab=leads" class="bntm-tab <?php echo $active_tab === 'leads' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Leads
            </a>
            <a href="?tab=interactions" class="bntm-tab <?php echo $active_tab === 'interactions' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Interactions
            </a>
            <a href="?tab=settings" class="bntm-tab <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                Settings
            </a>
        </div>

        <!-- Tab Content -->
        <div class="bntm-tab-content">
            <?php if ($active_tab === 'overview'): ?>
                <?php echo crm_overview_tab($business_id); ?>
            <?php elseif ($active_tab === 'contacts'): ?>
                <?php echo crm_contacts_tab($business_id); ?>
            <?php elseif ($active_tab === 'leads'): ?>
                <?php echo crm_leads_tab($business_id); ?>
            <?php elseif ($active_tab === 'interactions'): ?>
                <?php echo crm_interactions_tab($business_id); ?>
            <?php elseif ($active_tab === 'settings'): ?>
                <?php echo crm_settings_tab($business_id); ?>
            <?php endif; ?>
        </div>
    </div>

    <style>
    /* ── Shared CRM styles ── */
    .bntm-crm-container { font-family: inherit; }
    .bntm-tabs { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:24px; border-bottom:2px solid #e5e7eb; padding-bottom:0; }
    .bntm-tab { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; font-size:14px; font-weight:500; color:#6b7280; text-decoration:none; border-radius:6px 6px 0 0; border:none; background:transparent; cursor:pointer; transition:color .15s,background .15s; margin-bottom:-2px; border-bottom:2px solid transparent; }
    .bntm-tab:hover { color:#111827; background:#f3f4f6; }
    .bntm-tab.active { color:var(--bntm-primary,#6366f1); border-bottom:2px solid var(--bntm-primary,#6366f1); background:#fff; }
    .bntm-tab-content { min-height:300px; }

    /* Stats row */
    .bntm-stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px; }
    .bntm-stat-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .bntm-stat-card .stat-icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .bntm-stat-card .stat-content h3 { margin:0 0 4px; font-size:12px; font-weight:500; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; }
    .bntm-stat-card .stat-number { margin:0 0 2px; font-size:26px; font-weight:700; color:#111827; line-height:1; }
    .bntm-stat-card .stat-label { font-size:11px; color:#9ca3af; }

    /* Form section */
    .bntm-form-section { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px 24px; margin-bottom:20px; }
    .bntm-form-section h3 { margin:0 0 16px; font-size:16px; font-weight:600; color:#111827; }

    /* Inputs/selects/buttons */
    .crm-input, .crm-select, .crm-textarea {
        width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px;
        font-size:14px; color:#111827; background:#fff; box-sizing:border-box;
        transition:border-color .15s,box-shadow .15s;
    }
    .crm-input:focus, .crm-select:focus, .crm-textarea:focus {
        outline:none; border-color:var(--bntm-primary,#6366f1);
        box-shadow:0 0 0 3px rgba(99,102,241,.12);
    }
    .crm-textarea { resize:vertical; min-height:80px; }
    .crm-form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
    .crm-form-row.single { grid-template-columns:1fr; }
    .crm-form-row.triple { grid-template-columns:1fr 1fr 1fr; }
    .crm-form-group { display:flex; flex-direction:column; gap:4px; }
    .crm-form-group label { font-size:12px; font-weight:500; color:#374151; }

    /* Table */
    .bntm-table-wrapper { overflow-x:auto; border-radius:10px; border:1px solid #e5e7eb; }
    .bntm-table { width:100%; border-collapse:collapse; font-size:14px; }
    .bntm-table thead tr { background:#f9fafb; }
    .bntm-table th { padding:11px 14px; text-align:left; font-size:11px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; }
    .bntm-table td { padding:12px 14px; border-top:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
    .bntm-table tbody tr:hover { background:#fafafa; }

    /* Badges */
    .crm-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:600; text-transform:capitalize; }
    .crm-badge-new        { background:#eff6ff; color:#2563eb; }
    .crm-badge-contacted  { background:#fef3c7; color:#b45309; }
    .crm-badge-qualified  { background:#ecfdf5; color:#059669; }
    .crm-badge-won        { background:#d1fae5; color:#065f46; }
    .crm-badge-lost       { background:#fee2e2; color:#991b1b; }
    .crm-badge-active     { background:#ecfdf5; color:#059669; }
    .crm-badge-inactive   { background:#f3f4f6; color:#6b7280; }
    .crm-badge-open       { background:#eff6ff; color:#2563eb; }
    .crm-badge-closed     { background:#f3f4f6; color:#6b7280; }
    .crm-badge-call       { background:#faf5ff; color:#7c3aed; }
    .crm-badge-email      { background:#ecfeff; color:#0891b2; }
    .crm-badge-meeting    { background:#fff7ed; color:#c2410c; }
    .crm-badge-note       { background:#f0fdf4; color:#15803d; }
    .crm-badge-high       { background:#fee2e2; color:#991b1b; }
    .crm-badge-medium     { background:#fef3c7; color:#b45309; }
    .crm-badge-low        { background:#f0fdf4; color:#15803d; }

    /* Buttons */
    .bntm-btn-primary   { background:var(--bntm-primary,#6366f1); color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; transition:background .15s,opacity .15s; }
    .bntm-btn-primary:hover { background:var(--bntm-primary-hover,#4f46e5); }
    .bntm-btn-secondary { background:#fff; color:#374151; border:1px solid #d1d5db; padding:9px 18px; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; transition:background .15s; }
    .bntm-btn-secondary:hover { background:#f9fafb; }
    .bntm-btn-danger    { background:#ef4444; color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; transition:background .15s; }
    .bntm-btn-danger:hover { background:#dc2626; }
    .bntm-btn-small { padding:5px 12px; font-size:12px; }
    .bntm-btn-icon  { background:transparent; border:none; cursor:pointer; padding:5px; border-radius:6px; color:#6b7280; transition:color .15s,background .15s; }
    .bntm-btn-icon:hover { background:#f3f4f6; color:#111827; }
    button:disabled { opacity:.55; cursor:not-allowed; }

    /* Notices */
    .bntm-notice { padding:12px 16px; border-radius:8px; font-size:14px; margin-bottom:12px; }
    .bntm-notice-success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
    .bntm-notice-error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

    /* Modal */
    .crm-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center; padding:16px; }
    .crm-modal-overlay.open { display:flex; }
    .crm-modal { background:#fff; border-radius:16px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,.2); }
    .crm-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 0; }
    .crm-modal-header h3 { margin:0; font-size:18px; font-weight:700; color:#111827; }
    .crm-modal-body { padding:20px 24px 24px; }
    .crm-modal-footer { display:flex; gap:10px; justify-content:flex-end; padding:0 24px 24px; }

    /* Filter bar */
    .crm-filter-bar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:16px; }
    .crm-search-input { flex:1; min-width:180px; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; color:#111827; }
    .crm-search-input:focus { outline:none; border-color:var(--bntm-primary,#6366f1); }

    /* Frontend pages grid */
    .bntm-frontend-pages-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; }
    .bntm-page-card { border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; background:#fff; }
    .bntm-page-card-header { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
    .bntm-page-card-icon { width:32px; height:32px; border-radius:8px; background:var(--bntm-primary,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; }
    .bntm-page-audience-badge { font-size:11px; font-weight:600; padding:3px 8px; border-radius:999px; }
    .bntm-badge-public   { background:#dbeafe; color:#1d4ed8; }
    .bntm-badge-loggedin { background:#fef3c7; color:#b45309; }
    .bntm-page-card-body { padding:14px 16px; }
    .bntm-page-card-body h4 { margin:0 0 6px; font-size:14px; font-weight:600; color:#111827; }
    .bntm-page-card-body p  { margin:0; font-size:13px; color:#6b7280; }
    .bntm-page-card-footer { padding:12px 16px; display:flex; gap:8px; border-top:1px solid #f3f4f6; }

    /* Pipeline kanban */
    .crm-pipeline { display:flex; gap:14px; overflow-x:auto; padding-bottom:8px; }
    .crm-pipeline-col { flex:0 0 220px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; padding:14px; }
    .crm-pipeline-col-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
    .crm-pipeline-col-header h4 { margin:0; font-size:13px; font-weight:600; color:#374151; text-transform:capitalize; }
    .crm-pipeline-count { font-size:11px; font-weight:700; background:#e5e7eb; color:#6b7280; border-radius:999px; padding:2px 8px; }
    .crm-lead-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:8px; cursor:pointer; transition:box-shadow .15s; }
    .crm-lead-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .crm-lead-card h5 { margin:0 0 4px; font-size:13px; font-weight:600; color:#111827; }
    .crm-lead-card .lead-value { font-size:12px; font-weight:700; color:var(--bntm-primary,#6366f1); }
    .crm-lead-card .lead-contact { font-size:11px; color:#9ca3af; margin-top:4px; }

    /* Action buttons in table */
    .crm-actions { display:flex; gap:4px; }

    /* Interaction list */
    .crm-interaction-item { display:flex; gap:14px; padding:14px 0; border-bottom:1px solid #f3f4f6; }
    .crm-interaction-item:last-child { border-bottom:none; }
    .crm-interaction-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .crm-interaction-meta { font-size:11px; color:#9ca3af; margin-top:2px; }

    /* Responsive */
    @media(max-width:640px) {
        .crm-form-row { grid-template-columns:1fr; }
        .crm-form-row.triple { grid-template-columns:1fr; }
        .bntm-stats-row { grid-template-columns:1fr 1fr; }
        .crm-pipeline { flex-direction:column; }
        .crm-pipeline-col { flex:unset; }
    }
    </style>

    <script>
    // ── Shared utilities ──
    function crmShowToast(msg, type) {
        var el = document.getElementById('crm-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'crm-toast';
            el.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(el);
        }
        var t = document.createElement('div');
        t.style.cssText = 'padding:12px 18px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.15);transition:opacity .3s;max-width:320px;';
        t.style.background = type === 'success' ? '#065f46' : '#991b1b';
        t.style.color = '#fff';
        t.textContent = msg;
        el.appendChild(t);
        setTimeout(function(){ t.style.opacity='0'; setTimeout(function(){ t.remove(); },300); }, 3000);
    }

    function crmOpenModal(id) {
        var m = document.getElementById(id);
        if (m) { m.classList.add('open'); document.body.style.overflow='hidden'; }
    }
    function crmCloseModal(id) {
        var m = document.getElementById(id);
        if (m) { m.classList.remove('open'); document.body.style.overflow=''; }
    }

    // Close modal on overlay click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('crm-modal-overlay')) {
            e.target.classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    // Copy URL helper
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('copy-page-url')) {
            var url = e.target.getAttribute('data-url');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function(){ crmShowToast('URL copied!','success'); });
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
                crmShowToast('URL copied!','success');
            }
        }
    });
    </script>
    <?php
    $content = ob_get_clean();
    return bntm_universal_container('CRM', $content);
}

// ============================================================
// TAB: OVERVIEW
// ============================================================

function crm_overview_tab($business_id) {
    global $wpdb;
    $stats = crm_get_stats($business_id);

    ob_start();
    ?>
    <!-- Stat Cards -->
    <div class="bntm-stats-row">
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:var(--bntm-primary,#6366f1);">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="stat-content">
                <h3>Total Leads</h3>
                <p class="stat-number"><?php echo number_format($stats['total_leads']); ?></p>
                <span class="stat-label"><?php echo number_format($stats['active_contacts']); ?> contacts</span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#10b981;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div class="stat-content">
                <h3>Active Opportunities</h3>
                <p class="stat-number"><?php echo number_format($stats['active_opportunities']); ?></p>
                <span class="stat-label"><?php echo crm_format_price($stats['pipeline_value']); ?> open pipeline</span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#f59e0b;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-content">
                <h3>Won / Closed Clients</h3>
                <p class="stat-number"><?php echo number_format($stats['won_leads']); ?></p>
                <span class="stat-label"><?php echo crm_format_price($stats['won_value']); ?> value</span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#ef4444;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6L6 18M6 6l12 12"/></svg>
            </div>
            <div class="stat-content">
                <h3>Lost / Ended Deals</h3>
                <p class="stat-number"><?php echo number_format($stats['lost_deals']); ?></p>
                <span class="stat-label">Closed or ended</span>
            </div>
        </div>
    </div>

    <div class="bntm-stats-row">
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#6366f1;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="stat-content">
                <h3>MOTM Completion</h3>
                <p class="stat-number"><?php echo number_format($stats['motm_completion']); ?>%</p>
                <span class="stat-label">Leads with MOTM uploaded</span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#0ea5e9;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </div>
            <div class="stat-content">
                <h3>Pipeline Type Breakdown</h3>
                <p class="stat-number" style="font-size:18px;line-height:1.2;">&nbsp;</p>
                <span class="stat-label">
                    <?php foreach ($stats['pipeline_type_breakdown'] as $row): ?>
                        <?php echo esc_html(crm_pipeline_type_label($row['pipeline_type'])); ?>: <?php echo number_format($row['total']); ?><br>
                    <?php endforeach; ?>
                </span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#14b8a6;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18M3 18h18"/></svg>
            </div>
            <div class="stat-content">
                <h3>Leads by Source</h3>
                <p class="stat-number" style="font-size:18px;line-height:1.2;">&nbsp;</p>
                <span class="stat-label">
                    <?php foreach ($stats['lead_source_breakdown'] as $row): ?>
                        <?php echo esc_html($row['lead_source'] ?: 'Unspecified'); ?>: <?php echo number_format($row['total']); ?><br>
                    <?php endforeach; ?>
                </span>
            </div>
        </div>
        <div class="bntm-stat-card">
            <div class="stat-icon" style="background:#f59e0b;">
                <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </div>
            <div class="stat-content">
                <h3>Deals by Product / Service</h3>
                <p class="stat-number" style="font-size:18px;line-height:1.2;">&nbsp;</p>
                <span class="stat-label">
                    <?php foreach ($stats['product_service_breakdown'] as $row): ?>
                        <?php echo esc_html($row['item']); ?>: <?php echo number_format($row['total']); ?><br>
                    <?php endforeach; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Recent Interactions -->
    <div class="bntm-form-section">
        <h3>Recent Interactions</h3>
        <?php
        $recent = $wpdb->get_results($wpdb->prepare(
            "SELECT i.*, c.first_name, c.last_name
             FROM {$wpdb->prefix}crm_interactions i
             LEFT JOIN {$wpdb->prefix}crm_contacts c ON c.id = i.contact_id
             WHERE i.business_id = %d AND i.status = 'active'
             ORDER BY i.interaction_date DESC LIMIT 5",
            $business_id
        ));
        $type_colors = ['call'=>'#7c3aed','email'=>'#0891b2','meeting'=>'#c2410c','note'=>'#15803d'];
        $type_icons  = [
            'call'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
            'email'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
            'meeting' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            'note'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        ];
        if (empty($recent)): ?>
        <p style="color:#9ca3af;font-size:14px;margin:0;">No interactions yet. Log your first interaction from the Interactions tab.</p>
        <?php else: ?>
        <div>
            <?php foreach ($recent as $item):
                $col   = $type_colors[$item->type] ?? '#6b7280';
                $ipath = $type_icons[$item->type] ?? $type_icons['note'];
                ?>
            <div class="crm-interaction-item">
                <div class="crm-interaction-icon" style="background:<?php echo esc_attr($col); ?>22;">
                    <svg width="18" height="18" fill="none" stroke="<?php echo esc_attr($col); ?>" viewBox="0 0 24 24"><?php echo $ipath; ?></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:500;color:#111827;"><?php echo esc_html($item->subject); ?></div>
                    <div class="crm-interaction-meta">
                        <?php echo esc_html($item->first_name . ' ' . $item->last_name); ?> &middot; <?php echo date('M j, Y g:i A', strtotime($item->interaction_date)); ?>
                    </div>
                </div>
                <span class="crm-badge crm-badge-<?php echo esc_attr($item->type); ?>"><?php echo esc_html($item->type); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Frontend Pages -->
    <div class="bntm-form-section">
        <h3>Frontend Pages</h3>
        <p style="color:#6b7280;margin-bottom:16px;font-size:14px;">Public-facing pages for this module. Share these links with your customers.</p>
        <div class="bntm-frontend-pages-grid">
            <div class="bntm-page-card">
                <div class="bntm-page-card-header">
                    <div class="bntm-page-card-icon">
                        <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="bntm-page-audience-badge bntm-badge-public">Public</span>
                </div>
                <div class="bntm-page-card-body">
                    <h4>Contact Form</h4>
                    <p>Public lead capture form — visitors can submit their details and a lead is automatically created.</p>
                </div>
                <div class="bntm-page-card-footer">
                    <?php
                    $page = get_page_by_path('contact-form');
                    $url  = $page ? get_permalink($page->ID) : '#';
                    ?>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="bntm-btn-primary bntm-btn-small">Open Page</a>
                    <button class="bntm-btn-secondary bntm-btn-small copy-page-url" data-url="<?php echo esc_url($url); ?>">Copy URL</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Overview tab specific — none needed beyond shared styles */
    </style>
    <script>
    (function() {
        // Overview tab JS — no actions needed
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================
// TAB: CONTACTS
// ============================================================

function crm_contacts_tab($business_id) {
    global $wpdb;
    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_contacts WHERE business_id = %d ORDER BY created_at DESC",
        $business_id
    ));

    ob_start();
    ?>
    <!-- Add button + filter -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div class="crm-filter-bar" style="margin:0;flex:1;">
            <input type="text" id="contact-search" class="crm-search-input" placeholder="Search contacts by name, email or company...">
            <select id="contact-status-filter" class="crm-select" style="width:auto;min-width:130px;">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button class="bntm-btn-primary" onclick="crmOpenModal('add-contact-modal')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-3px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Contact
        </button>
    </div>

    <div class="bntm-table-wrapper">
        <table class="bntm-table" id="contacts-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:32px;">No contacts yet. Add your first contact to get started.</td></tr>
                <?php else: ?>
                <?php foreach ($contacts as $c): ?>
                <tr data-name="<?php echo esc_attr(strtolower($c->first_name . ' ' . $c->last_name . ' ' . $c->email . ' ' . $c->company)); ?>"
                    data-status="<?php echo esc_attr($c->status); ?>">
                    <td>
                        <div style="font-weight:500;color:#111827;"><?php echo esc_html($c->first_name . ' ' . $c->last_name); ?></div>
                    </td>
                    <td><?php echo esc_html($c->email); ?></td>
                    <td><?php echo esc_html($c->phone); ?></td>
                    <td><?php echo esc_html($c->company); ?></td>
                    <td><span class="crm-badge crm-badge-<?php echo esc_attr($c->status); ?>"><?php echo esc_html($c->status); ?></span></td>
                    <td style="font-size:13px;color:#6b7280;"><?php echo date('M j, Y', strtotime($c->created_at)); ?></td>
                    <td>
                        <div class="crm-actions">
                            <button class="bntm-btn-icon" title="View" onclick="crmViewContact('<?php echo esc_attr($c->rand_id); ?>')">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button class="bntm-btn-icon" title="Edit" onclick="crmEditContact(<?php echo esc_attr(json_encode(['rand_id'=>$c->rand_id,'first_name'=>$c->first_name,'last_name'=>$c->last_name,'email'=>$c->email,'phone'=>$c->phone,'company'=>$c->company,'status'=>$c->status,'notes'=>$c->notes])); ?>)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="bntm-btn-icon" title="Delete" onclick="crmDeleteContact('<?php echo esc_attr($c->rand_id); ?>', this)" style="color:#ef4444;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Contact Modal -->
    <div class="crm-modal-overlay" id="add-contact-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Add Contact</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('add-contact-modal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>First Name *</label><input type="text" id="add-first-name" class="crm-input" placeholder="John"></div>
                    <div class="crm-form-group"><label>Last Name *</label><input type="text" id="add-last-name" class="crm-input" placeholder="Doe"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Email</label><input type="email" id="add-email" class="crm-input" placeholder="john@example.com"></div>
                    <div class="crm-form-group"><label>Phone</label><input type="text" id="add-phone" class="crm-input" placeholder="+1 555 000 0000"></div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Company</label><input type="text" id="add-company" class="crm-input" placeholder="Acme Corp"></div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Notes</label><textarea id="add-notes" class="crm-textarea" placeholder="Any additional notes..."></textarea></div>
                </div>
                <div id="add-contact-msg"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('add-contact-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="add-contact-btn" onclick="crmSubmitAddContact()">Add Contact</button>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div class="crm-modal-overlay" id="edit-contact-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Edit Contact</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('edit-contact-modal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="edit-contact-rand-id">
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>First Name *</label><input type="text" id="edit-first-name" class="crm-input"></div>
                    <div class="crm-form-group"><label>Last Name *</label><input type="text" id="edit-last-name" class="crm-input"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Email</label><input type="email" id="edit-email" class="crm-input"></div>
                    <div class="crm-form-group"><label>Phone</label><input type="text" id="edit-phone" class="crm-input"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Company</label><input type="text" id="edit-company" class="crm-input"></div>
                    <div class="crm-form-group"><label>Status</label>
                        <select id="edit-status" class="crm-select"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                    </div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Notes</label><textarea id="edit-notes" class="crm-textarea"></textarea></div>
                </div>
                <div id="edit-contact-msg"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('edit-contact-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="edit-contact-btn" onclick="crmSubmitEditContact()">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- View Contact Modal -->
    <div class="crm-modal-overlay" id="view-contact-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Contact Details</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('view-contact-modal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="crm-modal-body" id="view-contact-body">
                <p style="color:#9ca3af;">Loading...</p>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('view-contact-modal')">Close</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // Live search + filter
        var searchEl  = document.getElementById('contact-search');
        var filterEl  = document.getElementById('contact-status-filter');
        function filterContacts() {
            var q  = searchEl ? searchEl.value.toLowerCase() : '';
            var st = filterEl ? filterEl.value : '';
            var rows = document.querySelectorAll('#contacts-table tbody tr[data-name]');
            rows.forEach(function(r) {
                var nameMatch   = r.getAttribute('data-name').includes(q);
                var statusMatch = !st || r.getAttribute('data-status') === st;
                r.style.display = (nameMatch && statusMatch) ? '' : 'none';
            });
        }
        if (searchEl) searchEl.addEventListener('input', filterContacts);
        if (filterEl) filterEl.addEventListener('change', filterContacts);

        // Add
        window.crmSubmitAddContact = function() {
            var btn = document.getElementById('add-contact-btn');
            var fn  = document.getElementById('add-first-name').value.trim();
            var ln  = document.getElementById('add-last-name').value.trim();
            if (!fn || !ln) { document.getElementById('add-contact-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">First and last name are required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_add_contact');
            fd.append('nonce', crm_nonce);
            fd.append('first_name', fn);
            fd.append('last_name', ln);
            fd.append('email', document.getElementById('add-email').value.trim());
            fd.append('phone', document.getElementById('add-phone').value.trim());
            fd.append('company', document.getElementById('add-company').value.trim());
            fd.append('notes', document.getElementById('add-notes').value.trim());
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Contact added!','success'); crmCloseModal('add-contact-modal'); location.reload(); }
                    else { document.getElementById('add-contact-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">' + d.data.message + '</div>'; }
                })
                .catch(function(){ btn.disabled=false; crmShowToast('Request failed.','error'); });
        };

        // Edit populate
        window.crmEditContact = function(data) {
            document.getElementById('edit-contact-rand-id').value = data.rand_id;
            document.getElementById('edit-first-name').value  = data.first_name;
            document.getElementById('edit-last-name').value   = data.last_name;
            document.getElementById('edit-email').value       = data.email;
            document.getElementById('edit-phone').value       = data.phone;
            document.getElementById('edit-company').value     = data.company;
            document.getElementById('edit-status').value      = data.status;
            document.getElementById('edit-notes').value       = data.notes || '';
            document.getElementById('edit-contact-msg').innerHTML = '';
            crmOpenModal('edit-contact-modal');
        };

        // Edit submit
        window.crmSubmitEditContact = function() {
            var btn = document.getElementById('edit-contact-btn');
            var fn  = document.getElementById('edit-first-name').value.trim();
            var ln  = document.getElementById('edit-last-name').value.trim();
            if (!fn || !ln) { document.getElementById('edit-contact-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">First and last name are required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_edit_contact');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id',    document.getElementById('edit-contact-rand-id').value);
            fd.append('first_name', fn);
            fd.append('last_name', ln);
            fd.append('email',   document.getElementById('edit-email').value.trim());
            fd.append('phone',   document.getElementById('edit-phone').value.trim());
            fd.append('company', document.getElementById('edit-company').value.trim());
            fd.append('status',  document.getElementById('edit-status').value);
            fd.append('notes',   document.getElementById('edit-notes').value.trim());
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Contact updated!','success'); crmCloseModal('edit-contact-modal'); location.reload(); }
                    else { document.getElementById('edit-contact-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">' + d.data.message + '</div>'; }
                })
                .catch(function(){ btn.disabled=false; crmShowToast('Request failed.','error'); });
        };

        // Delete
        window.crmDeleteContact = function(rand_id, btn) {
            if (!confirm('Delete this contact? This will also remove their leads and interactions.')) return;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_delete_contact');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id', rand_id);
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Contact deleted.','success'); btn.closest('tr').remove(); }
                    else { crmShowToast(d.data.message,'error'); }
                })
                .catch(function(){ btn.disabled=false; crmShowToast('Request failed.','error'); });
        };

        // View contact
        window.crmViewContact = function(rand_id) {
            document.getElementById('view-contact-body').innerHTML = '<p style="color:#9ca3af;">Loading...</p>';
            crmOpenModal('view-contact-modal');
            var fd = new FormData();
            fd.append('action', 'crm_get_contact');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id', rand_id);
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(r=>r.json())
                .then(function(d) {
                    if (d.success) {
                        var c = d.data.contact;
                        var ints = d.data.interactions;
                        var leads = d.data.leads;
                        var html = '<div style="margin-bottom:20px;">'
                            + '<div style="display:flex;gap:16px;flex-wrap:wrap;">'
                            + '<div style="flex:1;min-width:140px;"><div style="font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Name</div><div style="font-size:15px;font-weight:600;color:#111827;">' + escHtml(c.first_name+' '+c.last_name) + '</div></div>'
                            + '<div style="flex:1;min-width:140px;"><div style="font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Email</div><div style="font-size:14px;">' + escHtml(c.email) + '</div></div>'
                            + '<div style="flex:1;min-width:140px;"><div style="font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Phone</div><div style="font-size:14px;">' + escHtml(c.phone) + '</div></div>'
                            + '<div style="flex:1;min-width:140px;"><div style="font-size:11px;color:#9ca3af;text-transform:uppercase;font-weight:600;">Company</div><div style="font-size:14px;">' + escHtml(c.company) + '</div></div>'
                            + '</div>';
                        if (c.notes) html += '<div style="margin-top:12px;padding:10px 14px;background:#f9fafb;border-radius:8px;font-size:13px;color:#374151;">' + escHtml(c.notes) + '</div>';
                        html += '</div>';
                        html += '<div style="font-size:13px;font-weight:600;color:#6b7280;margin-bottom:8px;">LEADS (' + leads.length + ')</div>';
                        if (leads.length) {
                            leads.forEach(function(l){ html += '<div style="padding:8px 12px;background:#f9fafb;border-radius:8px;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;"><span style="font-size:13px;font-weight:500;">' + escHtml(l.title) + '</span><span class="crm-badge crm-badge-'+escHtml(l.stage)+'">' + escHtml(l.stage) + '</span></div>'; });
                        } else { html += '<p style="font-size:13px;color:#9ca3af;margin:0 0 12px;">No leads.</p>'; }
                        html += '<div style="font-size:13px;font-weight:600;color:#6b7280;margin:12px 0 8px;">RECENT INTERACTIONS (' + ints.length + ')</div>';
                        if (ints.length) {
                            ints.forEach(function(i){ html += '<div style="padding:8px 12px;border-bottom:1px solid #f3f4f6;"><div style="font-size:13px;font-weight:500;">' + escHtml(i.subject) + '</div><div style="font-size:11px;color:#9ca3af;">' + escHtml(i.type) + ' &middot; ' + escHtml(i.interaction_date) + '</div></div>'; });
                        } else { html += '<p style="font-size:13px;color:#9ca3af;margin:0;">No interactions.</p>'; }
                        document.getElementById('view-contact-body').innerHTML = html;
                    }
                });
        };

        function escHtml(str) { var d = document.createElement('div'); d.appendChild(document.createTextNode(str||'')); return d.innerHTML; }
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================
// TAB: LEADS
// ============================================================

function crm_leads_tab($business_id) {
    global $wpdb;

    $pipeline_type = isset($_GET['pipeline']) && in_array($_GET['pipeline'], ['subscription', 'enterprise']) ? sanitize_text_field($_GET['pipeline']) : 'subscription';
    $stages        = crm_get_pipeline_stages($business_id, $pipeline_type);
    $lead_sources  = crm_get_lead_sources();
    $product_types = crm_get_product_types();

    $leads_by_stage = [];
    foreach ($stages as $stage) {
        $leads_by_stage[$stage] = $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, c.first_name, c.last_name
             FROM {$wpdb->prefix}crm_leads l
             LEFT JOIN {$wpdb->prefix}crm_contacts c ON c.id = l.contact_id
             WHERE l.business_id = %d AND l.pipeline_type = %s AND l.stage = %s AND l.status = 'open'
             ORDER BY l.created_at DESC",
            $business_id, $pipeline_type, $stage
        ));
    }

    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT id, first_name, last_name FROM {$wpdb->prefix}crm_contacts WHERE business_id = %d AND status = 'active' ORDER BY first_name",
        $business_id
    ));

    ob_start();
    ?>
    <div class="crm-filter-bar" style="margin-bottom:16px;">
        <select id="lead-pipeline-filter" class="crm-select" style="width:auto;min-width:150px;" onchange="crmApplyLeadFilters()">
            <option value="">All Pipelines</option>
            <?php foreach (crm_get_pipeline_types() as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($pipeline_type, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="lead-stage-filter" class="crm-select" style="width:auto;min-width:170px;" onchange="crmApplyLeadFilters()">
            <option value="">All Stages</option>
            <?php foreach ($stages as $s): ?>
                <option value="<?php echo esc_attr($s); ?>"><?php echo esc_html($s); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="lead-status-filter" class="crm-select" style="width:auto;min-width:150px;" onchange="crmApplyLeadFilters()">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="won">Won</option>
            <option value="lost">Lost</option>
        </select>
        <select id="lead-source-filter" class="crm-select" style="width:auto;min-width:170px;" onchange="crmApplyLeadFilters()">
            <option value="">All Sources</option>
            <?php foreach ($lead_sources as $source_key => $source_label): ?>
                <option value="<?php echo esc_attr($source_key); ?>"><?php echo esc_html($source_label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <select id="lead-view-mode" class="crm-select" style="width:auto;min-width:140px;" onchange="crmSwitchLeadView(this.value)">
                <option value="pipeline">Pipeline View</option>
                <option value="list">List View</option>
            </select>
        </div>
        <button class="bntm-btn-primary" onclick="crmOpenModal('add-lead-modal')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-3px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Lead
        </button>
    </div>

    <!-- Pipeline view -->
    <div id="lead-view-pipeline">
        <div class="crm-pipeline">
            <?php foreach ($stages as $stage):
                $stage_leads = $leads_by_stage[$stage] ?? [];
                $stage_value = array_sum(array_column($stage_leads, 'value'));
                ?>
            <div class="crm-pipeline-col">
                <div class="crm-pipeline-col-header">
                    <h4><?php echo esc_html($stage); ?></h4>
                    <span class="crm-pipeline-count"><?php echo count($stage_leads); ?></span>
                </div>
                <div style="font-size:11px;color:#9ca3af;margin-bottom:10px;"><?php echo crm_format_price($stage_value); ?></div>
                <?php foreach ($stage_leads as $l): ?>
                <div class="crm-lead-card" data-pipeline-type="<?php echo esc_attr($l->pipeline_type); ?>" data-stage="<?php echo esc_attr($l->stage); ?>" data-status="<?php echo esc_attr($l->status); ?>" data-product-type="<?php echo esc_attr($l->product_type); ?>" data-service-type="<?php echo esc_attr($l->service_type); ?>" data-source="<?php echo esc_attr($l->lead_source); ?>" onclick="crmEditLead(<?php echo esc_attr(json_encode(['rand_id'=>$l->rand_id,'title'=>$l->title,'contact_id'=>$l->contact_id,'value'=>$l->value,'pipeline_type'=>$l->pipeline_type,'product_type'=>$l->product_type,'service_type'=>$l->service_type,'lead_source'=>$l->lead_source,'motm_uploaded'=>$l->motm_uploaded,'ended_reason'=>$l->ended_reason,'stage'=>$l->stage,'priority'=>$l->priority,'expected_close'=>$l->expected_close,'notes'=>$l->notes,'status'=>$l->status])); ?>)">
                    <h5><?php echo esc_html($l->title); ?></h5>
                    <div class="lead-value"><?php echo crm_format_price($l->value); ?></div>
                    <div class="lead-contact"><?php echo esc_html($l->first_name . ' ' . $l->last_name); ?></div>
                    <div style="margin-top:8px;">
                        <span class="crm-badge crm-badge-<?php echo esc_attr($l->priority); ?>" style="font-size:10px;"><?php echo esc_html($l->priority); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($stage_leads)): ?>
                <div style="text-align:center;color:#d1d5db;font-size:12px;padding:16px 0;">Empty</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- List view -->
    <div id="lead-view-list" style="display:none;">
        <?php
        $all_leads = $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, c.first_name, c.last_name
             FROM {$wpdb->prefix}crm_leads l
             LEFT JOIN {$wpdb->prefix}crm_contacts c ON c.id = l.contact_id
             WHERE l.business_id = %d
             ORDER BY l.created_at DESC",
            $business_id
        ));
        ?>
        <div class="bntm-table-wrapper">
            <table class="bntm-table">
                <thead><tr><th>Title</th><th>Contact</th><th>Pipeline</th><th>Product / Service</th><th>Source</th><th>Value</th><th>Stage</th><th>Priority</th><th>Close Date</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (empty($all_leads)): ?>
                <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:32px;">No leads yet.</td></tr>
                <?php else: ?>
                <?php foreach ($all_leads as $l): ?>
                <tr data-pipeline-type="<?php echo esc_attr($l->pipeline_type); ?>" data-product-type="<?php echo esc_attr($l->product_type); ?>" data-service-type="<?php echo esc_attr($l->service_type); ?>" data-source="<?php echo esc_attr($l->lead_source); ?>" data-status="<?php echo esc_attr($l->status); ?>">
                    <td style="font-weight:500;color:#111827;"><?php echo esc_html($l->title); ?></td>
                    <td><?php echo esc_html($l->first_name . ' ' . $l->last_name); ?></td>
                    <td><?php echo esc_html(crm_pipeline_type_label($l->pipeline_type)); ?></td>
                    <td><?php echo esc_html($l->pipeline_type === 'subscription' ? $l->product_type : $l->service_type); ?></td>
                    <td><?php echo esc_html($l->lead_source ?: '—'); ?></td>
                    <td style="font-weight:600;color:var(--bntm-primary,#6366f1);"><?php echo crm_format_price($l->value); ?></td>
                    <td><span class="crm-badge crm-badge-<?php echo esc_attr(sanitize_title($l->stage)); ?>"><?php echo esc_html($l->stage); ?></span></td>
                    <td><span class="crm-badge crm-badge-<?php echo esc_attr($l->priority); ?>"><?php echo esc_html($l->priority); ?></span></td>
                    <td style="font-size:13px;color:#6b7280;"><?php echo $l->expected_close ? date('M j, Y', strtotime($l->expected_close)) : '—'; ?></td>
                    <td><span class="crm-badge crm-badge-<?php echo esc_attr($l->status); ?>"><?php echo esc_html($l->status); ?></span></td>
                    <td>
                        <div class="crm-actions">
                            <button class="bntm-btn-icon" title="Edit" onclick="crmEditLead(<?php echo esc_attr(json_encode(['rand_id'=>$l->rand_id,'title'=>$l->title,'contact_id'=>$l->contact_id,'value'=>$l->value,'pipeline_type'=>$l->pipeline_type,'product_type'=>$l->product_type,'service_type'=>$l->service_type,'lead_source'=>$l->lead_source,'motm_uploaded'=>$l->motm_uploaded,'ended_reason'=>$l->ended_reason,'stage'=>$l->stage,'priority'=>$l->priority,'expected_close'=>$l->expected_close,'notes'=>$l->notes,'status'=>$l->status])); ?>)">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button class="bntm-btn-icon" title="Delete" onclick="crmDeleteLead('<?php echo esc_attr($l->rand_id); ?>', this)" style="color:#ef4444;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Lead Modal -->
    <div class="crm-modal-overlay" id="add-lead-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Add Lead</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('add-lead-modal')"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="crm-modal-body">
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Lead Title *</label><input type="text" id="add-lead-title" class="crm-input" placeholder="e.g. Website Redesign for Acme"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Pipeline Type</label>
                        <select id="add-lead-pipeline-type" class="crm-select" onchange="crmRefreshLeadFields('add')">
                            <?php foreach (crm_get_pipeline_types() as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $pipeline_type); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group"><label>Lead Source</label>
                        <select id="add-lead-source" class="crm-select">
                            <option value="">— Select Source —</option>
                            <?php foreach ($lead_sources as $source_key => $source_label): ?>
                                <option value="<?php echo esc_attr($source_key); ?>"><?php echo esc_html($source_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group" id="add-lead-product-group"><label>Product Type</label>
                        <select id="add-lead-product-type" class="crm-select">
                            <option value="">— Select Product —</option>
                            <?php foreach ($product_types as $product_key => $product_label): ?>
                                <option value="<?php echo esc_attr($product_key); ?>"><?php echo esc_html($product_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group" id="add-lead-service-group" style="display:none;"><label>Service Type</label>
                        <input type="text" id="add-lead-service-type" class="crm-input" placeholder="e.g. Web development"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Contact</label>
                        <select id="add-lead-contact" class="crm-select">
                            <option value="">— Select Contact —</option>
                            <?php foreach ($contacts as $c): ?><option value="<?php echo esc_attr($c->id); ?>"><?php echo esc_html($c->first_name . ' ' . $c->last_name); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group"><label>Value</label><input type="number" id="add-lead-value" class="crm-input" placeholder="0.00" min="0" step="0.01"></div>
                </div>
                <div class="crm-form-row triple">
                    <div class="crm-form-group"><label>Stage</label>
                        <select id="add-lead-stage" class="crm-select"></select>
                    </div>
                    <div class="crm-form-group"><label>Priority</label>
                        <select id="add-lead-priority" class="crm-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
                    </div>
                    <div class="crm-form-group"><label>Expected Close</label><input type="date" id="add-lead-close" class="crm-input"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" id="add-lead-motm" style="margin:0;"> MOTM Uploaded</label></div>
                    <div class="crm-form-group" id="add-lead-ended-reason-group" style="display:none;"><label>Ended Reason</label>
                        <select id="add-lead-ended-reason" class="crm-select">
                            <option value="">— Select Reason —</option>
                            <?php foreach (crm_get_ended_reasons() as $reason_key => $reason_label): ?>
                                <option value="<?php echo esc_attr($reason_key); ?>"><?php echo esc_html($reason_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Notes</label><textarea id="add-lead-notes" class="crm-textarea" placeholder="Additional details..."></textarea></div>
                </div>
                <div id="add-lead-msg"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('add-lead-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="add-lead-btn" onclick="crmSubmitAddLead()">Add Lead</button>
            </div>
        </div>
    </div>

    <!-- Edit Lead Modal -->
    <div class="crm-modal-overlay" id="edit-lead-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Edit Lead</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('edit-lead-modal')"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="crm-modal-body">
                <input type="hidden" id="edit-lead-rand-id">
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Lead Title *</label><input type="text" id="edit-lead-title" class="crm-input"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Pipeline Type</label>
                        <select id="edit-lead-pipeline-type" class="crm-select" onchange="crmRefreshLeadFields('edit')">
                            <?php foreach (crm_get_pipeline_types() as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group"><label>Lead Source</label>
                        <select id="edit-lead-source" class="crm-select">
                            <option value="">— Select Source —</option>
                            <?php foreach ($lead_sources as $source_key => $source_label): ?>
                                <option value="<?php echo esc_attr($source_key); ?>"><?php echo esc_html($source_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group" id="edit-lead-product-group"><label>Product Type</label>
                        <select id="edit-lead-product-type" class="crm-select">
                            <option value="">— Select Product —</option>
                            <?php foreach ($product_types as $product_key => $product_label): ?>
                                <option value="<?php echo esc_attr($product_key); ?>"><?php echo esc_html($product_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group" id="edit-lead-service-group" style="display:none;"><label>Service Type</label>
                        <input type="text" id="edit-lead-service-type" class="crm-input" placeholder="e.g. Web development"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Contact</label>
                        <select id="edit-lead-contact" class="crm-select">
                            <option value="">— Select Contact —</option>
                            <?php foreach ($contacts as $c): ?><option value="<?php echo esc_attr($c->id); ?>"><?php echo esc_html($c->first_name . ' ' . $c->last_name); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group"><label>Value</label><input type="number" id="edit-lead-value" class="crm-input" min="0" step="0.01"></div>
                </div>
                <div class="crm-form-row triple">
                    <div class="crm-form-group"><label>Stage</label>
                        <select id="edit-lead-stage" class="crm-select"></select>
                    </div>
                    <div class="crm-form-group"><label>Priority</label>
                        <select id="edit-lead-priority" class="crm-select"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option></select>
                    </div>
                    <div class="crm-form-group"><label>Expected Close</label><input type="date" id="edit-lead-close" class="crm-input"></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Status</label>
                        <select id="edit-lead-status" class="crm-select"><option value="open">Open</option><option value="won">Won</option><option value="lost">Lost</option></select>
                    </div>
                    <div class="crm-form-group"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" id="edit-lead-motm" style="margin:0;"> MOTM Uploaded</label></div>
                </div>
                <div class="crm-form-row">
                    <div class="crm-form-group" id="edit-lead-ended-reason-group" style="display:none;"><label>Ended Reason</label>
                        <select id="edit-lead-ended-reason" class="crm-select">
                            <option value="">— Select Reason —</option>
                            <?php foreach (crm_get_ended_reasons() as $reason_key => $reason_label): ?>
                                <option value="<?php echo esc_attr($reason_key); ?>"><?php echo esc_html($reason_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Notes</label><textarea id="edit-lead-notes" class="crm-textarea"></textarea></div>
                </div>
                <div id="edit-lead-msg"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('edit-lead-modal')">Cancel</button>
                <button class="bntm-btn-danger bntm-btn-small" onclick="crmDeleteCurrentLead()">Delete</button>
                <button class="bntm-btn-primary" id="edit-lead-btn" onclick="crmSubmitEditLead()">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var leadPipelineStages = {
            subscription: <?php echo json_encode(crm_get_pipeline_stages($business_id, 'subscription')); ?>,
            enterprise: <?php echo json_encode(crm_get_pipeline_stages($business_id, 'enterprise')); ?>
        };

        function crmSetStageOptions(prefix, pipeline) {
            var select = document.getElementById(prefix + '-lead-stage');
            if (!select) return;
            select.innerHTML = '';
            var stages = leadPipelineStages[pipeline] || leadPipelineStages.subscription;
            stages.forEach(function(stage) {
                var opt = document.createElement('option');
                opt.value = stage;
                opt.textContent = stage;
                select.appendChild(opt);
            });
        }

        function crmRefreshLeadFields(mode) {
            var pipeline = document.getElementById(mode + '-lead-pipeline-type').value;
            var productGroup = document.getElementById(mode + '-lead-product-group');
            var serviceGroup = document.getElementById(mode + '-lead-service-group');
            var endedGroup = document.getElementById(mode + '-lead-ended-reason-group');
            var stageSelect = document.getElementById(mode + '-lead-stage');

            if (pipeline === 'enterprise') {
                productGroup.style.display = 'none';
                serviceGroup.style.display = '';
            } else {
                productGroup.style.display = '';
                serviceGroup.style.display = 'none';
            }

            crmSetStageOptions(mode, pipeline);
            if (endedGroup && stageSelect) {
                endedGroup.style.display = stageSelect.value === 'Subscription Ended' ? '' : 'none';
            }
        }

        function crmApplyLeadFilters() {
            var pipelineFilter = document.getElementById('lead-pipeline-filter');
            var stageFilter = document.getElementById('lead-stage-filter');
            var statusFilter = document.getElementById('lead-status-filter');
            var sourceFilter = document.getElementById('lead-source-filter');

            if (pipelineFilter && pipelineFilter.value && pipelineFilter.value !== '<?php echo esc_js($pipeline_type); ?>') {
                var params = new URLSearchParams(window.location.search);
                params.set('tab', 'leads');
                params.set('pipeline', pipelineFilter.value);
                window.location.search = params.toString();
                return;
            }

            var stageValue = stageFilter ? stageFilter.value.toLowerCase() : '';
            var statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
            var sourceValue = sourceFilter ? sourceFilter.value.toLowerCase() : '';

            document.querySelectorAll('.crm-lead-card, #lead-view-list tbody tr').forEach(function(item) {
                var match = true;
                var itemStage = (item.getAttribute('data-stage') || '').toLowerCase();
                var itemStatus = (item.getAttribute('data-status') || '').toLowerCase();
                var itemSource = (item.getAttribute('data-source') || '').toLowerCase();

                if (stageValue && itemStage !== stageValue) {
                    match = false;
                }
                if (statusValue && itemStatus !== statusValue) {
                    match = false;
                }
                if (sourceValue && itemSource !== sourceValue) {
                    match = false;
                }

                item.style.display = match ? '' : 'none';
            });
        }

        window.crmSwitchLeadView = function(v) {
            document.getElementById('lead-view-pipeline').style.display = v === 'pipeline' ? '' : 'none';
            document.getElementById('lead-view-list').style.display      = v === 'list' ? '' : 'none';
        };

        window.crmSubmitAddLead = function() {
            var btn = document.getElementById('add-lead-btn');
            var t   = document.getElementById('add-lead-title').value.trim();
            if (!t) { document.getElementById('add-lead-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">Lead title is required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_add_lead');
            fd.append('nonce', crm_nonce);
            fd.append('title',          t);
            fd.append('contact_id',     document.getElementById('add-lead-contact').value);
            fd.append('value',          document.getElementById('add-lead-value').value || 0);
            fd.append('pipeline_type',  document.getElementById('add-lead-pipeline-type').value);
            fd.append('product_type',   document.getElementById('add-lead-product-type').value);
            fd.append('service_type',   document.getElementById('add-lead-service-type').value);
            fd.append('lead_source',    document.getElementById('add-lead-source').value);
            fd.append('motm_uploaded',  document.getElementById('add-lead-motm').checked ? 1 : 0);
            fd.append('ended_reason',   document.getElementById('add-lead-ended-reason').value);
            fd.append('stage',          document.getElementById('add-lead-stage').value);
            fd.append('priority',       document.getElementById('add-lead-priority').value);
            fd.append('expected_close', document.getElementById('add-lead-close').value);
            fd.append('notes',          document.getElementById('add-lead-notes').value.trim());
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                btn.disabled=false;
                if(d.success){crmShowToast('Lead added!','success');crmCloseModal('add-lead-modal');location.reload();}
                else{document.getElementById('add-lead-msg').innerHTML='<div class="bntm-notice bntm-notice-error">'+d.data.message+'</div>';}
            }).catch(function(){btn.disabled=false;crmShowToast('Request failed.','error');});
        };

        window.crmEditLead = function(data) {
            document.getElementById('edit-lead-rand-id').value       = data.rand_id;
            document.getElementById('edit-lead-title').value         = data.title;
            document.getElementById('edit-lead-contact').value       = data.contact_id;
            document.getElementById('edit-lead-value').value         = data.value;
            document.getElementById('edit-lead-pipeline-type').value = data.pipeline_type || 'subscription';
            document.getElementById('edit-lead-source').value        = data.lead_source || '';
            document.getElementById('edit-lead-product-type').value  = data.product_type || '';
            document.getElementById('edit-lead-service-type').value  = data.service_type || '';
            document.getElementById('edit-lead-motm').checked        = parseInt(data.motm_uploaded || 0, 10) === 1;
            document.getElementById('edit-lead-ended-reason').value  = data.ended_reason || '';
            crmRefreshLeadFields('edit');
            document.getElementById('edit-lead-stage').value       = data.stage;
            document.getElementById('edit-lead-priority').value    = data.priority;
            document.getElementById('edit-lead-close').value       = data.expected_close || '';
            document.getElementById('edit-lead-status').value      = data.status;
            document.getElementById('edit-lead-notes').value    = data.notes || '';
            document.getElementById('edit-lead-msg').innerHTML  = '';
            crmOpenModal('edit-lead-modal');
        };

        window.crmSubmitEditLead = function() {
            var btn = document.getElementById('edit-lead-btn');
            var t   = document.getElementById('edit-lead-title').value.trim();
            if (!t) { document.getElementById('edit-lead-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">Lead title is required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_edit_lead');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id',        document.getElementById('edit-lead-rand-id').value);
            fd.append('title',          t);
            fd.append('contact_id',     document.getElementById('edit-lead-contact').value);
            fd.append('value',          document.getElementById('edit-lead-value').value || 0);
            fd.append('pipeline_type',  document.getElementById('edit-lead-pipeline-type').value);
            fd.append('product_type',   document.getElementById('edit-lead-product-type').value);
            fd.append('service_type',   document.getElementById('edit-lead-service-type').value);
            fd.append('lead_source',    document.getElementById('edit-lead-source').value);
            fd.append('motm_uploaded',  document.getElementById('edit-lead-motm').checked ? 1 : 0);
            fd.append('ended_reason',   document.getElementById('edit-lead-ended-reason').value);
            fd.append('stage',          document.getElementById('edit-lead-stage').value);
            fd.append('priority',       document.getElementById('edit-lead-priority').value);
            fd.append('expected_close', document.getElementById('edit-lead-close').value);
            fd.append('status',         document.getElementById('edit-lead-status').value);
            fd.append('notes',          document.getElementById('edit-lead-notes').value.trim());
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                btn.disabled=false;
                if(d.success){crmShowToast('Lead updated!','success');crmCloseModal('edit-lead-modal');location.reload();}
                else{document.getElementById('edit-lead-msg').innerHTML='<div class="bntm-notice bntm-notice-error">'+d.data.message+'</div>';}
            }).catch(function(){btn.disabled=false;crmShowToast('Request failed.','error');});
        };

        window.crmDeleteCurrentLead = function() {
            var rand_id = document.getElementById('edit-lead-rand-id').value;
            crmDeleteLead(rand_id, null);
        };

        window.crmDeleteLead = function(rand_id, btn) {
            if (!confirm('Delete this lead?')) return;
            if (btn) btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_delete_lead');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id', rand_id);
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                if(btn) btn.disabled=false;
                if(d.success){crmShowToast('Lead deleted.','success');crmCloseModal('edit-lead-modal');location.reload();}
                else{crmShowToast(d.data.message,'error');}
            }).catch(function(){if(btn)btn.disabled=false;crmShowToast('Request failed.','error');});
        };

        crmRefreshLeadFields('add');
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================
// TAB: INTERACTIONS
// ============================================================

function crm_interactions_tab($business_id) {
    global $wpdb;

    $interactions = $wpdb->get_results($wpdb->prepare(
        "SELECT i.*, c.first_name, c.last_name
         FROM {$wpdb->prefix}crm_interactions i
         LEFT JOIN {$wpdb->prefix}crm_contacts c ON c.id = i.contact_id
         WHERE i.business_id = %d AND i.status = 'active'
         ORDER BY i.interaction_date DESC
         LIMIT 100",
        $business_id
    ));

    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT id, first_name, last_name FROM {$wpdb->prefix}crm_contacts WHERE business_id = %d AND status = 'active' ORDER BY first_name",
        $business_id
    ));

    ob_start();
    ?>
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div class="crm-filter-bar" style="margin:0;flex:1;">
            <select id="int-type-filter" class="crm-select" style="width:auto;min-width:130px;">
                <option value="">All Types</option>
                <option value="call">Call</option>
                <option value="email">Email</option>
                <option value="meeting">Meeting</option>
                <option value="note">Note</option>
            </select>
        </div>
        <button class="bntm-btn-primary" onclick="crmOpenModal('add-interaction-modal')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-3px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Log Interaction
        </button>
    </div>

    <div class="bntm-table-wrapper">
        <table class="bntm-table" id="interactions-table">
            <thead><tr><th>Subject</th><th>Contact</th><th>Type</th><th>Date</th><th>Details</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($interactions)): ?>
            <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:32px;">No interactions logged yet.</td></tr>
            <?php else: ?>
            <?php foreach ($interactions as $i): ?>
            <tr data-type="<?php echo esc_attr($i->type); ?>">
                <td style="font-weight:500;color:#111827;"><?php echo esc_html($i->subject); ?></td>
                <td><?php echo esc_html($i->first_name . ' ' . $i->last_name); ?></td>
                <td><span class="crm-badge crm-badge-<?php echo esc_attr($i->type); ?>"><?php echo esc_html($i->type); ?></span></td>
                <td style="font-size:13px;color:#6b7280;"><?php echo date('M j, Y g:i A', strtotime($i->interaction_date)); ?></td>
                <td style="font-size:13px;color:#6b7280;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo esc_attr($i->details); ?>"><?php echo esc_html($i->details ?: '—'); ?></td>
                <td>
                    <button class="bntm-btn-icon" title="Delete" onclick="crmDeleteInteraction('<?php echo esc_attr($i->rand_id); ?>', this)" style="color:#ef4444;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Interaction Modal -->
    <div class="crm-modal-overlay" id="add-interaction-modal">
        <div class="crm-modal">
            <div class="crm-modal-header">
                <h3>Log Interaction</h3>
                <button class="bntm-btn-icon" onclick="crmCloseModal('add-interaction-modal')"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="crm-modal-body">
                <div class="crm-form-row">
                    <div class="crm-form-group"><label>Contact *</label>
                        <select id="add-int-contact" class="crm-select">
                            <option value="">— Select Contact —</option>
                            <?php foreach ($contacts as $c): ?><option value="<?php echo esc_attr($c->id); ?>"><?php echo esc_html($c->first_name . ' ' . $c->last_name); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-group"><label>Type *</label>
                        <select id="add-int-type" class="crm-select"><option value="note">Note</option><option value="call">Call</option><option value="email">Email</option><option value="meeting">Meeting</option></select>
                    </div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Subject *</label><input type="text" id="add-int-subject" class="crm-input" placeholder="Brief description of the interaction"></div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Date &amp; Time</label><input type="datetime-local" id="add-int-date" class="crm-input"></div>
                </div>
                <div class="crm-form-row single">
                    <div class="crm-form-group"><label>Details</label><textarea id="add-int-details" class="crm-textarea" placeholder="Full notes about this interaction..."></textarea></div>
                </div>
                <div id="add-int-msg"></div>
            </div>
            <div class="crm-modal-footer">
                <button class="bntm-btn-secondary" onclick="crmCloseModal('add-interaction-modal')">Cancel</button>
                <button class="bntm-btn-primary" id="add-int-btn" onclick="crmSubmitAddInteraction()">Log Interaction</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // Set default datetime
        var dtInput = document.getElementById('add-int-date');
        if (dtInput) {
            var now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dtInput.value = now.toISOString().slice(0,16);
        }

        // Filter by type
        var typeFilter = document.getElementById('int-type-filter');
        if (typeFilter) {
            typeFilter.addEventListener('change', function() {
                var v = this.value;
                document.querySelectorAll('#interactions-table tbody tr[data-type]').forEach(function(r){
                    r.style.display = (!v || r.getAttribute('data-type') === v) ? '' : 'none';
                });
            });
        }

        window.crmSubmitAddInteraction = function() {
            var btn  = document.getElementById('add-int-btn');
            var con  = document.getElementById('add-int-contact').value;
            var subj = document.getElementById('add-int-subject').value.trim();
            if (!con || !subj) { document.getElementById('add-int-msg').innerHTML = '<div class="bntm-notice bntm-notice-error">Contact and subject are required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_add_interaction');
            fd.append('nonce', crm_nonce);
            fd.append('contact_id', con);
            fd.append('type',       document.getElementById('add-int-type').value);
            fd.append('subject',    subj);
            fd.append('details',    document.getElementById('add-int-details').value.trim());
            fd.append('interaction_date', document.getElementById('add-int-date').value);
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                btn.disabled=false;
                if(d.success){crmShowToast('Interaction logged!','success');crmCloseModal('add-interaction-modal');location.reload();}
                else{document.getElementById('add-int-msg').innerHTML='<div class="bntm-notice bntm-notice-error">'+d.data.message+'</div>';}
            }).catch(function(){btn.disabled=false;crmShowToast('Request failed.','error');});
        };

        window.crmDeleteInteraction = function(rand_id, btn) {
            if (!confirm('Delete this interaction?')) return;
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_delete_interaction');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id', rand_id);
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                btn.disabled=false;
                if(d.success){crmShowToast('Interaction deleted.','success');btn.closest('tr').remove();}
                else{crmShowToast(d.data.message,'error');}
            }).catch(function(){btn.disabled=false;crmShowToast('Request failed.','error');});
        };
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================
// TAB: SETTINGS
// ============================================================

function crm_settings_tab($business_id) {
    $currency = bntm_get_setting('crm_currency', 'USD');
    $pipeline_types = crm_get_pipeline_types();
    $selected_pipeline = isset($_GET['pipeline']) && array_key_exists($_GET['pipeline'], $pipeline_types) ? sanitize_text_field($_GET['pipeline']) : 'subscription';
    $stages   = crm_get_pipeline_stages($business_id, $selected_pipeline);
    $int_types = ['call', 'email', 'meeting', 'note'];

    ob_start();
    ?>
    <div class="bntm-form-section">
        <h3>General Settings</h3>
        <div class="crm-form-row" style="max-width:400px;">
            <div class="crm-form-group">
                <label>Currency</label>
                <select id="settings-currency" class="crm-select">
                    <option value="USD" <?php selected($currency,'USD'); ?>>USD — US Dollar ($)</option>
                    <option value="EUR" <?php selected($currency,'EUR'); ?>>EUR — Euro (€)</option>
                    <option value="GBP" <?php selected($currency,'GBP'); ?>>GBP — British Pound (£)</option>
                    <option value="PHP" <?php selected($currency,'PHP'); ?>>PHP — Philippine Peso (₱)</option>
                    <option value="AED" <?php selected($currency,'AED'); ?>>AED — UAE Dirham (AED)</option>
                    <option value="SAR" <?php selected($currency,'SAR'); ?>>SAR — Saudi Riyal (SAR)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bntm-form-section">
        <h3>Pipeline Stages</h3>
        <p style="font-size:14px;color:#6b7280;margin-bottom:16px;">Manage the stages of your sales pipeline. Stages are applied in order.</p>
        <div class="crm-form-row" style="margin-bottom:16px;">
            <div class="crm-form-group" style="flex:1;min-width:240px;">
                <label>Pipeline Type</label>
                <select id="settings-pipeline-type" class="crm-select" onchange="crmLoadPipelineStages(this.value)">
                    <?php foreach ($pipeline_types as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_pipeline, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div id="pipeline-stages-list">
            <?php foreach ($stages as $index => $stage): ?>
            <div class="crm-stage-item" data-index="<?php echo $index; ?>" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <div style="color:#9ca3af;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <input type="text" class="crm-input stage-name-input" value="<?php echo esc_attr($stage); ?>" style="flex:1;max-width:300px;">
                <button class="bntm-btn-icon" style="color:#ef4444;" onclick="crmRemoveStage(this)" title="Remove stage">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="bntm-btn-secondary bntm-btn-small" style="margin-top:8px;" onclick="crmAddStageRow()">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-2px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Stage
        </button>
    </div>

    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <button class="bntm-btn-primary" id="save-settings-btn" onclick="crmSaveSettings()">Save Settings</button>
        <div id="settings-msg" style="flex:1;"></div>
    </div>

    <script>
    (function() {
        window.crmAddStageRow = function() {
            var list = document.getElementById('pipeline-stages-list');
            var div  = document.createElement('div');
            div.className = 'crm-stage-item';
            div.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px;';
            div.innerHTML = '<div style="color:#9ca3af;"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></div>'
                + '<input type="text" class="crm-input stage-name-input" placeholder="Stage name" style="flex:1;max-width:300px;">'
                + '<button class="bntm-btn-icon" style="color:#ef4444;" onclick="crmRemoveStage(this)" title="Remove"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
            list.appendChild(div);
        };

        window.crmRemoveStage = function(btn) {
            btn.closest('.crm-stage-item').remove();
        };

        window.crmSaveSettings = function() {
            var btn = document.getElementById('save-settings-btn');
            var stageInputs = document.querySelectorAll('.stage-name-input');
            var stages = [];
            stageInputs.forEach(function(i){ var v=i.value.trim(); if(v) stages.push(v); });
            if (!stages.length) { document.getElementById('settings-msg').innerHTML='<div class="bntm-notice bntm-notice-error">At least one pipeline stage is required.</div>'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action', 'crm_save_settings');
            fd.append('nonce', crm_nonce);
            fd.append('currency', document.getElementById('settings-currency').value);
            fd.append('stages', JSON.stringify(stages));
            fetch(ajaxurl,{method:'POST',body:fd}).then(r=>r.json()).then(function(d){
                btn.disabled=false;
                if(d.success){crmShowToast('Settings saved!','success');document.getElementById('settings-msg').innerHTML='<div class="bntm-notice bntm-notice-success">Settings saved successfully.</div>';}
                else{document.getElementById('settings-msg').innerHTML='<div class="bntm-notice bntm-notice-error">'+d.data.message+'</div>';}
            }).catch(function(){btn.disabled=false;crmShowToast('Request failed.','error');});
        };
    })();
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================
// AJAX HANDLERS
// ============================================================

function bntm_ajax_crm_add_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $first_name  = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name   = sanitize_text_field($_POST['last_name'] ?? '');
    $email       = sanitize_email($_POST['email'] ?? '');
    $phone       = sanitize_text_field($_POST['phone'] ?? '');
    $company     = sanitize_text_field($_POST['company'] ?? '');
    $notes       = sanitize_textarea_field($_POST['notes'] ?? '');

    if (!$first_name || !$last_name) {
        wp_send_json_error(['message' => 'First and last name are required.']);
    }

    $result = $wpdb->insert(
        $wpdb->prefix . 'crm_contacts',
        [
            'rand_id'     => bntm_rand_id(),
            'business_id' => $business_id,
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'email'       => $email,
            'phone'       => $phone,
            'company'     => $company,
            'notes'       => $notes,
            'status'      => 'active',
        ],
        ['%s','%d','%s','%s','%s','%s','%s','%s','%s']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Contact added successfully!', 'id' => $wpdb->insert_id]);
    } else {
        wp_send_json_error(['message' => 'Failed to add contact. Please try again.']);
    }
}

function bntm_ajax_crm_edit_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');
    $first_name  = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name   = sanitize_text_field($_POST['last_name'] ?? '');
    $email       = sanitize_email($_POST['email'] ?? '');
    $phone       = sanitize_text_field($_POST['phone'] ?? '');
    $company     = sanitize_text_field($_POST['company'] ?? '');
    $status      = sanitize_text_field($_POST['status'] ?? 'active');
    $notes       = sanitize_textarea_field($_POST['notes'] ?? '');

    if (!$first_name || !$last_name) {
        wp_send_json_error(['message' => 'First and last name are required.']);
    }

    $result = $wpdb->update(
        $wpdb->prefix . 'crm_contacts',
        ['first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone'=>$phone,'company'=>$company,'status'=>$status,'notes'=>$notes],
        ['rand_id'=>$rand_id,'business_id'=>$business_id],
        ['%s','%s','%s','%s','%s','%s','%s'],
        ['%s','%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Contact updated successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to update contact.']);
    }
}

function bntm_ajax_crm_delete_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}crm_contacts WHERE rand_id = %s AND business_id = %d",
        $rand_id, $business_id
    ));

    if (!$contact) { wp_send_json_error(['message' => 'Contact not found.']); }

    $wpdb->query('START TRANSACTION');
    try {
        $wpdb->delete($wpdb->prefix . 'crm_interactions', ['contact_id'=>$contact->id,'business_id'=>$business_id], ['%d','%d']);
        $wpdb->delete($wpdb->prefix . 'crm_leads',        ['contact_id'=>$contact->id,'business_id'=>$business_id], ['%d','%d']);
        $wpdb->delete($wpdb->prefix . 'crm_contacts',     ['id'=>$contact->id,'business_id'=>$business_id],         ['%d','%d']);
        $wpdb->query('COMMIT');
        wp_send_json_success(['message' => 'Contact deleted.']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Failed to delete contact.']);
    }
}

function bntm_ajax_crm_get_contact() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');

    $contact = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_contacts WHERE rand_id = %s AND business_id = %d",
        $rand_id, $business_id
    ), ARRAY_A);

    if (!$contact) { wp_send_json_error(['message' => 'Contact not found.']); }

    $leads = $wpdb->get_results($wpdb->prepare(
        "SELECT title, stage, value FROM {$wpdb->prefix}crm_leads WHERE contact_id = %d AND business_id = %d ORDER BY created_at DESC LIMIT 10",
        $contact['id'], $business_id
    ), ARRAY_A);

    $interactions = $wpdb->get_results($wpdb->prepare(
        "SELECT type, subject, interaction_date FROM {$wpdb->prefix}crm_interactions WHERE contact_id = %d AND business_id = %d AND status = 'active' ORDER BY interaction_date DESC LIMIT 10",
        $contact['id'], $business_id
    ), ARRAY_A);

    wp_send_json_success(['contact' => $contact, 'leads' => $leads, 'interactions' => $interactions]);
}

function bntm_ajax_crm_add_lead() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id    = get_current_user_id();
    $title          = sanitize_text_field($_POST['title'] ?? '');
    $contact_id     = intval($_POST['contact_id'] ?? 0);
    $value          = floatval($_POST['value'] ?? 0);
    $pipeline_type  = sanitize_text_field($_POST['pipeline_type'] ?? 'subscription');
    if (!in_array($pipeline_type, ['subscription', 'enterprise'])) {
        $pipeline_type = 'subscription';
    }
    $product_type   = sanitize_text_field($_POST['product_type'] ?? '');
    $service_type   = sanitize_text_field($_POST['service_type'] ?? '');
    $lead_source    = sanitize_text_field($_POST['lead_source'] ?? '');
    $motm_uploaded  = intval($_POST['motm_uploaded'] ?? 0) ? 1 : 0;
    $ended_reason   = sanitize_text_field($_POST['ended_reason'] ?? '');
    $stage          = sanitize_text_field($_POST['stage'] ?? 'new');
    $priority       = sanitize_text_field($_POST['priority'] ?? 'medium');
    $expected_close = sanitize_text_field($_POST['expected_close'] ?? '');
    $notes          = sanitize_textarea_field($_POST['notes'] ?? '');

    if (!$title) { wp_send_json_error(['message' => 'Lead title is required.']); }

    $close_date = $expected_close ? date('Y-m-d', strtotime($expected_close)) : null;

    $result = $wpdb->insert(
        $wpdb->prefix . 'crm_leads',
        [
            'rand_id'        => bntm_rand_id(),
            'business_id'    => $business_id,
            'contact_id'     => $contact_id,
            'title'          => $title,
            'value'          => $value,
            'pipeline_type'  => $pipeline_type,
            'product_type'   => $product_type,
            'service_type'   => $service_type,
            'lead_source'    => $lead_source,
            'motm_uploaded'  => $motm_uploaded,
            'ended_reason'   => $ended_reason,
            'stage'          => $stage,
            'priority'       => $priority,
            'expected_close' => $close_date,
            'notes'          => $notes,
            'status'         => 'open',
        ],
        ['%s','%d','%d','%s','%f','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Lead added successfully!', 'id' => $wpdb->insert_id]);
    } else {
        wp_send_json_error(['message' => 'Failed to add lead. Please try again.']);
    }
}

function bntm_ajax_crm_edit_lead() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id    = get_current_user_id();
    $rand_id        = sanitize_text_field($_POST['rand_id'] ?? '');
    $title          = sanitize_text_field($_POST['title'] ?? '');
    $contact_id     = intval($_POST['contact_id'] ?? 0);
    $value          = floatval($_POST['value'] ?? 0);
    $pipeline_type  = sanitize_text_field($_POST['pipeline_type'] ?? 'subscription');
    if (!in_array($pipeline_type, ['subscription', 'enterprise'])) {
        $pipeline_type = 'subscription';
    }
    $product_type   = sanitize_text_field($_POST['product_type'] ?? '');
    $service_type   = sanitize_text_field($_POST['service_type'] ?? '');
    $lead_source    = sanitize_text_field($_POST['lead_source'] ?? '');
    $motm_uploaded  = intval($_POST['motm_uploaded'] ?? 0) ? 1 : 0;
    $ended_reason   = sanitize_text_field($_POST['ended_reason'] ?? '');
    $stage          = sanitize_text_field($_POST['stage'] ?? 'new');
    $priority       = sanitize_text_field($_POST['priority'] ?? 'medium');
    $expected_close = sanitize_text_field($_POST['expected_close'] ?? '');
    $status         = sanitize_text_field($_POST['status'] ?? 'open');
    $notes          = sanitize_textarea_field($_POST['notes'] ?? '');

    if (!$title) { wp_send_json_error(['message' => 'Lead title is required.']); }

    $close_date = $expected_close ? date('Y-m-d', strtotime($expected_close)) : null;

    $result = $wpdb->update(
        $wpdb->prefix . 'crm_leads',
        [
            'title'         => $title,
            'contact_id'    => $contact_id,
            'value'         => $value,
            'pipeline_type' => $pipeline_type,
            'product_type'  => $product_type,
            'service_type'  => $service_type,
            'lead_source'   => $lead_source,
            'motm_uploaded' => $motm_uploaded,
            'ended_reason'  => $ended_reason,
            'stage'         => $stage,
            'priority'      => $priority,
            'expected_close'=> $close_date,
            'status'        => $status,
            'notes'         => $notes,
        ],
        ['rand_id'=>$rand_id,'business_id'=>$business_id],
        ['%s','%d','%f','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s'],
        ['%s','%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Lead updated successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to update lead.']);
    }
}

function bntm_ajax_crm_delete_lead() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');

    $result = $wpdb->delete(
        $wpdb->prefix . 'crm_leads',
        ['rand_id'=>$rand_id,'business_id'=>$business_id],
        ['%s','%d']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Lead deleted.']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete lead.']);
    }
}

function bntm_ajax_crm_update_lead_stage() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');
    $stage       = sanitize_text_field($_POST['stage'] ?? '');

    if (!$stage) { wp_send_json_error(['message' => 'Stage is required.']); }

    $result = $wpdb->update(
        $wpdb->prefix . 'crm_leads',
        ['stage' => $stage],
        ['rand_id'=>$rand_id,'business_id'=>$business_id],
        ['%s'],
        ['%s','%d']
    );

    if ($result !== false) {
        wp_send_json_success(['message' => 'Lead stage updated.']);
    } else {
        wp_send_json_error(['message' => 'Failed to update stage.']);
    }
}

function bntm_ajax_crm_add_interaction() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id       = get_current_user_id();
    $contact_id        = intval($_POST['contact_id'] ?? 0);
    $type              = sanitize_text_field($_POST['type'] ?? 'note');
    $subject           = sanitize_text_field($_POST['subject'] ?? '');
    $details           = sanitize_textarea_field($_POST['details'] ?? '');
    $interaction_date  = sanitize_text_field($_POST['interaction_date'] ?? '');

    if (!$contact_id || !$subject) { wp_send_json_error(['message' => 'Contact and subject are required.']); }

    // Verify contact belongs to this business
    $contact = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}crm_contacts WHERE id = %d AND business_id = %d",
        $contact_id, $business_id
    ));
    if (!$contact) { wp_send_json_error(['message' => 'Invalid contact.']); }

    $int_dt = $interaction_date ? date('Y-m-d H:i:s', strtotime($interaction_date)) : current_time('mysql');

    $result = $wpdb->insert(
        $wpdb->prefix . 'crm_interactions',
        [
            'rand_id'          => bntm_rand_id(),
            'business_id'      => $business_id,
            'contact_id'       => $contact_id,
            'type'             => $type,
            'subject'          => $subject,
            'details'          => $details,
            'interaction_date' => $int_dt,
            'status'           => 'active',
        ],
        ['%s','%d','%d','%s','%s','%s','%s','%s']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Interaction logged successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to log interaction.']);
    }
}

function bntm_ajax_crm_delete_interaction() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    global $wpdb;
    $business_id = get_current_user_id();
    $rand_id     = sanitize_text_field($_POST['rand_id'] ?? '');

    $result = $wpdb->delete(
        $wpdb->prefix . 'crm_interactions',
        ['rand_id'=>$rand_id,'business_id'=>$business_id],
        ['%s','%d']
    );

    if ($result) {
        wp_send_json_success(['message' => 'Interaction deleted.']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete interaction.']);
    }
}

function bntm_ajax_crm_save_settings() {
    check_ajax_referer('crm_nonce', 'nonce');
    if (!is_user_logged_in()) { wp_send_json_error(['message' => 'Unauthorized']); }

    $business_id = get_current_user_id();
    $currency    = sanitize_text_field($_POST['currency'] ?? 'USD');
    $stages_json = sanitize_text_field($_POST['stages'] ?? '[]');

    $allowed_currencies = ['USD','EUR','GBP','PHP','AED','SAR'];
    if (!in_array($currency, $allowed_currencies)) { $currency = 'USD'; }

    $stages = json_decode(stripslashes($stages_json), true);
    if (!is_array($stages)) { $stages = ['new','contacted','qualified','won','lost']; }
    $stages = array_values(array_filter(array_map('sanitize_text_field', $stages)));

    bntm_set_setting('crm_currency', $currency);
    bntm_set_setting('crm_pipeline_stages_' . $business_id, json_encode($stages));

    wp_send_json_success(['message' => 'Settings saved successfully!']);
}

// ============================================================
// FRONTEND SHORTCODE: CONTACT FORM
// ============================================================

function bntm_shortcode_crm_contact_form() {
    ob_start();
    ?>
    <div class="crm-public-page">
        <div class="crm-public-form-card">
            <div class="crm-public-form-header">
                <div class="crm-public-form-icon">
                    <svg width="32" height="32" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h2>Get In Touch</h2>
                <p>Fill out the form below and we'll get back to you shortly.</p>
            </div>
            <div class="crm-public-form-body">
                <div id="crm-public-success" style="display:none;" class="crm-public-success-msg">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3>Thank you!</h3>
                    <p>Your message has been received. We'll be in touch soon.</p>
                </div>
                <div id="crm-public-form-inner">
                    <div class="crm-pub-row">
                        <div class="crm-pub-group"><label>First Name *</label><input type="text" id="pub-first-name" class="crm-pub-input" placeholder="John"></div>
                        <div class="crm-pub-group"><label>Last Name *</label><input type="text" id="pub-last-name" class="crm-pub-input" placeholder="Doe"></div>
                    </div>
                    <div class="crm-pub-row">
                        <div class="crm-pub-group"><label>Email *</label><input type="email" id="pub-email" class="crm-pub-input" placeholder="john@example.com"></div>
                        <div class="crm-pub-group"><label>Phone</label><input type="text" id="pub-phone" class="crm-pub-input" placeholder="+1 555 000 0000"></div>
                    </div>
                    <div class="crm-pub-row single">
                        <div class="crm-pub-group"><label>Company</label><input type="text" id="pub-company" class="crm-pub-input" placeholder="Your company (optional)"></div>
                    </div>
                    <div class="crm-pub-row single">
                        <div class="crm-pub-group"><label>Message</label><textarea id="pub-message" class="crm-pub-textarea" placeholder="How can we help you?"></textarea></div>
                    </div>
                    <div id="crm-pub-error" class="crm-pub-error" style="display:none;"></div>
                    <button class="crm-pub-submit" id="crm-pub-submit-btn" onclick="crmPublicSubmit()">
                        <span id="crm-pub-btn-text">Send Message</span>
                        <span id="crm-pub-btn-spinner" style="display:none;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:crmSpin 1s linear infinite;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    var ajaxurl_crm_pub = '<?php echo admin_url('admin-ajax.php'); ?>';
    var crm_pub_nonce   = '<?php echo wp_create_nonce('crm_nonce'); ?>';

    function crmPublicSubmit() {
        var btn   = document.getElementById('crm-pub-submit-btn');
        var errEl = document.getElementById('crm-pub-error');
        var fn    = document.getElementById('pub-first-name').value.trim();
        var ln    = document.getElementById('pub-last-name').value.trim();
        var em    = document.getElementById('pub-email').value.trim();

        errEl.style.display = 'none';
        if (!fn || !ln || !em) {
            errEl.textContent = 'First name, last name, and email are required.';
            errEl.style.display = 'block';
            return;
        }

        btn.disabled = true;
        document.getElementById('crm-pub-btn-text').style.display    = 'none';
        document.getElementById('crm-pub-btn-spinner').style.display = 'inline-flex';

        var fd = new FormData();
        fd.append('action',     'crm_submit_contact_form');
        fd.append('nonce',      crm_pub_nonce);
        fd.append('first_name', fn);
        fd.append('last_name',  ln);
        fd.append('email',      em);
        fd.append('phone',      document.getElementById('pub-phone').value.trim());
        fd.append('company',    document.getElementById('pub-company').value.trim());
        fd.append('message',    document.getElementById('pub-message').value.trim());

        fetch(ajaxurl_crm_pub, { method:'POST', body:fd })
            .then(r => r.json())
            .then(function(d) {
                btn.disabled = false;
                document.getElementById('crm-pub-btn-text').style.display    = 'inline';
                document.getElementById('crm-pub-btn-spinner').style.display = 'none';
                if (d.success) {
                    document.getElementById('crm-public-form-inner').style.display = 'none';
                    document.getElementById('crm-public-success').style.display    = 'block';
                } else {
                    errEl.textContent   = d.data.message || 'Submission failed.';
                    errEl.style.display = 'block';
                }
            })
            .catch(function() {
                btn.disabled = false;
                document.getElementById('crm-pub-btn-text').style.display    = 'inline';
                document.getElementById('crm-pub-btn-spinner').style.display = 'none';
                errEl.textContent   = 'Network error. Please try again.';
                errEl.style.display = 'block';
            });
    }
    </script>

    <style>
    @keyframes crmSpin { to { transform: rotate(360deg); } }

    .crm-public-page {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
        box-sizing: border-box;
    }

    .crm-public-form-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 8px 40px rgba(0,0,0,.1);
        width: 100%;
        max-width: 540px;
        overflow: hidden;
    }

    .crm-public-form-header {
        background: var(--bntm-primary, #6366f1);
        padding: 36px 36px 28px;
        text-align: center;
        color: #fff;
    }

    .crm-public-form-icon {
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .crm-public-form-header h2 {
        margin: 0 0 8px;
        font-size: 26px;
        font-weight: 700;
        color: #fff;
    }

    .crm-public-form-header p {
        margin: 0;
        font-size: 15px;
        color: rgba(255,255,255,.8);
    }

    .crm-public-form-body {
        padding: 32px 36px;
    }

    .crm-pub-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .crm-pub-row.single { grid-template-columns: 1fr; }

    .crm-pub-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .crm-pub-group label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
    }

    .crm-pub-input, .crm-pub-textarea {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 14px;
        color: #111827;
        transition: border-color .15s, box-shadow .15s;
        box-sizing: border-box;
        width: 100%;
    }

    .crm-pub-input:focus, .crm-pub-textarea:focus {
        outline: none;
        border-color: var(--bntm-primary, #6366f1);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }

    .crm-pub-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .crm-pub-submit {
        width: 100%;
        padding: 13px;
        background: var(--bntm-primary, #6366f1);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, opacity .15s;
        margin-top: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .crm-pub-submit:hover:not(:disabled) { background: var(--bntm-primary-hover, #4f46e5); }
    .crm-pub-submit:disabled { opacity: .6; cursor: not-allowed; }

    .crm-pub-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .crm-public-success-msg {
        text-align: center;
        padding: 20px 0;
        color: #059669;
    }

    .crm-public-success-msg svg { margin-bottom: 12px; }
    .crm-public-success-msg h3 { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #111827; }
    .crm-public-success-msg p  { margin: 0; font-size: 15px; color: #6b7280; }

    @media (max-width: 480px) {
        .crm-pub-row { grid-template-columns: 1fr; }
        .crm-public-form-body { padding: 24px 20px; }
        .crm-public-form-header { padding: 28px 20px 20px; }
    }
    </style>
    <?php
    return ob_get_clean();
}

// ============================================================
// AJAX: PUBLIC CONTACT FORM SUBMISSION
// ============================================================

function bntm_ajax_crm_submit_contact_form() {
    check_ajax_referer('crm_nonce', 'nonce');

    global $wpdb;

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name  = sanitize_text_field($_POST['last_name'] ?? '');
    $email      = sanitize_email($_POST['email'] ?? '');
    $phone      = sanitize_text_field($_POST['phone'] ?? '');
    $company    = sanitize_text_field($_POST['company'] ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');

    if (!$first_name || !$last_name || !$email) {
        wp_send_json_error(['message' => 'First name, last name, and email are required.']);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Please enter a valid email address.']);
    }

    // Assign to a default business (first admin user)
    $admin_users = get_users(['role' => 'administrator', 'number' => 1]);
    $business_id = !empty($admin_users) ? $admin_users[0]->ID : 1;

    $wpdb->query('START TRANSACTION');
    try {
        // Check for existing contact by email for this business
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}crm_contacts WHERE email = %s AND business_id = %d LIMIT 1",
            $email, $business_id
        ));

        $contact_id = $existing;

        if (!$existing) {
            $wpdb->insert(
                $wpdb->prefix . 'crm_contacts',
                ['rand_id'=>bntm_rand_id(),'business_id'=>$business_id,'first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email,'phone'=>$phone,'company'=>$company,'notes'=>$message,'status'=>'active'],
                ['%s','%d','%s','%s','%s','%s','%s','%s','%s']
            );
            $contact_id = $wpdb->insert_id;
        }

        // Create lead
        $lead_title = 'Enquiry from ' . $first_name . ' ' . $last_name;
        $wpdb->insert(
            $wpdb->prefix . 'crm_leads',
            ['rand_id'=>bntm_rand_id(),'business_id'=>$business_id,'contact_id'=>$contact_id,'title'=>$lead_title,'value'=>0,'stage'=>'new','priority'=>'medium','notes'=>$message,'status'=>'open'],
            ['%s','%d','%d','%s','%f','%s','%s','%s','%s']
        );

        // Log interaction
        if ($message) {
            $wpdb->insert(
                $wpdb->prefix . 'crm_interactions',
                ['rand_id'=>bntm_rand_id(),'business_id'=>$business_id,'contact_id'=>$contact_id,'type'=>'note','subject'=>'Contact form submission','details'=>$message,'interaction_date'=>current_time('mysql'),'status'=>'active'],
                ['%s','%d','%d','%s','%s','%s','%s','%s']
            );
        }

        $wpdb->query('COMMIT');
        wp_send_json_success(['message' => 'Thank you! Your message has been received.']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Submission failed. Please try again.']);
    }
}

// ============================================================
// HELPER FUNCTIONS
// ============================================================

function crm_format_price($amount) {
    $currency = bntm_get_setting('crm_currency', 'USD');
    $symbols  = [
        'USD' => '$',
        'EUR' => '&euro;',
        'GBP' => '&pound;',
        'PHP' => '&#8369;',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
    ];
    $symbol = $symbols[$currency] ?? '$';
    return $symbol . number_format((float)$amount, 2);
}

function crm_get_pipeline_types() {
    return [
        'subscription' => 'Subscription',
        'enterprise'   => 'Enterprise',
    ];
}

function crm_pipeline_type_label($type) {
    $types = crm_get_pipeline_types();
    return $types[$type] ?? ucfirst($type);
}

function crm_get_default_pipeline_stages($pipeline_type = 'subscription') {
    if ($pipeline_type !== 'enterprise') {
        $pipeline_type = 'subscription';
    }

    $defaults = [
        'subscription' => [
            'New Lead',
            'Qualified Lead',
            'Exploratory Meeting / Demo',
            'Proposal Sent',
            'Negotiation / Revision',
            'Terms Agreed',
            'Onboarding',
            'Active Client',
            'Subscription Ended',
        ],
        'enterprise' => [
            'New Lead',
            'Qualified Lead',
            'Exploratory Meeting',
            'Proposal Sent',
            'Negotiation / Revision',
            'Contract Signed',
            'Initial Payment Received',
            'Handoff to Operations',
            'Project Completion',
            'Final Payment Received',
            'Lost',
        ],
    ];

    return $defaults[$pipeline_type];
}

function crm_get_pipeline_stages($business_id, $pipeline_type = 'subscription') {
    $pipeline_type = in_array($pipeline_type, ['subscription', 'enterprise']) ? $pipeline_type : 'subscription';
    $setting_key = 'crm_pipeline_stages_' . $business_id . '_' . $pipeline_type;
    $saved = bntm_get_setting($setting_key, '');
    if ($saved) {
        $stages = json_decode($saved, true);
        if (is_array($stages) && !empty($stages)) {
            return $stages;
        }
    }

    $legacy = bntm_get_setting('crm_pipeline_stages_' . $business_id, '');
    if ($legacy) {
        $stages = json_decode($legacy, true);
        if (is_array($stages) && !empty($stages)) {
            return $stages;
        }
    }

    return crm_get_default_pipeline_stages($pipeline_type);
}

function crm_get_lead_sources() {
    return [
        'Website'       => 'Website',
        'Referral'      => 'Referral',
        'Email'         => 'Email',
        'Social Media'  => 'Social Media',
        'Partner'       => 'Partner',
        'Event'         => 'Event',
        'Other'         => 'Other',
    ];
}

function crm_get_product_types() {
    return [
        'Hub'   => 'Hub',
        'Spree' => 'Spree',
    ];
}

function crm_get_ended_reasons() {
    return [
        'Cancelled'    => 'Cancelled',
        'Churned'      => 'Churned',
        'Expired'      => 'Expired',
        'Completed'    => 'Completed',
        'Paused'       => 'Paused',
        'Non-Renewal'  => 'Non-Renewal',
    ];
}

function crm_get_stats($business_id) {
    global $wpdb;

    $contacts_table     = $wpdb->prefix . 'crm_contacts';
    $leads_table        = $wpdb->prefix . 'crm_leads';
    $interactions_table = $wpdb->prefix . 'crm_interactions';

    $total_contacts         = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$contacts_table} WHERE business_id = %d", $business_id));
    $active_contacts        = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$contacts_table} WHERE business_id = %d AND status = 'active'", $business_id));
    $total_leads            = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d", $business_id));
    $active_opportunities   = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d AND status = 'open'", $business_id));
    $pipeline_value         = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(value) FROM {$leads_table} WHERE business_id = %d AND status = 'open'", $business_id));
    $won_leads              = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d AND status = 'won'", $business_id));
    $won_value              = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(value) FROM {$leads_table} WHERE business_id = %d AND status = 'won'", $business_id));
    $lost_deals             = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d AND (status = 'lost' OR stage = 'Subscription Ended')", $business_id));
    $motm_uploaded          = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d AND motm_uploaded = 1", $business_id));
    $motm_total             = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$leads_table} WHERE business_id = %d", $business_id));
    $pipeline_type_breakdown = $wpdb->get_results($wpdb->prepare("SELECT pipeline_type, COUNT(*) AS total FROM {$leads_table} WHERE business_id = %d GROUP BY pipeline_type", $business_id), ARRAY_A);
    $lead_source_breakdown  = $wpdb->get_results($wpdb->prepare("SELECT lead_source, COUNT(*) AS total FROM {$leads_table} WHERE business_id = %d GROUP BY lead_source", $business_id), ARRAY_A);
    $product_service_breakdown = $wpdb->get_results($wpdb->prepare(
        "SELECT COALESCE(NULLIF(product_type, ''), NULLIF(service_type, ''), 'Other') AS item, COUNT(*) AS total FROM {$leads_table} WHERE business_id = %d GROUP BY item",
        $business_id
    ), ARRAY_A);
    $total_interactions      = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$interactions_table} WHERE business_id = %d AND status = 'active'", $business_id));
    $monthly_interactions    = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$interactions_table} WHERE business_id = %d AND status = 'active' AND interaction_date >= %s",
        $business_id, date('Y-m-01')
    ));

    return [
        'total_contacts'          => $total_contacts,
        'active_contacts'         => $active_contacts,
        'total_leads'             => $total_leads,
        'active_opportunities'    => $active_opportunities,
        'pipeline_value'          => $pipeline_value ?: 0,
        'won_leads'               => $won_leads,
        'won_value'               => $won_value ?: 0,
        'lost_deals'              => $lost_deals,
        'motm_uploaded'           => $motm_uploaded,
        'motm_completion'         => $motm_total ? round($motm_uploaded / $motm_total * 100) : 0,
        'pipeline_type_breakdown' => $pipeline_type_breakdown,
        'lead_source_breakdown'   => $lead_source_breakdown,
        'product_service_breakdown' => $product_service_breakdown,
        'total_interactions'      => $total_interactions,
        'monthly_interactions'    => $monthly_interactions,
    ];
}
