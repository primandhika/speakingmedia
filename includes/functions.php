<?php
// functions.php - Fungsi-fungsi PHP

function getProgressText($clicks) {
    if ($clicks == 0) {
        return 'Belum dipelajari';
    } elseif ($clicks < 5) {
        return 'Baru mulai (' . ($clicks * 20) . '%)';
    } elseif ($clicks < 10) {
        return 'Sedang belajar (' . ($clicks * 10) . '%)';
    } else {
        return 'Dikuasai (' . min(100, $clicks * 5) . '%)';
    }
}

function getProgressPercentage($clicks) {
    return min(100, $clicks * 10);
}

function mergeMaterialClicks(&$materials) {
    foreach ($materials as $key => &$material) {
        if (isset($_SESSION['material_clicks'][$key])) {
            $material['clicks'] = $_SESSION['material_clicks'][$key];
        }
    }
}

function updateMaterialClick($materialKey, &$materials) {
    if (array_key_exists($materialKey, $materials)) {
        if (!isset($_SESSION['material_clicks'][$materialKey])) {
            $_SESSION['material_clicks'][$materialKey] = 0;
        }
        $_SESSION['material_clicks'][$materialKey]++;
        $materials[$materialKey]['clicks'] = $_SESSION['material_clicks'][$materialKey];
    }
}

function getStudyStatistics($materials) {
    $totalClicks = array_sum(array_column($materials, 'clicks'));
    $studiedMaterials = count(array_filter($materials, function($m) { 
        return $m['clicks'] > 0; 
    }));
    $totalMaterials = count($materials);
    
    return [
        'total_clicks' => $totalClicks,
        'studied_materials' => $studiedMaterials,
        'total_materials' => $totalMaterials
    ];
}
?>