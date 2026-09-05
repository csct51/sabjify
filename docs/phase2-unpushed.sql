-- ============================================================================
-- Phase 2 unpushed schema — raw SQL for production (MySQL 8 / MariaDB)
-- Equivalent of all 22 unpushed migrations (nothing uploaded since deaec59).
-- Assumptions: InnoDB, utf8mb4/utf8mb4_unicode_ci, prod is at pushed state.
-- BACK UP FIRST. Run sections in order, exactly once.
-- Either run this file OR `php artisan migrate --force` (files are the
-- source of truth) — never both without Section 8, or migrations replay.
-- "AFTER column" placement from migrations is omitted (cosmetic only).
-- ============================================================================

-- ============ 0. PRE-CHECK (read-only, run first) ============
-- Expect: no suppliers/purchases/wastages tables yet; units WITHOUT
-- base_unit/is_base columns; products WITHOUT current_stock/base_unit.
SHOW TABLES LIKE 'suppliers';
SHOW TABLES LIKE 'purchases';
SHOW TABLES LIKE 'wastages';
SHOW COLUMNS FROM `units` LIKE 'base_unit';
SHOW COLUMNS FROM `products` LIKE 'current_stock';

-- ============ 1. NEW TABLES (final column form) ============
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `contact` VARCHAR(255) NOT NULL,
  `address` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `purchase_number` VARCHAR(255) NOT NULL UNIQUE,
  `supplier_id` BIGINT UNSIGNED NULL,
  `supplier_name` VARCHAR(255) NULL,
  `purchase_date` DATE NOT NULL,
  `remark` TEXT NULL,
  `total_amount` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `purchases_purchase_date_index` (`purchase_date`),
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `unit` VARCHAR(255) NOT NULL,
  `rate` INT UNSIGNED NOT NULL,
  `qty` DECIMAL(10, 3) NOT NULL DEFAULT 0,
  `line_total` INT UNSIGNED NOT NULL,
  `base_qty` DECIMAL(12, 3) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `purchase_items_product_id_purchase_id_index` (`product_id`, `purchase_id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wastages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `wastage_number` VARCHAR(255) NOT NULL UNIQUE,
  `wastage_date` DATE NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `remark` TEXT NULL,
  `total_qty` DECIMAL(12, 3) NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `wastages_wastage_date_index` (`wastage_date`),
  CONSTRAINT `wastages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wastage_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `wastage_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `unit` VARCHAR(255) NOT NULL,
  `qty` DECIMAL(10, 3) NOT NULL,
  `base_qty` DECIMAL(12, 3) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX `wastage_items_product_id_wastage_id_index` (`product_id`, `wastage_id`),
  CONSTRAINT `wastage_items_wastage_id_foreign` FOREIGN KEY (`wastage_id`) REFERENCES `wastages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wastage_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ 2. EXISTING TABLES — new columns (final form) ============
ALTER TABLE `units`
  ADD COLUMN `base_unit` VARCHAR(255) NULL,
  ADD COLUMN `to_base_factor` DECIMAL(10, 4) NOT NULL DEFAULT 1,
  ADD COLUMN `is_base` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `purchase_unit` VARCHAR(255) NULL,
  ADD COLUMN `integer_only` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `products`
  ADD COLUMN `current_stock` DECIMAL(12, 3) NOT NULL DEFAULT 0,
  ADD COLUMN `base_unit` VARCHAR(255) NULL,
  ADD COLUMN `low_stock` DECIMAL(12, 3) NULL;

ALTER TABLE `order_items`
  ADD COLUMN `product_unit_id` BIGINT UNSIGNED NULL,
  ADD COLUMN `base_qty` DECIMAL(10, 3) NULL,
  ADD CONSTRAINT `order_items_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE SET NULL;

-- ============ 3. DATA — unit conversion map (053633) ============
UPDATE `units` SET `base_unit` = 'g', `to_base_factor` = 1000 WHERE `name` = '1 kg';
UPDATE `units` SET `base_unit` = 'g', `to_base_factor` = 500 WHERE `name` = '500 g';
UPDATE `units` SET `base_unit` = 'g', `to_base_factor` = 250 WHERE `name` = '250 g';
UPDATE `units` SET `base_unit` = 'piece', `to_base_factor` = 1 WHERE `name` = '1 pc';
UPDATE `units` SET `base_unit` = 'piece', `to_base_factor` = 1 WHERE `name` = 'piece';
UPDATE `units` SET `base_unit` = 'g', `to_base_factor` = 1000 WHERE `name` = 'kg';
UPDATE `units` SET `base_unit` = 'piece', `to_base_factor` = 12 WHERE `name` = 'dozen';
UPDATE `units` SET `base_unit` = 'piece', `to_base_factor` = 1 WHERE `name` = 'bunch';

-- ============ 4. DATA — purchase-unit reference row (070419) ============
INSERT INTO `units` (`name`, `base_unit`, `to_base_factor`, `sort_order`, `is_base`, `purchase_unit`, `integer_only`, `created_at`, `updated_at`)
SELECT 'kg', 'g', 1000, COALESCE(MAX(`sort_order`), 0) + 1, 0, NULL, 0, NOW(), NOW() FROM `units`
WHERE NOT EXISTS (SELECT 1 FROM `units` WHERE `name` = 'kg');

-- ============ 5. DATA — base rows (121042): flag piece, insert g + ml ============
UPDATE `units` SET `base_unit` = NULL, `to_base_factor` = 1, `is_base` = 1, `purchase_unit` = 'piece', `integer_only` = 1, `updated_at` = NOW() WHERE `name` = 'piece';

INSERT INTO `units` (`name`, `base_unit`, `to_base_factor`, `sort_order`, `is_base`, `purchase_unit`, `integer_only`, `created_at`, `updated_at`)
SELECT 'g', NULL, 1, COALESCE(MAX(`sort_order`), 0) + 1, 1, 'kg', 0, NOW(), NOW() FROM `units`
WHERE NOT EXISTS (SELECT 1 FROM `units` WHERE `name` = 'g');

INSERT INTO `units` (`name`, `base_unit`, `to_base_factor`, `sort_order`, `is_base`, `purchase_unit`, `integer_only`, `created_at`, `updated_at`)
SELECT 'ml', NULL, 1, COALESCE(MAX(`sort_order`), 0) + 1, 1, 'litre', 0, NOW(), NOW() FROM `units`
WHERE NOT EXISTS (SELECT 1 FROM `units` WHERE `name` = 'ml');

-- ============ 6. DATA — backfill products.base_unit (072147, faithful mirror) ============
-- Mirrors the migration's PHP logic: first unit row's base if g/piece, else 'g'.
UPDATE `products` AS `p`
SET `p`.`base_unit` = COALESCE(
  (SELECT CASE WHEN `u`.`base_unit` IN ('g', 'piece') THEN `u`.`base_unit` ELSE 'g' END
   FROM `product_units` AS `pu` LEFT JOIN `units` AS `u` ON `u`.`name` = `pu`.`unit`
   WHERE `pu`.`product_id` = `p`.`id` ORDER BY `pu`.`sort_order`, `pu`.`price` LIMIT 1),
  'g')
WHERE `p`.`base_unit` IS NULL;

-- ============ 7. DATA — backfill products.low_stock defaults (053809) ============
UPDATE `products` SET `low_stock` = 1000 WHERE `low_stock` IS NULL AND `base_unit` = 'g';
UPDATE `products` SET `low_stock` = 10 WHERE `low_stock` IS NULL AND `base_unit` = 'piece';
UPDATE `products` SET `low_stock` = 1000 WHERE `low_stock` IS NULL AND `base_unit` = 'ml';

-- SKIPPED ON PURPOSE (safe to skip on production):
-- * 072148 repair_pre_seed_kg_stock — prod never had unconverted rows (seed precedes any purchase). Nothing to repair.
-- * 120601 drop_unused_purchase_wastage_unit_refs — prod never had those columns. Nothing to drop.

-- ============ 8. REGISTER migrations (so artisan never replays them) ============
-- All rows land in ONE new batch (scalar subquery evaluates once). Re-run safe.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT `m`.`name`, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`) FROM (
  SELECT '2026_09_02_051222_create_suppliers_table' AS `name` UNION ALL
  SELECT '2026_09_02_053633_add_conversion_to_units_table' UNION ALL
  SELECT '2026_09_02_053640_create_purchases_table' UNION ALL
  SELECT '2026_09_02_053647_create_purchase_items_table' UNION ALL
  SELECT '2026_09_02_053655_add_product_unit_id_to_order_items_table' UNION ALL
  SELECT '2026_09_02_065121_add_current_stock_to_products_table' UNION ALL
  SELECT '2026_09_02_112333_create_wastages_table' UNION ALL
  SELECT '2026_09_02_112334_create_wastage_items_table' UNION ALL
  SELECT '2026_09_03_054728_alter_products_current_stock_decimal' UNION ALL
  SELECT '2026_09_03_054729_alter_purchase_items_qty_decimal' UNION ALL
  SELECT '2026_09_03_054730_alter_wastage_items_qty_decimal' UNION ALL
  SELECT '2026_09_03_054731_alter_wastages_total_qty_decimal' UNION ALL
  SELECT '2026_09_03_070419_seed_purchase_units' UNION ALL
  SELECT '2026_09_03_072146_add_base_unit_to_products_table' UNION ALL
  SELECT '2026_09_03_072147_backfill_products_base_unit' UNION ALL
  SELECT '2026_09_03_072148_repair_pre_seed_kg_stock' UNION ALL
  SELECT '2026_09_03_120601_drop_unused_purchase_wastage_unit_refs' UNION ALL
  SELECT '2026_09_03_121041_add_base_config_to_units_table' UNION ALL
  SELECT '2026_09_03_121042_seed_base_unit_rows' UNION ALL
  SELECT '2026_09_05_053808_add_low_stock_to_products_table' UNION ALL
  SELECT '2026_09_05_053809_backfill_products_low_stock' UNION ALL
  SELECT '2026_09_05_060819_add_base_qty_to_order_items_table'
) AS `m`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migrations`.`migration` = `m`.`name`);

-- ============ 9. VERIFY (read-only) ============
SELECT COUNT(*) AS suppliers, (SELECT COUNT(*) FROM purchases) AS purchases, (SELECT COUNT(*) FROM wastages) AS wastages FROM suppliers;
SELECT `name`, `base_unit`, `to_base_factor`, `is_base`, `purchase_unit`, `integer_only` FROM `units` ORDER BY `sort_order`;
SELECT COUNT(*) AS null_base FROM `products` WHERE `base_unit` IS NULL;
SELECT COUNT(*) AS registered FROM `migrations` WHERE `migration` LIKE '2026_09%';
