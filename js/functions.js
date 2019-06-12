function ajax_request(send_url, send_data, on_success, options) {

    var send_type = '';

    if (options != null && typeof(options.type) != 'undefined' && options.type != '')
        send_type = options.type;
    else
        send_type = 'post';

    var datestamp = new Date().getTime();
    var response_data = $.ajax({
        url: send_url + '#' + datestamp,
        data: send_data,
        type: send_type,
        beforeSend: function () {
            show_loading();
        },
        complete: function () {
            hide_loading();
        },
        success: function (resp) {
            if ($.isFunction(on_success)) {
                on_success(resp, options);
            } else {
                eval(on_success + "(resp, options);");
            }
        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });
}

function show_loading() {
    $('#loader').fadeIn(300);
}

function hide_loading() {
    $('#loader').fadeOut(300);
}

$.fn.selectRange = function (start, end) {
    return this.each(function () {
        var self = this;
        if (self.setSelectionRange) {
            self.focus();
            self.setSelectionRange(start, end);
        } else if (self.createTextRange) {
            var range = self.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};

function viewport() {
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return {width: e[a + 'Width'], height: e[a + 'Height']};
}

function focusField(field) {
    var field_obj = null;
    if (field != '') {
        field_obj = $('#' + field).length > 0 ? $('#' + field) : $('[name="' + field + '"]');

        if (field_obj.length <= 0) return false;

        switch (field_obj.attr("type")) {
            case "text":
                field_obj.selectRange(0, field_obj.val().length);
                break;
            default:
                field_obj.focus();
                break;
        }
    }
    return true;
}

function setErrorField(field) {
    var field_obj = null;
    if (field != '') {
        field_obj = $('#' + field).length > 0 ? $('#' + field) : $('[name="' + field + '"]');

        if (field_obj.length <= 0) return false;
        else
            field_obj.addClass("form-error");
    }
    return true;
}

function check_response(response) {
    response = unescape(response);
    var regex = new RegExp('\{RESPONSE CODE: "([0-9-]*)"\}(\{MSG: "([^"]*)"\}\)?({INFO: "([^."]*)"\})?', "i");

    if (response.match(regex) != null) {
        var response_info = response.match(regex);
        var response_code = parseInt(response_info[1]);
        var response_msg = typeof(response_info[3]) != 'undefined' ? response_info[3] : '';
        var response_field = typeof(response_info[5]) != 'undefined' ? response_info[5] : '';

        return {'code': response_code, 'msg': response_msg, 'field': response_field};
    } else {
//errorMsg("Ha ocurrido un error al enviar los datos.");
        return false;
    }

}

function default_window_request_act(response_string) {

    var response = check_response(response_string);

    switch (response.code) {

        default:
            alert("Ha ocurrido un error");
            break;
        case 0:
            alert(response.msg);
            focusField(response.field);
            setErrorField(response.field);
            break;
        case 1:
            if (response.msg)
                $(location).attr("href", response.msg);
            else
                $("#window").remove();
            break;

    }
}

function reload_window_request_act(response_string) {

    var response = check_response(response_string);

    switch (response.code) {

        default:
            alert("Ha ocurrido un error");
            break;
        case 0:
            alert(response.msg);
            focusField(response.field);
            setErrorField(response.field);
            break;
        case 1:
            location.reload();
            break;

    }
}

function destroyWindow() {
    var eventWindow = $("#window");

    if (eventWindow.length > 0)
        eventWindow.remove();

    return false;
}

function login(response_string) {
    var response = check_response(response_string);

    switch (response.code) {

        default:
            alert("Ha ocurrido un error, no pudo ser procesada la solicitud");
            break;
        case -1:
        case 0:
            alert(response.msg);
            $('.captcha_refresh').click();
            focusField(response.field);
            setErrorField(response.field);
            break;

        case 1:
            $("#loginForm").submit();
            break;

    }
}

function reset_password(response_string) {
    var response = check_response(response_string);

    switch (response.code) {

        default:
            alert("Ha ocurrido un error, no pudo ser procesada la solicitud");
            break;
        case -1:
        case 0:
            alert(response.msg);
            $('.captcha_refresh').click();
            focusField(response.field);
            setErrorField(response.field);
            break;

        case 1:
            $(location).attr("href", response.msg);
            break;

    }
}

function modificar_usuario(userid, show_user_access) {

    var user_firstname = '';
    var user_lastname = '';
    var user_email = '';
    var user_ci = '';
    var user_access = '';
    var user_ps = '';
    var user_rs = '';

    var response_data = $.ajax({
        url: 'users.php?action=userinfo_request&id=' + userid,
        beforeSend: function () {
            $('#loader').show();
        },
        complete: function () {
            $('#loader').hide();
        },
        success: function (resp) {

            var regex = new RegExp('({USER_FIRSTNAME: "([^"]*)"})?({USER_LASTNAME: "([^"]*)"})?({USER_EMAIL: "([^"]*)"})?({USER_CI: "([^"]*)"})?({USER_ACCESS: "([^."]*)"})?({USER_PS: "([^."]*)"})?({USER_RS: "([^."]*)"})?', "i");

            if (resp.match(regex) != null) {
                var userinfo = resp.match(regex);
                user_firstname = typeof(userinfo[2]) != 'undefined' ? userinfo[2] : '';
                user_lastname = typeof(userinfo[4]) != 'undefined' ? userinfo[4] : '';
                user_email = typeof(userinfo[6]) != 'undefined' ? userinfo[6] : '';
                user_ci = typeof(userinfo[8]) != 'undefined' ? userinfo[8] : '';
                user_access = typeof(userinfo[10]) != 'undefined' ? userinfo[10] : '';
                user_ps = typeof(userinfo[12]) != 'undefined' ? userinfo[12] : '';
                user_rs = typeof(userinfo[14]) != 'undefined' ? userinfo[14] : '';

                var eventWindow = $("#window");
                var target = $("#content");

                if (eventWindow.length > 0)
                    destroyWindow();

                var user_access_html = '';

                if (show_user_access == "true")
                    user_access_html = '<h4>Permisos de Usuario</h4>' + user_access;

                var htmlwindow = $(
                    '<div id="window" class="modal fade">' +
                    '<div class="modal-dialog"><form class="form-horizontal">' +
                    '<div class="modal-content">' +

                    ' <div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '<h3 class="modal-title">Modificar Usuario</h3>' +
                    '</div>' +

                    '<div class="modal-body">' +

                    '<div class="form-group">' +
                    '<div class="col-xs-6">' +
                    '<label for="user_firstname">Nombre</label>' +
                    '<input type="text" name="user_firstname" class="form-control" value="' + user_firstname + '" id="user_firstname"/>' +
                    '</div>' +
                    '<div class="col-xs-6">' +
                    '<label for="user_lastname">Apellidos</label>' +
                    '<input type="text" name="user_lastname" class="form-control" value="' + user_lastname + '" id="user_lastname"/>' +
                    '</div></div>' +


                    '<div class="form-group">' +
                    '<div class="col-xs-6">' +

                    '<label for="user_ci" >Cédula de Identidad</label>' +
                    '<input type="text" name="user_ci" class="form-control" value="' + user_ci + '" id="user_ci" disabled />' +
                    '</div>' +
                    '<div class="col-xs-6">' +
                    '<label for="user_email">Correo Electrónico</label>' +
                    '<input type="text" name="user_email" class="form-control" value="' + user_email + '" id="user_email" autocomplete="off"/>' +

                    '</div></div>' +

                    '<div class="form-group">' +
                    '<div class="col-xs-6">' +

                    '<label for="pregunta_seguridad" >Pregunta de Seguridad</label>' +
                    '<input type="text" name="pregunta_seguridad" class="form-control" value="' + user_ps + '" id="pregunta_seguridad" />' +
                    '</div>' +
                    '<div class="col-xs-6">' +
                    '<label for="respuesta_seguridad">Respuesta de Seguridad</label>' +
                    '<input type="text" name="respuesta_seguridad" class="form-control" value="' + user_rs + '" id="respuesta_seguridad" />' +

                    '</div></div>' +

                    '<div class="form-group">' +
                    '<div class="col-xs-6">' +

                    '<label for="user_pass" >Nueva Contraseña</label>' +
                    '<input type="password" name="user_pass" class="form-control" id="user_pass" autocomplete="off"/>' +
                    '</div>' +
                    '<div class="col-xs-6">' +
                    '<label for="user_re_pass" >Repita Contraseña</label>' +
                    '<input type="password" name="user_re_pass" class="form-control" id="user_re_pass"  />' +
                    '</div>' +
                    '</div>' +

                    user_access_html +

                    '</div><!-- /.modal-body -->' +

                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
                    '<button type="submit" class="btn btn-primary btn-action">Guardar Cambios</button>' +


                    '</div><!-- /.modal-content -->' +
                    '</form></div><!-- /.modal-dialog -->' +
                    '</div>');

                $("body").prepend(htmlwindow);

                htmlwindow.modal({backdrop: false})
                    .trigger("windowEvents").find(".btn-action").click(function (e) {
                        e.preventDefault();
                        modificacion_usuario(userid, $(this).closest("form"));
                    });


                $("#window > .modal-dialog").draggable().css("cursor", "move");


            }
        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });


    return false;
}

function window_agregar_sede(cliente_id) {
    destroyWindow();


    var htmlwindow = $(
        '<div id="window" class="modal fade small">' +
        '<div class="modal-dialog"><form class="form-horizontal">' +
        '<div class="modal-content">' +

        ' <div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
        '<h3 class="modal-title">Agregar Sede</h3>' +
        '</div>' +

        '<div class="modal-body">' +

        '<div class="form-group">' +
        '<div class="col-xs-12">' +
        '<label for="nombre_sede">Nombre de la Sede</label>' +
        '<input type="text" name="nombre_sede" class="form-control" value="" id="nombre_sede"/>' +
        '</div>' +
        '<div class="col-xs-12"><br />' +
        '<label for="info">Info</label>' +
        '<textarea id="info" name="info" class="form-control" rows="2"></textarea>' +
        '</div></div>' +

        '</div><!-- /.modal-body -->' +

        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
        '<button type="submit" class="btn btn-primary btn-action" customerid="' + cliente_id + '">Registrar Sede</button>' +
        '</div>' +

        '</div><!-- /.modal-content -->' +
        '</form></div><!-- /.modal-dialog -->' +
        '</div>'
    );

    htmlwindow.modal({backdrop: false})
        .trigger("windowEvents").find(".btn-action").click(function (e) {
            e.preventDefault();
            agregar_sede($(this).attr("customerid"), $(this).closest("form"));
        });


    $("#window > .modal-dialog").draggable().css("cursor", "move");
    focusField("nombre_sede");

}

function window_editar_sede(sede_id) {

    var nombre_sede = '';
    var info = '';

    var response_data = $.ajax({
        url: 'customers.php?action=sedeinfo_request&id=' + sede_id,
        beforeSend: function () {
            $('#loader').show();
        },
        complete: function () {
            $('#loader').hide();
        },
        success: function (resp) {

            var regex = new RegExp('({HEADQUARTER_NAME: "([^"]*)"})?({HEADQUARTER_INFO: "([^"]*)"})?', "i");

            if (resp.match(regex) != null) {
                var headquarter = resp.match(regex);
                nombre_sede = typeof(headquarter[2]) != 'undefined' ? headquarter[2] : '';
                info = typeof(headquarter[4]) != 'undefined' ? headquarter[4] : '';

                var htmlwindow = $(
                    '<div id="window" class="modal fade small">' +
                    '<div class="modal-dialog"><form class="form-horizontal">' +
                    '<div class="modal-content">' +

                    ' <div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '<h3 class="modal-title">Modificar Sede</h3>' +
                    '</div>' +

                    '<div class="modal-body">' +

                    '<div class="form-group">' +
                    '<div class="col-xs-12">' +
                    '<label for="nombre_sede">Nombre de la Sede</label>' +
                    '<input type="text" name="nombre_sede" class="form-control" value="' + nombre_sede + '" id="nombre_sede"/>' +
                    '</div>' +
                    '<div class="col-xs-12"><br />' +
                    '<label for="info">Info</label>' +
                    '<textarea id="info" name="info" class="form-control" rows="2">' + info + '</textarea>' +
                    '</div></div>' +

                    '<div class="form-group">' +
                    '<div class="col-xs-12">' +
                    '<label class="checkbox-inline">' +
                    '<input type="checkbox" name="guardar_nombre" value="1" checked disabled /> Guardar Nombre anterior de la Sede</label>' +
                    '</div></div>' +

                    '</div><!-- /.modal-body -->' +

                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
                    '<button type="submit" class="btn btn-primary btn-action" data-headquarterid="' + sede_id + '">Guardar Cambios</button>' +
                    '</div>' +

                    '</div><!-- /.modal-content -->' +
                    '</form></div><!-- /.modal-dialog -->' +
                    '</div>'
                );

                htmlwindow.modal({backdrop: false})
                    .trigger("windowEvents").find(".btn-action").click(function (e) {
                        e.preventDefault();
                        modificar_sede($(this).attr("data-headquarterid"), $(this).closest("form"));
                    });

                var input_changes = {
                    'before': '',
                    'after': ''
                };

                htmlwindow.find("[name=nombre_sede]").on('keydown', function (e) {
                    input_changes.before = jQuery(this).val();
                }).on('keyup', function (e) {
                    input_changes.after = jQuery(this).val();
                    if (input_changes.before != input_changes.after) {
                        htmlwindow.find("[name=guardar_nombre]").removeAttr("disabled");
                    }
                });


                $("#window > .modal-dialog").draggable().css("cursor", "move");
                focusField("nombre_sede");


            }
        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });
}


function window_move_headquarter(options, on_success) {

    var response_data = $.ajax({
        url: 'includes/ajax.php?action=load_select_customers',
        beforeSend: function () {
            $('#loader').show();
        },
        complete: function () {
            $('#loader').hide();
        },
        success: function (resp) {
            var htmlwindow = $(
                '<div id="window" class="modal fade">' +
                '<div class="modal-dialog">' +
                '<div class="modal-content">' +
                '<form class="form-horizontal">' +
                ' <div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                '<h3 class="modal-title">Mover Sede</h3>' +
                '</div>' +

                '<div class="modal-body"> <!-- Nav tabs -->' +
                '<p>Por favor seleccione el cliente a donde desea mover la sede "' + options.headquarter_name + '"</p>' +
                '<ul class="nav nav-tabs" role="tablist">' +
                '<li role="presentation" class="active">' +
                '<a href="#existing-customer" aria-controls="home" role="tab" data-toggle="tab">Cliente Existente</a>' +
                '</li>' +
                '<li role="presentation">' +
                '<a href="#new-customer" aria-controls="profile" role="tab" data-toggle="tab">Nuevo Cliente</a>' +
                '</li>' +
                '</ul>' +
                '<!-- Tab panes -->' +
                '<div class="tab-content">' +
                '<div role="tabpanel" class="tab-pane active" id="existing-customer">' +

                '<div class="form-group">' +
                '<div class="col-xs-12">' +
                '<div class="select2-wide">' +
                '<select name="cliente" class="form-control" id="cliente">' +
                '<option></option>' +
                resp +
                '</select>' +
                '</div><!-- /.select2-wide -->' +
                '</div><!-- /.col-xs-12 -->' +
                '</div><!-- /.form-group -->' +
                '</div><!-- /.tab-pane -->' +
                '<div role="tabpanel" class="tab-pane" id="new-customer">' +
                '<h4>Datos Básicos</h4>' +
                '<div class="form-group">' +
                '<div class="col-sm-6">' +
                '<label for="razon_social" class="control-label">Razón Social</label>' +
                '<input type="text" name="razon_social" class="form-control" value="" id="razon_social" placeholder="Nombre del Cliente" disabled="disabled" />' +
                '</div><!-- /.col-sm-6 -->' +
                '</div><!-- /.form-group -->' +

                '</div><!-- /.tab-pane -->' +
                '</div><!-- /.modal-body -->' +

                '</div><!-- /.tab-content -->' +

                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
                '<button type="submit" class="btn btn-primary btn-action">Aceptar</button>' +
                '</div>' +
                '</form>' +
                '</div><!-- /.modal-content -->' +
                '</div><!-- /.modal-dialog -->' +
                '</div>'
            );

            htmlwindow.modal({backdrop: false}).trigger("windowEvents").find(".btn-action").click(function (e) {
                e.preventDefault();
                if (typeof(on_success) != 'undefined') {
                    var $existing_customer = $('#existing-customer');
                    var send_opts = {
                        'type': $existing_customer.is(':visible') ? 1 : 0,
                        'data': $(this).closest('form').serialize()
                    };
                    on_success(send_opts);
                }
            });

            $customerSelect = htmlwindow.find('select[name=cliente]');
            $dropdownParent = $customerSelect.closest('.select2-wide');

            $customerSelect.select2({
                placeholder: "Seleccione un cliente",
                allowClear: true,
                dropdownParent: $dropdownParent,
                closeOnSelect: false,
                theme: "bootstrap",
                language: "es"
            }).on("select2:closing", function (e) {
                e.preventDefault();
            }).on("select2:close", function (e) {
                $(this).select2("open");
            }).on("select2:unselect", function (e) {
                $dropdownParent.find("[aria-selected=true]").attr("aria-selected", false);
            }).select2("open");

            $searchResults = $dropdownParent.find('.select2-results__options');

            $searchResults.on('mouseleave', function () {
                $(this).children("li").removeClass("select2-results__option--highlighted");
            });

            $('#window').bind('hidden.bs.tab shown.bs.tab', '[data-toggle=tab]', function (e) {
                $(this).find("input, select, textarea, button").each(function () {
                    if ($(this).is(":visible")) {
                        $(this).removeAttr("disabled");
                    } else {
                        $(this).attr("disabled", "disabled");
                    }
                })

            }).draggable().css("cursor", "move");
            focusField("nombre_sede");
        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });
}

function agregar_sede(customerid, form) {
    var response = ajax_request('customers.php?action=add_headquarter_request&id=' + customerid, form.serialize(), 'reload_window_request_act');
    return false;
}

function modificar_sede(headquarterid, form) {
    var response = ajax_request('customers.php?action=edit_headquarter_request&id=' + headquarterid, form.serialize(), 'reload_window_request_act');
    return false;
}

function window_agregar_razon_social(customerid) {
    destroyWindow();
    var htmlwindow = $(
        '<div id="window" class="modal fade small">' +
        '<div class="modal-dialog"><form class="form-horizontal">' +
        '<div class="modal-content">' +

        ' <div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
        '<h3 class="modal-title">Nueva Razón Social</h3>' +
        '</div>' +

        '<div class="modal-body">' +

        '<div class="form-group">' +
        '<div class="col-xs-12">' +
        '<label for="user_lastname">Razón Social</label>' +
        '<input type="text" name="razon_social" class="form-control" value="" id="razon_social"/>' +
        '</div></div>' +

        '<div class="form-group">' +
        '<div class="col-xs-12">' +
        '<label for="user_firstname">Desde</label>' +
        '<input type="text" name="fecha_asignacion" class="form-control date" value="" id="fecha_asignacion"/>' +
        '</div></div>' +

        '<div class="form-group">' +
        '<div class="col-xs-12">' +
        '<label class="checkbox-inline">' +
        '<input type="checkbox" name="actual" value="1" checked /> Establecer como actual</label>' +
        '</div></div>' +


        '</div><!-- /.modal-body -->' +

        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
        '<button type="submit" class="btn btn-primary btn-action">Guardar Cambios</button>' +
        '</div>' +


        '</div><!-- /.modal-content -->' +
        '</form></div><!-- /.modal-dialog -->' +
        '</div>'
    );
    htmlwindow.modal({backdrop: false})
        .trigger("windowEvents").find(".btn-action").click(function (e) {
            e.preventDefault();
            agregar_razon_social(customerid, $(this).closest("form"));
        });


    $("#window > .modal-dialog").draggable().css("cursor", "move");

    focusField("razon_social");

}

function window_editar_razon_social(customer_name_id) {

    var razon_social = '';
    var fecha_asignacion = '';
    var actual = '';

    var response_data = $.ajax({
        url: 'customers.php?action=customername_info_request&id=' + customer_name_id,
        beforeSend: function () {
            $('#loader').show();
        },
        complete: function () {
            $('#loader').hide();
        },
        success: function (resp) {

            var regex = new RegExp('({NAME: "([^"]*)"})?({DATE: "([^"]*)"})?({CURRENT: "([^"]*)"})?', "i");

            if (resp.match(regex) != null) {
                var nameinfo = resp.match(regex);
                razon_social = typeof(nameinfo[2]) != 'undefined' ? nameinfo[2] : '';
                fecha_asignacion = typeof(nameinfo[4]) != 'undefined' ? nameinfo[4] : '';
                actual = typeof(nameinfo[6]) != 'undefined' && nameinfo[6] == 1 ? ' checked' : '';

                var htmlwindow = $(
                    '<div id="window" class="modal fade small">' +
                    '<div class="modal-dialog"><form class="form-horizontal">' +
                    '<div class="modal-content">' +

                    ' <div class="modal-header">' +
                    '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                    '<h3 class="modal-title">Nueva Razón Social</h3>' +
                    '</div>' +

                    '<div class="modal-body">' +

                    '<div class="form-group">' +
                    '<div class="col-xs-12">' +
                    '<label for="user_lastname">Razón Social</label>' +
                    '<input type="text" name="razon_social" class="form-control" value="' + razon_social + '" id="razon_social"/>' +
                    '</div></div>' +

                    '<div class="form-group">' +
                    '<div class="col-xs-12">' +
                    '<label for="user_firstname">Desde</label>' +
                    '<input type="text" name="fecha_asignacion" class="form-control date" value="' + fecha_asignacion + '" id="fecha_asignacion"/>' +
                    '</div></div>' +

                    '<div class="form-group">' +
                    '<div class="col-xs-12">' +
                    '<label class="checkbox-inline">' +
                    '<input type="checkbox" name="actual" value="1" ' + actual + ' /> Establecer como actual</label>' +
                    '</div></div>' +


                    '</div><!-- /.modal-body -->' +

                    '<div class="modal-footer">' +
                    '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
                    '<button type="submit" class="btn btn-primary btn-action">Guardar Cambios</button>' +
                    '</div>' +


                    '</div><!-- /.modal-content -->' +
                    '</form></div><!-- /.modal-dialog -->' +
                    '</div>'
                );
                htmlwindow.modal({backdrop: false})
                    .trigger("windowEvents").find(".btn-action").click(function (e) {
                        e.preventDefault();
                        modificar_razon_social(customer_name_id, $(this).closest("form"));
                    });


                $("#window > .modal-dialog").draggable().css("cursor", "move");

                focusField("razon_social");


            }
        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });
}

function window_historial_sedes(headquarter_id) {

    var response_data = $.ajax({
        url: 'customers.php?action=headquarter-names-request&id=' + headquarter_id,
        beforeSend: function () {
            $('#loader').show();
        },
        complete: function () {
            $('#loader').hide();
        },
        success: function (resp) {

            var htmlwindow = $(
                '<div id="window" class="modal fade">' +
                '<div class="modal-dialog"><form class="form-horizontal">' +
                '<div class="modal-content">' +

                ' <div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
                '<h3 class="modal-title">Historial de Nombres de Sede</h3>' +
                '</div>' +
                '<div class="modal-body table-list">' +
                '<table class="table table-responsive">' +
                '<tr>' +
                '<th><strong>Nombre</strong></th>' +
                '<th><strong>Opciones</strong></th>' +
                '</tr>' +
                resp +
                '</table>' +


                '</div><!-- /.modal-body -->' +

                '<div class="modal-footer">' +
                '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
                '</div>' +


                '</div><!-- /.modal-content -->' +
                '</form></div><!-- /.modal-dialog -->' +
                '</div>'
            );

            htmlwindow.modal({backdrop: false})
                .trigger("windowEvents");

            $("#window > .modal-dialog").draggable().css("cursor", "move");
            focusField("razon_social");

        },
        error: function () {
            alert("Error: no se pudo establecer la conexión con el servidor.");
        }
    });
}

function agregar_razon_social(customerid, form) {
    var response = ajax_request('customers.php?action=add_name_request&id=' + customerid, form.serialize(), 'reload_window_request_act');
    return false;
}

function modificar_razon_social(customerid, form) {
    var response = ajax_request('customers.php?action=edit_name_request&id=' + customerid, form.serialize(), 'reload_window_request_act');
    return false;
}

function modificacion_usuario(userid, form) {
    var response = ajax_request('users.php?action=edit_request&id=' + userid, form.serialize(), 'reload_window_request_act');
    return false;
}

function creacion_usuario(form) {
    var response = ajax_request('users.php?action=create_request', form.serialize(), 'default_window_request_act');
    return false;
}

function creacion_cliente(form) {
    var response = ajax_request('customers.php?action=create_request', form.serialize(), 'default_window_request_act');
    return false;
}

function creacion_pozo(form) {
    var response = ajax_request('wells.php?action=create_request', form.serialize(), 'default_window_request_act');
    return false;
}

function modificacion_pozo(wellid, form) {
    var response = ajax_request('wells.php?action=edit_request&id=' + wellid, form.serialize(), 'default_window_request_act');
    return false;
}

function creacion_tarea(form) {
    $(window).unbind('beforeunload');
    var response = ajax_request('wells.php?action=new_task_request', form.serialize(), 'default_window_request_act');
    return false;
}

function modificar_tarea(task_id, form) {
    $(window).unbind('beforeunload');
    var response = ajax_request('wells.php?action=edit_task_request&id=' + task_id, form.serialize(), 'default_window_request_act');
    return false;
}

function guardar_ajustes(form) {
    var response = ajax_request('adjustments.php?action=save', form.serialize(), 'default_window_request_act');
    return false;
}

function get_filter_string(dropdown_menu) {

    var filter_list = [];
    var value_list = [];
    var search_filter = dropdown_menu.closest(".search-filter");
    var interval_mode = typeof(search_filter.attr("data-interval")) != 'undefined';
    var form_obj = dropdown_menu.find('input, select');
    var filter_string = '';

    if (form_obj.length != 2)
        interval_mode = false;

    form_obj.each(function (index) {

        switch ($(this).prop('tagName').toLowerCase()) {

            case 'input':
                if ($(this).val())
                    filter_list.push($(this).val());
                break;

            case 'select':
                var selected_option = $(this).children("option:selected");
                if (selected_option.val())
                    filter_list.push(selected_option.text());
                break;

            default:
                break;
        }

        value_list[index] = $(this).val();
        $(this).attr("default-value", $(this).val());

    });
    if (filter_list.length > 0) {
        if (interval_mode) {
            var unit = search_filter.attr("data-interval");
            if (filter_list.length == 1) {
                var interval_prefix = value_list[0] == '' ? 'Max' : 'Min';
                filter_string = ': (' + interval_prefix + ': ' + filter_list[0] + unit + ')';
            } else {
                filter_string = ': (' + filter_list.join(unit + ' ~ ') + unit + ')';
            }
        } else {
            filter_string = ': (' + filter_list.join(', ') + ')';
        }
    }

    return filter_string;

}

function preloadInstalledBomb(bomb_data, prefix) {

    var data_structure = {
        "tipo_bomba": bomb_data.bomba.tipo
    };

    switch (bomb_data.bomba.tipo) {

        case "1":
            data_structure = $.extend(data_structure, {
                "tipo1_modelo": bomb_data.bomba.modelo,
                "tipo1_marca": bomb_data.bomba.marca,
                "tipo1_etapas": bomb_data.bomba.etapas,
                "tipo1_serial": bomb_data.bomba.serial,

                "tipo1_marca_motor": bomb_data.motor.s2_tipo1_marca_motor,
                "tipo1_diam_motor": bomb_data.motor.s2_tipo1_diam_motor,
                "tipo1_nema_motor": bomb_data.motor.s2_tipo1_nema_motor,
                "tipo1_hp_motor": bomb_data.motor.s2_tipo1_hp_motor,
                "tipo1_voltaje_motor": bomb_data.motor.s2_tipo1_voltaje_motor,
                "tipo1_amp_nominal_motor": bomb_data.motor.s2_tipo1_amp_nominal_motor,
                "tipo1_amp_max_motor": bomb_data.motor.s2_tipo1_amp_max_motor,
                "tipo1_fases_motor": bomb_data.motor.s2_tipo1_fases_motor,
                "tipo1_rpm_motor": bomb_data.motor.s2_tipo1_rpm_motor,
                "tipo1_serial_motor": bomb_data.motor.s2_tipo1_serial_motor,

                "tipo1_cant_tubos0_acc": bomb_data.acc.s2_tipo1_cant_tubos0_acc,
                "tipo1_long_tubos0_acc": bomb_data.acc.s2_tipo1_long_tubos0_acc,
                "tipo1_diam_tubos0_acc": bomb_data.acc.s2_tipo1_diam_tubos0_acc,
                "tipo1_cant_tubos1_acc": bomb_data.acc.s2_tipo1_cant_tubos1_acc,
                "tipo1_long_tubos1_acc": bomb_data.acc.s2_tipo1_long_tubos1_acc,
                "tipo1_diam_tubos1_acc": bomb_data.acc.s2_tipo1_diam_tubos1_acc,
                "tipo1_cable_n_acc": bomb_data.acc.s2_tipo1_cable_n_acc,
                "tipo1_tipo_cable_acc": bomb_data.acc.s2_tipo1_tipo_cable_acc,
                "tipo1_long_cable_acc": bomb_data.acc.s2_tipo1_long_cable_acc,
                "tipo1_lineas_cable_acc": bomb_data.acc.s2_tipo1_lineas_cable_acc,
                "tipo1_nro_check0_acc": bomb_data.acc.s2_tipo1_nro_check0_acc,
                "tipo1_diam_check0_acc": bomb_data.acc.s2_tipo1_diam_check0_acc,
                "tipo1_nro_check1_acc": bomb_data.acc.s2_tipo1_nro_check1_acc,
                "tipo1_diam_check1_acc": bomb_data.acc.s2_tipo1_diam_check1_acc,
                "tipo1_cable_sonda_acc": bomb_data.acc.s2_tipo1_cable_sonda_acc,
                "tipo1_cant_electrodos_acc": bomb_data.acc.s2_tipo1_cant_electrodos_acc

            });
            break;

        case "2":
            data_structure = $.extend(data_structure, {
                "tipo2_modelo": bomb_data.bomba.modelo,
                "tipo2_marca": bomb_data.bomba.marca,
                "tipo2_etapas": bomb_data.bomba.etapas,
                "tipo2_serial": bomb_data.bomba.serial
            });

            switch (bomb_data.bomba.s2_tipo_motor) {
                case "1":
                    data_structure = $.extend(data_structure, {
                        "tipo_motor": bomb_data.motor.s2_tipo_motor,
                        "tipo2_marca_motor": bomb_data.motor.s2_tipo2_marca_motor,
                        "tipo2_diam_motor": bomb_data.motor.s2_tipo2_diam_motor,
                        "tipo2_nema_motor": bomb_data.motor.s2_tipo2_nema_motor,
                        "tipo2_hp_motor": bomb_data.motor.s2_tipo2_hp_motor,
                        "tipo2_voltaje_motor": bomb_data.motor.s2_tipo2_voltaje_motor,
                        "tipo2_amp_nominal_motor": bomb_data.motor.s2_tipo2_amp_nominal_motor,
                        "tipo2_amp_max_motor": bomb_data.motor.s2_tipo2_amp_max_motor,
                        "tipo2_fases_motor": bomb_data.motor.s2_tipo2_fases_motor,
                        "tipo2_rpm_motor": bomb_data.motor.s2_tipo2_rpm_motor,
                        "tipo2_serial_motor": bomb_data.motor.s2_tipo2_serial_motor
                    });
                    break;

                case "2":
                    data_structure = $.extend(data_structure, {
                        "tipo_motor": bomb_data.motor.s2_tipo_motor,
                        "tipo2_marca_motor": bomb_data.motor.s2_tipo2_marca_motor,
                        "tipo2_hp_motor": bomb_data.motor.s2_tipo2_hp_motor
                    });
                    break;
            }

            data_structure = $.extend(data_structure, {
                "tipo2_cant_tubos0_acc": bomb_data.acc.s2_tipo2_cant_tubos0_acc,
                "tipo2_long_tubos0_acc": bomb_data.acc.s2_tipo2_long_tubos0_acc,
                "tipo2_diam_tubos0_acc": bomb_data.acc.s2_tipo2_diam_tubos0_acc,
                "tipo2_cant_tubos1_acc": bomb_data.acc.s2_tipo2_cant_tubos1_acc,
                "tipo2_long_tubos1_acc": bomb_data.acc.s2_tipo2_long_tubos1_acc,
                "tipo2_diam_tubos1_acc": bomb_data.acc.s2_tipo2_diam_tubos1_acc,
                "tipo2_cable_n_acc": bomb_data.acc.s2_tipo2_cable_n_acc,
                "tipo2_tipo_cable_acc": bomb_data.acc.s2_tipo2_tipo_cable_acc,
                "tipo2_long_cable_acc": bomb_data.acc.s2_tipo2_long_cable_acc,
                "tipo2_lineas_cable_acc": bomb_data.acc.s2_tipo2_lineas_cable_acc,
                "tipo2_check_acc": bomb_data.acc.s2_tipo2_check_acc,
                "tipo2_cable_sonda_acc": bomb_data.acc.s2_tipo2_cable_sonda_acc,
                "tipo2_cant_electrodos_acc": bomb_data.acc.s2_tipo2_cant_electrodos_acc
            });

            break;
    }

    $.each(data_structure, function (field, value) {
        $("input[name=" + prefix + field + "]").val(value).change();
    });
}

function preloadExtractedBomb() {

    $("#opt-bomba-extraida select, #opt-bomba-extraida input").each(function () {

        if (typeof($(this).attr("name")) != 'undefined') {
            var str_field_name = $(this).attr("name").replace(/s1_/, "s2_");
            console.log(str_field_name);
            $("select[name=" + str_field_name + "], input[name=" + str_field_name + "]").val($(this).val()).change();
        }

    });

}

function validarCoordenadaE($coordE){
    return !!(coordenadaValida($coordE) && $coordE < 0 );
}

function coordenadaValida(number) {
    return !!(/^-?\d+\.\d+$/.test(number) && !isNaN(Number(number)));
}

$(document).on('keypress', 'input.numeric, input.numeric-float', function (e) {

    if (e.which != 8 && e.which != 0 && e.which != 13 && e.which != 46 && (e.which < 48 || e.which > 57))
        e.preventDefault();

});

$(document).on('keypress', 'input.numeric-int', function (e) {

    if (e.which != 8 && e.which != 0 && e.which != 13 && (e.which < 48 || e.which > 57))
        e.preventDefault();

});

$(document).on('hidden.bs.modal', '#window', function () {
    destroyWindow();
});