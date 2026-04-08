CREATE TABLE homepage_modules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(100) NOT NULL,
    module_type VARCHAR(100) NOT NULL,
    eyebrow VARCHAR(100) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    intro_text TEXT,
    anchor_id VARCHAR(100) DEFAULT NULL,
    style_variant VARCHAR(100) DEFAULT NULL,
    group_key VARCHAR(100) DEFAULT NULL,
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

CREATE TABLE module_rich_text_sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_id BIGINT UNSIGNED NOT NULL,
    body_text TEXT,
    cta_label VARCHAR(255) DEFAULT NULL,
    cta_url VARCHAR(255) DEFAULT NULL,
    secondary_cta_label VARCHAR(255) DEFAULT NULL,
    secondary_cta_url VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_module_rich_text_module (module_id),
    CONSTRAINT fk_module_rich_text_module FOREIGN KEY (module_id) REFERENCES homepage_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'executive_summary', 'rich_text', cb.subtitle, cb.title, cb.body_text, 'executive-summary', 'summary', NULL, 10, cb.is_active
FROM content_blocks cb
WHERE cb.section_key = 'homepage_intro'
  AND NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'executive_summary');

INSERT INTO module_rich_text_sections (module_id, body_text)
SELECT hm.id, cb.body_text
FROM homepage_modules hm
INNER JOIN content_blocks cb ON cb.section_key = 'homepage_intro'
WHERE hm.module_key = 'executive_summary'
  AND NOT EXISTS (SELECT 1 FROM module_rich_text_sections mrts WHERE mrts.module_id = hm.id);

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'experience_timeline', 'experience_timeline', 'Experience', 'Condensed timeline', 'Leadership and delivery history in the current homepage flow.', 'experience', 'timeline', NULL, 20, 1
WHERE NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'experience_timeline');

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'certifications', 'certifications', 'Credentials', 'Certifications', 'Professional qualifications and current credentials.', 'certifications', 'cards', NULL, 30, 1
WHERE NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'certifications');

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'technology_groups', 'technology_groups', cb.subtitle, cb.title, cb.body_text, 'technology-groups', 'grouped-capability', NULL, 40, cb.is_active
FROM content_blocks cb
WHERE cb.section_key = 'grouped_capability_intro'
  AND NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'technology_groups');

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'featured_portfolio', 'featured_portfolio', 'Portfolio', 'Featured work', 'Selected initiatives and delivery outcomes.', 'portfolio', 'cards', NULL, 50, 1
WHERE NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'featured_portfolio');

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'testimonials', 'testimonials', 'Testimonials', 'Selected references', 'Short quote cards and social proof.', 'testimonials', 'quotes', NULL, 60, 1
WHERE NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'testimonials');

INSERT INTO homepage_modules (module_key, module_type, eyebrow, title, intro_text, anchor_id, style_variant, group_key, display_order, is_active)
SELECT 'chatbot_teaser', 'cta_info', cb.subtitle, cb.title, cb.body_text, 'chatbot-teaser', 'callout', NULL, 70, cb.is_active
FROM content_blocks cb
WHERE cb.section_key = 'chatbot_teaser'
  AND NOT EXISTS (SELECT 1 FROM homepage_modules hm WHERE hm.module_key = 'chatbot_teaser');

INSERT INTO module_rich_text_sections (module_id, body_text)
SELECT hm.id, cb.body_text
FROM homepage_modules hm
INNER JOIN content_blocks cb ON cb.section_key = 'chatbot_teaser'
WHERE hm.module_key = 'chatbot_teaser'
  AND NOT EXISTS (SELECT 1 FROM module_rich_text_sections mrts WHERE mrts.module_id = hm.id);
