/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var ya_solicitado = false;
var puede_entregar = true;


function arena_ejercicio(id) {
    var ruta = '/arena/' + id;
    window.location.replace(ruta);
}
function solicitar(id) {
    var ruta = '/solicitar/' + id;
    $.get(ruta, function (data, status) {
        if (data.respuesta === 'OK') {
            location.reload();
        } else {
            $('#mensaje-titulo').html('&nbsp;ERROR...');
            $('#mensaje-texto').html(data.mensaje);
            $('#modal-avisos2').click();
        }
    });
}
$(function () {
    setInterval(function () {
        setTSC();
    }, 5000);
});
function setTSC() {
    var ruta = '/getTiempoSinComer';
    $.get(ruta, function (data, status) {
        if (data.porcentaje !== 'null') {
            $('#progress-tsc').width(data.porcentaje + '%');
            contenido = '';
            if (data.porcentaje > 0) {
                contenido += data.porcentaje.toFixed(2) + '%';
            }
            if (data.porcentaje <= 0) {
                contenido += 'MURIENDO';
            }
            $('#progress-tsc').html(contenido);
        }
    });
}
function obtenerTienda() {
    ruta = '/ciudadano/alimentacion/comida/tienda';
    $.get(ruta, function (data, status) {
        console.log(data);
        if (data.estado === 'OK') {
            if (data.message.YA_SOLICITADO) {
                ya_solicitado = true;
            }
            rellenarEstantes(data.message.EJERCICIOS);
        }
    });
}
function obtenerTiendaBebida() {
    ruta = '/ciudadano/alimentacion/bebida/tienda';
    $.get(ruta, function (data, status) {
        if (data.estado === 'OK') {
            if (data.message.YA_SOLICITADO) {
                ya_solicitado = true;
            }
            rellenarEstantes(data.message.EJERCICIOS);
        }
    });
}
function rellenarEstantes(productos) {
    contador_tienda = 1;
    contador_proceso = 1;
    contador_terminado = 1;
    $.each(productos, function (k, v) {
        if (v.ESTADO === 'no_solicitado') {
            contenido = '<span onclick="mostrarTienda(' + v.ID + ');">';
        }
        if (v.ESTADO === 'entregado') {
            contenido = '<span onclick="mostrarTerminado(' + v.ID + ');" class="opacidad">';
        }
        if (v.ESTADO === 'evaluado') {
        contenido = '<span onclick="mostrarTerminado(' + v.ID + ');">';
        }
        contenido += '  <img src="{{ asset('images / iconos / Comida / ') }}' + v.ICONO + '" />';
        contenido += '</span>';
        if (v.ESTADO === 'no_solicitado') {
            $("#tienda-" + contador_tienda).html(contenido);
            contador_tienda++;
        }
        if (v.ESTADO === 'solicitado') {
            mostrarProceso(v.ID);
        }
        if (v.ESTADO === 'evaluado' || v.ESTADO === 'entregado') {
            $("#terminado-" + contador_terminado).html(contenido);
            contador_terminado++;
        }
    });
}

