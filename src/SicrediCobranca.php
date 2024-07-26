<?php

namespace crtlmota\BancoSicrediConnector;

use crtlmota\BancoSicrediConnector\Exceptions\SicrediRequestException;
use crtlmota\BancoSicrediConnector\Exceptions\SicrediValidationException;
use crtlmota\BancoSicrediConnector\Cobranca\Boleto;

class SicrediCobranca extends HttpService
{
    /**
     *
     * @param CredencialsManager $credencialsManager
     */
    public function __construct(
        CredencialsManager $credencialsManager,
        ?string $apiBaseURL = null
    ) {
        $this->credencialsManager = $credencialsManager;
        if ($apiBaseURL) $this->apiBaseURL = $apiBaseURL;

        $this->checkOAuthToken(true);
    }

    /**
     * Transmite um boleto para o Banco Sicredi
     *
     * @param  Boleto $boleto Boleto a ser transmitido
     * @throws SicrediRequestException
     * @return array
     */
    public function createBoleto(Boleto $boleto, $preValidation = true)
    {
        $boleto->setCodigoBeneficiario($this->credencialsManager->getCodigoBeneficiario());
        if($preValidation) $boleto->validarBoleto();

        $response = $this->post("/cobranca/boleto/v1/boletos", $boleto);

        return $response;
    }

    /**
     *
     * @param  string $nossoNumero
     * @return \stdClass
     */
    public function getBoleto(string $nossoNumero): array
    {
        return $this->get("/cobranca/boleto/v1/boletos", [
                'nossoNumero' => $nossoNumero,
                'codigoBeneficiario' => $this->credencialsManager->getCodigoBeneficiario()
            ]);
    }

    /**
     * Faz download do PDF do boleto
     *
     * @param  string $nossoNumero
     * @param  string $savePath    Pasta a salvar o arquivo (default para a pasta de upload ou tmp)
     * @throws SicrediRequestException
     * @return string Caminho completo do arquivo baixado
     */
    public function getBoletoPdf(string $linhaDigitavel, string $savePath = null): string
    {
        if ($savePath == null) {
            $savePath = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        }

        $encoded = $this->getBoletoEncoded($linhaDigitavel);

        $filename = tempnam($savePath, "boleto-sicredi-") . ".pdf";

        if (!file_put_contents($filename, $encoded)) {
            throw new SicrediRequestException("Não foi possível decodificar o pdf");
        }

        return $filename;
    }

    /**
     *
     * @param  string $linhaDigitavel
     * @throws SicrediRequestException
     * @return string Conteúdo do PDF codificado
     */
    public function getBoletoEncoded(string $linhaDigitavel): string
    {
        $response = $this->get("/cobranca/boleto/v1/boletos/pdf", [
            "linhaDigitavel" => $linhaDigitavel
        ]);

        return $response;
    }
    /**
     * disabled in sandbox mode
     * @param  string $linhaDigitavel
     * @throws SicrediRequestException
     * @return void
     */
    public function baixaBoleto(string $nossoNumero)
    {
       return  $this->patch("/cobranca/boleto/v1/boletos/" . $nossoNumero . "/baixa", [], [
        "codigoBeneficiario" => $this->credencialsManager->getCodigoBeneficiario()
       ]);
    }

    /**
     *
     * @param  string $nossoNumero
     * @param  string $datavencimento formated date (Y-m-d)
     * @throws SicrediRequestException
     */
    public function updateVencimento(string $nossoNumero, string $datavencimento)
    {
        
        return $this->patch("/cobranca/boleto/v1/boletos/" . $nossoNumero . "/data-vencimento", [
            "dataVencimento" => $datavencimento
        ],[
            "codigoBeneficiario" => $this->credencialsManager->getCodigoBeneficiario()
        ]);
    }

