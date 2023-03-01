<?php
/**
 * @name      	CIntegracaoERP
 * @version   	alfa
 * @copyright	2015-05-18 &copy; Softdib
 * @author    	Guilherme Pinto
 * @description Classe elaborada para tratar a comunicação do recebimento e retorno dos arquivos TXT para o ERP
*/

class CIntegracaoERP{
	
	// Atributos aos arquivos
	public $tipoArquivo;
	public $arquivoTXT;
	
	public $CNPJ;
	public $cUF;
	public $tpEmis;
	public $chave;
	public $protocolo;
	public $recibo;
	public $justificativa;
	public $ambiente;
	
	public $modelo;
	public $ano;
	public $serie;
	public $numInicial;
	public $numFinal;
	
	public $sequencia;
	public $cOrgao;
	public $dhEvento;
	public $descCorrecao;

	// Atributos privados
	private $caixaEntrada;
	private $caixaSaida; 

	/*
	* Método publico para que dá entrada no arquivo da Nota carrega atributos
	*/
	public function mIntegracaoERP($pCaminho){
		// Verifica se arquivo passado existe
		if(!file_exists($pCaminho)){
			$this->log("Arquivo [".$pCaminho."] nao eh um arquivo valido para integracao.");
			return false;
		}
		
		// Verificar o tipo de arquivo solicitado
		// /user/nfe/cnpj/arquivo.txt
		$nomeArquivo 		= explode("/",$pCaminho);
		$tipoArquivo 		= explode("-",end($nomeArquivo));
                //$tipoArquivo            = explode("_",$tipoArquivo[0]);
		$this->tipoArquivo 	= $tipoArquivo[0];

                system("/usr/bin/dos2unix ".$pCaminho);
                
		if(!$this->arquivoTXT = utf8_encode(file_get_contents($pCaminho))){
                        echo "Nao foi possivel ler o arquivo de entrada, verifique sua permissao";
			$this->log("Nao foi possivel ler o arquivo de entrada, verifique sua permissao");
			return false;
		}
		
		switch($this->tipoArquivo){
			case "NFE":
                        case "NFC":
			break;
			
			case "CNFE":
                        case "CNFC":
				$this->arquivoTXT = file_get_contents($pCaminho);
				if(!$this->arquivoTXT){
					$this->log("Nao foi possivel ler o arquivo de entrada, verifique sua permissao");
					return false;
				}
				
				$dadosArquivo = explode("|",$this->arquivoTXT);

				$this->CNPJ                     = $dadosArquivo[0];
				$this->cUF			= $dadosArquivo[1];
				$this->tpEmis			= $dadosArquivo[2];
				$this->chave			= $dadosArquivo[3];
				$this->protocolo		= $dadosArquivo[4];
				$this->justificativa            = $dadosArquivo[5];
				$this->ambiente			= substr($dadosArquivo[6],0,1);
                                $this->modelo                   = substr($this->chave,20,2);

			break;
			
			case "INFE":
                        case "INFC":
				$dadosArquivo = explode("|",$this->arquivoTXT);

				$this->CNPJ			= $dadosArquivo[0];
				$this->cUF			= $dadosArquivo[1];
				$this->tpEmis			= $dadosArquivo[2];
				$this->modelo			= $dadosArquivo[3];
				$this->ano			= $dadosArquivo[4];
				$this->serie			= ltrim($dadosArquivo[5],"0");
				$this->numInicial		= ltrim($dadosArquivo[6],"0");
				$this->numFinal			= ltrim($dadosArquivo[7],"0");
				$this->justificativa            = trim($dadosArquivo[8]);
				$this->ambiente			= substr($dadosArquivo[9],0,1);
			break;

			case "CCNFE":
                        case "CCNFC":
				$dadosArquivo = explode("|",$this->arquivoTXT);

				$this->CNPJ                     = $dadosArquivo[0];
				$this->cUF			= $dadosArquivo[1]; 
				$this->tpEmis			= $dadosArquivo[2];
				$this->chave			= $dadosArquivo[3];
				$this->sequencia		= ltrim($dadosArquivo[4],0);
				$this->dhEvento			= $dadosArquivo[5];
				$this->descCorrecao		= trim($dadosArquivo[6]);
				$this->ambiente			= substr($dadosArquivo[7],0,1);

			break;

			case "SNFE":
                        case "SNFC":
				$dadosArquivo = explode("|",$this->arquivoTXT);

				$this->CNPJ			= $dadosArquivo[0];
				$this->cUF			= $dadosArquivo[1];
				$this->tpEmis			= $dadosArquivo[2];
    				$this->ambiente			= substr($dadosArquivo[3],0,1);
                                
			break;
			
			case "RNFE":
                        case "RNFC":
				$dadosArquivo = explode("|",$this->arquivoTXT);

				$this->CNPJ				= $dadosArquivo[0];
				$this->cUF				= $dadosArquivo[1];
				$this->tpEmis			= $dadosArquivo[2];
				$this->recibo			= $dadosArquivo[3];
				$this->chave			= $dadosArquivo[4];
				$this->ambiente			= substr($dadosArquivo[5],0,1);

			break;
		}
		
		return true;
		// Mover da pasta Processar para Processado
                if($this->tipoArquivo != "SNFE"){
                    if(!$this->__MoverProcessarProcessado($pCaminho)){
                            return false;
                    }
                }
		return true;
		
	}
		
		
   /*
	* Método privado para mover o arquivo de nota da pasta Processar para Processado
	*/
	private function __MoverProcessarProcessado($pArquivo){
		$arquivoAntigo 	= $pArquivo;
		$arquivoNovo 	= str_replace("Processar","Processado",$pArquivo);
		
		if(!rename($arquivoAntigo, $arquivoNovo)){
			$this->log("Não é possível mover o arquivo de Processar para Processar");
			return false;
		}
		return true;
	}
	
