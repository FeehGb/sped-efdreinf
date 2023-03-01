<?php 
// Autor : Fernando H. Crozetta
// Data  : 22/06/2017
// Descricao : Script que carrega o arquivo Config.ini do programa de NFE antigo e 
// 				busca os dados de cnpj(chave), junto com a senha(valor), 
//				criando um arquivo para cara cnpj encontrado.

// 				Cada arquivo craido será copiado de um arquivo template,
//				que deve ficar dentro do mesmo diretório deste programa.
// Modo de uso: entrar no diretorio deste programa, e executar o comando:
//		php cria_cnpj_ini.php /var/www/html/nf/nfe/config/config.ini
// Substituir o caminho e arquivo, caso nao seja o padrão


//caminho + Arquivo do config.ini antigo
$arquivo_antigo = $argv[1];
$config = parse_ini_file("../config/config.ini");
$dir_saida = $config['dados']."/config_cliente/";
$template = "template_cnpj.ini";

if (!file_exists($arquivo_antigo)) {
	echo "O arquivo nao existe";
	exit(1);
}
// Carrega arquivo antigo
$antigo = parse_ini_file($arquivo_antigo);

foreach ($antigo as $key => $value) {
	if (preg_match('/[0-9]{14}/', $key)) {
		if (!file_exists($dir_saida.$key.".ini")) {
			print_r("[Criando] ".$dir_saida.$key.".ini\n");
			exec("cp ".$template." ".$dir_saida.$key.".ini");
			exec('sed -i "s/ALTERAR_CNPJ/'.$key.'/g" '.$dir_saida.$key.".ini");
			exec('sed -i "s/ALTERAR_SENHA_CERT/'.$value.'/g" '.$dir_saida.$key.".ini");
		}
	}
}


?>