<?php
// Gedeelde <head>-inhoud. Zet $pageTitle vóór je dit bestand include't.
$pageTitle = $pageTitle ?? 'PlanYourTrip';
$cssVersion = @filemtime(__DIR__ . '/../assets/style.css') ?: 1;
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> · PlanYourTrip</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✈️</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="assets/style.css?v=<?= $cssVersion ?>">
