/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function setRelojDigital(dias, horas, minutos, segundos, id_div, class_div) {
    if (id_div !== null) {
        $('#' + id_div).html('');
    } else {
        $('.' + class_div).html('');
    }

    contenido = '';
    contenido += '<div class="reloj-digital">';
    if (dias >= 0) {
        contenido += '  <figure class="reloj-digital-dias">';
        if (dias >= 10) {
            contenido += '      <div><ul><li>' + dias + '</li></ul></div>';
        } else {
            contenido += '      <div><ul><li>0' + dias + '</li></ul></div>';
        }
        contenido += '      <figcaption>dias</figcaption>';
        contenido += '  </figure>';
    }
    if (horas >= 0) {
        contenido += '  <figure>';
        if (horas >= 10) {
            contenido += '      <div><ul><li>' + horas + '</li></ul></div>';
        } else {
            contenido += '      <div><ul><li>0' + horas + '</li></ul></div>';
        }
        contenido += '      <figcaption>horas</figcaption>';
        contenido += '  </figure>';
        contenido += '  <span>:</span>';
    }
    if (minutos >= 0) {
        contenido += '  <figure>';
        if (minutos >= 10) {
            contenido += '      <div><ul><li>' + minutos + '</li></ul></div>';
        } else {
            contenido += '      <div><ul><li>0' + minutos + '</li></ul></div>';
        }
        contenido += '      <figcaption>minutos</figcaption>';
        contenido += '  </figure>';
        contenido += '  <span>:</span>';
    }
    if (segundos >= 0) {
        contenido += '  <figure>';
        if (segundos >= 10) {
            contenido += '      <div><ul><li>' + segundos + '</li></ul></div>';
        } else {
            contenido += '      <div><ul><li>0' + segundos + '</li></ul></div>';
        }
        contenido += '      <figcaption>segundos</figcaption>';
        contenido += '  </figure>';
    }
    contenido += '</div>';

    if (id_div !== null) {
        $('#' + id_div).html(contenido);
    } else {
        $('.' + class_div).html(contenido);
    }
}

function segundosToDias(segundos) {
    var result = {'dias': 0, 'horas': 0, 'minutos': 0, 'segundos': 0};
    if (segundos < 0) {
        return result;
    }
    result['dias'] = Math.floor(segundos / 86400);
    result['horas'] = Math.floor(segundos / 3600) - (result['dias'] * 24);
    result['minutos'] = Math.floor(segundos / 60) - (result['dias'] * 24 * 60) - (result['horas'] * 60);
    result['segundos'] = Math.floor(segundos) - (result['dias'] * 24 * 60 * 60) - (result['horas'] * 60 * 60) - (result['minutos'] * 60);
    return result;
}

/**
 * Retorna el string sin las variables a cero
 * @param {int} dias
 * @param {int} horas
 * @param {int} minutos
 * @param {int} segundos
 * @returns {String}
 */
function segundosToDiasFormato(dias, horas, minutos, segundos) {
    var result = '';
    if(dias){
        result += dias + 'd '; 
    }
    if(horas){
        result += horas + 'h '; 
    }
    if(minutos){
        result += minutos + 'm '; 
    }
    if(segundos){
        result += segundos + 's '; 
    }
    return result;
}
