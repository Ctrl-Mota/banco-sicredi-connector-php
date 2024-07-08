<?php
namespace crtlmota\BancoSicrediConnector\Cobranca;

use crtlmota\BancoSicrediConnector\Serializable;

/**
 * @class Destinatario
 * @package crtlmota\BancoSicrediConnector\Cobranca
 * @rule Obrigatório caso seja um boleto com Distribuição de Crédito.
 */
class Destinatario extends Serializable {
    protected ?string $codigoAgencia = null;
    protected ?string $codigoBanco = null;
    protected ?int $floatSplit = null;
    protected ?string $nomeDestinatario = null;
    protected ?string $numeroContaCorrente = null;
    protected ?string $numeroCpfCnpj = null;
    protected ?int $parcelaRateio = null;
    protected ?float $valorPercentualRateio = null;

    /**
     * @return string|null
     */
    public function getCodigoAgencia(): ?string
    {
        return $this->codigoAgencia;
    }

    /**
     * Define o código da agência do destinatário.
     *
     * @param string|null $codigoAgencia Código da agência com 4 dígitos numéricos.
     * @return Destinatario
     */
    public function setCodigoAgencia(?string $codigoAgencia): Destinatario
    {
        $this->codigoAgencia = $codigoAgencia;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodigoBanco(): ?string
    {
        return $this->codigoBanco;
    }

    /**
     * Define o código do banco do destinatário.
     *
     * @param string|null $codigoBanco Código do banco com 3 dígitos numéricos.
     * @return Destinatario
     */
    public function setCodigoBanco(?string $codigoBanco): Destinatario
    {

        $this->codigoBanco = $codigoBanco;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getFloatSplit(): ?int
    {
        return $this->floatSplit;
    }

    /**
     * Define o Float Split do destinatário.
     *
     * @param int|null $floatSplit Float Split, podendo ser informado de 0 a 30.
     * @return Destinatario
     */
    public function setFloatSplit(?int $floatSplit): Destinatario
    {

        $this->floatSplit = $floatSplit;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomeDestinatario(): ?string
    {
        return $this->nomeDestinatario;
    }

    /**
     * Define o nome do destinatário.
     *
     * @param string|null $nomeDestinatario Nome do destinatário, podendo ter de 3 a 40 caracteres.
     * @return Destinatario
     */
    public function setNomeDestinatario(?string $nomeDestinatario): Destinatario
    {
       
        $this->nomeDestinatario = $nomeDestinatario;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroContaCorrente(): ?string
    {
        return $this->numeroContaCorrente;
    }

    /**
     * Define o número da conta corrente do destinatário.
     *
     * @param string|null $numeroContaCorrente Número da conta corrente, podendo ter de 4 a 13 caracteres numéricos.
     * @return Destinatario
     */
    public function setNumeroContaCorrente(?string $numeroContaCorrente): Destinatario
    {
      
        $this->numeroContaCorrente = $numeroContaCorrente;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroCpfCnpj(): ?string
    {
        return $this->numeroCpfCnpj;
    }

    /**
     * Define o CPF/CNPJ do destinatário.
     *
     * @param string|null $numeroCpfCnpj CPF/CNPJ do destinatário, podendo ter até 14 caracteres numéricos.
     * @return Destinatario
     */
    public function setNumeroCpfCnpj(?string $numeroCpfCnpj): Destinatario
    {
       
        $this->numeroCpfCnpj = $numeroCpfCnpj;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParcelaRateio(): ?int
    {
        return $this->parcelaRateio;
    }

    /**
     * Define o número da parcela do rateio.
     *
     * @param int|null $parcelaRateio Número da parcela do rateio, podendo ser informado de 1 a 30.
     * @return Destinatario
     */
    public function setParcelaRateio(?int $parcelaRateio): Destinatario
    {
       
        $this->parcelaRateio = $parcelaRateio;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getValorPercentualRateio(): ?float
    {
        return $this->valorPercentualRateio;
    }

    /**
     * Define o valor/percentual do rateio.
     *
     * @param float|null $valorPercentualRateio Valor/percentual do rateio, podendo ter até 16 dígitos, sendo dois deles para a casa decimal.
     * @return Destinatario
     */
    public function setValorPercentualRateio(?float $valorPercentualRateio): Destinatario
    {
        $this->valorPercentualRateio = $valorPercentualRateio;
        return $this;
    }
    
}
