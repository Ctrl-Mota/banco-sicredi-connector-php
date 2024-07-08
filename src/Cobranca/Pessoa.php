<?php

namespace crtlmota\BancoSicrediConnector\Cobranca;

use crtlmota\BancoSicrediConnector\Serializable;

class Pessoa extends Serializable
{
    protected ?string $tipoPessoa = null;
    protected ?string $documento = null;
    protected ?string $nome = null;
    protected ?string $logradouro = null;
    protected ?string $numeroEndereco = null;
    protected ?string $cidade = null;
    protected ?string $uf = null;
    protected ?string $cep = null;
    protected ?string $telefone = null;
    protected ?string $email = null;
    const TIPO_PESSOA_JURIDICA = 'PESSOA_JURIDICA';
    const TIPO_PESSOA_FISICA = 'PESSOA_FISICA';

    /**
     * @return string|null
     */
    public function getTipoPessoa(): ?string
    {
        return $this->tipoPessoa;
    }

    /**
     * Define o tipo de pessoa do pagador.
     *
     * @param string|null $tipoPessoa Tipo de pessoa do pagador, podendo ser "PESSOA_JURIDICA" ou "PESSOA_FISICA". 
     * @required campo requerido
     * @return self
     */
    public function setTipoPessoa(?string $tipoPessoa): self
    {
      
        $this->tipoPessoa = $tipoPessoa;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    /**
     * Define o CPF ou CNPJ do pagador.
     *
     * @param string|null $documento CPF ou CNPJ do pagador.
     * @required campo requerido
     * @return self
     */
    public function setDocumento(?string $documento): self
    {
       
        $this->documento = $documento;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNome(): ?string
    {
        return $this->nome;
    }

    /**
     * Define o nome do pagador.
     *
     * @param string|null $nome Nome do pagador.
     * @required campo requerido
     * @return self
     */
    public function setNome(?string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogradouro(): ?string
    {
        return $this->logradouro;
    }

    /**
     * Define o endereço do pagador.
     *
     * @param string|null $logradouro Endereço do pagador.
     * @return self
     */
    public function setLogradouro(?string $logradouro): self
    {
        $this->logradouro = $logradouro;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumeroEndereco(): ?string
    {
        return $this->numeroEndereco;
    }

    /**
     * Define a numeroEndereco do pagador.
     *
     * @param string|null $numeroEndereco Cidade do pagador.
     * @return self
     */
    public function setNumeroEndereco(?string $numeroEndereco): self
    {
        $this->numeroEndereco = $numeroEndereco;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    /**
     * Define a cidade do pagador.
     *
     * @param string|null $cidade Cidade do pagador.
     * @return self
     */
    public function setCidade(?string $cidade): self
    {
        $this->cidade = $cidade;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUf(): ?string
    {
        return $this->uf;
    }

    /**
     * Define a UF do pagador.
     *
     * @param string|null $uf @length(2) UF do pagador. 
     * @return self
     */
    public function setUf(?string $uf): self
    {
        
        $this->uf = $uf;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCep(): ?string
    {
        return $this->cep;
    }

    /**
     * Define o CEP do pagador.
     *
     * @param string|null $cep @length(8) CEP do pagador.
     * @return self
     */
    public function setCep(?string $cep): self
    {
        $this->cep = $cep;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    /**
     * Define o telefone do pagador.
     *
     * @param string|null $telefone Telefone do pagador.
     * @return self
     */
    public function setTelefone(?string $telefone): self
    {
        $this->telefone = $telefone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Define o email do pagador.
     *
     * @param string|null $email Email do pagador.
     * @return self
     */
    public function setEmail(?string $email): self
    {
        $this->email = filter_var($email, FILTER_VALIDATE_EMAIL);
        return $this;
    }
 
}
