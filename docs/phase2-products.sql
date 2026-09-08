-- ============================================================================
-- Phase 2 PRODUCTS — insert local catalog rows missing on prod (MySQL 8)
-- Run AFTER phase2-schema.sql. BACK UP FIRST. SINGLE-PASTE FORM.
-- ADDITIVE-ONLY: every product INSERT is absent-only by slug. Slugs
-- already on prod are fully SKIPPED (not even names touched). No UPDATE,
-- no DELETE anywhere. New rows: local values with stock forced 0 and
-- image forced NULL (admin uploads on prod; prod images never addressed).
-- Unit rows are added only for products left with zero units.
-- ============================================================================

-- ============ 0. PRE-CHECK (read-only before-photo) ============
SELECT COUNT(*) AS prod_products_before FROM `products`;

-- ============ 1. ENSURE categories (create-only-if-absent, by slug) ============
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES ('Fruits', 'fruits', 'Fresh seasonal fruits picked at their peak ripeness.', 'https://images.unsplash.com/photo-1630492729087-1a33255f5e64?q=80&w=600&auto=format&fit=crop', 1, 1, NOW(), NOW());
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES ('Vegetables', 'vegetables', 'Crisp farm-fresh vegetables delivered daily.', 'https://images.unsplash.com/photo-1589517576004-a198f11bc3a4?q=80&w=600&auto=format&fit=crop', 1, 2, NOW(), NOW());

