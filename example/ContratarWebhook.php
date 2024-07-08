
<?php

require_once "vendor/autoload.php";

use crtlmota\BancoSicrediConnector\SicrediCobranca;
use crtlmota\BancoSicrediConnector\Cobranca\Beneficiario;
use crtlmota\BancoSicrediConnector\CredencialsManager;
use crtlmota\BancoSicrediConnector\Cobranca\Boleto;
use crtlmota\BancoSicrediConnector\Cobranca\Pagador;
use crtlmota\BancoSicrediConnector\Cobranca\Pessoa;
use crtlmota\BancoSicrediConnector\Exceptions\SicrediValidationException;
use crtlmota\BancoSicrediConnector\Exceptions\SicrediRequestException;


/**
 * 
 * @warning Esses endpoint não estão habilitado no Sandbox do sicredi, vou deixar com os dados de sandbox apenas para compreenção 
 */
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$API_KEY = $_ENV['API_KEY'];


$apiUrl = "https://api-parceiro.sicredi.com.br/sb";
try {
    $SANDBOX_USERNAME = '123456789';
    $SANDBOX_PASSWORD = 'teste123';
    $SANDBOX_COOPERATIVA = '6789';
    $SANDBOX_POSTO = '03';
    $SANDEBOX_CODIGO_BENEFICIARIO = '12345';

    $banco = new SicrediCobranca(
        new CredencialsManager(
            $SANDBOX_USERNAME,
            $SANDBOX_PASSWORD,
            $API_KEY,
            'cobranca',
            $SANDBOX_COOPERATIVA,
            $SANDBOX_POSTO,
            $SANDEBOX_CODIGO_BENEFICIARIO,
        ),
        $apiUrl
    );
    $webhook = $banco->createWebhook('https://myapi.com.br/webhook/cobranca');
    /*
        Retorno esperado: 
        {
        "cooperativa": "6789",
        "posto": "03",
        "codBeneficiario": "12345",
        "eventos": [
            "LIQUIDACAO"
        ],
        "url": "https://teste.instituicao.cloud/v1/contratos",
        "urlStatus": "ATIVO",
        "contratoStatus": "ATIVO",
        "nomeResponsavel": "NOME RESPONSAVEL",
        "email": "TESTE@EMAIL.COM.BR",
        "telefone": "51 999999999"
        }
    */
    return $webhook;

} catch (SicrediValidationException  | SicrediRequestException $e) {
    printf($e->getMessage());
}
