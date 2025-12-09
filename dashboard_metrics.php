<?php


header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 

/**
 * 
 * @param int 
 * @return array
 */
function generate_mock_data($count = 100) {
    $data = [];
    
    $categories = ['Electronics', 'Apparel', 'Home Goods', 'Books', 'Groceries'];
    for ($i = 1; $i <= $count; $i++) {
        $category_index = mt_rand(0, 4);
        $price = round(mt_rand(100, 10000) / 100, 2);
        $stock = mt_rand(1, 500);
        $data[] = [
            'id' => $i,
            'name' => 'Product Name ' . $i,
            'price' => $price,
            'stock' => $stock,
            'category' => $categories[$category_index],
             
            'sales_revenue' => $price * mt_rand(10, 50) 
        ];
    }
    return $data;
}

$all_data = generate_mock_data(100);


$metrics = [];
$total_products = count($all_data);
$total_revenue = array_sum(array_column($all_data, 'sales_revenue'));
$total_stock = array_sum(array_column($all_data, 'stock'));


$metrics['kpi_total_products'] = $total_products;
$metrics['kpi_total_revenue'] = round($total_revenue, 2);
$metrics['kpi_average_stock'] = round($total_stock / $total_products, 0);


$category_data = [];
foreach ($all_data as $product) {
    $cat = $product['category'];
    if (!isset($category_data[$cat])) {
        $category_data[$cat] = ['count' => 0, 'total_sales' => 0, 'total_stock' => 0, 'total_price' => 0];
    }
    $category_data[$cat]['count']++;
    $category_data[$cat]['total_sales'] += $product['sales_revenue'];
    $category_data[$cat]['total_stock'] += $product['stock'];
    $category_data[$cat]['total_price'] += $product['price'];
}


$metrics['category_sales_chart'] = ['labels' => [], 'data' => []];
$metrics['category_stock_chart'] = ['labels' => [], 'data' => []];
$metrics['category_average_price'] = []; 

foreach ($category_data as $category => $data) {
    $avg_price = $data['count'] > 0 ? $data['total_price'] / $data['count'] : 0;
    
    $metrics['category_sales_chart']['labels'][] = $category;
    $metrics['category_sales_chart']['data'][] = round($data['total_sales'], 2);
    
    $metrics['category_stock_chart']['labels'][] = $category;
    $metrics['category_stock_chart']['data'][] = $data['total_stock'];
    
    $metrics['category_average_price'][$category] = round($avg_price, 2);
}

usort($all_data, fn($a, $b) => $b['sales_revenue'] <=> $a['sales_revenue']);
$top_5_products = array_slice($all_data, 0, 5);
$metrics['top_5_products_revenue'] = array_map(function($product) {
    return [
        'name' => $product['name'],
        'revenue' => round($product['sales_revenue'], 2)
    ];
}, $top_5_products);


echo json_encode($metrics, JSON_PRETTY_PRINT);
?>