    /**
     * Retorna lista de boletos registrados no banco
     * @params \DateTimeInterface $dia
     */
    public function listBoletosPagosByDay(
        \DateTimeInterface $dia,
        int $pagina = 1,
        ?string $cpfCnpjBeneficiario = null
    ): array {
       
        $url = "/cobranca/boleto/v1/boletos/liquidados/dia";
        $queryParams = [];
        if (!$dia) $dia = new \DateTime();
        $queryParams['dia'] = $dia->format('d/m/Y');
        $queryParams['codigoBeneficiario'] = $this->credencialsManager->getCodigoBeneficiario();
        $queryParams['pagina'] = $pagina;

        if ($cpfCnpjBeneficiario) $queryParams['cpfCnpjBeneficiarioFinal'] = $cpfCnpjBeneficiario;

        return $this->get($url, $queryParams);
    }

    // /**
    //  * Retorna o saldo da conta na data informada. Caso não seja informada
    //  * uma data, retorna o saldo atual.
    //  *
    //  * @param \DateTime $dataSaldo
    //  * @return float
    //  */
    // public function getSaldo(\DateTime $dataSaldo = null): array
    // {
    //     if (!$dataSaldo) {
    //         $dataSaldo = new \DateTime();
    //     }

    //     return $this->get("/banking/v2/saldo?dataSaldo=" . $dataSaldo->format('Y-m-d'));
    // }

    // /**
    //  * Consulta o extrato em um período entre datas específico. Para utilizar esta chamada,
    //  * suas credenciais junto ao Banco Sicredi precisam ter acesso à permissão "Consulta de extrato
    //  * e saldo", e você precisa declarar o escopo extrato.read ao criar o CredencialsManager.
    //  *
    //  * @param \DateTime dataInicio
    //  * @param \DateTime dataFim
    //  * @return \stdClass
    //  */
    // public function getExtrato(\DateTime $dataInicio, \DateTime $dataFim): array
    // {
    //     $url = "/banking/v2/extrato";
    //     $params = [];
    //     $params['dataInicio'] = $dataInicio->format('Y-m-d');
    //     $params['dataFim'] = $dataFim->format('Y-m-d');


    //     return $this->get($url, $params);
    // }

    // /**
    //  * Consulta o extrato COMPLETO em um período entre datas específico. Para utilizar esta chamada,
    //  * suas credenciais junto ao Banco sicredi precisam ter acesso à permissão "Consulta de extrato
    //  * e saldo", e você precisa declarar o escopo extrato.read ao criar o TokenRequest.
    //  * O extrato completo é paginado (diferente da função extrato)
    //  *
    //  *
    //  * @param \DateTime $dataInicio
    //  * @param \DateTime $dataFim
    //  * @param int $pagina Número da página, a primeira página é 0 (zero)
    //  * @param string $tipoOperacao 'C' para crédito, 'D' para débito
    //  * @param string $tipoTransacao PIX, CAMBIO, ESTORNO, etc.
    //  * @return \stdClass
    //  */
    // public function getExtratoCompleto(
    //     \DateTime $dataInicio,
    //     \DateTime $dataFim,
    //     int $pagina = 0,
    //     int $tamanhoPagina = 50,
    //     string $tipoOperacao = '',
    //     string $tipoTransacao = ''
    // ): array {
    //     $params['dataInicio'] = $dataInicio->format('Y-m-d');
    //     $params['dataFim'] = $dataFim->format('Y-m-d');
    //     $params['pagina'] = $pagina;
    //     $params['tamanhoPagina'] = $tamanhoPagina;
    //     $params['tipoOperacao'] = $tipoOperacao;
    //     $params['tipoTransacao'] = $tipoTransacao;

    //     $url = "/banking/v2/extrato/completo";

    //     return $this->get($url, $params);
    // }


    /**
     * Cria o webhook que receberá atualizações automáticos dos boletos (cobranças)
     *
     * @param $url
     * @return array
     * @throws SicrediRequestException
     * Exemplo de recebimento desse webhook após contratado
     * {
     *      "agencia": "9999",
     *      "posto": "99",
     *      "beneficiario": "12345",
     *      "nossoNumero": "221000144",
     *      "dataEvento":[2024,3,20,11,40,39,24000000]
     *      "movimento": "LIQUIDACAO_PIX",
     *      "valorLiquidacao": "101.01",
     *      "valorDesconto": "0",
     *      "valorJuros": "0",
     *      "valorMulta": "0",
     *      "valorAbatimento": "0",
     *      “carteira”: “CARTEIRA SIMPLES”
     *      "dataPrevisaoPagamento":[2024,3,20]
     *      "idEventoWebhook": "N000000000000000000000000000000LIQUIDACAO_PIX"
     *  }     
     */

