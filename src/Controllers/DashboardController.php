<?php
namespace App\Controllers;

use App\Services\DashboardService;

class DashboardController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        // Diagram 6.3: Admin Dashboard
        $stats = $this->dashboardService->getStatistics();
        require __DIR__ . '/../../views/admin/dashboard.php';
    }
}