<?php

namespace crtlmota\BancoSicrediConnector\Tests;

use PHPUnit\Framework\TestCase;
use crtlmota\BancoSicrediConnector\SicrediCobranca;
use crtlmota\BancoSicrediConnector\Cobranca\Beneficiario;
use crtlmota\BancoSicrediConnector\CredencialsManager;
use crtlmota\BancoSicrediConnector\Cobranca\Boleto;
use crtlmota\BancoSicrediConnector\Cobranca\Pagador;
use crtlmota\BancoSicrediConnector\Cobranca\Pessoa;
use crtlmota\BancoSicrediConnector\Exceptions\SicrediRequestException;
use crtlmota\BancoSicrediConnector\Exceptions\SicrediValidationException;
use DateTime;

final class SicrediCobrancaTest extends TestCase
{
    const SANDBOX_USERNAME = '123456789';
    const SANDBOX_PASSWORD = 'teste123';
    const SANDBOX_COOPERATIVA = '6789';
    const SANDBOX_POSTO = '03';
    const SANDBOX_CODIGO_BENEFICIARIO = '12345';
    /**
     * @test
     * @return void
     * @throws SicrediValidationException
     * @throws SicrediRequestException
     */
    public function test_create_boleto()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $API_KEY = $_ENV['API_KEY'];
        // AVISO:
        // estes testes não fazem sentido se não forem alterados
        // com dados possíveis de boletos e de correntista

        $apiUrl = "https://api-parceiro.sicredi.com.br/sb";

