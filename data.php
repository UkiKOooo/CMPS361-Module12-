<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 

function generate_mock_data($count = 100) {
    $data = [];
    for ($i = 1; $i <= $count; $i++) {
        $data[] = [
            'id' => $i,
            'name' => 'Product Name ' . $i,
            'price' => round(mt_rand(100, 10000) / 100, 2),
            'stock' => mt_rand(1, 500),
            'category' => 'Category ' . (mt_rand(1, 5))
        ];
    }
    return $data;
}


$all_data = generate_mock_data(100); 
$total_records = count($all_data);


$category_counts = [];
foreach ($all_data as $item) {
    $category = $item['category'];
    if (!isset($category_counts[$category])) {
        $category_counts[$category] = 0;
    }
    $category_counts[$category]++;
}

ksort($category_counts);

// --- 1. Get Parameters ---

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_dir = isset($_GET['sort_dir']) ? strtolower($_GET['sort_dir']) : 'asc';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : ''; 

// --- 2. Apply Search Filter (Crucial: filtering happens here) ---
if (!empty($search)) {
    
    $all_data = array_filter($all_data, function($item) use ($search) {
        foreach ($item as $key => $value) {
            if (stripos((string)$value, $search) !== false) {
                return true; 
            }
        }
        return false; 
    });
    $total_records = count($all_data);
}

// --- 3. Validate and Apply Sorting ---

$allowed_sort_fields = ['id', 'name', 'price', 'stock', 'category'];

if (!in_array($sort_by, $allowed_sort_fields)) {
    $sort_by = 'id';
}
if ($sort_dir !== 'asc' && $sort_dir !== 'desc') {
    $sort_dir = 'asc';
}

usort($all_data, function($a, $b) use ($sort_by, $sort_dir) {
    $val_a = $a[$sort_by];
    $val_b = $b[$sort_by];

    if (is_numeric($val_a) && is_numeric($val_b)) {
        if ($val_a == $val_b) return 0;
        $comparison = ($val_a < $val_b) ? -1 : 1;
    } else {
        $comparison = strcmp($val_a, $val_b);
    }
    return ($sort_dir === 'asc') ? $comparison : -$comparison;
});


// --- 4. Pagination Logic ---

$items_per_page = 10; 
$total_pages = ceil($total_records / $items_per_page); 

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($current_page < 1) $current_page = 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages; 
if ($total_pages === 0) $current_page = 1; 

$offset = ($current_page - 1) * $items_per_page;

$page_data = array_slice($all_data, $offset, $items_per_page);

// --- 5. API Response (Adding New Data for Chart) ---

$response = [
    'status' => 'success',
    'total_records' => $total_records,
    'items_per_page' => $items_per_page,
    'total_pages' => $total_pages,
    'current_page' => $current_page,
    'data' => $page_data,
    'category_counts' => $category_counts 
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>