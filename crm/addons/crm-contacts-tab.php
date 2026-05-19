<?php
// ============================================================
// TAB: CONTACTS  —  Revamped (Apple HIG, new lead fields)
// ============================================================

function crm_contacts_tab($business_id) {
    global $wpdb;
    $contacts = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}crm_contacts WHERE business_id = %d ORDER BY created_at DESC",
        $business_id
    ));

    // Pipeline stages for the Add/Edit Lead modal inside the contact detail drawer
    $pipeline_stages = crm_get_pipeline_stages($business_id);

    ob_start();
    ?>

    <!-- ══════════════════════════════════════════════════
         CONTACTS TAB — Apple HIG Styles
    ══════════════════════════════════════════════════ -->
    <style>
    /* ── Reset & base ── */
    .crm-c *,
    .crm-c *::before,
    .crm-c *::after { box-sizing: border-box; }

    .crm-c {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Helvetica Neue", sans-serif;
        -webkit-font-smoothing: antialiased;
        color: #1c1c1e;
    }

    /* ── Toolbar ── */
    .crm-c-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .crm-c-search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 360px;
    }
    .crm-c-search-wrap svg.search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #8e8e93;
        pointer-events: none;
    }
    .crm-c-search {
        width: 100%;
        padding: 8px 12px 8px 34px;
        background: #f2f2f7;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        color: #1c1c1e;
        outline: none;
        transition: box-shadow .15s;
    }
    .crm-c-search:focus {
        box-shadow: 0 0 0 3px rgba(0, 122, 255, .25);
        background: #fff;
        border: 1px solid #007aff;
    }
    .crm-c-search::placeholder { color: #8e8e93; }

    .crm-c-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .crm-c-filter-pill {
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #d1d1d6;
        background: #fff;
        color: #3c3c43;
        cursor: pointer;
        transition: background .12s, border-color .12s;
        white-space: nowrap;
    }
    .crm-c-filter-pill.active,
    .crm-c-filter-pill:hover { background: #007aff; border-color: #007aff; color: #fff; }

    .crm-c-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #007aff;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .12s, transform .1s;
        white-space: nowrap;
    }
    .crm-c-add-btn:hover  { background: #0062cc; }
    .crm-c-add-btn:active { transform: scale(0.97); }

    /* ── Contact List ── */
    .crm-c-list {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e5e5ea;
        overflow: hidden;
    }

    .crm-c-list-header {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 1fr 90px 80px;
        padding: 9px 16px;
        background: #f9f9fb;
        border-bottom: 1px solid #e5e5ea;
    }
    .crm-c-list-header span {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #8e8e93;
    }

    .crm-c-row {
        display: grid;
        grid-template-columns: 2fr 1.5fr 1fr 1fr 90px 80px;
        align-items: center;
        padding: 13px 16px;
        border-bottom: 1px solid #f2f2f7;
        cursor: pointer;
        transition: background .1s;
    }
    .crm-c-row:last-child { border-bottom: none; }
    .crm-c-row:hover { background: #f9f9fb; }

    /* avatar + name cell */
    .crm-c-name-cell {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }
    .crm-c-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e3eeff;
        color: #007aff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        letter-spacing: -.02em;
    }
    /* Avatar color variety */
    .crm-c-avatar.av-green  { background: #e3f9e9; color: #25a244; }
    .crm-c-avatar.av-orange { background: #fff0e1; color: #bf6400; }
    .crm-c-avatar.av-purple { background: #f0eaff; color: #7a43c2; }
    .crm-c-avatar.av-pink   { background: #ffe6f0; color: #c0297e; }
    .crm-c-avatar.av-teal   { background: #e0f9f4; color: #0f766e; }

    .crm-c-name { font-size: 14px; font-weight: 600; color: #1c1c1e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .crm-c-company { font-size: 12px; color: #8e8e93; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .crm-c-cell {
        font-size: 13px;
        color: #3c3c43;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 8px;
    }
    .crm-c-cell.muted { color: #aeaeb2; }

    /* Status badge */
    .crm-c-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .crm-c-badge::before {
        content: '';
        display: inline-block;
        width: 6px; height: 6px;
        border-radius: 50%;
    }
    .crm-c-badge-active  { background: #e6f9ee; color: #1a7f3c; }
    .crm-c-badge-active::before  { background: #25a244; }
    .crm-c-badge-inactive{ background: #f2f2f7; color: #6e6e73; }
    .crm-c-badge-inactive::before{ background: #aeaeb2; }

    /* Row actions */
    .crm-c-actions {
        display: flex;
        align-items: center;
        gap: 2px;
        justify-content: flex-end;
        opacity: 0;
        transition: opacity .1s;
    }
    .crm-c-row:hover .crm-c-actions { opacity: 1; }

    .crm-c-icon-btn {
        width: 30px; height: 30px;
        border-radius: 8px;
        border: none;
        background: transparent;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        color: #8e8e93;
        transition: background .1s, color .1s;
    }
    .crm-c-icon-btn:hover { background: #f2f2f7; color: #1c1c1e; }
    .crm-c-icon-btn.danger:hover { background: #fff0f0; color: #ff3b30; }

    /* Empty state */
    .crm-c-empty {
        padding: 56px 24px;
        text-align: center;
        color: #8e8e93;
    }
    .crm-c-empty svg { margin-bottom: 14px; opacity: .4; }
    .crm-c-empty h3 { margin: 0 0 6px; font-size: 16px; font-weight: 600; color: #3c3c43; }
    .crm-c-empty p  { margin: 0; font-size: 14px; }

    /* ══════════════════════════════════════════════════
       SLIDE-OVER DETAIL DRAWER
    ══════════════════════════════════════════════════ */
    .crm-drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9998;
        background: rgba(0,0,0,.35);
        backdrop-filter: blur(2px);
    }
    .crm-drawer-overlay.open { display: block; }

    .crm-drawer {
        position: fixed;
        top: 0; right: 0; bottom: 0;
        width: 420px;
        max-width: 100vw;
        background: #f2f2f7;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        transform: translateX(110%);
        transition: transform .3s cubic-bezier(.4,0,.2,1);
        box-shadow: -4px 0 32px rgba(0,0,0,.12);
    }
    .crm-drawer.open { transform: translateX(0); }

    .crm-drawer-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px 12px;
        background: #f2f2f7;
        border-bottom: 1px solid #e5e5ea;
        flex-shrink: 0;
    }
    .crm-drawer-topbar h2 { margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1e; }

    .crm-drawer-close {
        width: 30px; height: 30px;
        border-radius: 50%;
        border: none;
        background: #e5e5ea;
        color: #6e6e73;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        font-size: 16px;
        transition: background .1s;
    }
    .crm-drawer-close:hover { background: #d1d1d6; }

    .crm-drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    /* Drawer card sections */
    .crm-dc {
        background: #fff;
        border-radius: 13px;
        border: 1px solid #e5e5ea;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .crm-dc-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid #f2f2f7;
    }
    .crm-dc-head h4 {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #8e8e93;
    }
    .crm-dc-edit-btn {
        font-size: 13px;
        font-weight: 500;
        color: #007aff;
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }
    .crm-dc-edit-btn:hover { opacity: .7; }

    /* Profile hero */
    .crm-dc-profile {
        padding: 20px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .crm-dc-avatar-lg {
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; font-weight: 700;
        flex-shrink: 0;
    }
    .crm-dc-info { flex: 1; min-width: 0; }
    .crm-dc-info h3 { margin: 0 0 3px; font-size: 20px; font-weight: 700; color: #1c1c1e; }
    .crm-dc-info p  { margin: 0; font-size: 14px; color: #6e6e73; }

    /* Info rows */
    .crm-dc-row {
        display: flex;
        align-items: center;
        padding: 11px 16px;
        border-bottom: 1px solid #f2f2f7;
        gap: 12px;
    }
    .crm-dc-row:last-child { border-bottom: none; }
    .crm-dc-row-icon { color: #8e8e93; flex-shrink: 0; }
    .crm-dc-row-label { font-size: 13px; color: #8e8e93; flex: 0 0 90px; }
    .crm-dc-row-val   { font-size: 14px; color: #1c1c1e; font-weight: 500; flex: 1; }
    .crm-dc-row-val.empty { color: #c7c7cc; font-weight: 400; }

    /* Notes block */
    .crm-dc-notes {
        padding: 12px 16px;
        font-size: 14px;
        color: #3c3c43;
        line-height: 1.55;
    }
    .crm-dc-notes.empty { color: #c7c7cc; font-style: italic; }

    /* Mini lead card in drawer */
    .crm-dc-lead-item {
        display: flex;
        align-items: center;
        padding: 11px 16px;
        border-bottom: 1px solid #f2f2f7;
        gap: 10px;
    }
    .crm-dc-lead-item:last-child { border-bottom: none; }
    .crm-dc-lead-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .crm-dc-lead-name { font-size: 14px; font-weight: 500; color: #1c1c1e; flex: 1; }
    .crm-dc-lead-val  { font-size: 13px; color: #007aff; font-weight: 600; }
    .crm-dc-stage-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: capitalize;
    }

    /* Interaction item in drawer */
    .crm-dc-int-item {
        display: flex;
        gap: 12px;
        padding: 10px 16px;
        border-bottom: 1px solid #f2f2f7;
        align-items: flex-start;
    }
    .crm-dc-int-item:last-child { border-bottom: none; }
    .crm-dc-int-icon {
        width: 30px; height: 30px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .crm-dc-int-subj { font-size: 13px; font-weight: 600; color: #1c1c1e; }
    .crm-dc-int-meta { font-size: 12px; color: #8e8e93; margin-top: 1px; }

    /* Drawer footer action row */
    .crm-drawer-footer {
        padding: 12px 16px;
        border-top: 1px solid #e5e5ea;
        background: #f2f2f7;
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .crm-drawer-btn {
        flex: 1;
        padding: 10px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .12s;
    }
    .crm-drawer-btn:active { opacity: .75; }
    .crm-drawer-btn-primary  { background: #007aff; color: #fff; }
    .crm-drawer-btn-primary:hover { background: #0062cc; }
    .crm-drawer-btn-danger   { background: #fff0f0; color: #ff3b30; border: 1px solid #ffc9c7; }
    .crm-drawer-btn-danger:hover { background: #ffdede; }

    /* ══════════════════════════════════════════════════
       SHEET MODAL  (Add / Edit Contact, Add Lead)
    ══════════════════════════════════════════════════ */
    .crm-sheet-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10010;
        background: rgba(0,0,0,.4);
        align-items: flex-end;
        justify-content: center;
        padding: 0;
    }
    .crm-sheet-overlay.open { display: flex; }

    .crm-sheet {
        background: #fff;
        border-radius: 20px 20px 0 0;
        width: 100%;
        max-width: 600px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        transform: translateY(100%);
        transition: transform .3s cubic-bezier(.4,0,.2,1);
        overflow: hidden;
    }
    .crm-sheet-overlay.open .crm-sheet { transform: translateY(0); }

    .crm-sheet-handle {
        width: 36px; height: 4px;
        border-radius: 2px;
        background: #d1d1d6;
        margin: 10px auto 0;
        flex-shrink: 0;
    }

    .crm-sheet-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px 10px;
        flex-shrink: 0;
    }
    .crm-sheet-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1e; }

    .crm-sheet-cancel {
        font-size: 16px;
        font-weight: 400;
        color: #007aff;
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }
    .crm-sheet-save {
        font-size: 16px;
        font-weight: 600;
        color: #007aff;
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }
    .crm-sheet-save:disabled { color: #aeaeb2; }

    .crm-sheet-body {
        flex: 1;
        overflow-y: auto;
        padding: 8px 16px 20px;
    }

    /* Grouped form sections (iOS-style) */
    .crm-form-group-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #8e8e93;
        margin: 20px 0 6px 4px;
    }
    .crm-form-card {
        background: #fff;
        border-radius: 13px;
        border: 1px solid #e5e5ea;
        overflow: hidden;
        margin-bottom: 2px;
    }
    .crm-form-field {
        display: flex;
        align-items: center;
        padding: 0 14px;
        border-bottom: 1px solid #f2f2f7;
        min-height: 44px;
        gap: 12px;
    }
    .crm-form-field:last-child { border-bottom: none; }
    .crm-form-field label {
        font-size: 14px;
        font-weight: 500;
        color: #1c1c1e;
        flex: 0 0 110px;
        padding: 11px 0;
    }
    .crm-form-field input,
    .crm-form-field select,
    .crm-form-field textarea {
        flex: 1;
        border: none;
        outline: none;
        font-size: 14px;
        color: #1c1c1e;
        background: transparent;
        padding: 11px 0;
        font-family: inherit;
        -webkit-font-smoothing: antialiased;
    }
    .crm-form-field input::placeholder,
    .crm-form-field textarea::placeholder { color: #c7c7cc; }
    .crm-form-field select { color: #1c1c1e; cursor: pointer; }
    .crm-form-field textarea { resize: none; min-height: 80px; padding-top: 11px; align-self: flex-start; }

    .crm-form-field-msg {
        padding: 0 16px 10px;
        font-size: 12px;
    }
    .crm-form-field-msg.error { color: #ff3b30; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .crm-c-list-header,
        .crm-c-row { grid-template-columns: 2fr 1fr 80px 64px; }
        .crm-c-list-header span:nth-child(2),
        .crm-c-row > .crm-c-cell:nth-child(2) { display: none; }
        .crm-c-list-header span:nth-child(3),
        .crm-c-row > .crm-c-cell:nth-child(3) { display: none; }
        .crm-drawer { width: 100vw; }
    }
    </style>

    <div class="crm-c">

        <!-- ── Toolbar ── -->
        <div class="crm-c-toolbar">
            <div class="crm-c-search-wrap">
                <svg class="search-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="crm-c-search" class="crm-c-search" placeholder="Search contacts…" autocomplete="off">
            </div>
            <div class="crm-c-toolbar-right">
                <button class="crm-c-filter-pill active" data-status="">All</button>
                <button class="crm-c-filter-pill" data-status="active">Active</button>
                <button class="crm-c-filter-pill" data-status="inactive">Inactive</button>
                <button class="crm-c-add-btn" onclick="crmCOpenAddSheet()">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    New Contact
                </button>
            </div>
        </div>

        <!-- ── Contact List ── -->
        <div class="crm-c-list" id="crm-c-list">
            <div class="crm-c-list-header" aria-hidden="true">
                <span>Name</span>
                <span>Email</span>
                <span>Phone</span>
                <span>Company</span>
                <span>Status</span>
                <span style="text-align:right;">Actions</span>
            </div>

            <?php if (empty($contacts)): ?>
            <div class="crm-c-empty">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4" stroke-width="1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <h3>No contacts yet</h3>
                <p>Add your first contact to get started.</p>
            </div>
            <?php else:
                $avatar_colors = ['av-blue','av-green','av-orange','av-purple','av-pink','av-teal'];
                $i = 0;
                foreach ($contacts as $c):
                    $initials = strtoupper(substr($c->first_name,0,1) . substr($c->last_name,0,1));
                    $av_class = $avatar_colors[$i % count($avatar_colors)];
                    $i++;
                    $row_data = json_encode([
                        'rand_id'    => $c->rand_id,
                        'first_name' => $c->first_name,
                        'last_name'  => $c->last_name,
                        'email'      => $c->email,
                        'phone'      => $c->phone,
                        'company'    => $c->company,
                        'status'     => $c->status,
                        'notes'      => $c->notes,
                        'initials'   => $initials,
                        'av_class'   => $av_class,
                        'created_at' => $c->created_at,
                    ]);
            ?>
            <div class="crm-c-row"
                 data-search="<?php echo esc_attr(strtolower($c->first_name . ' ' . $c->last_name . ' ' . $c->email . ' ' . $c->company)); ?>"
                 data-status="<?php echo esc_attr($c->status); ?>"
                 onclick="crmCOpenDrawer(<?php echo esc_attr($row_data); ?>)"
                 role="row" tabindex="0"
                 onkeydown="if(event.key==='Enter')crmCOpenDrawer(<?php echo esc_attr($row_data); ?>)">

                <!-- Name + avatar -->
                <div class="crm-c-name-cell">
                    <div class="crm-c-avatar <?php echo esc_attr($av_class); ?>" aria-hidden="true"><?php echo esc_html($initials); ?></div>
                    <div style="min-width:0;">
                        <div class="crm-c-name"><?php echo esc_html($c->first_name . ' ' . $c->last_name); ?></div>
                        <?php if ($c->company): ?><div class="crm-c-company"><?php echo esc_html($c->company); ?></div><?php endif; ?>
                    </div>
                </div>

                <!-- Email -->
                <div class="crm-c-cell <?php echo $c->email ? '' : 'muted'; ?>">
                    <?php echo esc_html($c->email ?: '—'); ?>
                </div>

                <!-- Phone -->
                <div class="crm-c-cell <?php echo $c->phone ? '' : 'muted'; ?>">
                    <?php echo esc_html($c->phone ?: '—'); ?>
                </div>

                <!-- Company -->
                <div class="crm-c-cell <?php echo $c->company ? '' : 'muted'; ?>">
                    <?php echo esc_html($c->company ?: '—'); ?>
                </div>

                <!-- Status -->
                <div>
                    <span class="crm-c-badge crm-c-badge-<?php echo esc_attr($c->status); ?>"><?php echo esc_html(ucfirst($c->status)); ?></span>
                </div>

                <!-- Actions -->
                <div class="crm-c-actions" onclick="event.stopPropagation()">
                    <button class="crm-c-icon-btn"
                            title="Edit contact"
                            aria-label="Edit <?php echo esc_attr($c->first_name . ' ' . $c->last_name); ?>"
                            onclick="crmCOpenEditSheet(<?php echo esc_attr($row_data); ?>)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button class="crm-c-icon-btn danger"
                            title="Delete contact"
                            aria-label="Delete <?php echo esc_attr($c->first_name . ' ' . $c->last_name); ?>"
                            onclick="crmCDelete('<?php echo esc_attr($c->rand_id); ?>', this)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div><!-- /.crm-c-list -->

    </div><!-- /.crm-c -->


    <!-- ══════════════════════════════════════════════════
         DETAIL DRAWER
    ══════════════════════════════════════════════════ -->
    <div class="crm-drawer-overlay" id="crm-drawer-overlay" onclick="crmCCloseDrawer()"></div>
    <aside class="crm-drawer" id="crm-drawer" role="dialog" aria-modal="true" aria-label="Contact details">
        <div class="crm-drawer-topbar">
            <h2 id="crm-drawer-title">Contact</h2>
            <button class="crm-drawer-close" onclick="crmCCloseDrawer()" aria-label="Close">✕</button>
        </div>
        <div class="crm-drawer-body" id="crm-drawer-body">
            <p style="color:#8e8e93;font-size:14px;">Loading…</p>
        </div>
        <div class="crm-drawer-footer">
            <button class="crm-drawer-btn crm-drawer-btn-primary" id="crm-drawer-edit-btn">Edit Contact</button>
            <button class="crm-drawer-btn crm-drawer-btn-danger" id="crm-drawer-del-btn">Delete</button>
        </div>
    </aside>


    <!-- ══════════════════════════════════════════════════
         ADD CONTACT SHEET
    ══════════════════════════════════════════════════ -->
    <div class="crm-sheet-overlay" id="crm-add-sheet" role="dialog" aria-modal="true" aria-label="Add contact">
        <div class="crm-sheet">
            <div class="crm-sheet-handle" aria-hidden="true"></div>
            <div class="crm-sheet-header">
                <button class="crm-sheet-cancel" onclick="crmCCloseSheet('crm-add-sheet')">Cancel</button>
                <h3>New Contact</h3>
                <button class="crm-sheet-save" id="crm-add-save" onclick="crmCSubmitAdd()">Add</button>
            </div>
            <div class="crm-sheet-body">

                <div class="crm-form-group-label">Name</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="add-fn">First Name</label>
                        <input id="add-fn" type="text" placeholder="Required" autocomplete="given-name">
                    </div>
                    <div class="crm-form-field">
                        <label for="add-ln">Last Name</label>
                        <input id="add-ln" type="text" placeholder="Required" autocomplete="family-name">
                    </div>
                </div>

                <div class="crm-form-group-label">Contact Info</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="add-em">Email</label>
                        <input id="add-em" type="email" placeholder="Optional" autocomplete="email">
                    </div>
                    <div class="crm-form-field">
                        <label for="add-ph">Phone</label>
                        <input id="add-ph" type="tel" placeholder="Optional" autocomplete="tel">
                    </div>
                    <div class="crm-form-field">
                        <label for="add-co">Company</label>
                        <input id="add-co" type="text" placeholder="Optional" autocomplete="organization">
                    </div>
                </div>

                <div class="crm-form-group-label">Notes</div>
                <div class="crm-form-card">
                    <div class="crm-form-field" style="align-items:flex-start;">
                        <textarea id="add-notes" placeholder="Any relevant details…" rows="3"></textarea>
                    </div>
                </div>

                <div id="add-msg" class="crm-form-field-msg error" style="display:none;"></div>

            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         EDIT CONTACT SHEET
    ══════════════════════════════════════════════════ -->
    <div class="crm-sheet-overlay" id="crm-edit-sheet" role="dialog" aria-modal="true" aria-label="Edit contact">
        <div class="crm-sheet">
            <div class="crm-sheet-handle" aria-hidden="true"></div>
            <div class="crm-sheet-header">
                <button class="crm-sheet-cancel" onclick="crmCCloseSheet('crm-edit-sheet')">Cancel</button>
                <h3>Edit Contact</h3>
                <button class="crm-sheet-save" id="crm-edit-save" onclick="crmCSubmitEdit()">Save</button>
            </div>
            <div class="crm-sheet-body">
                <input type="hidden" id="edit-rand-id">

                <div class="crm-form-group-label">Name</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="edit-fn">First Name</label>
                        <input id="edit-fn" type="text" placeholder="Required">
                    </div>
                    <div class="crm-form-field">
                        <label for="edit-ln">Last Name</label>
                        <input id="edit-ln" type="text" placeholder="Required">
                    </div>
                </div>

                <div class="crm-form-group-label">Contact Info</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="edit-em">Email</label>
                        <input id="edit-em" type="email" placeholder="Optional">
                    </div>
                    <div class="crm-form-field">
                        <label for="edit-ph">Phone</label>
                        <input id="edit-ph" type="tel" placeholder="Optional">
                    </div>
                    <div class="crm-form-field">
                        <label for="edit-co">Company</label>
                        <input id="edit-co" type="text" placeholder="Optional">
                    </div>
                </div>

                <div class="crm-form-group-label">Status</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="edit-st">Status</label>
                        <select id="edit-st">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="crm-form-group-label">Notes</div>
                <div class="crm-form-card">
                    <div class="crm-form-field" style="align-items:flex-start;">
                        <textarea id="edit-notes" rows="3" placeholder="Any relevant details…"></textarea>
                    </div>
                </div>

                <div id="edit-msg" class="crm-form-field-msg error" style="display:none;"></div>

            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         ADD LEAD SHEET  (opened from contact drawer)
    ══════════════════════════════════════════════════ -->
    <div class="crm-sheet-overlay" id="crm-lead-sheet" role="dialog" aria-modal="true" aria-label="Add lead">
        <div class="crm-sheet">
            <div class="crm-sheet-handle" aria-hidden="true"></div>
            <div class="crm-sheet-header">
                <button class="crm-sheet-cancel" onclick="crmCCloseSheet('crm-lead-sheet')">Cancel</button>
                <h3>New Lead</h3>
                <button class="crm-sheet-save" id="crm-lead-save" onclick="crmCSubmitLead()">Add</button>
            </div>
            <div class="crm-sheet-body">
                <input type="hidden" id="lead-contact-id">

                <div class="crm-form-group-label">Lead Details</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="lead-title">Title</label>
                        <input id="lead-title" type="text" placeholder="e.g. Website Redesign">
                    </div>
                    <div class="crm-form-field">
                        <label for="lead-value">Value</label>
                        <input id="lead-value" type="number" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>

                <div class="crm-form-group-label">Pipeline</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="lead-stage">Stage</label>
                        <select id="lead-stage">
                            <?php foreach ($pipeline_stages as $s): ?>
                            <option value="<?php echo esc_attr($s); ?>"><?php echo esc_html(ucfirst($s)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="crm-form-field">
                        <label for="lead-priority">Priority</label>
                        <select id="lead-priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="crm-form-field">
                        <label for="lead-pipeline-type">Pipeline Type</label>
                        <select id="lead-pipeline-type">
                            <option value="sales">Sales</option>
                            <option value="partnership">Partnership</option>
                            <option value="renewal">Renewal</option>
                            <option value="upsell">Upsell</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="crm-form-group-label">Source &amp; Product</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="lead-source">Lead Source</label>
                        <select id="lead-source">
                            <option value="">— Select —</option>
                            <option value="website">Website</option>
                            <option value="referral">Referral</option>
                            <option value="social">Social Media</option>
                            <option value="email">Email Campaign</option>
                            <option value="event">Event / Trade Show</option>
                            <option value="cold_outreach">Cold Outreach</option>
                            <option value="partner">Partner</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="crm-form-field">
                        <label for="lead-product">Product Type</label>
                        <select id="lead-product">
                            <option value="">— Select —</option>
                            <option value="saas">SaaS / Software</option>
                            <option value="consulting">Consulting</option>
                            <option value="hardware">Hardware</option>
                            <option value="service">Service</option>
                            <option value="license">License</option>
                            <option value="support">Support Plan</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="crm-form-group-label">Timeline &amp; Status</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="lead-close">Expected Close</label>
                        <input id="lead-close" type="date">
                    </div>
                    <div class="crm-form-field">
                        <label for="lead-status">Status</label>
                        <select id="lead-status">
                            <option value="open">Open</option>
                            <option value="won">Won</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>
                </div>

                <div class="crm-form-group-label">Notes</div>
                <div class="crm-form-card">
                    <div class="crm-form-field" style="align-items:flex-start;">
                        <textarea id="lead-notes" rows="3" placeholder="Deal context, requirements…"></textarea>
                    </div>
                </div>

                <div class="crm-form-group-label">Note Priority</div>
                <div class="crm-form-card">
                    <div class="crm-form-field">
                        <label for="lead-note-priority">Note Priority</label>
                        <select id="lead-note-priority">
                            <option value="low">Low — FYI only</option>
                            <option value="medium" selected>Medium — Review when possible</option>
                            <option value="high">High — Action required</option>
                        </select>
                    </div>
                </div>

                <div id="lead-msg" class="crm-form-field-msg error" style="display:none;"></div>

            </div>
        </div>
    </div>


    <!-- ══════════════════════════════════════════════════
         JAVASCRIPT
    ══════════════════════════════════════════════════ -->
    <script>
    (function() {

        // ── Search & filter ──────────────────────────────
        var searchEl = document.getElementById('crm-c-search');
        var pills    = document.querySelectorAll('.crm-c-filter-pill');
        var activeStatus = '';

        function filterList() {
            var q = searchEl ? searchEl.value.toLowerCase() : '';
            document.querySelectorAll('#crm-c-list .crm-c-row').forEach(function(r) {
                var nameMatch   = r.getAttribute('data-search').includes(q);
                var statusMatch = !activeStatus || r.getAttribute('data-status') === activeStatus;
                r.style.display = (nameMatch && statusMatch) ? '' : 'none';
            });
        }

        if (searchEl) searchEl.addEventListener('input', filterList);

        pills.forEach(function(pill) {
            pill.addEventListener('click', function() {
                pills.forEach(function(p){ p.classList.remove('active'); });
                pill.classList.add('active');
                activeStatus = pill.getAttribute('data-status');
                filterList();
            });
        });

        // ── Sheet helpers ────────────────────────────────
        window.crmCOpenSheet  = function(id) {
            var el = document.getElementById(id);
            if (el) { el.classList.add('open'); document.body.style.overflow = 'hidden'; }
        };
        window.crmCCloseSheet = function(id) {
            var el = document.getElementById(id);
            if (el) { el.classList.remove('open'); document.body.style.overflow = ''; }
        };

        // ── Drawer helpers ───────────────────────────────
        window.crmCOpenDrawer  = function(data) {
            var drawer = document.getElementById('crm-drawer');
            var overlay = document.getElementById('crm-drawer-overlay');
            document.getElementById('crm-drawer-title').textContent = data.first_name + ' ' + data.last_name;
            overlay.classList.add('open');
            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';

            // Wire footer buttons
            document.getElementById('crm-drawer-edit-btn').onclick = function() {
                crmCCloseDrawer();
                crmCOpenEditSheet(data);
            };
            document.getElementById('crm-drawer-del-btn').onclick = function() {
                crmCDelete(data.rand_id, this);
            };

            // Render drawer body immediately with local data then fetch more
            renderDrawerLocal(data);
            fetchDrawerData(data);
        };
        window.crmCCloseDrawer = function() {
            document.getElementById('crm-drawer').classList.remove('open');
            document.getElementById('crm-drawer-overlay').classList.remove('open');
            document.body.style.overflow = '';
        };

        function renderDrawerLocal(data) {
            var stageColors = {
                new:'#007aff', contacted:'#ff9500', qualified:'#34c759',
                won:'#30d158', lost:'#ff3b30'
            };
            var html = '';

            // Profile card
            html += '<div class="crm-dc">'
                 + '<div class="crm-dc-profile">'
                 + '<div class="crm-dc-avatar-lg ' + escAttr(data.av_class) + '">' + escHtml(data.initials) + '</div>'
                 + '<div class="crm-dc-info"><h3>' + escHtml(data.first_name + ' ' + data.last_name) + '</h3>'
                 + '<p>' + (data.company ? escHtml(data.company) : '<span style="color:#c7c7cc">No company</span>') + '</p>'
                 + '</div></div>';

            // Status
            html += '<div class="crm-dc-row">'
                 + '<svg class="crm-dc-row-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>'
                 + '<span class="crm-dc-row-label">Status</span>'
                 + '<span class="crm-dc-row-val"><span class="crm-c-badge crm-c-badge-' + escAttr(data.status) + '">' + escHtml(ucfirst(data.status)) + '</span></span>'
                 + '</div>';

            // Email
            html += drawerRow('M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','Email', data.email || null);
            // Phone
            html += drawerRow('M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z','Phone', data.phone || null);
            html += '</div>';

            // Notes card
            html += '<div class="crm-dc"><div class="crm-dc-head"><h4>Notes</h4></div>'
                 + '<div class="crm-dc-notes' + (data.notes ? '' : ' empty') + '">' + (data.notes ? escHtml(data.notes) : 'No notes added') + '</div>'
                 + '</div>';

            // Leads placeholder
            html += '<div class="crm-dc" id="crm-drawer-leads">'
                 + '<div class="crm-dc-head"><h4>Leads</h4>'
                 + '<button class="crm-dc-edit-btn" onclick="crmCOpenLeadSheet(\'' + escAttr(data.rand_id) + '\')">+ Add Lead</button>'
                 + '</div>'
                 + '<div style="padding:12px 16px;color:#8e8e93;font-size:13px;" id="crm-drawer-leads-inner">Loading…</div>'
                 + '</div>';

            // Interactions placeholder
            html += '<div class="crm-dc" id="crm-drawer-ints">'
                 + '<div class="crm-dc-head"><h4>Recent Interactions</h4></div>'
                 + '<div style="padding:12px 16px;color:#8e8e93;font-size:13px;" id="crm-drawer-ints-inner">Loading…</div>'
                 + '</div>';

            document.getElementById('crm-drawer-body').innerHTML = html;
        }

        function fetchDrawerData(data) {
            var fd = new FormData();
            fd.append('action', 'crm_get_contact');
            fd.append('nonce', crm_nonce);
            fd.append('rand_id', data.rand_id);
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    if (!d.success) return;
                    var leads  = d.data.leads;
                    var ints   = d.data.interactions;
                    var stageColors = {
                        new:'#007aff', contacted:'#ff9500', qualified:'#ff9f0a',
                        won:'#34c759', lost:'#ff3b30'
                    };

                    // Render leads
                    var leadsEl = document.getElementById('crm-drawer-leads-inner');
                    if (leadsEl) {
                        if (!leads.length) {
                            leadsEl.innerHTML = '<span style="color:#c7c7cc;font-style:italic;">No leads yet</span>';
                        } else {
                            var lhtml = '';
                            leads.forEach(function(l) {
                                var col = stageColors[l.stage] || '#8e8e93';
                                lhtml += '<div class="crm-dc-lead-item">'
                                    + '<div class="crm-dc-lead-dot" style="background:' + col + ';"></div>'
                                    + '<span class="crm-dc-lead-name">' + escHtml(l.title) + '</span>'
                                    + '<span class="crm-dc-stage-pill" style="background:' + col + '22;color:' + col + ';">' + escHtml(l.stage) + '</span>'
                                    + '</div>';
                            });
                            leadsEl.outerHTML = lhtml;
                            // outerHTML replacement: also remove the wrapping div
                            var wrap = document.getElementById('crm-drawer-leads-inner');
                            if (wrap) wrap.outerHTML = lhtml;
                        }
                    }

                    // Render interactions
                    var intsEl = document.getElementById('crm-drawer-ints-inner');
                    if (intsEl) {
                        if (!ints.length) {
                            intsEl.innerHTML = '<span style="color:#c7c7cc;font-style:italic;">No interactions logged</span>';
                        } else {
                            var typeConfig = {
                                call:    { color:'#7c3aed', icon:'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z' },
                                email:   { color:'#0891b2', icon:'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
                                meeting: { color:'#c2410c', icon:'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
                                note:    { color:'#15803d', icon:'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
                            };
                            var ihtml = '';
                            ints.forEach(function(i) {
                                var tc = typeConfig[i.type] || typeConfig.note;
                                ihtml += '<div class="crm-dc-int-item">'
                                    + '<div class="crm-dc-int-icon" style="background:' + tc.color + '18;">'
                                    + '<svg width="14" height="14" fill="none" stroke="' + tc.color + '" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + tc.icon + '"/></svg>'
                                    + '</div>'
                                    + '<div><div class="crm-dc-int-subj">' + escHtml(i.subject) + '</div>'
                                    + '<div class="crm-dc-int-meta">' + escHtml(ucfirst(i.type)) + ' · ' + escHtml(i.interaction_date) + '</div></div>'
                                    + '</div>';
                            });
                            intsEl.outerHTML = ihtml;
                            var wrap2 = document.getElementById('crm-drawer-ints-inner');
                            if (wrap2) wrap2.outerHTML = ihtml;
                        }
                    }
                });
        }

        function drawerRow(iconPath, label, val) {
            return '<div class="crm-dc-row">'
                 + '<svg class="crm-dc-row-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + iconPath + '"/></svg>'
                 + '<span class="crm-dc-row-label">' + label + '</span>'
                 + '<span class="crm-dc-row-val' + (val ? '' : ' empty') + '">' + (val ? escHtml(val) : 'Not set') + '</span>'
                 + '</div>';
        }

        // ── Lead sheet ────────────────────────────────
        window.crmCOpenLeadSheet = function(rand_id) {
            // We need the DB contact id — store it on the hidden field via rand_id lookup
            document.getElementById('lead-contact-id').value = rand_id; // will be resolved server-side via rand_id
            document.getElementById('lead-title').value   = '';
            document.getElementById('lead-value').value   = '';
            document.getElementById('lead-notes').value   = '';
            document.getElementById('lead-msg').style.display = 'none';
            crmCOpenSheet('crm-lead-sheet');
        };

        window.crmCSubmitLead = function() {
            var btn       = document.getElementById('crm-lead-save');
            var title     = document.getElementById('lead-title').value.trim();
            var contactRandId = document.getElementById('lead-contact-id').value;
            var msgEl     = document.getElementById('lead-msg');
            if (!title) { msgEl.textContent = 'Lead title is required.'; msgEl.style.display = 'block'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action',          'crm_add_lead');
            fd.append('nonce',           crm_nonce);
            fd.append('contact_rand_id', contactRandId);
            fd.append('title',           title);
            fd.append('value',           document.getElementById('lead-value').value || 0);
            fd.append('stage',           document.getElementById('lead-stage').value);
            fd.append('priority',        document.getElementById('lead-priority').value);
            fd.append('pipeline_type',   document.getElementById('lead-pipeline-type').value);
            fd.append('lead_source',     document.getElementById('lead-source').value);
            fd.append('product_type',    document.getElementById('lead-product').value);
            fd.append('expected_close',  document.getElementById('lead-close').value);
            fd.append('status',          document.getElementById('lead-status').value);
            fd.append('notes',           document.getElementById('lead-notes').value.trim());
            fd.append('note_priority',   document.getElementById('lead-note-priority').value);
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Lead added!','success'); crmCCloseSheet('crm-lead-sheet'); location.reload(); }
                    else { msgEl.textContent = d.data.message; msgEl.style.display = 'block'; }
                })
                .catch(function() { btn.disabled = false; crmShowToast('Request failed.','error'); });
        };

        // ── Add Contact ───────────────────────────────
        window.crmCOpenAddSheet = function() {
            ['add-fn','add-ln','add-em','add-ph','add-co','add-notes'].forEach(function(id){
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('add-msg').style.display = 'none';
            crmCOpenSheet('crm-add-sheet');
        };

        window.crmCSubmitAdd = function() {
            var btn   = document.getElementById('crm-add-save');
            var fn    = document.getElementById('add-fn').value.trim();
            var ln    = document.getElementById('add-ln').value.trim();
            var msgEl = document.getElementById('add-msg');
            if (!fn || !ln) { msgEl.textContent = 'First and last name are required.'; msgEl.style.display = 'block'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action',      'crm_add_contact');
            fd.append('nonce',       crm_nonce);
            fd.append('first_name',  fn);
            fd.append('last_name',   ln);
            fd.append('email',       document.getElementById('add-em').value.trim());
            fd.append('phone',       document.getElementById('add-ph').value.trim());
            fd.append('company',     document.getElementById('add-co').value.trim());
            fd.append('notes',       document.getElementById('add-notes').value.trim());
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Contact added!','success'); crmCCloseSheet('crm-add-sheet'); location.reload(); }
                    else { msgEl.textContent = d.data.message; msgEl.style.display = 'block'; }
                })
                .catch(function() { btn.disabled = false; crmShowToast('Request failed.','error'); });
        };

        // ── Edit Contact ──────────────────────────────
        window.crmCOpenEditSheet = function(data) {
            document.getElementById('edit-rand-id').value = data.rand_id;
            document.getElementById('edit-fn').value    = data.first_name;
            document.getElementById('edit-ln').value    = data.last_name;
            document.getElementById('edit-em').value    = data.email;
            document.getElementById('edit-ph').value    = data.phone;
            document.getElementById('edit-co').value    = data.company;
            document.getElementById('edit-st').value    = data.status;
            document.getElementById('edit-notes').value = data.notes || '';
            document.getElementById('edit-msg').style.display = 'none';
            crmCOpenSheet('crm-edit-sheet');
        };

        window.crmCSubmitEdit = function() {
            var btn   = document.getElementById('crm-edit-save');
            var fn    = document.getElementById('edit-fn').value.trim();
            var ln    = document.getElementById('edit-ln').value.trim();
            var msgEl = document.getElementById('edit-msg');
            if (!fn || !ln) { msgEl.textContent = 'First and last name are required.'; msgEl.style.display = 'block'; return; }
            btn.disabled = true;
            var fd = new FormData();
            fd.append('action',      'crm_edit_contact');
            fd.append('nonce',       crm_nonce);
            fd.append('rand_id',     document.getElementById('edit-rand-id').value);
            fd.append('first_name',  fn);
            fd.append('last_name',   ln);
            fd.append('email',       document.getElementById('edit-em').value.trim());
            fd.append('phone',       document.getElementById('edit-ph').value.trim());
            fd.append('company',     document.getElementById('edit-co').value.trim());
            fd.append('status',      document.getElementById('edit-st').value);
            fd.append('notes',       document.getElementById('edit-notes').value.trim());
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    btn.disabled = false;
                    if (d.success) { crmShowToast('Contact updated!','success'); crmCCloseSheet('crm-edit-sheet'); location.reload(); }
                    else { msgEl.textContent = d.data.message; msgEl.style.display = 'block'; }
                })
                .catch(function() { btn.disabled = false; crmShowToast('Request failed.','error'); });
        };

        // ── Delete Contact ────────────────────────────
        window.crmCDelete = function(rand_id, btnEl) {
            if (!confirm('Delete this contact? Their leads and interactions will also be removed.')) return;
            if (btnEl) btnEl.disabled = true;
            var fd = new FormData();
            fd.append('action',  'crm_delete_contact');
            fd.append('nonce',   crm_nonce);
            fd.append('rand_id', rand_id);
            fetch(ajaxurl, { method:'POST', body:fd })
                .then(function(r){ return r.json(); })
                .then(function(d) {
                    if (btnEl) btnEl.disabled = false;
                    if (d.success) { crmShowToast('Contact deleted.','success'); crmCCloseDrawer(); location.reload(); }
                    else { crmShowToast(d.data.message,'error'); }
                })
                .catch(function() { if (btnEl) btnEl.disabled = false; crmShowToast('Request failed.','error'); });
        };

        // ── Utilities ─────────────────────────────────
        function escHtml(str) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(String(str || '')));
            return d.innerHTML;
        }
        function escAttr(str) {
            return String(str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        function ucfirst(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Close sheets on overlay click
        document.querySelectorAll('.crm-sheet-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    overlay.classList.remove('open');
                    document.body.style.overflow = '';
                }
            });
        });

    })();
    </script>

    <?php
    return ob_get_clean();
}

// ============================================================
// NOTE: The Add Lead AJAX handler (bntm_ajax_crm_add_lead) should
// be updated to also accept and store these new fields:
//
//   contact_rand_id  — resolve to contact_id before insert
//   pipeline_type    — VARCHAR(50)  e.g. 'sales','partnership'
//   lead_source      — VARCHAR(50)  e.g. 'website','referral'
//   product_type     — VARCHAR(50)  e.g. 'saas','consulting'
//   note_priority    — VARCHAR(20)  e.g. 'low','medium','high'
//
// Add those columns to the crm_leads table migration:
//   pipeline_type VARCHAR(50) NOT NULL DEFAULT 'sales',
//   lead_source   VARCHAR(100) NOT NULL DEFAULT '',
//   product_type  VARCHAR(100) NOT NULL DEFAULT '',
//   note_priority VARCHAR(20) NOT NULL DEFAULT 'medium',
//
// Update bntm_ajax_crm_add_lead() to:
//   1. Accept contact_rand_id → look up the contact id for this business
//   2. Insert the 4 new fields
// ============================================================
