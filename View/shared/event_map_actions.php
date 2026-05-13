<?php
declare(strict_types=1);

/** @var string $map_address Lieu / adresse pour géocodage (Nominatim) */
$mapAddressForActions = trim((string) ($map_address ?? ''));
if ($mapAddressForActions === '') {
    return;
}
if (preg_match('/^(à|a)\s*d[ée]finir\.?$/iu', $mapAddressForActions)) {
    return;
}
$mapActionsClass = trim((string) ($map_actions_class ?? 'mt-2'));
$addrEsc = htmlspecialchars($mapAddressForActions, ENT_QUOTES, 'UTF-8');
$classEsc = $mapActionsClass !== '' ? htmlspecialchars($mapActionsClass, ENT_QUOTES, 'UTF-8') : '';
?>
<div class="d-flex flex-wrap gap-2<?= $classEsc !== '' ? ' ' . $classEsc : '' ?>">
    <button type="button" class="btn btn-outline-warning btn-sm" data-map-focus-btn data-map-address="<?= $addrEsc ?>">
        <i class="fa-solid fa-map-location-dot me-1" aria-hidden="true"></i>Voir sur la carte
    </button>
    <button type="button" class="btn btn-outline-primary btn-sm" data-map-route-btn data-map-address="<?= $addrEsc ?>">
        <i class="fa-solid fa-route me-1" aria-hidden="true"></i>Itinéraire
    </button>
</div>
