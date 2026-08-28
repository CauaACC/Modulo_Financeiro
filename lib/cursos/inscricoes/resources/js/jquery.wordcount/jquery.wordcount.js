/*
 * 
 * Textarea Word Count Jquery Plugin 
 * Version 1.0
 * 
 * Copyright (c) 2008 Roshan Bhattarai
 * website : http://roshanbh.com.np
 * 
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * 
*/

jQuery.fn.wordCount = function(params){
    var p = {
        counterElement         : "display_count",
        amountWord             : 3,
        fullsizeWordAcceptable : 10
    };
    var maxlimit;
    var word;
    var typed_words;
    var total_words;
    //var teclaLiberada = true;
    var current_word;

    if(params) {
        jQuery.extend(p, params);
    }

    this.keyup(function(e) {
//        switch(e.keyCode) {
//        case  8:
//        case 37:
//        case 38:
//        case 39:
//        case 40:
//            break;
//        default:
            word = this.value;
            typed_words = word.split(/[\s\.\?]+/);
            total_words = typed_words.length;
            current_word = typed_words[total_words-1];
            if(word == "") { total_words = 0; }
            //Verifica se a palavra atingiu o número de caracteres máximo
//            if(current_word.length > p.fullsizeWordAcceptable) {
//                return false;
//            }
            
            jQuery('#'+p.counterElement).html(total_words);
            jQuery('#current_word').html(current_word); //" | "+posicaoCursor+" | posEsq:"+posEsquerda+"-posDir:"+posDireita);
            
//            if(this.value.length>3) {
//                return false;
//            }
//            break;
//        }
    });
    
    //for each keypress function on text areas
//    this.keyup(function() {
//        
//        return false;
//        //alert(e.keyCode);
//        //total_words = this.value.length-1;
//        word = this.value;
//        //word = word.substring(0, word.length);
//        typed_words = word.split(/[\s\.\?]+/);
//        total_words = typed_words.length;
//        current_word = typed_words[total_words-1];
//        if(word == "") { total_words = 0; }
//        
//        //Obter a posição do cursor dentro da textarea
//        posicaoCursor = getCaret(this);
//        //Obter espaço a esquerda
//        posEsquerda = word.lastIndexOf(" ", posicaoCursor-1);
//        //if(posEsquerda == -1) { posEsquerda = 0; }
//        posDireita  = word.indexOf(" ", posicaoCursor);
//        if(posDireita == -1) { posDireita = word.length; }
//        //Obter a palavra atual a partir da posição
//        current_word = word.substring(posEsquerda, posDireita);
//        //====
//        
//        if(total_words > p.amountWord) {
//            total_words = p.amountWord;
//            maxlimit = this.value.length-1;
//            this.value = this.value.substring(0, maxlimit);
//        } else {
//            if(current_word.length > p.fullsizeWordAcceptable) {
//                current_word = current_word.substring(0, current_word.length-1);
//                posicaoCursor--;
//                maxlimit = this.value.length-1;
//                this.value = this.value.substring(0, maxlimit);
//            }
//        }
//        jQuery('#'+p.counterElement).html(total_words);
//        jQuery('#current_word').html(current_word+" | "+posicaoCursor+" | posEsq:"+posEsquerda+"-posDir:"+posDireita);
//    });
    
    function getCaret(el) {
        if (el.selectionStart) {
            //alert("1 - " + el.selectionStart);
            return el.selectionStart;
         } else if (document.selection) {
            el.focus();
            var r = document.selection.createRange();
            if (r == null) {
                return 0;
            }
            var re = el.createTextRange(),
            rc = re.duplicate();
            re.moveToBookmark(r.getBookmark());
            rc.setEndPoint('EndToStart', re);
            //alert("2 - " + rc.text.length);
            return rc.text.length;
        }
        return 0;
    }    
};