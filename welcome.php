<?php
// welcome.php - Dashboard Environment
session_start();


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Welcome, <?php echo $username; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-header { background-color: #343a40; color: white; padding: 20px 0; margin-bottom: 20px; }
        .kpi-card { border-left: 5px solid; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,.1); }
        .kpi-card-products { border-left-color: #007bff; }
        .kpi-card-revenue { border-left-color: #28a745; }
        .kpi-card-stock { border-left-color: #ffc107; }
        .chart-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,.1); }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Analytics Dashboard</h1>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, <strong><?php echo $username; ?></strong></span>
                <a href="products.php" class="btn btn-outline-light me-2">Products</a>
                <a href="activities.php" class="btn btn-outline-light me-2">Activities</a>
                <a href="welcome.php?logout=true" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card p-3 kpi-card kpi-card-products">
                    <div class="card-body">
                        <div class="text-uppercase fw-bold text-muted">Total Products</div>
                        <h2 class="display-4" id="kpi-products">...</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 kpi-card kpi-card-revenue">
                    <div class="card-body">
                        <div class="text-uppercase fw-bold text-muted">Total Revenue</div>
                        <h2 class="display-4 text-success" id="kpi-revenue">...</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 kpi-card kpi-card-stock">
                    <div class="card-body">
                        <div class="text-uppercase fw-bold text-muted">Avg. Stock Per Product</div>
                        <h2 class="display-4 text-warning" id="kpi-avg-stock">...</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="chart-container mb-4">
                    <h5 class="card-title">Sales Revenue by Category</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-container mb-4">
                    <h5 class="card-title">Stock Quantity by Category</h5>
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="chart-container mb-4">
                    <h5 class="card-title">Top 5 Products by Revenue</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody id="top-products-list">
                            <tr><td colspan="3">Loading top products...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        const API_URL = 'dashboard_metrics.php';

        async function fetchMetrics() {
            try {
                const response = await fetch(API_URL);
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                const metrics = await response.json();
                console.log('Fetched Metrics:', metrics);
                
                updateKPIs(metrics);
                renderCharts(metrics);
                renderTopProducts(metrics);

            } catch (error) {
                console.error('Error fetching dashboard metrics:', error);
                document.getElementById('kpi-products').innerText = 'Error';
                document.getElementById('kpi-revenue').innerText = 'Error';
                document.getElementById('kpi-avg-stock').innerText = 'Error';
            }
        }

        function updateKPIs(metrics) {
            // 更新 KPI 卡片
            document.getElementById('kpi-products').innerText = metrics.kpi_total_products.toLocaleString();
            document.getElementById('kpi-revenue').innerText = `$${metrics.kpi_total_revenue.toLocaleString()}`;
            document.getElementById('kpi-avg-stock').innerText = metrics.kpi_average_stock.toLocaleString();
        }

        function renderCharts(metrics) {
            // 渲染 Category Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: metrics.category_sales_chart.labels,
                    datasets: [{
                        label: 'Total Sales Revenue ($)',
                        data: metrics.category_sales_chart.data,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
                        ],
                        borderColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // 渲染 Category Stock Chart
            const stockCtx = document.getElementById('stockChart').getContext('2d');
            new Chart(stockCtx, {
                type: 'doughnut', // 使用甜甜圈图展示库存占比
                data: {
                    labels: metrics.category_stock_chart.labels,
                    datasets: [{
                        label: 'Total Stock Quantity',
                        data: metrics.category_stock_chart.data,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Stock Distribution'
                        }
                    }
                }
            });
        }
        
        function renderTopProducts(metrics) {
            const listBody = document.getElementById('top-products-list');
            listBody.innerHTML = ''; 
            
            metrics.top_5_products_revenue.forEach((product, index) => {
                const row = listBody.insertRow();
                const rankCell = row.insertCell();
                const nameCell = row.insertCell();
                const revenueCell = row.insertCell();
                
                rankCell.innerText = index + 1;
                nameCell.innerText = product.name;
                revenueCell.innerText = `$${product.revenue.toLocaleString()}`;
                revenueCell.classList.add('fw-bold', 'text-success');
            });
        }



        document.addEventListener('DOMContentLoaded', fetchMetrics);
    </script>
</body>
</html>