    public function createWebhook($webhookUrl, ?array $eventos = null): array
    {
        $url = "/cobranca/boleto/v1/webhook/contrato/";
        $allEvents = [
            "LIQUIDACAO",
            // "LIQUIDACAO_PIX",
            // "LIQUIDACAO_COMPE_H5",
            // "LIQUIDACAO_COMPE_H6",
            // "LIQUIDACAO_COMPE_H8",
            // "LIQUIDACAO_REDE",
            // "LIQUIDACAO_CARTORIO",
            // "AVISO_PAGAMENTO_COMPE",
            // "ESTORNO_LIQUIDACAO_REDE"
        ];

        if ($eventos) $allEvents = $eventos;

        $payload = [
            "cooperativa" => $this->credencialsManager->getCooperativa(),
            "posto" => $this->credencialsManager->getPosto(),
            "codBeneficiario" =>  $this->credencialsManager->getCodigoBeneficiario(),
            "eventos" => $allEvents,
            "url" => $webhookUrl,
            "urlStatus" => "ATIVO",
            "contratoStatus" => "ATIVO",
        ];

        //Verifica se a URL do webhook é válida
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            throw new SicrediValidationException(["URL inválida"]);
        }
        try {

            return $this->post($url, $payload);
        } catch (SicrediRequestException $e) {
            if ($e->getCode() == 422 && $e->getMessage() == "Contrato já existente.") {
                return $this->getWebhook();
            }
            throw $e;
        }
    }

    /**
     * Retorna o webhook cadastrado, se houver
     *
     * @return array
     * @throws SicrediRequestException status 422 quando não encontrado
     * 
     */

    public function getWebhook(): array
    {
        $params = [
            "cooperativa" => $this->credencialsManager->getCooperativa(),
            "posto" => $this->credencialsManager->getPosto(),
            "beneficiario" =>  $this->credencialsManager->getCodigoBeneficiario()
        ];

        $url = "/cobranca/boleto/v1/webhook/contrato/";

        return $this->get($url, $params);
    }

    /**
     * update webhook, se houver
     * @throws SicrediRequestException
     * @return bool
     */
    public function updateUrlWebhook($webhookUrl): bool
    {
        try {
            $existentWebhook = $this->getWebhook();

            if ($existentWebhook) {
                $url = "/cobranca/boleto/v1/webhook/contrato/";
                if($existentWebhook['url'] === $webhookUrl) return true;
                $existentWebhook['url'] = $webhookUrl;
                $existentWebhook['urlStatus'] = "ATIVO";
                $delete = $this->put($url, $existentWebhook);

                return true;
            }
            return false;
        } catch (SicrediRequestException $e) {
            if ($e->getCode() == 422 && $e->getMessage() == "Nenhum contrato encontrado") {
                return false;
            }
            throw $e;
        }
    }
    /**
     * Deleta o webhook, se houver
     * Infelizmente esses endpoint do webwook não seguem um padrão de mercado, achei conveninente reduzir excessões desse processo
     * @throws SicrediRequestException
     * @return bool
     */
    public function deleteWebhook(): bool
    {
        try {
            $existentWebhook = $this->getWebhook();

            if ($existentWebhook) {
                $url = "/cobranca/boleto/v1/webhook/contrato/";
                $existentWebhook['contratoStatus'] = "INATIVO";
                $existentWebhook['status'] = "INATIVO";
                $delete = $this->put($url, $existentWebhook);

                return true;
            }
            return false;
        } catch (SicrediRequestException $e) {
            if ($e->getCode() == 422 && $e->getMessage() == "Nenhum contrato encontrado") {
                return false;
            }
            throw $e;
        }
    }


}
