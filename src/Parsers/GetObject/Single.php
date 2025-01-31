<?php namespace PHRETS\Parsers\GetObject;

use PHRETS\Http\Response;
use PHRETS\Models\BaseObject;
use PHRETS\Models\RETSError;

class Single
{
    public function parse(Response $response)
    {

        $obj = new BaseObject;

        $obj->setContent(($response->getBody()) ? $response->getBody()->__toString() : null);
        $obj->setContentDescription($response->getHeader('Content-Description'));
        $obj->setContentSubDescription($response->getHeader('Content-Sub-Description'));
        $obj->setContentId($response->getHeader('Content-ID'));
        $obj->setObjectId($response->getHeader('Object-ID'));
        $obj->setContentType($response->getHeader('Content-Type'));
        $obj->setLocation($response->getHeader('Location'));
        $obj->setMimeVersion($response->getHeader('MIME-Version'));
        $obj->setPreferred($response->getHeader('Preferred'));

        // Handle errors
        if ($this->isError($response)) {
            $xml = $response->xml();
            $error = new RETSError;
    
            if (isset($xml['ReplyCode'])) {
                $error->setCode((string) $xml['ReplyCode']);
            }
            if (isset($xml['ReplyText'])) {
                $error->setMessage((string) $xml['ReplyText']);
            }
    
            $obj->setError($error);
        }
    
        return $obj;
    }

    protected function isError(Response $response)
    {
        $retsError = $response->getHeader('RETS-Error');
        if (is_array($retsError) && in_array('1', $retsError)) {
            return true;
        }
    
        $contentTypes = $response->getHeader('Content-Type');

        if (is_array($contentTypes) && !empty($contentTypes)) {
            $contentType = $contentTypes[0];
        } else {
            $contentType = null;
        };

        if ($contentType && strpos($contentType, 'text/xml') !== false) {
            $xml = $response->xml();
    
            if (isset($xml['ReplyCode']) && $xml['ReplyCode'] != 0) {
                return true;
            }
        }
    
        return false;
    }
    
}
