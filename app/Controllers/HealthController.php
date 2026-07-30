<?php

namespace App\Controllers;

use App\Libraries\SystemMonitoringService;

class HealthController extends BaseController
{
    public function live()
    {
        if (!$this->allowRequest('health-live', 120, 60)) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'status' => 'rate_limited',
                    'timestamp' => date(DATE_ATOM),
                ]);
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-store, max-age=0')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setJSON([
                'status' => 'alive',
                'timestamp' => date(DATE_ATOM),
                'release' => trim((string) env('deployment.release', '')),
            ]);
    }

    public function ready()
    {
        if (!$this->allowRequest('health-ready', 60, 60)) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'status' => 'rate_limited',
                    'timestamp' => date(DATE_ATOM),
                ]);
        }

        $report = (new SystemMonitoringService())->report();
        $statusCode = $report['status'] === 'critical' ? 503 : 200;

        return $this->response
            ->setStatusCode($statusCode)
            ->setHeader('Cache-Control', 'no-store, max-age=0')
            ->setHeader('X-Robots-Tag', 'noindex, nofollow')
            ->setJSON([
                'status' => $report['status'],
                'score' => $report['score'],
                'checks' => $report['counts'],
                'timestamp' => $report['generated_at'],
                'release' => $report['environment']['release'],
            ]);
    }

    private function allowRequest(
        string $scope,
        int $capacity,
        int $seconds
    ): bool {
        $key = $scope
            . ':'
            . hash('sha256', $this->request->getIPAddress());

        return service('throttler')->check(
            $key,
            $capacity,
            $seconds
        );
    }
}
