-- ============================================================================
-- Set product stock quantities (MySQL 8 / MariaDB). BACK UP FIRST.
-- ACTIVE: sellable-everywhere version (all products are gram-based now).
--   Gram products -> 10000 base units (10 kg = 10+ packs, shop shows stocked).
--   Anything non-gram      -> 10 base units (10 pieces / 10 ml).
-- Only the `current_stock` column is touched: prices, images, units,
-- toggles and thresholds are never addressed. Re-running is harmless
-- (same values written again). Works on local and prod alike.
-- ============================================================================

UPDATE `products` SET `current_stock` = 10000 WHERE `base_unit` = 'g';
UPDATE `products` SET `current_stock` = 10 WHERE `base_unit` != 'g';

-- ============ VERIFY (read-only) ============
-- Expect: one row per base present, minimums 10000 (g) / 10 (others).
SELECT `base_unit`, COUNT(*) AS n, MIN(`current_stock`) AS min_stock FROM `products` GROUP BY `base_unit`;

-- ============================================================================
-- ALTERNATIVE (commented out): literal "everything to 10".
-- WARNING: 10 grams is less than one pack, so gram products will still show
-- Out of Stock, and everything under low_stock 1000 rings the low-stock
-- bell. Uncomment ONLY if that is really what you want.
-- ============================================================================
-- UPDATE `products` SET `current_stock` = 10;
-- SELECT COUNT(*) AS total, SUM(`current_stock` = 10) AS at_ten FROM `products`;