function mostrarTienda(id) {
    var ruta = '/ciudadano/alimentacion/comida/obtenerDetalle/' + id;
    $.get(ruta, function (data, status) {
        if (data.estado === 'OK') {
            contenido = '';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-fecha">';
            contenido += '      <span class="fa fa-calendar">';
            contenido += '          ' + data.message.FECHA.date;
            contenido += '      </span>';
            contenido += '  </div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-enunciado">';
            contenido += '      ' + data.message.ENUNCIADO;
            contenido += '  </div>';
            contenido += '</div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-coste">';
            contenido += '      <span class="fa fa-money"> </span>&nbsp;';
            if (data.message.COSTE.dias !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.dias + '</span>d&nbsp;';
            }
            if (data.message.COSTE.horas !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.horas + '</span>h&nbsp;';
            }
            if (data.message.COSTE.minutos !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.minutos + '</span>m&nbsp;';
            }
            if (data.message.COSTE.segundos !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.segundos + '</span>s&nbsp;';
            }
            if (!data.message.COSTE.segundos &&
                    !data.message.COSTE.minutos &&
                    !data.message.COSTE.horas &&
                    !data.message.COSTE.dias) {
                contenido += '<span style="color:green;">gratis</span>&nbsp;';
            }
            contenido += '  </div>';
            contenido += '</div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12">';
            if (!ya_solicitado) {
                contenido += '      <button class="btn detalle-boton" onclick="solicitar(' + data.message.ID + ');">';
                contenido += '          SOLICITAR';
                contenido += '      </button>';
            } else {
                contenido += '      <button class="btn detalle-boton disabled" title="YA HAY UN EJERCICIO SOLICITADO">';
                contenido += '          SOLICITAR';
                contenido += '      </button>';
            }
            contenido += '  </div>';
            contenido += '</div>';
            $('#detalle-tienda').html(contenido);
        } else {

        }
    });
}
function mostrarProceso(id) {
    var ruta = '/ciudadano/alimentacion/comida/obtenerDetalle/' + id;
    $.get(ruta, function (data, status) {
        if (data.estado === 'OK') {
            contenido = '';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-fecha">';
            contenido += '      <span class="fa fa-calendar">';
            contenido += '          ' + data.message.FECHA.date;
            contenido += '      </span>';
            contenido += '  </div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-enunciado">';
            contenido += '      ' + data.message.ENUNCIADO;
            contenido += '  </div>';
            contenido += '</div>';
            //contenido += '<center><i class="fa fa-spinner fa-spin" id="carga"></i></center>';
            //contenido += '<form action="/ciudadano/alimentacion/comida/entregarAlimento" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="entrega();" id="form-entrega" >';
            //contenido += '  <div class="input-group">';
            //contenido += '      <label class="input-group-btn">';
            //contenido += '          <span class="btn boton-entrega">';
            //contenido += '              Buscar&hellip; <input type="file" id="entrega_proceso" name="ENTREGA" id="ENTREGA" style="display: none;" required>';
            //contenido += '          </span>';
            //contenido += '      </label>';
            //contenido += '      <input type="text" class="form-control" readonly placeholder="Sube tu entrega">';
            //contenido += '  </div>';
            //contenido += '  <input type="hidden" name="id_ejercicio" value="' + data.message.ID + '">';
            //contenido += '  <iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>';
            //contenido += '  <center><span id="error" style="color:red;"></span></center>';
            contenido += '  <div class="row">';
            contenido += '      <div class="col-md-12">';
            contenido += '          <button class="btn detalle-boton" onclick="arena_ejercicio(' + data.message.ID + ');">';
            //contenido += '          <button type="submit" class="btn detalle-boton">';
            contenido += '              ENTREGAR';
            contenido += '          </button>';
            contenido += '      </div>';
            contenido += '  </div>';
            //contenido += '</form>';
            $('#detalle-proceso-individual').html(contenido);
            $("#carga").hide();
        } else {

        }
    });
}
function mostrarTerminado(id) {
    var ruta = '/ciudadano/alimentacion/comida/obtenerDetalle/' + id;
    $.get(ruta, function (data, status) {
        if (data.estado === 'OK') {
            contenido = '';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-fecha">';
            contenido += '      <span class="fa fa-calendar">';
            contenido += '          ' + data.message.FECHA.date;
            contenido += '      </span>';
            contenido += '  </div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-enunciado">';
            contenido += '      ' + data.message.ENUNCIADO;
            contenido += '  </div>';
            contenido += '</div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12 detalle-coste">';
            contenido += '      <span class="fa fa-money"> </span>&nbsp;';
            if (data.message.COSTE.dias !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.dias + '</span>d&nbsp;';
            }
            if (data.message.COSTE.horas !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.horas + '</span>h&nbsp;';
            }
            if (data.message.COSTE.minutos !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.minutos + '</span>m&nbsp;';
            }
            if (data.message.COSTE.segundos !== 0) {
                contenido += '<span style="color:red;">' + data.message.COSTE.segundos + '</span>s&nbsp;';
            }
            if (!data.message.COSTE.segundos &&
                    !data.message.COSTE.minutos &&
                    !data.message.COSTE.horas &&
                    !data.message.COSTE.dias) {
                contenido += '<span style="color:green;">gratis</span>&nbsp;';
            }
            contenido += '  </div>';
            contenido += '</div>';
            contenido += '<div class="row">';
            contenido += '  <div class="col-md-12">';
            if (!ya_solicitado) {
                if (data.message.ESTADO === 'entregado') {
                    contenido += '      <button class="btn detalle-boton" onclick="arena_ejercicio(' + data.message.ID + ');">';
                    contenido += '          MODIFICAR ENTREGA';
                } else {
                    contenido += '      <button class="btn detalle-boton" onclick="solicitar(' + data.message.ID + ');">';
                    contenido += '          VOLVER A SOLICITAR';
                }
                contenido += '      </button>';
            } else {
                contenido += '      <button class="btn detalle-boton disabled" title="YA HAY UN EJERCICIO SOLICITADO">';
                contenido += '          VOLVER A SOLICITAR';
                contenido += '      </button>';
            }
            contenido += '  </div>';
            contenido += '</div>';
            $('#detalle-terminado').html(contenido);
        } else {

        }
    });
}
$(function () {

    // We can attach the `fileselect` event to all file inputs on the page
    $(document).on('change', ':file', function () {
        var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);
    });

    // We can watch for our custom `fileselect` event like this
    $(document).ready(function () {

    });

});
/*
 $('#ENTREGA').bind('change', function () {
 $("#carga").show();
 var tamanio = this.files[0].size;
 if (tamanio > {{ max_size }}) {
 puede_entregar = false;
 $("#error").show();
 $('#error').html('Se ha sobrepasado el tamaño máximo permitido: ' + tamanio + '/{{max_size}}');
 } else {
 $("#error").hide();
 }
 $("#carga").hide();
 });
 $("#form-entrega").submit(function (e) {
 if (puede_entregar) {
 return true;
 }
 return false;
 });
 */

$(document).ready(function () {
    obtenerTienda();
    setTSC();
    setTSB();
    $(".btn-pref .btn").click(function () {
    $(".btn-pref .btn").removeClass("btn-primary").addClass("btn-default");
            $(this).removeClass("btn-default").addClass("btn-primary");
    });
    { % if info.message is defined or info.type is defined % }
    $('#box_modal_result').click();
    { % endif % }

    $(':file').on('fileselect', function (event, numFiles, label) {

        var input = $(this).parents('.input-group').find(':text'),
                log = numFiles > 1 ? numFiles + ' files selected' : label;

        if (input.length) {
            input.val(log);
        } else {
            if (log)
                alert(log);
        }

    });
});