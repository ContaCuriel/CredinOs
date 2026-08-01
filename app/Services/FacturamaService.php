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

        // 1. Intentamos ACTUALIZAR el certificado primero (Petición PUT en API LITE)
        $response = Http::withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->put($this->apiUrl . "/api-lite/csds/{$rfc}", $payload);

        // 2. Si Facturama responde con un error, lo CREAMOS (Petición POST en API LITE)
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

    public function getInvoicePdf(string $facturamaId)
    {
        $response = $this->getInvoiceFile($facturamaId, 'pdf');
        
        if ($response->failed()) {
            throw new \Exception('Error al descargar de Facturama: ' . $response->body());
        }
        
        return $response->json('Content');
    }

    public function getInvoiceXml(string $facturamaId)
    {
        $response = $this->getInvoiceFile($facturamaId, 'xml');
        
        if ($response->failed()) {
            throw new \Exception('Error al descargar de Facturama: ' . $response->body());
        }
        
        return base64_decode($response->json('Content'));
    }

    public function sendInvoiceByEmail(string $facturamaId, string $email, ?string $subject = null, ?string $comments = null)
    {
        $type = 'issuedLite';

        $queryParams = http_build_query([
            'CfdiType' => $type,
            'CfdiId' => $facturamaId,
            'Email' => $email,
        ]);

        $fullUrl = "{$this->apiUrl}/Cfdi?{$queryParams}";

        if ($subject) {
            $fullUrl .= "&Subject=" . urlencode($subject);
        }
        if ($comments) {
            $fullUrl .= "&Comments=" . urlencode($comments);
        }
        
        Log::info("Enviando factura por correo a la URL: {$fullUrl}");

        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->post($fullUrl);
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

    public function uploadLogo(string $base64Image, string $imageType)
    {
        $endpoint = '/TaxEntity/UploadLogo';
        $payload = [
            'Image' => $base64Image,
            'Type' => $imageType,
        ];

        Log::info("Subiendo logo a Facturama.");

        return Http::withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->put($this->apiUrl . $endpoint, $payload);
    }

    public function getAcuse($id, $format = 'pdf', $type = 'issuedLite')
    {
        return Http::timeout(40)
            ->withoutVerifying()
            ->withBasicAuth($this->apiUser, $this->apiPassword)
            ->get("{$this->apiUrl}/acuse/{$format}/{$type}/{$id}");
    }

    /**
     * Obtiene el archivo PDF o XML de un CFDI timbrado
     * 
     * @param string $id El ID único de Facturama
     * @param string $format 'pdf' o 'xml'
     */
    public function getFile($id, $format)
    {
        // Usamos 'issuedLite' porque estás usando la API Multiemisor para tus clientes
        $endpoint = "{$this->apiUrl}/cfdi/{$format}/issuedLite/{$id}";

        return Http::withoutVerifying() // 🔥 Esta es la magia que brinca el error de SSL
                   ->withBasicAuth($this->apiUser, $this->apiPassword)
                   ->get($endpoint);
    }
}