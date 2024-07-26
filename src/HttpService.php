<?php

namespace crtlmota\BancoSicrediConnector;

use crtlmota\BancoSicrediConnector\Exceptions\SicrediRequestException;

class HttpService
{
    protected ?CredencialsManager $credencialsManager;

    protected string $apiBaseURL = "https://api-parceiro.sicredi.com.br";

    function formatHeader($assocArray)
    {
        $headers = [];
        foreach ($assocArray as $key => $value) {
            $headers[] = "$key: $value";
        }
        return $headers;
    }
    /**
     * Inicializa a conexão com a API
     *
     * @param array $headers
     */
    protected function intanceRequest($url, array &$headers)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $this->apiBaseURL . $url);

        $headers = array_merge(
            [
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            $this->credencialsManager->getHeaders(),
            $headers
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->formatHeader($headers));

        return $curl;
    }

    /**
     *
     * @param string $url
     * @param \JsonSerializable $data
     * @param array $headers
     * @throws SicrediRequestException
     * @return \stdClass
     */
    public function login(
        array $headers = [],
        $forcePassword = false
    ) {
        $url = "/auth/openapi/token";

        $headers['content-type'] = 'application/x-www-form-urlencoded';

        $body = $this->credencialsManager->getBodyLogin();
        if ($this->credencialsManager->hasValidRefreshToken()) {
            $body['grant_type'] = 'refresh_token';
        }

        $curl = $this->intanceRequest($url, $headers);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($body));

        return $this->handleResponse($curl, $headers);
    }
    public function get(string $url, array $queryParams = [], array $headers = [])
    {
        $this->checkOAuthToken();

        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams);
            $url = $url . '?' . $queryString;
        }

        $curl = $this->intanceRequest($url, $headers);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

        return $this->handleResponse($curl, $headers);
    }

    public function post(string $url, $body, ?array $headers = [])
    {
        $this->checkOAuthToken();

        $curl = $this->intanceRequest($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));

        return $this->handleResponse($curl, $headers);
    }

    public function patch(string $url, array $body = [], ?array $headers = [])
    {
        $this->checkOAuthToken();

        $curl = $this->intanceRequest($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));

        return $this->handleResponse($curl,$headers);
    }
    public function put(string $url, $body, ?array $headers = [])
    {
        $this->checkOAuthToken();

        $curl = $this->intanceRequest($url, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));

        return $this->handleResponse($curl, $headers);
    }



    protected function handleResponse($curl, $requestHeaders)
    {
        $response = curl_exec($curl);

        // Trata possíveis erros na requisição cURL
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            throw new SicrediRequestException("Erro de processamento: " . $error_msg, 500);
        }
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        curl_close($curl);
        // Decodifica a resposta JSON
        $header = substr($response, 0, $header_size);
        $headersResponse = [];
        $headerRows = explode("\n", $header);

        foreach ($headerRows as $key => $row) {
            $headerParts = explode(":", $row, 2);
            if (count($headerParts) < 2) {
                continue;
            }

            $key = trim($headerParts[0]);
            $values = explode(";", trim($headerParts[1]));
            if (empty($values)) continue;
            // Armazena os cabeçalhos com a mesma chave como um array
            $headersResponse[$key] = count($values) > 1 ? $values : $values[0];
        }

        $body = substr($response, $header_size);
        if (!empty($body) && $headersResponse['content-type'] === 'application/json') {
            $response = json_decode($body, true);
        } else {
            $response = $body;
        }

        // Verifica se houve erro na decodificação do JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SicrediRequestException($body, $statusCode);
        }
        
        if ($statusCode < 200 || $statusCode > 299) {
            if (isset($response['message'])) {
                $response = $response['message'];
            } else if (isset($response['error'])) {
                $response = $response['error'];
            } elseif ($statusCode === 401) {
                $response = "Não foi possível autenticar com as credenciais.";
            }

            throw new SicrediRequestException($response, $statusCode);
        }
        return $response;
    }
    /**
     * Tenta carregar token através de callback, verifica se tem o token oAuth
     * disponível, se ele está expirado, requisitando novo token e executando
     * o callback se necessário.
     *
     * @param boolean $emitCallbacks Callbacks serão executados (default true)
     */
    protected function checkOAuthToken($emitCallbacks = true)
    {
        if ($emitCallbacks && $this->credencialsManager->tokenLoadCallback && !$this->credencialsManager->hasValidToken()) {
            if (($loadedTokenData = ($this->credencialsManager->tokenLoadCallback)()) !== false) {
                $this->credencialsManager->setAccessToken($loadedTokenData["access_token"]);
                $this->credencialsManager->setTokenExpiresIn($loadedTokenData["expires_in"]);
                $this->credencialsManager->setTokenTimestamp($loadedTokenData["timestamp"]);
                $this->credencialsManager->setRefreshToken($loadedTokenData["refresh_token"]);
                $this->credencialsManager->setRefreshTokenExpiresIn($loadedTokenData["refresh_expires_in"]);
            } else {
                $this->credencialsManager->setAccessToken(null);
            }
        }



        if (!$this->credencialsManager->hasValidToken()) {
            $response = $this->login();

            $this->credencialsManager->setAccessToken($response['access_token']);
            $this->credencialsManager->setRefreshToken($response['refresh_token']);
            $this->credencialsManager->setTokenExpiresIn($response['expires_in']);
            $this->credencialsManager->setRefreshTokenExpiresIn($response['refresh_expires_in']);
            $this->credencialsManager->setTokenTimestamp(time());
            if ($emitCallbacks && $this->credencialsManager->tokenNewCallback) {
                ($this->credencialsManager->tokenNewCallback)(json_encode($this->exportOAuthToken()));
            }
        }
    }

    /**
     * return current oAuthToken data
     *
     * @return []
     */
    public function exportOAuthToken()
    {
        $this->checkOauthToken(false);
        return [
            "access_token" => $this->credencialsManager->getAccessToken(),
            "refresh_token" => $this->credencialsManager->getRefreshToken(),
            "refresh_expires_in" => $this->credencialsManager->getRefreshTokenExpiresIn(),
            "expires_in" => $this->credencialsManager->getTokenExpiresIn(),
            "timestamp" => $this->credencialsManager->getTokenTimestamp()
        ];
    }
}
