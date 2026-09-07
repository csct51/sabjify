-- ============================================================================
-- Phase 2 DELTA: delivery_slot column missed by phase2-unpushed.sql
-- Standalone file. Run AFTER phase2-unpushed.sql (order vs that file does
-- not matter; statements are independent).
-- The old file is intentionally left untouched as the record of what ran.
-- BACK UP FIRST. Run once.
-- ============================================================================

-- ============ 0. PRE-CHECK (read-only, run first) ============
-- Expect: Empty set (column missing). If it returns a row, the column
-- already exists (e.g. migrate --force covered it): skip §1, but still
-- run §2 (self-skipping) and §3 to confirm.
SHOW COLUMNS FROM `orders` LIKE 'delivery_slot';

-- ============ 1. FIX — exact mirror of migration ============
-- 2026_08_31_112247_add_delivery_slot_to_orders_table.php:
--   $table->string('delivery_slot')->nullable()->after('notes');
-- (AFTER placement omitted: cosmetic only.)
ALTER TABLE `orders`
  ADD COLUMN `delivery_slot` VARCHAR(255) NULL;

-- ============ 2. REGISTER migration (so artisan never replays it) ============
-- Re-run safe: skipped automatically if the row already exists.
INSERT INTO `migrations` (`migration`, `batch`)
SELECT `m`.`name`, (SELECT COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`) FROM (
  SELECT '2026_08_31_112247_add_delivery_slot_to_orders_table' AS `name`
) AS `m`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migrations`.`migration` = `m`.`name`);

-- ============ 3. VERIFY (read-only) ============
-- Expect: one row describing a nullable VARCHAR(255).
SHOW COLUMNS FROM `orders` LIKE 'delivery_slot';
