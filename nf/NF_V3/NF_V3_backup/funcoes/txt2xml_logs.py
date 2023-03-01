#!/usr/bin/env python
# -*- coding: utf-8 -*-

logs = []



def addMag (msg): 
    logs.append(
        str ( msg ) 
    )
    
def addLog (nomeLinha, dadosCobol, dadosTxt, encode, numeroLinha, desc): 
    
    logs.append('|'.join([
        "",
        '{:^7}' .format ( str ( nomeLinha   ) ) , 
        '{:^5}' .format ( str ( dadosCobol  ) ) , 
        '{:^5}' .format ( str ( dadosTxt    ) ) , 
        '{:^8}' .format ( str ( encode      ) ) , 
        '{:^10}'.format ( str ( numeroLinha ) ) , 
        '{:^14}'.format ( str ( desc        ) ) , 
        " ",
        "\n"
    ]))
    
    
def log (msg, file): 
    from subprocess import call
    
    my_hash = my_md5(file)
    
    logCobol = "Erro interno: " + my_hash
    
    
    
    
    
    msg = "\n".join([
            ".------------------------------------------------------.  "   ,
            "| LINHA | COB | MAP | Encode | NumLinha |  desc Error  |\\ "  ,
            "|------------------------------------------------------| |"   ,
            "".join(logs)                                                  +
            "|______________________________________________________| |"   ,
            " \\______________________________________________________\\|" ,
            "",
            file
        ])
        
        
    with open("/var/www/html/transf/"+my_hash+".html",'w') as file_htm:
        
        fn = "onclick=\"parent.$('#divJanelaSubPrograma').dialog('close'); return false;\""
        
        fechar = '<div ' + fn + ' style="position: fixed;right: 10px;cursor: pointer;top: 10px;color: white;background-color: red;padding: 6px;border-radius: 25px; user-select: none;">X</div>'
        
        
        file_htm.write(fechar + "<pre>" + msg + "<pre>")
        
        
    msgLog = "\n".join([
        "[ERRO] " + my_hash,
        file,
        msg
    ])
    
    call(["php", "/var/www/html/nf/NF_V3/NF_V3/funcoes/flog_py.php", msgLog])
    
    print(logCobol)
    
    exit()
    
def addOnTable():
    pass
    


def my_md5(fname):
    import hashlib
    hash_md5 = hashlib.md5()
    with open(fname, "rb") as f:
        for chunk in iter(lambda: f.read(4096), b""):
            hash_md5.update(chunk)
    return hash_md5.hexdigest()
    












