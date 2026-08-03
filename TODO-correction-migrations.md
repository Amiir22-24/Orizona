# Plan de correction des migrations

## ✅ 1. `2026_03_15_124307_add_land_to_catalog_type_enum.php`
- [x] Corriger syntaxe PostgreSQL → MySQL MODIFY COLUMN ENUM

## ✅ 2. `2026_03_31_000001_make_contract_url_nullable_on_occupancy_contracts.php`
- [x] Corriger syntaxe PostgreSQL → Schema::table()->nullable()->change()

## ✅ 3. `2026_04_01_000003_make_receipts_pdf_url_nullable.php`
- [x] Corriger syntaxe PostgreSQL → Schema::table()->nullable()->change()

## ✅ 4. `2026_04_09_131457_add_agent_validated_at_and_rejected_by_to_occupancy_requests_table.php`
- [x] Corriger syntaxe PostgreSQL → MySQL MODIFY COLUMN VARCHAR

## ✅ 5. `2026_04_05_121831_add_deleted_at_to_commissions_table.php`
- [x] Implémenter la migration vide (softDeletes)

## ✅ 6. `2026_04_06_084153_update_property_quality_ratings.php`
- [x] Supprimer (migration vide, inutile)

## ✅ 7. `2026_04_08_002102_add_contract_url_to_occupancy_requests_table.php`
- [x] Supprimer (doublon de 002135)