-- ============ 2. PRODUCTS (absent-only by slug; present slugs skipped whole) ============
SET @s := (SELECT COALESCE(MAX(`sort_order`), 0) FROM `products`);

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Cluster Beans (Gawar/Guar Beans)', 'cluster-beans-gawarguar-beans', 'Fresh Cluster Beans (Gawar/Guar Beans) sourced from trusted local growers.', 'gawar,गवार', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'cluster-beans-gawarguar-beans');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Drumstick (Moringa)', 'drumstick-moringa', 'Fresh Drumstick (Moringa) sourced from trusted local growers.', 'drumstick,सहजन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'drumstick-moringa');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Cauliflower', 'cauliflower', 'Fresh Cauliflower sourced from trusted local growers.', 'gobhi,फूलगोभी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'cauliflower');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Bottle Gourd', 'bottle-gourd', 'Fresh Bottle Gourd sourced from trusted local growers.', 'lauki,लौकी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'bottle-gourd');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Fenugreek Leaves (Methi)', 'fenugreek-leaves-methi', 'Fresh Fenugreek Leaves (Methi) sourced from trusted local growers.', 'methi,मेथी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'fenugreek-leaves-methi');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Coriander Leaves', 'coriander-leaves', 'Fresh Coriander Leaves sourced from trusted local growers.', 'dhaniya,धनिया', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'coriander-leaves');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Onion', 'onion', 'Fresh Onion sourced from trusted local growers.', 'pyaaz,प्याज़', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 1, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'onion');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Agra Potato', 'agra-potato', 'Fresh Agra Potato sourced from trusted local growers.', 'aloo,आलू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'agra-potato');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'West Bengal Hill Potato', 'west-bengal-hill-potato', 'Fresh West Bengal Hill Potato sourced from trusted local growers.', 'pahadi aloo,पहाड़ी आलू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'west-bengal-hill-potato');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Sweet Corn', 'sweet-corn', 'Fresh Sweet Corn sourced from trusted local growers.', 'bhutta,भुट्टा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'sweet-corn');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Green Capsicum', 'green-capsicum', 'Fresh Green Capsicum sourced from trusted local growers.', 'shimla mirch,शिमला मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'green-capsicum');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Tomato', 'tomato', 'Fresh Tomato sourced from trusted local growers.', 'tamatar,टमाटर', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 1, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'tomato');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Taro Root (Arbi/Colocasia)', 'taro-root-arbicolocasia', 'Fresh Taro Root (Arbi/Colocasia) sourced from trusted local growers.', 'arbi,अरबी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'taro-root-arbicolocasia');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Brinjal (Eggplant)', 'brinjal-eggplant', 'Fresh Brinjal (Eggplant) sourced from trusted local growers.', 'baingan,बैंगन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'brinjal-eggplant');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Cucumber', 'cucumber', 'Fresh Cucumber sourced from trusted local growers.', 'kheera,खीरा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'cucumber');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Large Green Chilli', 'large-green-chilli', 'Fresh Large Green Chilli sourced from trusted local growers.', 'mirch,हरी मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'large-green-chilli');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Fresh Ginger', 'fresh-ginger', 'Fresh Fresh Ginger sourced from trusted local growers.', 'adrak,अदरक', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'fresh-ginger');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Old Ginger', 'old-ginger', 'Fresh Old Ginger sourced from trusted local growers.', 'adrak,अदरक', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'old-ginger');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Green Chilli', 'green-chilli', 'Fresh Green Chilli sourced from trusted local growers.', 'hari mirch,हरी मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'green-chilli');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Green Chilli (Bag)', 'green-chilli-bag', 'Fresh Green Chilli (Bag) sourced from trusted local growers.', 'hari mirch,हरी मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'green-chilli-bag');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Pumpkin', 'pumpkin', 'Fresh Pumpkin sourced from trusted local growers.', 'kaddu,कद्दू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'pumpkin');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Cabbage', 'cabbage', 'Fresh Cabbage sourced from trusted local growers.', 'patta gobhi,पत्ता गोभी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'cabbage');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Carrot', 'carrot', 'Fresh Carrot sourced from trusted local growers.', 'gajar,गाजर', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'carrot');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Carrot (Loose)', 'carrot-loose', 'Fresh Carrot (Loose) sourced from trusted local growers.', 'gajar,गाजर', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'carrot-loose');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Sponge Gourd (Galka)', 'sponge-gourd-galka', 'Fresh Sponge Gourd (Galka) sourced from trusted local growers.', 'galka,गलका', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'sponge-gourd-galka');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Papaya', 'papaya', 'Fresh Papaya sourced from trusted local growers.', 'papita,पपीता', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'papaya');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Pointed Gourd (Parwal)', 'pointed-gourd-parwal', 'Fresh Pointed Gourd (Parwal) sourced from trusted local growers.', 'parwal,परवल', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'pointed-gourd-parwal');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Bitter Gourd (Bitter Melon)', 'bitter-gourd-bitter-melon', 'Fresh Bitter Gourd (Bitter Melon) sourced from trusted local growers.', 'karela,करेला', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'bitter-gourd-bitter-melon');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Lemon', 'lemon', 'Fresh Lemon sourced from trusted local growers.', 'nimbu,नींबू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 1, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'lemon');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Garlic', 'garlic', 'Fresh Garlic sourced from trusted local growers.', 'lehsun,लहसुन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'garlic');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Beetroot', 'beetroot', 'Fresh Beetroot sourced from trusted local growers.', 'chukandar,चुकंदर', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'beetroot');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Kakdi (Kheksi)', 'kakdi-kheksi', 'Fresh Kakdi (Kheksi) sourced from trusted local growers.', 'kheksi,खेकसी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'kakdi-kheksi');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Raw Banana', 'raw-banana', 'Fresh Raw Banana sourced from trusted local growers.', 'kela,केला', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'raw-banana');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Kundru (Ivy Gourd)', 'kundru-ivy-gourd', 'Fresh Kundru (Ivy Gourd) sourced from trusted local growers.', 'kundru,कुंदरू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'kundru-ivy-gourd');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Singhi Brinjal (Singhi Bhata)', 'singhi-brinjal-singhi-bhata', 'Fresh Singhi Brinjal (Singhi Bhata) sourced from trusted local growers.', 'baingan,बैंगन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'singhi-brinjal-singhi-bhata');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Banana', 'banana', 'Fresh Banana sourced from trusted local growers.', 'kela,केला', 'dozen', 'piece', 100, NULL, 0, 50, NULL, 1, 1, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'banana');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Naspati', 'naspati', 'Fresh Naspati sourced from trusted local growers.', 'naspati,नाशपाती', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'naspati');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Apple', 'apple', 'Fresh Apple sourced from trusted local growers.', 'seb,सेब', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 1, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'apple');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Anar', 'anar', 'Fresh Anar sourced from trusted local growers.', 'anar,अनार', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'anar');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Amrud', 'amrud', 'Fresh Amrud sourced from trusted local growers.', 'amrud,अमरूद', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'amrud');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Nariyal Pani (Bangalore)', 'nariyal-pani-bangalore', 'Fresh Nariyal Pani (Bangalore) sourced from trusted local growers.', 'nariyal,नारियल', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'nariyal-pani-bangalore');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Nariyal Pani (Kerala)', 'nariyal-pani-kerala', 'Fresh Nariyal Pani (Kerala) sourced from trusted local growers.', 'nariyal,नारियल', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'nariyal-pani-kerala');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Papita', 'papita', 'Fresh Papita sourced from trusted local growers.', 'papita,पपीता', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'papita');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Mango', 'mango', 'Fresh Mango sourced from trusted local growers.', 'aam,आम', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'mango');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Aalu Bukhara', 'aalu-bukhara', 'Fresh Aalu Bukhara sourced from trusted local growers.', 'aloo bukhara,आलू बुखारा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'aalu-bukhara');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Bell Pepper (Shimla Mirch)', 'bell-pepper-shimla-mirch', 'Fresh Bell Pepper (Shimla Mirch) sourced from trusted local growers.', 'shimla mirch,शिमला मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'bell-pepper-shimla-mirch');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Bitter Gourd (Karela)', 'bitter-gourd-karela', 'Fresh Bitter Gourd (Karela) sourced from trusted local growers.', 'karela,करेला', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'bitter-gourd-karela');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Black Yardlong Beans (Barbatti)', 'black-yardlong-beans-barbatti', 'Fresh Black Yardlong Beans (Barbatti) sourced from trusted local growers.', 'barbatti,बरबट्टी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'black-yardlong-beans-barbatti');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Bottle Gourd (Lauki)', 'bottle-gourd-lauki', 'Fresh Bottle Gourd (Lauki) sourced from trusted local growers.', 'lauki,लौकी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'bottle-gourd-lauki');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Broad Beans (Sem/Seem)', 'broad-beans-semseem', 'Fresh Broad Beans (Sem/Seem) sourced from trusted local growers.', 'sem,सेम', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'broad-beans-semseem');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Ginger', 'ginger', 'Fresh Ginger sourced from trusted local growers.', 'adrak,अदरक', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'ginger');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Hill Potato (West Bengal)', 'hill-potato-west-bengal', 'Fresh Hill Potato (West Bengal) sourced from trusted local growers.', 'aloo,आलू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'hill-potato-west-bengal');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Ladyfinger (Okra/Bhindi)', 'ladyfinger-okrabhindi', 'Fresh Ladyfinger (Okra/Bhindi) sourced from trusted local growers.', 'bhindi,भिंडी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'ladyfinger-okrabhindi');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Purple Brinjal (Eggplant)', 'purple-brinjal-eggplant', 'Fresh Purple Brinjal (Eggplant) sourced from trusted local growers.', 'baingan,बैंगन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'purple-brinjal-eggplant');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Radish', 'radish', 'Fresh Radish sourced from trusted local growers.', 'mooli,मूली', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'radish');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Red Amaranth (Lal Bhaji)', 'red-amaranth-lal-bhaji', 'Fresh Red Amaranth (Lal Bhaji) sourced from trusted local growers.', 'lal bhaji,लाल भाजी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'red-amaranth-lal-bhaji');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Round Gourd (Tinda)', 'round-gourd-tinda', 'Fresh Round Gourd (Tinda) sourced from trusted local growers.', 'tinda,टिंडा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'round-gourd-tinda');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'White Yardlong Beans (Barbatti)', 'white-yardlong-beans-barbatti', 'Fresh White Yardlong Beans (Barbatti) sourced from trusted local growers.', 'barbatti,बरबट्टी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'white-yardlong-beans-barbatti');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Onion (Pyaj Bhaji)', 'onion-pyaj-bhaji', 'Fresh Onion (Pyaj Bhaji) sourced from trusted local growers.', 'pyaaz bhaji,प्याज़ भाजी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'onion-pyaj-bhaji');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Brinjal – Gulabi (Pink)', 'brinjal-gulabi-pink', 'Fresh Brinjal – Gulabi (Pink) sourced from trusted local growers.', 'baingan,बैंगन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'brinjal-gulabi-pink');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Kheksi', 'kheksi', 'Fresh Kheksi sourced from trusted local growers.', 'kheksi,खेकसी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'kheksi');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Asparagus imported', 'asparagus-imported', 'Fresh Asparagus imported sourced from trusted local growers.', 'asparagus,एस्पेरेगस', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'asparagus-imported');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Avocado imported', 'avocado-imported', 'Fresh Avocado imported sourced from trusted local growers.', 'avocado,एवोकाडो', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'avocado-imported');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Baby corn punnet', 'baby-corn-punnet', 'Fresh Baby corn punnet sourced from trusted local growers.', 'baby corn,बेबी कॉर्न', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'baby-corn-punnet');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Broccoli with stem', 'broccoli-with-stem', 'Fresh Broccoli with stem sourced from trusted local growers.', 'broccoli,ब्रोकली', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'broccoli-with-stem');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Chinese cabbage', 'chinese-cabbage', 'Fresh Chinese cabbage sourced from trusted local growers.', 'chinese patta gobhi,चाइनीज़ पत्ता गोभी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'chinese-cabbage');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Red cabbage', 'red-cabbage', 'Fresh Red cabbage sourced from trusted local growers.', 'lal patta gobhi,लाल पत्ता गोभी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'red-cabbage');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Red cap/yello A grade', 'red-capyello-a-grade', 'Fresh Red cap/yello A grade sourced from trusted local growers.', 'shimla mirch,शिमला मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'red-capyello-a-grade');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Celery', 'celery', 'Fresh Celery sourced from trusted local growers.', 'celery,सेलेरी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'celery');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Cherry tomato', 'cherry-tomato', 'Fresh Cherry tomato sourced from trusted local growers.', 'cherry tamatar,चेरी टमाटर', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'cherry-tomato');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Thai ginger', 'thai-ginger', 'Fresh Thai ginger sourced from trusted local growers.', 'thai ginger,थाई अदरक', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'thai-ginger');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Pilled garlic', 'pilled-garlic', 'Fresh Pilled garlic sourced from trusted local growers.', 'lehsun,लहसुन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'pilled-garlic');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Basil', 'basil', 'Fresh Basil sourced from trusted local growers.', 'tulsi,तुलसी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'basil');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Parsalay', 'parsalay', 'Fresh Parsalay sourced from trusted local growers.', 'ajmod,अजमोद', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'parsalay');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Rosemary', 'rosemary', 'Fresh Rosemary sourced from trusted local growers.', 'rosemary,रोज़मेरी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'rosemary');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Thayme', 'thayme', 'Fresh Thayme sourced from trusted local growers.', 'thyme,थाइम', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'thayme');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'fruits' LIMIT 1), 'Kafir lime', 'kafir-lime', 'Fresh Kafir lime sourced from trusted local growers.', 'kafir nimbu,काफिर नींबू', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'kafir-lime');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Leek', 'leek', 'Fresh Leek sourced from trusted local growers.', 'leek,लीक', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'leek');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Lemon grass', 'lemon-grass', 'Fresh Lemon grass sourced from trusted local growers.', 'nimbu ghaas,नींबू घास', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'lemon-grass');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Ice burg', 'ice-burg', 'Fresh Ice burg sourced from trusted local growers.', 'iceberg,आइसबर्ग', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'ice-burg');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Lettuce leafy', 'lettuce-leafy', 'Fresh Lettuce leafy sourced from trusted local growers.', 'salad patta,सलाद पत्ता', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'lettuce-leafy');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Roman green', 'roman-green', 'Fresh Roman green sourced from trusted local growers.', 'romaine,रोमेन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'roman-green');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Lolorossa', 'lolorossa', 'Fresh Lolorossa sourced from trusted local growers.', 'lolorossa,लोलोरोसा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'lolorossa');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Mushroom', 'mushroom', 'Fresh Mushroom sourced from trusted local growers.', 'mushroom,मशरूम', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'mushroom');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Pokchoy', 'pokchoy', 'Fresh Pokchoy sourced from trusted local growers.', 'pokchoy,पोकचॉय', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'pokchoy');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Zucchini green', 'zucchini-green', 'Fresh Zucchini green sourced from trusted local growers.', 'zucchini,ज़ुकिनी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'zucchini-green');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Zuccni yellow', 'zuccni-yellow', 'Fresh Zuccni yellow sourced from trusted local growers.', 'zucchini,ज़ुकिनी', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'zuccni-yellow');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Thai chilly', 'thai-chilly', 'Fresh Thai chilly sourced from trusted local growers.', 'thai mirch,थाई मिर्च', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'thai-chilly');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Edible flowers', 'edible-flowers', 'Fresh Edible flowers sourced from trusted local growers.', 'phool,फूल', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'edible-flowers');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Micro green', 'micro-green', 'Fresh Micro green sourced from trusted local growers.', 'microgreen,माइक्रोग्रीन', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'micro-green');

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `alternate_names`, `unit`, `base_unit`, `price`, `mrp`, `current_stock`, `low_stock`, `image`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`)
SELECT (SELECT `id` FROM `categories` WHERE `slug` = 'vegetables' LIMIT 1), 'Chinese cucumber', 'chinese-cucumber', 'Fresh Chinese cucumber sourced from trusted local growers.', 'kheera,खीरा', '1 kg', 'g', 100, NULL, 0, 1000, NULL, 1, 0, (@s := @s + 1), NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `slug` = 'chinese-cucumber');

-- ============ 3. UNIT rows for products left with zero units ============
-- Single 1 kg row priced at the row price. Products already having any
-- unit row are skipped automatically.
INSERT INTO `product_units` (`product_id`, `unit`, `price`, `mrp`, `in_stock`, `sort_order`, `created_at`, `updated_at`)
SELECT `p`.`id`, '1 kg', `p`.`price`, NULL, 1, 0, NOW(), NOW() FROM `products` AS `p`
WHERE `p`.`slug` IN ('cluster-beans-gawarguar-beans', 'drumstick-moringa', 'cauliflower', 'bottle-gourd', 'fenugreek-leaves-methi', 'coriander-leaves', 'onion', 'agra-potato', 'west-bengal-hill-potato', 'sweet-corn', 'green-capsicum', 'tomato', 'taro-root-arbicolocasia', 'brinjal-eggplant', 'cucumber', 'large-green-chilli', 'fresh-ginger', 'old-ginger', 'green-chilli', 'green-chilli-bag', 'pumpkin', 'cabbage', 'carrot', 'carrot-loose', 'sponge-gourd-galka', 'papaya', 'pointed-gourd-parwal', 'bitter-gourd-bitter-melon', 'lemon', 'garlic', 'beetroot', 'kakdi-kheksi', 'raw-banana', 'kundru-ivy-gourd', 'singhi-brinjal-singhi-bhata', 'banana', 'naspati', 'apple', 'anar', 'amrud', 'nariyal-pani-bangalore', 'nariyal-pani-kerala', 'papita', 'mango', 'aalu-bukhara', 'bell-pepper-shimla-mirch', 'bitter-gourd-karela', 'black-yardlong-beans-barbatti', 'bottle-gourd-lauki', 'broad-beans-semseem', 'ginger', 'hill-potato-west-bengal', 'ladyfinger-okrabhindi', 'purple-brinjal-eggplant', 'radish', 'red-amaranth-lal-bhaji', 'round-gourd-tinda', 'white-yardlong-beans-barbatti', 'onion-pyaj-bhaji', 'brinjal-gulabi-pink', 'kheksi', 'asparagus-imported', 'avocado-imported', 'baby-corn-punnet', 'broccoli-with-stem', 'chinese-cabbage', 'red-cabbage', 'red-capyello-a-grade', 'celery', 'cherry-tomato', 'thai-ginger', 'pilled-garlic', 'basil', 'parsalay', 'rosemary', 'thayme', 'kafir-lime', 'leek', 'lemon-grass', 'ice-burg', 'lettuce-leafy', 'roman-green', 'lolorossa', 'mushroom', 'pokchoy', 'zucchini-green', 'zuccni-yellow', 'thai-chilly', 'edible-flowers', 'micro-green', 'chinese-cucumber')
AND NOT EXISTS (SELECT 1 FROM `product_units` AS `pu` WHERE `pu`.`product_id` = `p`.`id`);

-- ============ 4. VERIFY (read-only after-photo) ============
-- Expect: 91 (every local slug present on prod now).
SELECT COUNT(*) AS merged_slugs_present FROM `products` WHERE `slug` IN ('cluster-beans-gawarguar-beans', 'drumstick-moringa', 'cauliflower', 'bottle-gourd', 'fenugreek-leaves-methi', 'coriander-leaves', 'onion', 'agra-potato', 'west-bengal-hill-potato', 'sweet-corn', 'green-capsicum', 'tomato', 'taro-root-arbicolocasia', 'brinjal-eggplant', 'cucumber', 'large-green-chilli', 'fresh-ginger', 'old-ginger', 'green-chilli', 'green-chilli-bag', 'pumpkin', 'cabbage', 'carrot', 'carrot-loose', 'sponge-gourd-galka', 'papaya', 'pointed-gourd-parwal', 'bitter-gourd-bitter-melon', 'lemon', 'garlic', 'beetroot', 'kakdi-kheksi', 'raw-banana', 'kundru-ivy-gourd', 'singhi-brinjal-singhi-bhata', 'banana', 'naspati', 'apple', 'anar', 'amrud', 'nariyal-pani-bangalore', 'nariyal-pani-kerala', 'papita', 'mango', 'aalu-bukhara', 'bell-pepper-shimla-mirch', 'bitter-gourd-karela', 'black-yardlong-beans-barbatti', 'bottle-gourd-lauki', 'broad-beans-semseem', 'ginger', 'hill-potato-west-bengal', 'ladyfinger-okrabhindi', 'purple-brinjal-eggplant', 'radish', 'red-amaranth-lal-bhaji', 'round-gourd-tinda', 'white-yardlong-beans-barbatti', 'onion-pyaj-bhaji', 'brinjal-gulabi-pink', 'kheksi', 'asparagus-imported', 'avocado-imported', 'baby-corn-punnet', 'broccoli-with-stem', 'chinese-cabbage', 'red-cabbage', 'red-capyello-a-grade', 'celery', 'cherry-tomato', 'thai-ginger', 'pilled-garlic', 'basil', 'parsalay', 'rosemary', 'thayme', 'kafir-lime', 'leek', 'lemon-grass', 'ice-burg', 'lettuce-leafy', 'roman-green', 'lolorossa', 'mushroom', 'pokchoy', 'zucchini-green', 'zuccni-yellow', 'thai-chilly', 'edible-flowers', 'micro-green', 'chinese-cucumber');
-- Expect: Empty set (every merged product has at least one unit row).
SELECT `p`.`slug` FROM `products` AS `p` WHERE `p`.`slug` IN ('cluster-beans-gawarguar-beans', 'drumstick-moringa', 'cauliflower', 'bottle-gourd', 'fenugreek-leaves-methi', 'coriander-leaves', 'onion', 'agra-potato', 'west-bengal-hill-potato', 'sweet-corn', 'green-capsicum', 'tomato', 'taro-root-arbicolocasia', 'brinjal-eggplant', 'cucumber', 'large-green-chilli', 'fresh-ginger', 'old-ginger', 'green-chilli', 'green-chilli-bag', 'pumpkin', 'cabbage', 'carrot', 'carrot-loose', 'sponge-gourd-galka', 'papaya', 'pointed-gourd-parwal', 'bitter-gourd-bitter-melon', 'lemon', 'garlic', 'beetroot', 'kakdi-kheksi', 'raw-banana', 'kundru-ivy-gourd', 'singhi-brinjal-singhi-bhata', 'banana', 'naspati', 'apple', 'anar', 'amrud', 'nariyal-pani-bangalore', 'nariyal-pani-kerala', 'papita', 'mango', 'aalu-bukhara', 'bell-pepper-shimla-mirch', 'bitter-gourd-karela', 'black-yardlong-beans-barbatti', 'bottle-gourd-lauki', 'broad-beans-semseem', 'ginger', 'hill-potato-west-bengal', 'ladyfinger-okrabhindi', 'purple-brinjal-eggplant', 'radish', 'red-amaranth-lal-bhaji', 'round-gourd-tinda', 'white-yardlong-beans-barbatti', 'onion-pyaj-bhaji', 'brinjal-gulabi-pink', 'kheksi', 'asparagus-imported', 'avocado-imported', 'baby-corn-punnet', 'broccoli-with-stem', 'chinese-cabbage', 'red-cabbage', 'red-capyello-a-grade', 'celery', 'cherry-tomato', 'thai-ginger', 'pilled-garlic', 'basil', 'parsalay', 'rosemary', 'thayme', 'kafir-lime', 'leek', 'lemon-grass', 'ice-burg', 'lettuce-leafy', 'roman-green', 'lolorossa', 'mushroom', 'pokchoy', 'zucchini-green', 'zuccni-yellow', 'thai-chilly', 'edible-flowers', 'micro-green', 'chinese-cucumber') AND NOT EXISTS (SELECT 1 FROM `product_units` AS `pu` WHERE `pu`.`product_id` = `p`.`id`);
-- Expect: before-count + new arrivals (nothing removed, ever).
SELECT COUNT(*) AS prod_products_after FROM `products`;
