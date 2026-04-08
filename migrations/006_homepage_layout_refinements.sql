ALTER TABLE content_blocks
    ADD COLUMN homepage_position ENUM('top', 'middle', 'bottom') NOT NULL DEFAULT 'top' AFTER section_key;

UPDATE content_blocks
SET homepage_position = CASE section_key
    WHEN 'grouped_capability_intro' THEN 'middle'
    WHEN 'chatbot_teaser' THEN 'bottom'
    ELSE 'top'
END
WHERE homepage_position = 'top';

ALTER TABLE content_blocks
    ADD INDEX idx_content_blocks_position_sort (is_active, homepage_position, sort_order);
