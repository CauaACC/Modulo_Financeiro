<?php
Function FWA_URL_Prefixo(){
    if(WB_GRUPO!='' and WB_PROJETO!=''){
        return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
    }else if(WB_PROJETO!=''){
        return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
    }else{
        return "/inscricoes";
    }
//        
//  Switch ($_SERVER['SERVER_NAME']){
//    Case "localhost":
//    Case "particular.localhost":
//        if(COD_EVENTO == 1){
//            return "/cbmp/9congresso/inscricoes";
//        }
//        if(COD_EVENTO == 2){
//            return "/inesco/4congresso/inscricoes";
//        }
//        if(COD_EVENTO == 3){
//            return "/inesco/5mostraparanaensesaude/inscricoes";
//        }
//        if(COD_EVENTO == 4){
//            return "/inesco/5congresso/inscricoes";
//        }
//        break;
//    Case "zaqueu.no-ip.org:8245":
//    Case "zaqueu.no-ip.org":
//    Case "zaqueu.ddns.net:8245":
//    Case "zaqueu.ddns.net":
//    Case "opctec.com.br":
//    Case "www.opctec.com.br":
//        if(COD_EVENTO == 1){
//            return "/cbmp/9congresso/inscricoes";
//        }
//        if(COD_EVENTO == 2){
//            return "/inesco/4congresso/inscricoes";
//        }
//        if(COD_EVENTO == 3){
//            return "/inesco/5mostraparanaensesaude/inscricoes";
//        }
//        if(COD_EVENTO == 4){
//            return "/inesco/5congresso/inscricoes";
//        }
//        if(COD_EVENTO == 5){
//            return "/cresspr/plenaria/inscricoes";
//        }
//        break;
//    Case "sis.congressosaudepublica.org.br":
//        if(COD_EVENTO == 4){
//            return "/inesco/5congresso/inscricoes";
//        }
//        break;
//    Case "eventos.localhost":
//        if(WB_GRUPO!='' and WB_PROJETO!=''){
//            return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
//        }else if(WB_PROJETO!=''){
//            return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
//        }else{
//            return "/inscricoes";
//        }
//        break;
//    Case "eventos.test":
//        if(WB_GRUPO!='' and WB_PROJETO!=''){
//            return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
//        }else if(WB_PROJETO!=''){
//            return "/".WB_GRUPO."/".WB_PROJETO."/inscricoes";
//        }else{
//            return "/inscricoes";
//        }
//        break;
//    Default:
//      #echo $FWA_URL_Prefixo;
//      return false;
//    break;
//  }
}
