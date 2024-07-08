<?php

namespace crtlmota\BancoSicrediConnector\Cobranca;

use crtlmota\BancoSicrediConnector\Exceptions\SicrediValidationException;
use crtlmota\BancoSicrediConnector\Utils;

trait ValidationTrait 
{
    function validarBoleto() :  void
    {
        $dados = \json_decode(json_encode($this), true);
        $erros = [];
        // Validação do Beneficiário Final
    if (!isset($dados['beneficiario']) || !is_array($dados['beneficiario'])) {
        $erros[] = "O campo 'Beneficiário Final' é obrigatório e deve ser um objeto.";
    } else {
        if (!isset($dados['beneficiario']['documento']) || empty($dados['beneficiario']['documento'])) {
            $erros[] = "O campo 'Documento do Beneficiário Final' é obrigatório.";
        }
        if (!isset($dados['beneficiario']['tipoPessoa']) || empty($dados['beneficiario']['tipoPessoa'])) {
            $erros[] = "O campo 'Tipo de Pessoa do Beneficiário Final' é obrigatório.";
        }
        if (!isset($dados['beneficiario']['nome']) || empty($dados['beneficiario']['nome'])) {
            $erros[] = "O campo 'Nome do Beneficiário Final' é obrigatório.";
        }
    }

    // Validação do Pagador
    if (!isset($dados['pagador']) || !is_array($dados['pagador'])) {
        $erros[] = "O campo 'Pagador' é obrigatório e deve ser um objeto.";
    } else {
        if (!isset($dados['pagador']['documento']) || empty($dados['pagador']['documento'])) {
            $erros[] = "O campo 'Documento do Pagador' é obrigatório.";
        }
        if (!isset($dados['pagador']['tipoPessoa']) || empty($dados['pagador']['tipoPessoa'])) {
            $erros[] = "O campo 'Tipo de Pessoa do Pagador' é obrigatório.";
        }
        if (!isset($dados['pagador']['nome']) || empty($dados['pagador']['nome'])) {
            $erros[] = "O campo 'Nome do Pagador' é obrigatório.";
        }
    }

    // Validação dos campos obrigatórios
    $camposObrigatorios = [
        'codigoBeneficiario',
        'dataVencimento',
        'especieDocumento',
        'seuNumero',
        'valor',
    ];

    foreach ($camposObrigatorios as $campo) {
        if (!isset($dados[$campo]) || empty($dados[$campo])) {
            $erros[] = "O campo '$campo' é obrigatório.";
        }
    }

    // Validação do tipo de cobrança
    if (!isset($dados['tipoCobranca']) || empty($dados['tipoCobranca'])) {
        $erros[] = "O campo 'Tipo de Cobrança' é obrigatório.";
    } else {
        if (!in_array($dados['tipoCobranca'], ['NORMAL', 'HIBRIDO'])) {
            $erros[] = "O campo 'Tipo de Cobrança' deve ser 'NORMAL' ou 'HIBRIDO'.";
        }
    }

    // Validação da espécie do documento
    if (!isset($dados['especieDocumento']) || empty($dados['especieDocumento'])) {
        $erros[] = "O campo 'Espécie do Documento' é obrigatório.";
    } else {
        $especiesValidas = [
            'DUPLICATA_MERCANTIL_INDICACAO',
            'DUPLICATA_RURAL',
            'NOTA_PROMISSORIA',
            'NOTA_PROMISSORIA_RURAL',
            'NOTA_SEGUROS',
            'RECIBO',
            'LETRA_CAMBIO',
            'NOTA_DEBITO',
            'DUPLICATA_SERVICO_INDICACAO',
            'OUTROS',
            'BOLETO_PROPOSTA',
            'CARTAO_CREDITO',
            'BOLETO_DEPOSITO'
        ];
        if (!in_array($dados['especieDocumento'], $especiesValidas)) {
            $erros[] = "O campo 'Espécie do Documento' deve ser um dos valores válidos.";
        }
    }

    // Validação da data de vencimento
    if (!isset($dados['dataVencimento']) || empty($dados['dataVencimento'])) {
        $erros[] = "O campo 'Data de Vencimento' é obrigatório.";
    } else {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['dataVencimento'])) {
            $erros[] = "O campo 'Data de Vencimento' deve estar no formato YYYY-MM-DD.";
        }
    }

    // Validação do valor
    if (!isset($dados['valor']) || empty($dados['valor'])) {
        $erros[] = "O campo 'Valor' é obrigatório.";
    } else {
        if (!is_numeric($dados['valor'])) {
            $erros[] = "O campo 'Valor' deve ser um número.";
        }
    }

    // Validação do tipo de desconto
    if (isset($dados['tipoDesconto']) && !empty($dados['tipoDesconto'])) {
        if (!in_array($dados['tipoDesconto'], ['VALOR', 'PERCENTUAL'])) {
            $erros[] = "O campo 'Tipo de Desconto' deve ser 'VALOR' ou 'PERCENTUAL'.";
        }
    }

    // Validação dos valores e datas de desconto
    if (isset($dados['valorDesconto1']) && !empty($dados['valorDesconto1'])) {
        if (!is_numeric($dados['valorDesconto1'])) {
            $erros[] = "O campo 'Valor do Desconto 1' deve ser um número.";
        }
        if (isset($dados['dataDesconto1']) && !empty($dados['dataDesconto1'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['dataDesconto1'])) {
                $erros[] = "O campo 'Data do Desconto 1' deve estar no formato YYYY-MM-DD.";
            }
        }
    }
    if (isset($dados['valorDesconto2']) && !empty($dados['valorDesconto2'])) {
        if (!is_numeric($dados['valorDesconto2'])) {
            $erros[] = "O campo 'Valor do Desconto 2' deve ser um número.";
        }
        if (isset($dados['dataDesconto2']) && !empty($dados['dataDesconto2'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['dataDesconto2'])) {
                $erros[] = "O campo 'Data do Desconto 2' deve estar no formato YYYY-MM-DD.";
            }
        }
    }
    if (isset($dados['valorDesconto3']) && !empty($dados['valorDesconto3'])) {
        if (!is_numeric($dados['valorDesconto3'])) {
            $erros[] = "O campo 'Valor do Desconto 3' deve ser um número.";
        }
        if (isset($dados['dataDesconto3']) && !empty($dados['dataDesconto3'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dados['dataDesconto3'])) {
                $erros[] = "O campo 'Data do Desconto 3' deve estar no formato YYYY-MM-DD.";
            }
        }
    }

    // Validação do tipo de juros
    if (isset($dados['tipoJuros']) && !empty($dados['tipoJuros'])) {
        if (!in_array($dados['tipoJuros'], ['VALOR', 'PERCENTUAL'])) {
            $erros[] = "O campo 'Tipo de Juros' deve ser 'VALOR' ou 'PERCENTUAL'.";
        }
    }

    // Validação dos juros e multa
    if (isset($dados['juros']) && !empty($dados['juros'])) {
        if (!is_numeric($dados['juros'])) {
            $erros[] = "O campo 'Juros' deve ser um número.";
        }
    }
    if (isset($dados['multa']) && !empty($dados['multa'])) {
        if (!is_numeric($dados['multa'])) {
            $erros[] = "O campo 'Multa' deve ser um número.";
        }
    }

    // Validação dos informativos
    if (isset($dados['informativos']) && !empty($dados['informativos'])) {
        if (!is_array($dados['informativos'])) {
            $erros[] = "O campo 'Informativos' deve ser um array.";
        } else {
            if (count($dados['informativos']) > 5) {
                $erros[] = "O campo 'Informativos' pode ter no máximo 5 itens.";
            } else {
                foreach ($dados['informativos'] as $informativo) {
                    if (strlen($informativo) > 80) {
                        $erros[] = "Cada item do campo 'Informativos' pode ter no máximo 80 caracteres.";
                    }
                }
            }
        }
    }

    // Validação das mensagens
    if (isset($dados['mensagens']) && !empty($dados['mensagens'])) {
        if (!is_array($dados['mensagens'])) {
            $erros[] = "O campo 'Mensagens' deve ser um array.";
        } else {
            if (count($dados['mensagens']) > 4) {
                $erros[] = "O campo 'Mensagens' pode ter no máximo 4 itens.";
            } else {
                foreach ($dados['mensagens'] as $mensagem) {
                    if (strlen($mensagem) > 80) {
                        $erros[] = "Cada item do campo 'Mensagens' pode ter no máximo 80 caracteres.";
                    }
                }
            }
        }
    }

    // Validação do Split (Distribuição de Crédito)
    if (isset($dados['regraRateio']) && !empty($dados['regraRateio'])) {
        if (!in_array($dados['regraRateio'], ['MENOR_VALOR', 'VALOR_COBRADO', 'VALOR_REGISTRO'])) {
            $erros[] = "O campo 'Regra de Rateio' deve ser 'MENOR_VALOR', 'VALOR_COBRADO' ou 'VALOR_REGISTRO'.";
        }
        if (!isset($dados['repasseAutomaticoSplit']) || empty($dados['repasseAutomaticoSplit'])) {
            $erros[] = "O campo 'Repasse Automático do Split' é obrigatório.";
        } else {
            if (!in_array($dados['repasseAutomaticoSplit'], ['SIM', 'NAO'])) {
                $erros[] = "O campo 'Repasse Automático do Split' deve ser 'SIM' ou 'NAO'.";
            }
        }
        if (!isset($dados['tipoValorRateio']) || empty($dados['tipoValorRateio'])) {
            $erros[] = "O campo 'Tipo de Valor do Rateio' é obrigatório.";
        } else {
            if (!in_array($dados['tipoValorRateio'], ['PERCENTUAL', 'VALOR'])) {
                $erros[] = "O campo 'Tipo de Valor do Rateio' deve ser 'PERCENTUAL' ou 'VALOR'.";
            }
        }
        if (!isset($dados['destinatarios']) || empty($dados['destinatarios'])) {
            $erros[] = "O campo 'Destinatários' é obrigatório.";
        } else {
            if (!is_array($dados['destinatarios'])) {
                $erros[] = "O campo 'Destinatários' deve ser um array.";
            } else {
                if (count($dados['destinatarios']) > 30) {
                    $erros[] = "O campo 'Destinatários' pode ter no máximo 30 itens.";
                } else {
                    foreach ($dados['destinatarios'] as $destinatario) {
                        if (!isset($destinatario['codigoAgencia']) || empty($destinatario['codigoAgencia'])) {
                            $erros[] = "O campo 'Código da Agência' é obrigatório.";
                        }
                        if (!isset($destinatario['codigoBanco']) || empty($destinatario['codigoBanco'])) {
                            $erros[] = "O campo 'Código do Banco' é obrigatório.";
                        }
                        if (!isset($destinatario['floatSplit']) || empty($destinatario['floatSplit'])) {
                            $erros[] = "O campo 'Float Split' é obrigatório.";
                        }
                        if (!isset($destinatario['nomeDestinatario']) || empty($destinatario['nomeDestinatario'])) {
                            $erros[] = "O campo 'Nome do Destinatário' é obrigatório.";
                        }
                        if (!isset($destinatario['numeroContaCorrente']) || empty($destinatario['numeroContaCorrente'])) {
                            $erros[] = "O campo 'Número da Conta Corrente' é obrigatório.";
                        }
                        if (!isset($destinatario['numeroCpfCnpj']) || empty($destinatario['numeroCpfCnpj'])) {
                            $erros[] = "O campo 'Número do CPF/CNPJ' é obrigatório.";
                        }
                        if (!isset($destinatario['parcelaRateio']) || empty($destinatario['parcelaRateio'])) {
                            $erros[] = "O campo 'Parcela do Rateio' é obrigatório.";
                        }
                        if (!isset($destinatario['valorPercentualRateio']) || empty($destinatario['valorPercentualRateio'])) {
                            $erros[] = "O campo 'Valor/Percentual do Rateio' é obrigatório.";
                        }
                    }
                }
            }
        }
    }

    
        if(!empty($erros)) throw new SicrediValidationException($erros);
        

    }
}
