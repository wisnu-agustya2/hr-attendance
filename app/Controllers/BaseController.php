<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    protected $helpers = ['form', 'url', 'format'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /** @return array{id:int, name:string, email:string, role:string} */
    protected function user(): array
    {
        return session('user');
    }

    protected function userId(): int
    {
        return (int) $this->user()['id'];
    }

    protected function now(): Time
    {
        return Time::now(config('App')->appTimezone);
    }

    /** Ambil ?month=YYYY-MM dari query string, default bulan ini. @return array{int, int} */
    protected function selectedMonth(): array
    {
        $value = (string) $this->request->getGet('month');
        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $value, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return [(int) $this->now()->getYear(), (int) $this->now()->getMonth()];
    }

    /**
     * Gabungkan aturan validasi dengan label bahasa Indonesia.
     *
     * @return array<string, array{label:string, rules:string}>
     */
    protected function withLabels(array $rules, array $labels): array
    {
        $out = [];
        foreach ($rules as $field => $rule) {
            $out[$field] = ['label' => $labels[$field] ?? $field, 'rules' => $rule];
        }

        return $out;
    }
}
