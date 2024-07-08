
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
			function (string $tokenJson) { //new token callback
				if ($tokenFile = fopen('sicredi-oauth-token.txt', 'w')) {
					fwrite($tokenFile, $tokenJson);
					fclose($tokenFile);
				}
			},
			function () { //get token callback
				$oAuthTokenData = null;
				// uso do @ para evitar o warning se o arquivo não existe
				if (($tokenFile = @fopen('sicredi-oauth-token.txt', 'r')) !== false) {
					// se tiver arquivo com token, carrega ele e retorna
					$tokenJson = fread($tokenFile, 8192);
					$oAuthTokenData = json_decode($tokenJson, true);
					fclose($tokenFile);
					return $oAuthTokenData;
				} else {
					// retorno "falso" força a emissão de novo token
					return false;
				}
			}
		),
		$apiUrl
	);

	$fakerB = \Faker\Factory::create();
	$fakerB->addProvider(new \Faker\Provider\pt_BR\Person($fakerB));

	$fakerP = \Faker\Factory::create();
	$fakerP->addProvider(new \Faker\Provider\pt_BR\Person($fakerP));




	$beneficiario = (new Beneficiario())
		->setTipoPessoa(Pessoa::TIPO_PESSOA_FISICA)
		->setDocumento($fakerB->cpf(false))
		->setNome($fakerB->name)
		->setLogradouro($fakerB->streetName)
		->setNumeroEndereco($fakerB->numberBetween(10, 999))
		->setCidade($fakerB->city)
		->setUf($fakerB->stateAbbr())
		->setCep($fakerB->numerify("########"));

	$pagador = (new Pagador())
		->setTipoPessoa(Pessoa::TIPO_PESSOA_FISICA)
		->setDocumento($fakerP->cpf(false))
		->setNome($fakerP->name)
		->setLogradouro($fakerP->streetName)
		->setNumeroEndereco($fakerP->numberBetween(10, 999))
		->setCidade($fakerP->city)
		->setUf($fakerP->stateAbbr())
		->setCep($fakerP->numerify("########"));

	$dataVencimento = (new DateTime())->modify("+30 day");
	$boleto = new Boleto();
	$boleto->setPagador($pagador);
	$boleto->setBeneficiario($beneficiario);
	$boleto->setSeuNumero("TESTE");
	$boleto->setValor(500.00);
	$boleto->setDataVencimento($dataVencimento->format("Y-m-d"));
	$boleto->setDesconto1((clone $dataVencimento)->modify('-20 days')->format("Y-m-d"), 200.00);
	$boleto->setDesconto2((clone $dataVencimento)->modify('-10 days')->format("Y-m-d"), 150.00);
	$boleto->setDesconto3((clone $dataVencimento)->modify('-5 days')->format("Y-m-d"), 100.00);
	$boleto->setJuros(1);
	$boleto->setMulta(12.00);
	$boleto->addMensagem('APÓS O VENCIMENTO, COBRAR MULTA DE ' . $boleto->getMulta() . '%');
	$boleto->addMensagem('APÓS O VENCIMENTO, COBRAR JUROS DE ' . $boleto->getJuros() . '% AO MÊS');

	$boletoResponse = $banco->createBoleto($boleto);

	/*
    exemplo de body esperado:
    {
			
			IF (tipoCobranca:HIBRIDO)
				"txid": "f69d2a0076fb4ea2bddd7babd1200525",
        "qrCode": "00020101021226930014br.gov.bcb.pix2571pix-qrcodeh.sicredi.com.br/qr/v2/cobv/6946459e4b6e4c19ab5c9689fe0df30a520400005303986540599.905802BR5921OLIVEIRA MULTI MARCAS6008BRASILIA62070503***6304E5E1",

			IF (tipoCobranca:SPLIT)
        "splitBoleto": {
					"repasseAutomaticoSplit": "SIM",
					"tipoValorRateio": "PERCENTUAL",
					"regraRateio": "VALOR_COBRADO",
					"destinatarios":
						[
							{
								"codigoBanco": "237",
								"codigoAgencia": "0434",
								"numeroContaCorrente": "2323232323",
								"numeroCpfCnpj": "02738306004",
								"nomeDestinatario": "DECIO OLIVEIRA",
								"parcelaRateio": "1",
								"valorPercentualRateio": 24.22,
								"floatSplit": 20
							}
						]
        }

				TODOS OS TIPOS DE COBRANÇA CONTÉM
        "linhaDigitavel": "74891125110061420512803153351030188640000009990",
        "codigoBarras": "74891886400000099901125100614205120315335103",
        "cooperativa": "0512",
        "posto": "03",
        "nossoNumero": "251006142"
    }
    
    */

	$findedBoleto = $banco->getBoleto($boletoResponse['nossoNumero']); //'Boleto'

	$findedBoletoPdfTempFilepath = $banco->getBoletoPdf($boletoResponse['linhaDigitavel']);

	$findedBoletoEncodedPdf = $banco->getBoletoEncoded($boletoResponse['linhaDigitavel']);

	$updatedBoleto = $banco->updateVencimento($boletoResponse['nossoNumero'], $dataVencimento->modify("+5 days")->format("Y-m-d"));

	$boletosPagos = $banco->listBoletosPagosByDay($dataVencimento->modify("+5 days"));

	$banco->baixaBoleto($boletoResponse['nossoNumero']);
		
} catch (SicrediValidationException | SicrediRequestException $e) {
	printf($e->getMessage());
}
