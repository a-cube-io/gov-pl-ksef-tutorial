<?php

namespace Src\Controller;

use Src\Model\Conversion;
use Src\Repository\InvoiceRepository;

class ConverterController
{
    private $db;
    private $requestMethod;
    private $conversionId;

    private $invoiceRepository;

    public function __construct($db, $requestMethod, $conversionId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->conversionId = $conversionId;

        $this->invoiceRepository = new InvoiceRepository($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->conversionId) {
                    $response = $this->getConversion($this->conversionId);
                } else {
                    $response = $this->getAllConversions();
                };
                break;
            case 'POST':
                $response = $this->createConversionFromRequest();
                break;
            case 'PUT':
                $response = $this->updateConversionFromRequest($this->conversionId);
                break;
            case 'DELETE':
                $response = $this->deleteConversion($this->conversionId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllConversions()
    {
        $result = $this->invoiceRepository->index();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getConversion($id)
    {
        $result = $this->invoiceRepository->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        if ((int)$result['status'] === (int)Conversion::CONVERSION_STATUSES['PROCESSED']) {
            $result['file'] = 'url_to_converted_file';
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createConversionFromRequest()
    {
        $input = json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateConversion($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->invoiceRepository->store($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = null;
        return $response;
    }

    private function updateConversionFromRequest($id)
    {
        $result = $this->invoiceRepository->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateStatus($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->invoiceRepository->update($id, $input['status']);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deleteConversion($id)
    {
        $result = $this->invoiceRepository->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->invoiceRepository->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validateConversion($input)
    {
        if (!isset($input['name'])) {
            return false;
        }
        return true;
    }

    private function validateStatus($input)
    {
        $status = $input['status'];

        if (!isset($status)
            || !(new Conversion())->isStatusAllowed($status)
        ) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
