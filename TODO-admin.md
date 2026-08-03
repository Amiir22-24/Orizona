# TODO - Amélioration Section Admin

## Étapes

- [x] 1. Analyser le code existant (contrôleurs, modèles, vues)
- [ ] 2. Créer le contrôleur `AdminWebController`
- [ ] 3. Créer les vues admin (dashboard, properties, property-detail, users, user-detail, support)
- [ ] 4. Ajouter les routes web pour l'admin
- [ ] 5. Ajouter le sous-menu admin + cloche de notifications dans le layout
- [ ] 6. Supprimer "Volume Financier" du dashboard admin
- [ ] 7. Ajouter les notifications (répertoriation propriété, enregistrement propriétaire, attribution propriété)
- [ ] 8. Tester les fonctionnalités

## Fichiers à créer

- `app/Http/Controllers/AdminWebController.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/properties.blade.php`
- `resources/views/admin/property-detail.blade.php`
- `resources/views/admin/users.blade.php`
- `resources/views/admin/user-detail.blade.php`
- `resources/views/admin/support.blade.php`

## Fichiers à modifier

- `routes/web.php`
- `resources/views/layouts/dashboard.blade.php`
- `resources/views/dashboard/admin.blade.php`
- `app/Http/Controllers/OccupancyWebController.php` (notif répertoriation)
- `app/Http/Controllers/WebAuthController.php` (notif enregistrement propriétaire)
- `app/Http/Controllers/OwnerManagementController.php` (notif attribution propriété)

