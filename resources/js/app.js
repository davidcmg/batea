/**
 * Lanza una ventana modal para borrar un usuario.
 */
$('#modalBorrarUsuario').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var modal = $(this);
  modal.find('.modal-form-id').text(id);
  modal.find('form').attr('action','/admin/users/'+id+'/delete');
});

/**
 * Lanza una ventana modal para borrar un servidor.
 */
$('#modalBorrarServidor').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var modal = $(this);
  modal.find('.modal-form-id').text(id);
  modal.find('form').attr('action','/admin/servers/'+id+'/delete');
});

/**
 * Lanza una ventana modal para borrar una tarea.
 */
$('#modalBorrarTarea').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var modal = $(this);
  modal.find('.modal-form-id').text(id);
  modal.find('form').attr('action','/admin/tasks/'+id+'/delete');
});

/**
 * Lanza una ventana modal para restaurar una copia de seguridad.
 */
$('#modalRestaurar').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var id = button.data('id');
  var depth = button.data('depth');
  var modal = $(this);
  modal.find('.modal-form-id').text(id);
  modal.find('form').attr('action','/admin/tasks/'+id+'/'+depth+'/restore');
});

/**
 * Habilita el plugin jqCron para que el formato cron sea user friendly.
 */
$('.jqCron').jqCron({
  enabled_minute: false,
  multiple_dom: true,
  multiple_month: true,
  multiple_mins: true,
  multiple_dow: true,
  multiple_time_hours: true,
  multiple_time_minutes: true,
  default_period: 'week',
  default_value: '0 4 * * 7',
  bind_to: $('.jqCron-input'),
  bind_method: {
    set: function($element, value) {
      $element.val(value);
    }
  },
  no_reset_button: false,
  lang: 'es'
});


var localTime = $('#server-clock').text();
var splitLocalTime = localTime.split(':');
var h = parseInt(splitLocalTime[0]);
var m = parseInt(splitLocalTime[1]);
var s = parseInt(splitLocalTime[2]);
var currentTime = new Date();
currentTime.setHours(h);
currentTime.setMinutes(m);
currentTime.setSeconds(s);


$(document).ready(function(){
  var serverClock = $('#server-clock');
  function update() {
    var localTime = serverClock.text();
    var splitLocalTime = localTime.split(':');
    var h = parseInt(splitLocalTime[0]);
    var m = parseInt(splitLocalTime[1]);
    var s = parseInt(splitLocalTime[2]);
    var currentTime = new Date();
    currentTime.setHours(h);
    currentTime.setMinutes(m);
    currentTime.setSeconds(s);
    var time2 = new Date(currentTime.valueOf() + 1000);
    var clock = time2.toTimeString().split(" ")[0];
    serverClock.html(clock);
    setTimeout(update, 1000);  
  }
  setTimeout(update, 1000);
});