<?php
    
    
    /**
     * function qrcode_gerarImagem
     * 
     * Cria a imagem do qrcode
     * 
     * @param String $url Url do qr code
     * @return Array $retorno [
     *      String url    => url de base do qrcode
     *      String file   => caminho do arquivo de imagem gerado
     *      String base64 => 64 da imagem do qrcode
     * ]
     */
    function qrcode_gerarImagem($url, $fileName)
    {
        // Lib que gera a imagem do qrCode 
        require_once("../libs/phpqrcode/qrlib.php");
        
        // faz um hash da url
        // $fileName = hash("md5", $url);
        
        // faz do hash um nome de arquivo de imagem
        // $fileName.=".png";
        
        // path da pasta temp
        // $tempPath = "{$tempDIr}";
        
        // caminho completo do temp com a imagem
        // $imgTempPath = "{$tempPath}$fileName";
        
        // Gera a imagem do qrCode
        // QRcode::png($url, $fileName);
        QRcode::png($url, $fileName);
        
        // pega o 64 da imagem criada
        $type = pathinfo($fileName, PATHINFO_EXTENSION);
        $data = file_get_contents($fileName);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        
        // prepara o retorno
        $retorno = array(
            "file"   => $fileName , // caminho do arquivo de imagem gerado
            "base64" => $base64   , // 64 da imagem do qrcode
            "url"    => $url      , // url de base do qrcode
        );
        
        // retorna
        return $retorno;
    }
    
    
    
    
    
    
    /*
    
    
    php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR &&  php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php /user/nfe/82206004000195/CaixaEntrada/Processar/NFE-000111202-NE-314903-20180104-170815.TXT PR 
    
    
    */
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    