	/*
	* Método privado para chamar o COBOL
	*/
	private function mChamarCobol(){
		system('COBDIR=/usr/cobol_mf_4.1');
		system('COBPATH=/user/objetos');
		system('TERM=ansi');
		system('export COBDIR COBPATH TERM');
		system('PATH=.:/sbin:/bin:/usr/sbin:/usr/bin:/usr/X11R6/bin:/user/bindib:/root/bin:/usr/cobol_mf/bin');
		system('LD_LIBRARY_PATH="/usr/cobol_mf_4.1/coblib" ; export LD_LIBRARY_PATH');
		system('set LANG=english_us.ascii');
		system('set LC_CTYPE=""');
		system('export LC_CTYPE LANG');
		system('EXTFH="/user/bindib/extfh.cfg"; export EXTFH');
		system('HOME='.$this->contribuinteBase);
		
		putenv("LD_LIBRARY_PATH=/usr/cobol_mf/coblib");
		putenv("COBDIR=/usr/cobol_mf");
		putenv("COBPATH=/user/objetos");
		putenv("COBSW=-F-l-Q");
		putenv("EXTFH=/user/bindib/extfh.cfg");
		putenv("TERM=linux");
		putenv("HOME=".$this->contribuinteBase);

		shell_exec("TERM=linux; export TERM; cd ".$this->contribuinteBase."; /usr/cobol_mf/cob41/bin/cobrun -F-l-Q /user/objetos/SVE350SNF.int  root &");
	}
	
	/*
	* Método privado para gravar log de Erro das funcoes
	*/
	public function log($erro,$programa="CIntegracaoERP"){
		$arquivo = "/user/nfe/log";
		file_put_contents($arquivo,$programa." (".date("d/m/Y H:i:s").") -> ".$erro."\n", FILE_APPEND);
	}
	
	/*
	* Método publico para gravar arquivo de retorno TXT da chamada da consulta para o cobol
	*/
	public function mRetornoCobol($pArray,$pTipo){
            $corpoArquivo = implode("|",$pArray);
            $nomeArquivo = $pTipo;
            if(!file_put_contents("/user/nfe/".$pArray['cnpj']."/CaixaSaida/Sefaz/".$nomeArquivo, $corpoArquivo)){
                    echo "Erro ao gravar arquivo de Integracao com ERP, verifique permissoes!";
                    return false;
            }
            //$this->mChamarCobol();
            return true;
	}
		
}
?>