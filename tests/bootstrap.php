<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/Support/Autoloader.php';

function test_pdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    if (method_exists($pdo, 'createFunction')) {
        $pdo->createFunction('NOW', static fn (): string => date('Y-m-d H:i:s'));
    } else {
        @$pdo->sqliteCreateFunction('NOW', static fn (): string => date('Y-m-d H:i:s'));
    }

    $schema = <<<'SQL'
CREATE TABLE documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    document_key TEXT NOT NULL UNIQUE,
    document_type TEXT NOT NULL DEFAULT 'file',
    label TEXT NOT NULL,
    description_text TEXT,
    file_path TEXT,
    external_url TEXT,
    mime_type TEXT,
    file_size_bytes INTEGER,
    is_public INTEGER NOT NULL DEFAULT 0,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE site_settings (
    id INTEGER PRIMARY KEY,
    site_title TEXT NOT NULL,
    hero_eyebrow TEXT,
    hero_headline TEXT NOT NULL,
    hero_subheadline TEXT,
    hero_supporting_text TEXT,
    profile_name TEXT,
    profile_role TEXT,
    profile_location TEXT,
    profile_availability TEXT,
    open_to_work INTEGER NOT NULL DEFAULT 0,
    primary_cta_mode TEXT NOT NULL DEFAULT 'register_request_chat',
    primary_cta_label TEXT NOT NULL,
    primary_cta_url TEXT NOT NULL,
    secondary_cta_label TEXT,
    secondary_cta_url TEXT,
    linkedin_url TEXT,
    github_url TEXT,
    contact_email TEXT,
    contact_phone TEXT,
    contact_location TEXT,
    footer_heading TEXT,
    footer_body TEXT,
    chatbot_teaser_enabled INTEGER NOT NULL DEFAULT 0,
    chatbot_teaser_label TEXT,
    headshot_document_id INTEGER,
    cv_document_id INTEGER,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE homepage_hero_settings (
    id INTEGER PRIMARY KEY,
    site_title TEXT NOT NULL,
    eyebrow TEXT,
    title TEXT NOT NULL,
    summary_text TEXT,
    supporting_text TEXT,
    profile_name TEXT,
    profile_role TEXT,
    profile_location TEXT,
    profile_availability TEXT,
    open_to_work INTEGER NOT NULL DEFAULT 0,
    cta_mode TEXT NOT NULL DEFAULT 'register_request_chat',
    primary_cta_label TEXT NOT NULL,
    primary_cta_url TEXT NOT NULL,
    secondary_cta_label TEXT,
    secondary_cta_url TEXT,
    headshot_document_id INTEGER,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE homepage_footer_settings (
    id INTEGER PRIMARY KEY,
    heading TEXT,
    body_text TEXT,
    contact_email TEXT,
    contact_phone TEXT,
    contact_location TEXT,
    cv_document_id INTEGER,
    linkedin_url TEXT,
    github_url TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE homepage_modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_key TEXT NOT NULL UNIQUE,
    module_type TEXT NOT NULL,
    eyebrow TEXT,
    title TEXT,
    intro_text TEXT,
    anchor_id TEXT,
    style_variant TEXT,
    media_document_id INTEGER,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_rich_text_payloads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL UNIQUE,
    body_text TEXT,
    primary_cta_label TEXT,
    primary_cta_url TEXT,
    secondary_cta_label TEXT,
    secondary_cta_url TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
);

CREATE TABLE module_timeline_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    entry_title TEXT NOT NULL,
    entry_subtitle TEXT,
    meta_text TEXT,
    summary_text TEXT,
    detail_text TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_timeline_highlights (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    timeline_entry_id INTEGER NOT NULL,
    highlight_text TEXT NOT NULL,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (timeline_entry_id) REFERENCES module_timeline_entries(id) ON DELETE CASCADE
);

CREATE TABLE module_pill_card_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    body_text TEXT,
    badge_text TEXT,
    link_label TEXT,
    link_url TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_case_study_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    category_text TEXT,
    summary_text TEXT,
    outcome_text TEXT,
    detail_text TEXT,
    link_label TEXT,
    link_url TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_list_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    item_title TEXT NOT NULL,
    item_body TEXT,
    item_meta TEXT,
    link_label TEXT,
    link_url TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_quote_card_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    quote_text TEXT NOT NULL,
    attribution_name TEXT NOT NULL,
    attribution_role TEXT,
    attribution_context TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_cta_banner_payloads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL UNIQUE,
    body_text TEXT,
    primary_cta_label TEXT,
    primary_cta_url TEXT,
    secondary_cta_label TEXT,
    secondary_cta_url TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE module_media_text_payloads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL UNIQUE,
    body_text TEXT,
    media_position TEXT NOT NULL DEFAULT 'right',
    primary_cta_label TEXT,
    primary_cta_url TEXT,
    secondary_cta_label TEXT,
    secondary_cta_url TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE profile_experience (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_title TEXT NOT NULL,
    company_name TEXT NOT NULL,
    start_date TEXT,
    end_date TEXT,
    is_current INTEGER NOT NULL DEFAULT 0,
    summary_text TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE profile_experience_highlights (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    experience_id INTEGER NOT NULL,
    highlight_text TEXT NOT NULL,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE profile_certifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    issuer TEXT,
    issued_year INTEGER,
    status_text TEXT,
    credential_url TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE profile_technology_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_key TEXT NOT NULL UNIQUE,
    group_label TEXT NOT NULL,
    intro_text TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE profile_technologies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    technology_group_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    logo_slug TEXT,
    logo_path TEXT,
    notes TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE portfolio_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    short_summary TEXT,
    category TEXT,
    problem_text TEXT,
    approach_text TEXT,
    outcome_text TEXT,
    tech_text TEXT,
    thumbnail_path TEXT,
    repo_url TEXT,
    demo_url TEXT,
    is_gated INTEGER NOT NULL DEFAULT 0,
    is_featured INTEGER NOT NULL DEFAULT 0,
    status TEXT,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE TABLE testimonials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quote_text TEXT NOT NULL,
    source_name TEXT NOT NULL,
    source_title TEXT,
    source_context TEXT,
    is_featured INTEGER NOT NULL DEFAULT 0,
    display_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
SQL;

    $pdo->exec($schema);

    return $pdo;
}
