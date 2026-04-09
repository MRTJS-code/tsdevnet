ALTER TABLE documents
    ADD COLUMN document_type VARCHAR(50) NOT NULL DEFAULT 'file' AFTER document_key,
    ADD COLUMN description_text TEXT DEFAULT NULL AFTER label,
    ADD COLUMN external_url VARCHAR(255) DEFAULT NULL AFTER file_path,
    ADD COLUMN file_size_bytes INT UNSIGNED DEFAULT NULL AFTER mime_type;

UPDATE documents
SET document_type = CASE document_key
    WHEN 'headshot' THEN 'headshot'
    WHEN 'cv' THEN 'cv_pdf'
    ELSE 'file'
END
WHERE document_type = 'file';

CREATE TABLE homepage_hero_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    site_title VARCHAR(255) NOT NULL,
    eyebrow VARCHAR(100) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    summary_text TEXT,
    supporting_text TEXT,
    profile_name VARCHAR(255) DEFAULT NULL,
    profile_role VARCHAR(255) DEFAULT NULL,
    profile_location VARCHAR(255) DEFAULT NULL,
    profile_availability VARCHAR(255) DEFAULT NULL,
    open_to_work TINYINT(1) NOT NULL DEFAULT 0,
    cta_mode VARCHAR(100) NOT NULL DEFAULT 'register_request_chat',
    primary_cta_label VARCHAR(255) NOT NULL,
    primary_cta_url VARCHAR(255) NOT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    headshot_document_id BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_homepage_hero_headshot_document FOREIGN KEY (headshot_document_id) REFERENCES documents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE homepage_footer_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    heading VARCHAR(255) DEFAULT NULL,
    body_text TEXT,
    contact_email VARCHAR(255) DEFAULT NULL,
    contact_phone VARCHAR(100) DEFAULT NULL,
    contact_location VARCHAR(255) DEFAULT NULL,
    cv_document_id BIGINT UNSIGNED DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_homepage_footer_cv_document FOREIGN KEY (cv_document_id) REFERENCES documents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO homepage_hero_settings (
    id, site_title, eyebrow, title, summary_text, supporting_text,
    profile_name, profile_role, profile_location, profile_availability,
    open_to_work, cta_mode, primary_cta_label, primary_cta_url,
    secondary_cta_label, secondary_cta_url, headshot_document_id
)
SELECT
    1,
    site_title,
    hero_eyebrow,
    hero_headline,
    hero_subheadline,
    hero_supporting_text,
    profile_name,
    profile_role,
    profile_location,
    profile_availability,
    open_to_work,
    primary_cta_mode,
    primary_cta_label,
    primary_cta_url,
    secondary_cta_label,
    secondary_cta_url,
    headshot_document_id
FROM site_settings
WHERE id = 1
  AND NOT EXISTS (SELECT 1 FROM homepage_hero_settings WHERE id = 1);

INSERT INTO homepage_footer_settings (
    id, heading, body_text, contact_email, contact_phone, contact_location,
    cv_document_id, linkedin_url, github_url
)
SELECT
    1,
    footer_heading,
    footer_body,
    contact_email,
    contact_phone,
    contact_location,
    cv_document_id,
    linkedin_url,
    github_url
FROM site_settings
WHERE id = 1
  AND NOT EXISTS (SELECT 1 FROM homepage_footer_settings WHERE id = 1);

DROP TABLE IF EXISTS module_rich_text_sections;
DROP TABLE IF EXISTS homepage_modules;

CREATE TABLE homepage_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(100) NOT NULL,
    module_type VARCHAR(100) NOT NULL,
    eyebrow VARCHAR(100) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    intro_text TEXT,
    anchor_id VARCHAR(100) DEFAULT NULL,
    style_variant VARCHAR(100) DEFAULT NULL,
    media_document_id BIGINT UNSIGNED DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_homepage_modules_key (module_key),
    INDEX idx_homepage_modules_active_sort (is_active, display_order),
    INDEX idx_homepage_modules_type (module_type),
    CONSTRAINT fk_homepage_modules_media_document FOREIGN KEY (media_document_id) REFERENCES documents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_rich_text_payloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    body_text TEXT,
    primary_cta_label VARCHAR(255) DEFAULT NULL,
    primary_cta_url VARCHAR(255) DEFAULT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_module_rich_text_payloads_module (module_id),
    CONSTRAINT fk_module_rich_text_payloads_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_timeline_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    entry_title VARCHAR(255) NOT NULL,
    entry_subtitle VARCHAR(255) DEFAULT NULL,
    meta_text VARCHAR(255) DEFAULT NULL,
    summary_text TEXT,
    detail_text TEXT,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_timeline_entries_module_sort (module_id, is_active, display_order),
    CONSTRAINT fk_module_timeline_entries_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_timeline_highlights (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    timeline_entry_id BIGINT UNSIGNED NOT NULL,
    highlight_text TEXT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_timeline_highlights_entry_sort (timeline_entry_id, is_active, display_order),
    CONSTRAINT fk_module_timeline_highlights_entry FOREIGN KEY (timeline_entry_id) REFERENCES module_timeline_entries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_pill_card_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    body_text TEXT,
    badge_text VARCHAR(100) DEFAULT NULL,
    link_label VARCHAR(100) DEFAULT NULL,
    link_url VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_pill_cards_module_sort (module_id, is_active, display_order),
    CONSTRAINT fk_module_pill_cards_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_case_study_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    category_text VARCHAR(100) DEFAULT NULL,
    summary_text TEXT,
    outcome_text TEXT,
    detail_text TEXT,
    link_label VARCHAR(100) DEFAULT NULL,
    link_url VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_case_studies_module_sort (module_id, is_active, display_order),
    CONSTRAINT fk_module_case_studies_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_list_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    item_title VARCHAR(255) NOT NULL,
    item_body TEXT,
    item_meta VARCHAR(255) DEFAULT NULL,
    link_label VARCHAR(100) DEFAULT NULL,
    link_url VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_list_items_module_sort (module_id, is_active, display_order),
    CONSTRAINT fk_module_list_items_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_quote_card_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    quote_text TEXT NOT NULL,
    attribution_name VARCHAR(255) NOT NULL,
    attribution_role VARCHAR(255) DEFAULT NULL,
    attribution_context VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_quote_cards_module_sort (module_id, is_active, display_order),
    CONSTRAINT fk_module_quote_cards_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_cta_banner_payloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    body_text TEXT,
    primary_cta_label VARCHAR(255) DEFAULT NULL,
    primary_cta_url VARCHAR(255) DEFAULT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_module_cta_banner_payloads_module (module_id),
    CONSTRAINT fk_module_cta_banner_payloads_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE module_media_text_payloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    body_text TEXT,
    media_position ENUM('left', 'right') NOT NULL DEFAULT 'right',
    primary_cta_label VARCHAR(255) DEFAULT NULL,
    primary_cta_url VARCHAR(255) DEFAULT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_module_media_text_payloads_module (module_id),
    CONSTRAINT fk_module_media_text_payloads_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
