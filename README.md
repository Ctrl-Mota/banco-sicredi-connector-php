
<img src="https://dev-sicredi.zendesk.com/hc/theming_assets/01HZH1M81943X61AXRC3PEJAMH" width="300px" alt="Sicredi Logo">

Conecte o Banco Sicredi com seu projeto PHP 
===========

Abstração de comunicação rest com API v3.3 fornecida pelo Banco Sicredi (748).

Inicialmente apenas a scopo de cobranças(crud de boleto + webhook) foi desenvolvido


Como usar:
----------

### 1. Instalação

Para utilizar a biblioteca através do composer:

#### Versão estável

```
composer require "crtlmota/banco-sicredi-connector"
```

### 2. [Solicite acesso ao sandbox do SICREDI](https://dev-sicredi.zendesk.com/hc/pt-br/requests/new)

### 3. Configure as credenciais
Esses exemplos são com dados padrão de teste, se possuir acesso ao sandbox / homologação basta executar e ver a mágica acontecer

```php
$banco = new SicrediCobranca(
	new CredencialsManager(
		'123456789', // USERNAME
		'teste123', // PASSWORD
		'API_KEY_0000000000000', // api key fornecida pela sicredi
		'cobranca', // scope
		'6789', // COOPERATIVA
		'03', // POSTO
		'12345', // CODIGO_BENEFICIARIO
		//(opcional) Também é possível adicionar cache  de token embutido para diminuir o tempo de processamento eliminando logins desnecessários
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
	"https://api-parceiro.sicredi.com.br/sb" // URL do sandbox
);
```
* Sem cache de token  
```sh
root@f660e935d737:/app# php vendor/bin/phpunit tests 
Time: 00:00.553, Memory: 8.00 MB
OK (5 tests, 5 assertions)
```

* Com cache de token
```sh
root@f660e935d737:/app# php vendor/bin/phpunit tests 
Time: 00:00.249, Memory: 8.00 MB
OK (5 tests, 5 assertions)
```

### 4. Registre um boleto

```php
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
	$boleto->setTipoCobranca('HIBRIDO');
	$boletoResponse = $banco->createBoleto($boleto);
	// OU defina false no segundo parametro para não aplicar a pré validação dos dados

	//Pode-se aplicar a pŕe validação localmente de forma manual, para executar um job de envio em massa mais tarde
	$boleto->validarBoleto();
	$job[] = $boleto;



/*
	exemplo de body esperado $boletoResponse:
	{
		TODOS OS TIPOS DE COBRANÇA CONTÉM
			"linhaDigitavel": "74891125110061420512803153351030188640000009990",
			"codigoBarras": "74891886400000099901125100614205120315335103",
			"cooperativa": "0512",
			"posto": "03",
			"nossoNumero": "251006142"
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
	}
    
*/

```
#### Conte também com outros métodos 

```php
	$findedBoleto = $banco->getBoleto($boletoResponse['nossoNumero']); //'busca Boleto pelo nossoNumero'

	$findedBoletoPdfTempFilepath = $banco->getBoletoPdf($boletoResponse['linhaDigitavel']);

	$findedBoletoEncodedPdf = $banco->getBoletoEncoded($boletoResponse['linhaDigitavel']);

	$updatedBoleto = $banco->updateVencimento($boletoResponse['nossoNumero'], $dataVencimento->modify("+5 days")->format("Y-m-d"));

	$boletosPagos = $banco->listBoletosPagosByDay($dataVencimento->modify("+5 days"));

	$banco->baixaBoleto($boletoResponse['nossoNumero']);
```

### 5. Go to production
Mude as credencias para o ambiente de produção e pronto sua aplicação estará integrada ao banco.

### Documentação 
[Segue link da documentação em pdf](https://drive.google.com/file/d/1J5GTvKWM_Mjs2mn_3DW3gM-wn1yvjS1j/view?usp=sharing) 
Exemplos: 
* Manipulação de cobranças [HandleBoleto.php](example/HandleBoleto.php) 

* Contrato de webwook [ContratarWebhook.php](example/ContratarWebhook.php) 

fornece o básico para a utilização das classes.

Os parâmetros para a execução do exemplo devem ser salvos no arquivo com o nome `.env`, exemplos de configuração encontram-se no arquivo `.env.example`

Facilitou sua vida?
-------------------

Se o código do projeto ajudou você em uma tarefa complexa, considere fazer uma doação ao autor pelo PIX abaixo.

Chave Pix: d8ea7c17-2f20-492f-b45b-1913bf9d5819

> **ATENÇÃO:**
>
> Todos os dados verificáveis precisam ser válidos Utilize sempre CPF/CNPJ, CEP, Cidade e Estado válidos Para evitar importunar estranhos utilize seus próprios dados ou de alguma pessoa que esteja ciente, pois as cobranças sempre são cadastradas no sistema quente do banco central e aparecerão no DDA dos sacados. Os dados de exemplo NÃO SÃO VÁLIDOS e se não forem alterados o script de exemplo não funcionará.


## Usando o atributo `others`

O atributo `others` permite adicionar dados adicionais à sua requisição, que não estão definidos como propriedades da classe. Isso é útil para enviar informações personalizadas ou campos específicos que não estão mapeados na estrutura padrão da API.

**Exemplo:**

```php
use crtlmota\BancoSicrediConnector\Boleto;

// Crie um objeto Serializable
$serializable = new Boleto();

// Adicione dados adicionais ao atributo `others`
$serializable->addOthers('custom_field_1', 'valor_do_campo_1');
$serializable->addOthers('custom_field_2', 'valor_do_campo_2');

// Converta o objeto para JSON
$json = json_encode($serializable);

// Exemplo de saída
{
    ... outros dados
    "valor": 100,
    "dataEmissao": '2024-07-01',
    "dataVencimento": '2024-07-01'
    "custom_field_1": "valor_do_campo_1",
    "custom_field_2": "valor_do_campo_2"
}
```

Licença
-------

Todo o código deste projeto está licensiado sob a GNU Lesser General Public License versão 3.

Pode ser utilizado inalterado em qualquer projeto fechado ou open source, alterações efetuadas precisam ser fornecidas em código aberto aos usuários do sistema.

