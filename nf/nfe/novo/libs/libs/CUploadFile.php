<?php
/**
 * @name      	CUploadFile
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer upload de um arquivo local para o diretorio específico
 * @TODO 		Fazer tudo.
*/

/**
 * Classe CUploadFile
 */

class CUploadFile{
	/*
     *	 Atributos (campos) da tabela Contribuintes
     */
	public $mensagemErro = "";

	/*
     *	 @function	upload - efetua upload do arquivo.
	 *	 @parms		Campo 			- campo $_POST['file'] recuperado da pagina
	 *				CaminhoSalvar	- caminho em que deseja salvar o post, /var/www/html/...
	 *				Extensoes 		- array de extensões válidas para upload, ('jpg','png','bmp')
	 *				Tamanho Max		- tamanho máximo desejado prefixo binário, 10KB ou 5MB ou 1GB
	 *				Novo Nome		- outro nome que sera salvo o arquivo
	 *	 @autor		Guilherme Silva
     */
	function upload($pCampo, $pCaminhoSalvar, $pExtensoes="", $tamanhoMax="", $pNovoNome=""){
		// Verifica se houve erro no carregamento
		if($pCampo['error'] > 0){
			$this->mensagemErro = "CUploadFile -> erro ".$pCampo['error']." ao carregar o arquivo ";
			return false;
		}
		// Caso tenha extenções validar
		if($pExtensoes!=""){
			if(!is_array($pExtensoes)){
				$this->mensagemErro = "CUploadFile -> parametro Extensoes deve ser um array ";
				return false;
			}
			$temp = explode(".", $pCampo["name"]);
			$extension = end($temp);
			
			if(!in_array($extension, $pExtensoes)){
				$this->mensagemErro = "CUploadFile -> a extensao do certificado deve ser .PFX ";
				return false;
			}
		}
		// Caso tenha setado tamanho maximo validar
		if($tamanhoMax!=""){
			$prefixo = substr(strtoupper($tamanhoMax),-2);
			$tamanhoMax = substr(strtoupper($tamanhoMax),0, -2);
			switch($prefixo){
				case "KB":
					$tamanhoMax = $tamanhoMax*1024;
				break;
				case "MB":
					$tamanhoMax = ($tamanhoMax*1024)*1024;
				break;
				case "GB":
					$tamanhoMax = (($tamanhoMax*1024)*1024)*1024;
				break;
				default:
					$this->mensagemErro = "CUploadFile -> parametro tamanhoMax nao identificado corretamente";
					return false;
				break;
			}
			if($pCampo["size"] > $tamanhoMax){
				$this->mensagemErro = "CUploadFile -> parametro tamanhoMax nao identificado corretamente";
				return false;
			}
		}

		if($pNovoNome == ""){
			$pNovoNome = $pCampo["name"];
		}

		if(!move_uploaded_file($pCampo["tmp_name"], $pCaminhoSalvar.$pNovoNome)){
			$this->mensagemErro = "CUploadFile -> Erro ao efetuar o upload ";
			return false;
		}

		return true;
	}
}
  
 ?>