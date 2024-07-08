<?php

namespace crtlmota\BancoSicrediConnector;

use Closure;

class CredencialsManager
{
    protected bool $isLogged = false;
    
    protected ?string $grant_type = "password";
    public ?string $context = "COBRANCA";
    
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected ?string $tokenExpiresIn = null;
    protected ?string $refreshTokenExpiresIn = null;
    protected ?string $tokenTimestamp = null;

    protected string $username;
    protected string $password;
    protected string $xApiKey;
    protected string $scope;
    protected string $cooperativa;
    protected string $posto;
    protected string $codigoBeneficiario;

    public ?Closure $tokenNewCallback = null;
    public ?Closure $tokenLoadCallback = null;

    /**
     * @param string $cooperativa (Código da Cooperativa)
     * @param string $posto (Código da Agencia)
     */
    public function __construct(
        string $username, 
        string $password, 
        string $xApiKey, 
        string $scope, 
        string $cooperativa, 
        string $posto,
        string $codigoBeneficiario,
        ?Closure $tokenNewCallback = null,
        ?Closure $tokenLoadCallback = null
    ){
        $this->username = $username;
        $this->password = $password;
        $this->xApiKey = $xApiKey;
        $this->scope = $scope;
        $this->cooperativa = $cooperativa;
        $this->posto = $posto;
        $this->codigoBeneficiario = $codigoBeneficiario;
        $this->tokenNewCallback = $tokenNewCallback;
        $this->tokenLoadCallback = $tokenLoadCallback;
       
    }

    
    public function getHeaders() {
        $authHeaders = [
            'x-api-key' => $this->xApiKey,
            'context' => $this->context,
            
        ];
        if($this->hasValidToken()) {
            $authHeaders['Authorization'] = 'Bearer ' . $this->accessToken;
            $authHeaders['cooperativa'] = $this->cooperativa;
            $authHeaders['posto'] = $this->posto;
        }
        return $authHeaders;
    }

    public function getBodyLogin() {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'refresh_token' => $this->refreshToken,
            'grant_type' => $this->grant_type,
            'scope' => $this->scope,
        ];
    }
    function hasValidToken() : bool {
        return !!$this->getAccessToken() && (($this->getTokenTimestamp() + $this->getTokenExpiresIn()) > (time() - 10));
    }
    function hasValidRefreshToken() : bool {
        return !!$this->getRefreshToken() && ($this->getTokenTimestamp() + $this->getRefreshTokenExpiresIn() > (time() - 10));
    }

    public function getXApiKey() : ?string {
        return $this->xApiKey;
    }

    public function setXApiKey(?string $xApiKey) {
        $this->xApiKey = $xApiKey;
        return $this;
    }

    public function getCodigoBeneficiario() : ?string {
        return $this->codigoBeneficiario;
    }
    public function getCooperativa() : ?string {
        return $this->cooperativa;
    }
    public function getPosto() : ?string {
        return $this->posto;
    }
    public function getAccessToken() : ?string {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken) {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getRefreshToken() : ?string {
        return $this->accessToken;
    }

    public function setRefreshToken(?string $refreshToken) {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getTokenExpiresIn() : ?int {
        return $this->tokenExpiresIn;
    }

    public function setTokenExpiresIn(?int $tokenExpiresIn) {
        $this->tokenExpiresIn = $tokenExpiresIn;
        return $this;
    }
    
    public function getRefreshTokenExpiresIn() : ?int {
        return $this->refreshTokenExpiresIn;
    }

    public function setRefreshTokenExpiresIn(?int $refreshTokenExpiresIn) {
        $this->refreshTokenExpiresIn = $refreshTokenExpiresIn;
        return $this;
    }

    public function getTokenTimestamp() : ?int {
        return $this->tokenTimestamp;
    }

    public function setTokenTimestamp(?int $tokenTimestamp) {
        $this->tokenTimestamp = $tokenTimestamp;
        return $this;
    }

     /**
     * Define uma função a ser chamada sempre que for necessária a
     * utilização do token oAuth permitindo a carga do token a partir
     * do cache e a contabilização da utilização tanto com base na
     * quantidade quanto com base no tempo.
     *
     * A função deve retornar um array com os elementos access_token,
     * expires_in e timestamp, ou false caso um novo token deva
     * ser emitido.
     *
     * É aconselhável que a função faça uso de alguma implementação de
     * cache e semáforos para garantir que a contabilização ocorra de forma
     * coerente entre processos.
     *
     * @param Closure $callback
     */
    public function setTokenLoadCallback(?Closure $callback)
    {
        $this->tokenLoadCallback = $callback;
    }

    /**
     * Define uma função a ser chamada quando um novo token for emitido,
     * permitindo que a aplicação armazene o novo token em um cache
     *
     * A função irá receber o token formatado como uma string JSON sobre
     * a saída do método exportOAuthToken(), que poderá ser usada para
     * importar o token posteriormente no parâmetro $oAuthTokenData do
     * construtor da classe SicrediCobranca.
     *
     * É aconselhável que a função faça uso de alguma implementação de
     * cache e semáforos para garantir que a o token seja utilizado
     * corretamente entre processos concorrentes.
     *
     * @param Closure $callback
     */
    public function setTokenNewCallback(?Closure $callback)
    {
        $this->tokenNewCallback = $callback;
    }

}
