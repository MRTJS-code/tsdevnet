CREATE TABLE admin_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(200) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_users_active_email (is_active, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE content_blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    body_text TEXT,
    meta_json JSON DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_content_blocks_section_key (section_key),
    INDEX idx_content_blocks_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE content_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id BIGINT UNSIGNED NOT NULL,
    item_key VARCHAR(100) DEFAULT NULL,
    label VARCHAR(255) DEFAULT NULL,
    title VARCHAR(255) DEFAULT NULL,
    body_text TEXT,
    link_url VARCHAR(255) DEFAULT NULL,
    meta_json JSON DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_content_items_block_id FOREIGN KEY (block_id) REFERENCES content_blocks(id) ON DELETE CASCADE,
    INDEX idx_content_items_block_sort (block_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assistant_knowledge (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    knowledge_key VARCHAR(100) NOT NULL,
    trigger_type ENUM('contains', 'exact') NOT NULL,
    trigger_value VARCHAR(255) NOT NULL,
    response_text TEXT NOT NULL,
    minimum_access_tier ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    priority INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_assistant_knowledge_active_tier_priority (is_active, minimum_access_tier, priority),
    INDEX idx_assistant_knowledge_key (knowledge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
