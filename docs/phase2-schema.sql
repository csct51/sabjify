-- ============================================================================
-- Phase 2 SCHEMA — match prod database to current local database (MySQL 8)
-- Run FIRST, before phase2-products.sql. BACK UP FIRST.
-- SINGLE-PASTE FORM: every ADD is guarded (absent-only). The human-gated
-- statements (delivery_slot ADD + dead-column DROPs) sit last: an error
-- there means "already done" — everything above already ran.
-- SAFE-BY-DESIGN: no UPDATE/DELETE on business rows, no backfills. Existing
-- products, prices, stock, images, links and admin edits are unreachable.
-- Tables this file touches: info_cards (create), units (canonical 10 names
-- only — custom admin unit rows survive; nothing FKs units.id, verified),
-- order_items (one always-empty dead column), migrations (registrations).
-- ============================================================================

-- ============ 0. PRE-CHECK (read-only before-photo, no action needed) ============
SHOW TABLES LIKE 'info_cards';
SHOW COLUMNS FROM `orders` LIKE 'delivery_slot';
SELECT `name` FROM `units` WHERE `name` IN ('1 kg', '500 g', '250 g', '1 pc', 'dozen', 'bunch', 'kg', 'piece', 'g', 'ml') ORDER BY `name`;
SHOW COLUMNS FROM `order_items` LIKE 'product_unit_id';
SELECT COUNT(*) AS registered_before FROM `migrations` WHERE `migration` LIKE '2026_09%' OR `migration` LIKE '2026_08_31%';

-- ============ 1. CREATE info_cards table (exact mirror of the migration) ============
CREATE TABLE IF NOT EXISTS `info_cards` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `position` TINYINT UNSIGNED NOT NULL UNIQUE,
  `icon` VARCHAR(50) NOT NULL,
  `title` VARCHAR(60) NOT NULL,
  `subtitle` VARCHAR(80) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ 2. CANONICAL units replace (scoped to the 10 reference names) ============
-- Deletes ONLY the 10 canonical rows, then re-inserts all 10 fixed
-- (sort 0-9). Custom admin-added unit rows are never matched, never touched.
-- Nothing references units.id (no FKs anywhere — verified in code), so no
-- other table is affected. Re-runnable: re-running restores the same 10.
DELETE FROM `units` WHERE `name` IN ('1 kg', '500 g', '250 g', '1 pc', 'dozen', 'bunch', 'kg', 'piece', 'g', 'ml');

INSERT INTO `units` (`name`, `base_unit`, `to_base_factor`, `sort_order`, `is_base`, `purchase_unit`, `integer_only`, `created_at`, `updated_at`) VALUES
('1 kg', 'g', 1000, 0, 0, NULL, 0, NOW(), NOW()),
('500 g', 'g', 500, 1, 0, NULL, 0, NOW(), NOW()),
('250 g', 'g', 250, 2, 0, NULL, 0, NOW(), NOW()),
('1 pc', 'piece', 1, 3, 0, NULL, 0, NOW(), NOW()),
('dozen', 'piece', 12, 4, 0, NULL, 0, NOW(), NOW()),
('bunch', 'piece', 1, 5, 0, NULL, 0, NOW(), NOW()),
('kg', 'g', 1000, 6, 0, NULL, 0, NOW(), NOW()),
('piece', NULL, 1, 7, 1, 'piece', 1, NOW(), NOW()),
('g', NULL, 1, 8, 1, 'kg', 0, NOW(), NOW()),
('ml', NULL, 1, 9, 1, 'litre', 0, NOW(), NOW());

-- ============ 3. REGISTER migrations (all self-skipping) ============
INSERT INTO `migrations` (`migration`, `batch`)
SELECT `m`.`name`, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`) FROM (
  SELECT '2026_08_31_112247_add_delivery_slot_to_orders_table' AS `name` UNION ALL
  SELECT '2026_09_08_080001_add_inventory_columns_to_products_table' UNION ALL
  SELECT '2026_09_08_080002_add_inventory_columns_to_order_items_table' UNION ALL
  SELECT '2026_09_08_080003_add_inventory_columns_to_units_table' UNION ALL
  SELECT '2026_09_08_080004_seed_inventory_reference_units' UNION ALL
  SELECT '2026_09_08_090001_create_info_cards_table' AS `name`
) AS `m`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migrations`.`migration` = `m`.`name`);

-- ============ 4. HUMAN-GATED TAIL — read §0 first, errors here are benign ============
-- These two jobs cannot self-skip in plain MySQL, so they sit last: anything
-- erroring here means "already done", and everything above already ran.
-- (a) orders.delivery_slot: RUN only if §0 showed it missing (mirror of the
-- 2026_08_31 migration; AFTER placement cosmetic). SKIP if present.
ALTER TABLE `orders` ADD COLUMN `delivery_slot` VARCHAR(255) NULL;
-- (b) Dead order_items.product_unit_id (+ FK): RUN only if §0 showed the
-- column. Nothing ever wrote to it (verified in code: zero assignments),
-- so dropping loses zero data either way. SKIP if absent.
ALTER TABLE `order_items` DROP FOREIGN KEY `order_items_product_unit_id_foreign`;
ALTER TABLE `order_items` DROP COLUMN `product_unit_id`;

-- ============ 6. VERIFY (read-only after-photo) ============
-- Expect: 1 row (table present; empty is fine — admin fills it).
SHOW TABLES LIKE 'info_cards';
-- Expect: all 10 reference rows with correct config.
SELECT `name`, `base_unit`, `to_base_factor`, `sort_order`, `is_base`, `purchase_unit`, `integer_only` FROM `units` ORDER BY `sort_order`;
-- Expect: Empty set (dead column gone — or already gone, same result).
SHOW COLUMNS FROM `order_items` LIKE 'product_unit_id';
-- Expect: 29 (23 old + delivery_slot + 5 new).
SELECT COUNT(*) AS registered_after FROM `migrations` WHERE `migration` LIKE '2026_09%' OR `migration` LIKE '2026_08_31%';
-- Expect: your product count, UNCHANGED (nothing in this file touches products).
SELECT COUNT(*) AS products FROM `products`;
