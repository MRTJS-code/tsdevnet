DROP TABLE IF EXISTS homepage_technology_entries;
DROP TABLE IF EXISTS homepage_technology_groups;
DROP TABLE IF EXISTS homepage_portfolio_items;
DROP TABLE IF EXISTS homepage_testimonials;
DROP TABLE IF EXISTS homepage_certifications;
DROP TABLE IF EXISTS homepage_experience_entries;
DROP TABLE IF EXISTS homepage_documents;
DROP TABLE IF EXISTS site_settings;

CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_key VARCHAR(100) NOT NULL,
    label VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_documents_key (document_key),
    INDEX idx_documents_public_sort (is_public, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE site_settings (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    site_title VARCHAR(255) NOT NULL,
    hero_eyebrow VARCHAR(100) DEFAULT NULL,
    hero_headline VARCHAR(255) NOT NULL,
    hero_subheadline TEXT,
    hero_supporting_text TEXT,
    profile_name VARCHAR(255) DEFAULT NULL,
    profile_role VARCHAR(255) DEFAULT NULL,
    profile_location VARCHAR(255) DEFAULT NULL,
    profile_availability VARCHAR(255) DEFAULT NULL,
    open_to_work TINYINT(1) NOT NULL DEFAULT 0,
    primary_cta_mode VARCHAR(100) NOT NULL DEFAULT 'register_request_chat',
    primary_cta_label VARCHAR(255) NOT NULL,
    primary_cta_url VARCHAR(255) NOT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    contact_phone VARCHAR(100) DEFAULT NULL,
    contact_location VARCHAR(255) DEFAULT NULL,
    footer_heading VARCHAR(255) DEFAULT NULL,
    footer_body TEXT,
    chatbot_teaser_enabled TINYINT(1) NOT NULL DEFAULT 0,
    chatbot_teaser_label VARCHAR(255) DEFAULT NULL,
    headshot_document_id BIGINT UNSIGNED DEFAULT NULL,
    cv_document_id BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_site_settings_headshot_document FOREIGN KEY (headshot_document_id) REFERENCES documents(id) ON DELETE SET NULL,
    CONSTRAINT fk_site_settings_cv_document FOREIGN KEY (cv_document_id) REFERENCES documents(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profile_experience (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    summary_text TEXT,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_profile_experience_active_sort (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profile_experience_highlights (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    experience_id BIGINT UNSIGNED NOT NULL,
    highlight_text TEXT NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_experience_highlights_experience FOREIGN KEY (experience_id) REFERENCES profile_experience(id) ON DELETE CASCADE,
    INDEX idx_profile_experience_highlights_sort (experience_id, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profile_certifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) DEFAULT NULL,
    issued_year SMALLINT UNSIGNED DEFAULT NULL,
    status_text VARCHAR(100) DEFAULT NULL,
    credential_url VARCHAR(255) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_profile_certifications_active_sort (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profile_technology_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_key VARCHAR(100) NOT NULL,
    group_label VARCHAR(255) NOT NULL,
    intro_text TEXT,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_profile_technology_group_key (group_key),
    INDEX idx_profile_technology_groups_active_sort (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE profile_technologies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    technology_group_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    logo_slug VARCHAR(100) DEFAULT NULL,
    logo_path VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_technologies_group FOREIGN KEY (technology_group_id) REFERENCES profile_technology_groups(id) ON DELETE CASCADE,
    INDEX idx_profile_technologies_group_sort (technology_group_id, is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE portfolio_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(150) NOT NULL,
    short_summary TEXT,
    category VARCHAR(100) DEFAULT NULL,
    problem_text TEXT,
    approach_text TEXT,
    outcome_text TEXT,
    tech_text TEXT,
    thumbnail_path VARCHAR(255) DEFAULT NULL,
    repo_url VARCHAR(255) DEFAULT NULL,
    demo_url VARCHAR(255) DEFAULT NULL,
    is_gated TINYINT(1) NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(100) DEFAULT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_portfolio_items_slug (slug),
    INDEX idx_portfolio_items_active_sort (is_active, is_featured, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE testimonials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_text TEXT NOT NULL,
    source_name VARCHAR(255) NOT NULL,
    source_title VARCHAR(255) DEFAULT NULL,
    source_context VARCHAR(255) DEFAULT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_testimonials_active_sort (is_active, is_featured, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