        try {
            $cred = new CredencialsManager(
                self::SANDBOX_USERNAME,
                self::SANDBOX_PASSWORD,
                $API_KEY,
                'cobranca',
                self::SANDBOX_COOPERATIVA,
                self::SANDBOX_POSTO,
                self::SANDBOX_CODIGO_BENEFICIARIO,
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
            );

            $banco = new SicrediCobranca(
                $cred,
                $apiUrl
            );

            $this->assertInstanceOf(SicrediCobranca::class, $banco);

            // $this->assertEquals($banco->getApiBaseURL(), $apiUrl);

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

            $dataVencimento = (new DateTime())->modify("+30 days");
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
            $this->assertIsArray($boletoResponse);
            $this->assertIsString($boletoResponse['nossoNumero']);
            $this->assertIsString($boletoResponse['codigoBarras']);
            $this->assertIsString($boletoResponse['linhaDigitavel']);

            $findedBoleto = $banco->getBoleto($boletoResponse['nossoNumero']);
            $this->assertIsArray($findedBoleto, 'busca de Boleto');

            $findedBoletoPdf = $banco->getBoletoPdf($boletoResponse['linhaDigitavel']);
            $this->assertIsString($findedBoletoPdf, 'temp path pdf do boleto resgatado com sucesso');
            
            $findedBoletoEncodedPdf = $banco->getBoletoEncoded($boletoResponse['linhaDigitavel']);
            $this->assertIsString($findedBoletoEncodedPdf, 'binario pdf do boleto resgatado com sucesso');

            $updatedBoleto = $banco->updateVencimento($boletoResponse['nossoNumero'], $dataVencimento->modify("+5 days")->format("Y-m-d"));
            $this->assertIsArray($updatedBoleto, 'Boleto atualizado com sucesso');

            $boletosPagos = $banco->listBoletosPagosByDay($dataVencimento->modify("+5 days"));
            $this->assertArrayHasKey('items', $boletosPagos);
            $this->assertIsArray($boletosPagos['items']);
            //não habilitado para sandbox
	        // $banco->baixaBoleto($boletoResponse['nossoNumero']);

        } catch (SicrediValidationException | SicrediRequestException | \Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @return void
     * @throws SicrediValidationException
     * @throws SicrediRequestException
     */
    public function test_fail_validation_on_create_boleto()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
        $API_KEY = $_ENV['API_KEY'];
        $apiUrl = "https://api-parceiro.sicredi.com.br/sb";
        $banco = new SicrediCobranca(
            new CredencialsManager(
                self::SANDBOX_USERNAME,
                self::SANDBOX_PASSWORD,
                $API_KEY,
                'cobranca',
                self::SANDBOX_COOPERATIVA,
                self::SANDBOX_POSTO,
                self::SANDBOX_CODIGO_BENEFICIARIO,
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


        // $this->assertEquals($banco->getApiBaseURL(), $apiUrl);

        $fakerB = \Faker\Factory::create();
        $fakerB->addProvider(new \Faker\Provider\pt_BR\Person($fakerB));

        $fakerP = \Faker\Factory::create();
        $fakerP->addProvider(new \Faker\Provider\pt_BR\Person($fakerP));
        try {
            $beneficiario = (new Beneficiario())
                ->setTipoPessoa(Pessoa::TIPO_PESSOA_FISICA)
                // ->setDocumento($fakerB->cpf(false))
                ->setNome($fakerB->name)
                ->setLogradouro($fakerB->streetName)
                ->setNumeroEndereco($fakerB->numberBetween(10, 999))
                ->setCidade($fakerB->city)
                ->setUf($fakerB->stateAbbr())
                ->setCep($fakerB->numerify("########"));

            $pagador = (new Pagador())
                ->setTipoPessoa(Pessoa::TIPO_PESSOA_FISICA)
                // ->setDocumento($fakerP->cpf(false))
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

            $banco->createBoleto($boleto);
        } catch (SicrediValidationException $e) {
            $this->assertIsString($e->getMessage());
        } catch (SicrediRequestException $e) {
            $this->fail($e->getMessage());
        }
    }
    /**
     * @test
     * @return void
     * @throws SicrediValidationException
     * @throws SicrediRequestException
     */
    public function test_fail_request_on_login()
    {
      
        $apiUrl = "https://api-parceiro.sicredi.com.br/sb";
        try {
            $banco = new SicrediCobranca(
                new CredencialsManager(
                    self::SANDBOX_USERNAME,
                    self::SANDBOX_PASSWORD,
                    'errorapikey',
                    'cobranca',
                    self::SANDBOX_COOPERATIVA,
                    self::SANDBOX_POSTO,
                    self::SANDBOX_CODIGO_BENEFICIARIO,
                ),
                $apiUrl
            );
        } catch (SicrediValidationException $e) {
        } catch (SicrediRequestException $e) {
            $this->assertIsString($e->getMessage());
        }
    }
    // Desabilitado para testes com sandbox pois a sicredi só permite criação de webhook em produção
    // /**
    //  * @test
    //  * @return void
    //  * @throws 
    //  * @throws SicrediValidationException
    //  * @throws SicrediRequestException
    //  * Habilitado somente em produção
    //  * @skip
    //  */
    // public function test_contract_webwook()
    // {
    //     $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    //     $dotenv->load();
    //     $API_KEY = $_ENV['API_KEY'];
    //     // AVISO:
    //     // estes testes não fazem sentido se não forem alterados
    //     // com dados possíveis de boletos e de correntista
    //     $apiUrl = "https://api-parceiro.sicredi.com.br/sb";
    //     try {
    //     $banco = new SicrediCobranca(
    //         new CredencialsManager(
    //             self::SANDBOX_USERNAME,
    //             self::SANDBOX_PASSWORD,
    //             $API_KEY,
    //             'cobranca',
    //             self::SANDBOX_COOPERATIVA,
    //             self::SANDBOX_POSTO,
    //             self::SANDBOX_CODIGO_BENEFICIARIO,
    //         ),
    //         $apiUrl
    //     );

    //     $this->assertInstanceOf(SicrediCobranca::class, $banco);

    //     $webhook = $banco->createWebhook('https://myapi.com.br/webhook/cobranca');
    //     $this->assertIsArray($webhook);

    //     } catch (SicrediValidationException  | SicrediRequestException $e) {
    //         $this->fail($e->getMessage());
    //     }
    // }
    // /**
    //  * @test
    //  * @return void
    //  * @throws SicrediValidationException
    //  * @throws SicrediRequestException
    //  * Habilitado somente em produção
    //  * @skip
    //  */
    // public function test_disabled_webwook()
    // {
    //     $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    //     $dotenv->load();
    //     $API_KEY = $_ENV['API_KEY'];
    //     // AVISO:
    //     // estes testes não fazem sentido se não forem alterados
    //     // com dados possíveis de boletos e de correntista
    //     $apiUrl = "https://api-parceiro.sicredi.com.br/";
    //     try {
    //     $banco = new SicrediCobranca(
    //         new CredencialsManager(
    //             self::SANDBOX_USERNAME,
    //             self::SANDBOX_PASSWORD,
    //             $API_KEY,
    //             'cobranca',
    //             self::SANDBOX_COOPERATIVA,
    //             self::SANDBOX_POSTO,
    //             self::SANDBOX_CODIGO_BENEFICIARIO,
    //         ),
    //         $apiUrl
    //     );

    //     $this->assertInstanceOf(SicrediCobranca::class, $banco);

    //     $webhook = $banco->deleteWebhook();
    //     $this->assertIsBool($webhook);
    //     $this->assertEquals(true, $webhook);

    //     } catch (SicrediValidationException  | SicrediRequestException $e) {
    //         $this->fail($e->getMessage());
    //     }
    // }

}
