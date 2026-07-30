<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacturamaService
{
    protected $apiUrl;
    protected $apiUser;
    protected $apiPassword;

    public function __construct()
    {
        // Si quieres hacer pruebas sin timbrar de verdad, cambia la URL a: 'https://apisandbox.facturama.mx'
        $this->apiUrl = 'https://api.facturama.mx'; 
        $this->apiUser = env('FACTURAMA_API_KEY');
        $this->apiPassword = env('FACTURAMA_SECRET_KEY');
    }

    public function uploadCsd(string $rfc, string $cerContent, string $keyContent, string $password)
    {
        $payload = [
            'Rfc' => $rfc,
            'Certificate' => base64_encode($cerContent),
            'PrivateKey' => base64_encode($keyContent),
            'PrivateKeyPassword' => $password,
        ];

        // 1. Intentamos ACTUALIZAR el certificado primero (Petición PUT)
        $response = Http::withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->put($this->apiUrl . "/api-lite/csds/{$rfc}", $payload);

        // 2. Si Facturama responde con un error, lo CREAMOS (Petición POST)
        if ($response->failed()) {
            $response = Http::withoutVerifying()
                ->withBasicAuth($this->apiUser, $this->apiPassword)
                ->post($this->apiUrl . '/api-lite/csds', $payload);
        }

        return $response;
    }

    public function createInvoice(array $invoiceData)
    {
        $endpoint = '/api-lite/3/cfdis'; 
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post($this->apiUrl . $endpoint, $invoiceData);
    }

    public function getInvoiceFile(string $facturamaId, string $format)
    {
        $type = 'issuedLite';
        $endpoint = "/cfdi/{$format}/{$type}/{$facturamaId}";
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->get($this->apiUrl . $endpoint);
    }

    public function cancelInvoice(string $uuid, string $motive, ?string $replacementUuid = null)
    {
        $endpoint = "/api-lite/cfdis/{$uuid}?motive={$motive}";
        if ($motive === '01' && $replacementUuid) {
            $endpoint .= "&uuidReplacement={$replacementUuid}";
        }
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->delete($this->apiUrl . $endpoint);
    }

    public function getAcuse($id, $format = 'pdf', $type = 'issuedLite')
    {
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->get("{$this->apiUrl}/acuse/{$format}/{$type}/{$id}");
    }
}