$(document).ready(function () {

    $('#act-login').click(function (event) {
        event.preventDefault();
        ajax_request('login.php?action=login_request', $(this).closest('form').serialize(), 'login');
    });

    $('#act-reset-password').click(function (event) {
        event.preventDefault();
        ajax_request('login.php?action=reset_password_request', $(this).closest('form').serialize(), 'reset_password');
    });

    $('#act-reset-password-final').click(function (event) {
        event.preventDefault();
        ajax_request('login.php?action=reset_password_request&step=2', $(this).closest('form').serialize(), 'reset_password');
    });

    $('#act-create-user').click(function (event) {
        event.preventDefault();
        creacion_usuario($(this).closest('form'));
    });

    $('.act-user-edit').click(function (event) {
        event.preventDefault();
        var data_access = $(this).attr("data-access");
        if (typeof(data_access) != 'undefined') {
            modificar_usuario($(this).attr("data-userid"), data_access);
        }
    });

    $('.act-delete-user').click(function (event) {
        event.preventDefault();
        if (confirm("¿Está seguro que desea eliminar el usuario?"))
            ajax_request('users.php?action=delete_request', {userid: $(this).attr("data-userid")}, 'reload_window_request_act');
    });

    $('.act-delete-customer').click(function (event) {
        event.preventDefault();
        if (confirm("¿Está seguro que desea eliminar el cliente?"))
            ajax_request('customers.php?action=delete_request', {customerid: $(this).attr("data-customerid")}, 'reload_window_request_act');
    });

    $('.act-delete-well').click(function (event) {
        event.preventDefault();
        if (confirm("¿Está seguro que desea eliminar el pozo?"))
            ajax_request('wells.php?action=delete_request', {wellid: $(this).attr("data-wellid")}, 'reload_window_request_act');
    });

    $('.act-delete-task').click(function (event) {
        event.preventDefault();
        if (confirm("¿Está seguro que desea eliminar el trabajo?"))
            ajax_request('wells.php?action=delete_task_request', {taskid: $(".well-task-details ul.nav-tabs li.active a").text()}, 'reload_window_request_act');
    });

    $('.act-headquarter-edit').click(function (event) {
        event.preventDefault();

        window_editar_sede($(this).attr("data-headquarterid"));
    });

    $('.act-headquarter-move').click(function (event) {
        event.preventDefault();

        var headquarter_id = $(this).attr("data-headquarterid");
        var headquarter_name = $(this).closest('tr').find('.headquarter-name').text();

        if (confirm('¿Está seguro que desea mover la sede "' + headquarter_name + '" a otro cliente?'))
            window_move_headquarter({
                'headquarter_id': headquarter_id,
                'headquarter_name': headquarter_name
            }, function (options) {
                ajax_request('customers.php?action=move_headquarer_request',
                    options.data + '&type=' + options.type + '&id=' + headquarter_id,
                    'default_window_request_act'
                );
            });
    });

    $('.act-headquarter-names').click(function (event) {
        event.preventDefault();

        window_historial_sedes($(this).attr("data-headquarterid"));
    });

    $('.act-customername-edit').click(function (event) {
        event.preventDefault();

        window_editar_razon_social($(this).attr("data-customernameid"));
    });

    $('.act-customername-set').click(function (event) {
        event.preventDefault();

        if (confirm("¿Está seguro que desea establecer esta razón social como actual?"))
            ajax_request('customers.php?action=set_current_name_request&id=' + $(this).attr("data-customernameid"), '', 'reload_window_request_act');

    });

    $('.act-customername-delete').click(function (event) {
        event.preventDefault();

        if (confirm("¿Está seguro que desea eliminar esta razón social?"))
            ajax_request('customers.php?action=delete_customername_request&id=' + $(this).attr("data-customernameid"), '', 'reload_window_request_act');

    });

    $(document).on('click', '.act-headquartername-set', function (event) {
        event.preventDefault();

        if (confirm("¿Está seguro que desea establecer el nombre de la sede como actual?"))
            ajax_request('customers.php?action=set_headquarter_name_request&id=' + $(this).attr("data-headquarterid"), '', 'reload_window_request_act');

    }).on('click', '.act-headquartername-delete', function (event) {
        event.preventDefault();

        if (confirm("¿Está seguro que desea eliminar el nombre de la sede?"))
            ajax_request('customers.php?action=delete_headquarter_name_request&id=' + $(this).attr("data-headquarterid"), '', 'reload_window_request_act');

    });

    $('.act-headquarter-delete').click(function (event) {
        event.preventDefault();

        if (confirm("¿Está seguro que desea eliminar esta sede?"))
            ajax_request('customers.php?action=delete_headquarter_request&id=' + $(this).attr("data-headquarterid"), '', 'reload_window_request_act');

    });

    $('#act-save-adjustments').click(function (event) {
        event.preventDefault();
        guardar_ajustes($(this).closest('form'));
    });

    $('#act-create-customer').click(function (event) {
        event.preventDefault();
        creacion_cliente($(this).closest('form'));
    });

    $('#act-create-well').click(function (event) {
        event.preventDefault();
        var $valido = true;
        var $mensajes = '';
        if (!coordenadaValida($('#coord_n').val())) {
            $mensajes += ".- El formato de la coordenada N es invalido\n";
            $valido = false;
        }
        if (!validarCoordenadaE($('#coord_e').val())) {
            $mensajes += ".- El formato de la coordenada E es invalido, debe estar expresado con un valor negativo\n";
            $valido = false;
        }
        if ($valido) {
            creacion_pozo($(this).closest('form'));
        } else {
            alert($mensajes);
        }
    });

    $('#act-edit-well').click(function (event) {
        event.preventDefault();
        var $valido = true;
        var $mensajes = '';
        if (!coordenadaValida($('#coord_n').val())) {
            $mensajes += ".- El formato de la coordenada N es invalido\n";
            $valido = false;
        }
        if (!validarCoordenadaE($('#coord_e').val())) {
            $mensajes += ".- El formato de la coordenada E es invalido, debe estar expresado con un valor negativo\n";
            $valido = false;
        }
        if ($valido) {
            modificacion_pozo($(this).attr("data-wellid"), $(this).closest('form'));
        } else {
            alert($mensajes);
        }
    });

    $('#act-create-task').click(function (event) {
        event.preventDefault();
        creacion_tarea($(this).closest('form'));
    });

    $("#act-go-edit-task").click(function (event) {
        event.preventDefault();

        var target_url = $(this).attr("href");
        var task_id = $(".well-task-details ul.nav-tabs li.active a").text();

        $(location).attr("href", target_url + "&id=" + task_id);

    });

    $("#act-go-print-task").click(function (event) {
        event.preventDefault();

        var target_url = $(this).attr("href");
        var task_id = $(".well-task-details ul.nav-tabs li.active a").text();

        window.open(target_url + "&id=" + task_id, '_blank');

    });

    $('#act-edit-task').click(function (event) {
        event.preventDefault();
        modificar_tarea($(this).attr("data-taskid"), $(this).closest('form'));
    });

    $('#act-create-equipment').click(function (event) {
        event.preventDefault();
        creacion_equipamiento($(this).closest('form'));
    });

    $('#window-add-headquarter').click(function (event) {
        event.preventDefault();
        window_agregar_sede($(this).attr("customerid"));
    });

    $('#window-create-name').click(function (event) {
        event.preventDefault();
        window_agregar_razon_social($(this).attr("customerid"));
    });

    $('#act-search-well').closest('form').submit(function (event) {

        var exclude_list = ['s', 'mode'];

        $(this).find('input, select, textarea').each(function () {
            if ($.inArray($(this).attr("name"), exclude_list) == -1 && $(this).val() == '')
                $(this).attr('disabled', 'disabled');
        });
    });

    $("select[name=estado]").change(function () {
        var datex = new Date().getTime();
        var target1 = $('select[name=municipio]');
        var filter = false;

        if ($(this).closest(".search-filter").length > 0)
            filter = true;

        $.ajax({
            url: 'includes/ajax.php?action=load_select_municipios&estado=' + $(this).val() + '#' + datex,
            data: filter ? 'filter=true' : '',
            beforeSend: function () {
                target1.find('option')
                    .remove()
                    .end()
                    .append("<option value=''>Cargando...</option>")
                ;
            },
            success: function (resp) {

                if (resp.match(/<option/g) && resp.match(/<option/g).length > 1)
                    target1.removeAttr('disabled');
                else
                    target1.attr('disabled', 'disabled');

                target1.find('option')
                    .remove()
                    .end()
                    .append(resp);
            }
        });

        var target2 = $('select[name=ciudad]');

        $.ajax({
            url: 'includes/ajax.php?action=load_select_ciudades&estado=' + $(this).val() + '#' + datex,
            data: filter ? 'filter=true' : '',
            beforeSend: function () {
                target2.find('option')
                    .remove()
                    .end()
                    .append("<option value=''>Cargando...</option>")
                ;
            },
            success: function (resp) {

                if (resp.match(/<option/g) && resp.match(/<option/g).length > 1)
                    target2.removeAttr('disabled');
                else
                    target2.attr('disabled', 'disabled');

                target2.find('option')
                    .remove()
                    .end()
                    .append(resp);
            }
        });

    });

    $("select[name=municipio], select[name=ciudad]").each(function () {
        if ($(this).find("option").length <= 1)
            $(this).attr("disabled", "disabled");
    });

    $("input.date").datepicker1({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd/mm/yy',
        yearRange: "1900:" + (new Date).getFullYear()
    });

    $('.search-filter').on('hide.bs.dropdown', function () {
        $(this).children('.dropdown-menu').find('input, select').each(function () {
            if (typeof($(this).attr('default-value')) != 'undefined')
                $(this).val($(this).attr('default-value'));
            else
                $(this).val('').change();
            $(this).removeClass("form-error");
        })
    });

    $('.search-filter .dropdown-menu, .ui-datepicker').click(function (e) {
        e.stopPropagation();
    });

    $('.search-filter li a').click(function (e) {

        e.preventDefault();

        var search_filter = $(this).closest('.search-filter');

        search_filter.find("a").removeClass("selected");

        if (typeof(search_filter.attr("data-single")) != 'undefined' && search_filter.attr("data-single") == 'true') {
            search_filter.find("input").val($(this).attr("href"));
            $(this).addClass("selected");
        }


    });

    $('.search-filter .dropdown-menu .btn-confirm').click(function (e) {

        e.preventDefault();

        var search_filter = $(this).closest('.search-filter');
        var dropdown_toggle = search_filter.find('.dropdown-toggle');
        var dropdown_menu = $(this).closest('.dropdown-menu');

        var validate = false;
        var data_error = false;


        if (typeof(search_filter.attr("data-validate")) != 'undefined')
            validate = search_filter.attr("data-validate");

        var form_obj = dropdown_menu.find('input, select');

        switch (validate) {
            case "number":

                form_obj.each(function () {
                    if ($(this).val() != "" && isNaN($(this).val())) {
                        data_error = true;
                        focusField($(this).attr("name"));
                        $(this).addClass("form-error");
                    }
                });
                break;
            case "date":

                break;
        }

        if (!data_error) {
            var filter_string = get_filter_string(dropdown_menu);

            dropdown_toggle.find('span.value').text(filter_string);
            dropdown_toggle.dropdown('toggle');
        }
    }).each(function (e) {
        var search_filter = $(this).closest('.search-filter');
        var dropdown_menu = search_filter.find('.dropdown-menu');
        var filter_string = get_filter_string(dropdown_menu);

        search_filter.find('button span.value').text(filter_string);
    });

    $('.search-filter .dropdown-menu .btn-cancel').click(function (event) {

        event.preventDefault();


        var dropdown_toggle = $(this).closest('.search-filter').find('.dropdown-toggle');
        var dropdown_menu = $(this).closest('.dropdown-menu');

        dropdown_menu.find('input, select').each(function () {
            $(this).val("").change();
        });

        var filter_string = get_filter_string(dropdown_menu);
        dropdown_toggle.find('span.value').html(filter_string);
        dropdown_toggle.dropdown('toggle');

    });

    $('.reset-search-filter').click(function (event) {

        event.preventDefault();
        $('.search-filter .dropdown-menu .btn-cancel').trigger('click');
        $('input[name=s]').focus();

    });

    $('select[name=mode]').change(function () {

        var search_input = $(this).closest('.input-group').find('input[name=s]');
        var filter_bar = $('.search-filter-bar');
        var placeholder_text = $(this).closest(".input-group").find(".input-group-btn span.text").text();

        switch ($(this).val()) {
            default:
                search_input.attr('placeholder', 'Ingrese el texto a buscar...')
                    .removeClass("numeric");
                filter_bar.show();
                search_input.autocomplete("enable").autocomplete("option", "source", 'includes/ajax.php?action=well_search_autocomplete&mode=' + $(this).val());
                break;

            case '7':
            case '6':
                search_input.attr('placeholder', 'Ingrese el ' + placeholder_text + ' a buscar...')
                    .addClass("numeric");
                filter_bar.hide();
                focusField("s");
                search_input.autocomplete("disable");
                break;

            case '5':
            case '4':
                search_input.attr('placeholder', 'Ingrese el ' + placeholder_text + ' a buscar...')
                    .addClass("numeric");
                filter_bar.show();
                search_input.autocomplete("disable");
                break;
        }

    });

    $(".agregar-sede").click(function (e) {
        e.preventDefault();

        var sede = $(".sede-info:first").clone(false);

        sede.find("input").val("");
        sede.appendTo(".sedes");

    });

    $(document).on('click', '.quitar-sede', function (e) {
        e.preventDefault();

        if ($(".sede-info").length > 1)
            $(this).closest(".sede-info").remove();

    });

    $('select[name=cliente]').change(function () {
        var datex = new Date().getTime();
        var target = $('select[name=sede]');
        var cliente_id = $(this).val();

        $.ajax({
            url: 'includes/ajax.php?action=load_select_sedes&cliente=' + cliente_id + '#' + datex,
            beforeSend: function () {
                target.find('option')
                    .remove()
                    .end()
                    .append("<option value=''>Cargando...</option>");
            },
            success: function (resp) {

                if (cliente_id)
                    target.removeAttr('disabled');
                else
                    target.attr('disabled', 'disabled');

                target.find('option')
                    .remove()
                    .end()
                    .append(resp).change();
            }
        });

    });

    $('select[name=sede]').change(function () {
        var datex = new Date().getTime();
        var target = $('select[name=pozo]');
        var sede_id = $(this).val();

        $.ajax({
            url: 'includes/ajax.php?action=load_select_pozos&sede=' + sede_id + '#' + datex,
            beforeSend: function () {
                target.find('option')
                    .remove()
                    .end()
                    .append("<option value=''>Cargando...</option>")
                ;
            },
            success: function (resp) {

                if (sede_id)
                    target.removeAttr('disabled');
                else
                    target.attr('disabled', 'disabled');

                target.find('option')
                    .remove()
                    .end()
                    .append(resp);
            }
        });

    });

    $('input[name=limpieza]').change(function () {
        if ($('input[name=limpieza]:checked').val() == 1)
            $('div#opt-limpieza').show();
        else
            $('div#opt-limpieza').hide();

    }).change();

    $('input[name=bomba_extraida]').change(function () {
        if ($('input[name=bomba_extraida]:checked').val() == 1) {

            if (preload_installed_bomb_data != null)
                if (confirm("¿Desea cargar los datos de la ultima bomba instalada?"))
                    preloadInstalledBomb(preload_installed_bomb_data, 's1_');

            $('div#opt-bomba-extraida').show();
        } else {
            $('div#opt-bomba-extraida').hide();
        }
    });

    if ($('input[name=bomba_extraida]:checked').val() == 1) {
        $('div#opt-bomba-extraida').show();
    } else {
        $('div#opt-bomba-extraida').hide();
    }

    $('input[name=bomba_instalada]').change(function () {
        if ($('input[name=bomba_instalada]:checked').val() == 1) {

            if ($('input[name=bomba_extraida]:checked').val() == 1)
                if (confirm("¿Desea cargar los datos de la bomba extraída?"))
                    preloadExtractedBomb();

            $('div#opt-bomba-instalada').show();
        } else {
            $('div#opt-bomba-instalada').hide();
        }
    });

    if ($('input[name=bomba_instalada]:checked').val() == 1) {
        $('div#opt-bomba-instalada').show();
    } else {
        $('div#opt-bomba-instalada').hide();
    }

    $('input[name=prueba_bombeo]').change(function () {
        if ($('input[name=prueba_bombeo]:checked').val() == 1)
            $('div#opt-prueba-bombeo').show();
        else
            $('div#opt-prueba-bombeo').hide();
    }).change();


    $('select.equipment_type').change(function () {

        var section = $(this).closest(".panel-body");
        section.find('.tipo_bomba')
            .hide().find("input, select, textarea").attr("disabled", "disabled");

        switch ($(this).val()) {
            case '1':
                section.find('div.opt-sumergible').show().find("input, select, textarea").removeAttr("disabled");
                break;

            case '2':
                section.find('div.opt-turbina').show().find("input, select, textarea").removeAttr("disabled");
                break;

        }

    }).change();

    $(document).on('keyup', '.form-error', function () {
        $('.form-error').removeClass("form-error");
    }).on('change', '.form-error', function () {
        $('.form-error').keyup();
    });

    $(document).on('windowEvents', function () {
        $("#window input.date").datepicker1({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            yearRange: "1900:" + (new Date).getFullYear(),
            onClose: function (date) {
                $(this).blur();
            }
        });
    });

    $("input.customer-search[name=s]").autocomplete({
        source: 'includes/ajax.php?action=customer_search_autocomplete',
        minLength: 2
    }).on("autocompleteselect", function (event, ui) {
        $(this).val(ui.item.value);
        $(this).closest("form").submit();
    });

    $("input.well-search[name=s]").autocomplete({
        source: 'includes/ajax.php?action=well_search_autocomplete&mode=' + $("select[name=mode]").val(),
        minLength: 2
    }).on("autocompleteselect", function (event, ui) {
        $(this).val(ui.item.value);
        $(this).closest("form").submit();
    });

    $("input[name=realizado_por]").autocomplete({
        source: 'includes/ajax.php?action=made_by_autocomplete',
        minLength: 2
    }).on("autocompleteselect", function (event, ui) {
        $(this).val(ui.item.value);
    });

    $("input[name=grua_usada]").autocomplete({
        source: 'includes/ajax.php?action=grua_autocomplete',
        minLength: 2
    }).on("autocompleteselect", function (event, ui) {
        $(this).val(ui.item.value);
    });

    $(window).resize(function () {
        if (viewport().width <= 768) {
            $(".mainmenu .menu-section").addClass("btn-group");
            $(".mainmenu .menu-section-container").addClass("dropdown-menu");
            $(".mainmenu .menu-parent").addClass("dropdown-toggle").attr("data-toggle", "dropdown");
        } else {
            $(".mainmenu .menu-section.open .dropdown-toggle").dropdown('toggle');

            $(".mainmenu .menu-section").removeClass("btn-group");
            $(".mainmenu .menu-section-container").removeClass("dropdown-menu");
            $(".mainmenu .menu-parent").removeClass("dropdown-toggle").removeAttr("data-toggle");
        }
    }).resize();

    var material_field = $("select[name=material]");

    material_field.change(function () {
        if ($(this).val() != null && $(this).val().toLowerCase() == 'otro') {
            $(this).select1("input");
            $(this).parents(".select1-content").last().find("input").focus();
        } else {
            $(this).select1("disable");
        }
    });

    if (material_field.select1("input")) {
        material_field.closest(".select1-content").find("span.text").text("Otro");
    }

    $(".motor_type a").click(function (event) {
        event.preventDefault();

        var section = $(this).closest(".opt-turbina");
        var motor_title = section.find(".motor_type_title");
        var extended_info = section.find(".extended");

        if (extended_info.length > 0) {
            if ($(this).attr("href") == "1") {
                extended_info.show();
                extended_info.find("input, select, textarea").removeAttr("disabled");
            } else {
                extended_info.hide();
                extended_info.find("input, select, textarea").attr("disabled", "disabled");
            }
            $(this).closest(".motor_type").find("input").val($(this).attr("href"));
            motor_title.text($(this).text());
        }
    });

    $(".motor_type input").change(function () {
        $(this).closest(".motor_type").find("a[href=" + $(this).val() + "]").click();
    });

    $(".motor_type").each(function () {

        var section = $(this).closest(".opt-turbina");
        var motor_title = section.find(".motor_type_title");
        var extended_info = section.find(".extended");
        var current_value = $(this).find("input").val();

        if (extended_info.length > 0) {
            if (current_value == "1") {
                extended_info.show();
                extended_info.find("input, select, textarea").removeAttr("disabled");
            } else {
                extended_info.hide();
                extended_info.find("input, select, textarea").attr("disabled", "disabled");
            }
            motor_title.text($(this).find("a[href=" + current_value + "]").text());

        }

    });

    /*
     $(document).on('change', "input[name='user_access[]'][value$='-2']", function () {
     var row = $.trim($(this).val()).substring(0, 1);
     var target = $(this).closest("form").find("input[name='user_access[]'][value|='" + row + "']");

     if ($(this).is(":checked"))
     target.prop('checked', $(this).prop('checked'));
     });

     $(document).on('change', "input[name='user_access[]'][value$='-1']", function () {
     var row = $.trim($(this).val()).substring(0, 1);
     var target = $(this).closest("form").find("input[name='user_access[]'][value|='" + row + "']");

     if (!$(this).is(":checked"))
     target.prop('checked', $(this).prop('checked'));
     });
     */
    $(document).on('click', "a#v_user_access, a#c_user_access, a#m_user_access, a#e_user_access", function (event) {
        event.preventDefault();

        var target = [];
        var target_values = [];

        switch ($(this).attr("id")) {
            case "v_user_access":
                target = $(this).closest("form").find("input[name='user_access[]'][value$='-1']");
                break;
            case "c_user_access":
                target = $(this).closest("form").find("input[name='user_access[]'][value$='-2']");
                break;
            case "m_user_access":
                target = $(this).closest("form").find("input[name='user_access[]'][value$='-3']");
                break;
            case "e_user_access":
                target = $(this).closest("form").find("input[name='user_access[]'][value$='-4']");
                break;

        }


        target.prop("checked", function (index, value) {
            target_values.push(value);
        });

        var src_input = $(this).find("input");

        if ($.inArray(false, target_values) != -1)
            src_input.val(0);
        else
            src_input.val(1);

        if (src_input.val() == 1) {
            target.prop('checked', false).change();
            src_input.val(0);
        } else {
            target.prop('checked', true).change();
            src_input.val(1);
        }

    });

    $(".scrollable-tabs")
        .append("<div class=\"scrollable-tabs-inner\"></div>")
        .prepend("<button class=\"scroll-tab-left\">&laquo;</button>")
        .append("<button class=\"scroll-tab-right\">&raquo;</button>").on("click", ".scroll-tab-right", function (e) {
        e.preventDefault();
        var container_width = parseInt($(this).closest(".scrollable-tabs").width());
        var tabs_container_obj = $(this).closest(".scrollable-tabs").find(".nav-tabs");
        var tabs_obj = tabs_container_obj.children("li");
        var current_position = parseInt(tabs_container_obj.css("left"));
        var px_movement = 0;

        tabs_obj.each(function (index) {
            px_movement = $(this).position().left + $(this).outerWidth();

            if (px_movement > container_width + Math.abs(current_position)) {
                tabs_container_obj.css("left", -$(this).position().left);
                return false;
            }
        });
    }).on("click", ".scroll-tab-left", function (e) {
        e.preventDefault();
        var container_width = parseInt($(this).closest(".scrollable-tabs").width());
        var tabs_container_obj = $(this).closest(".scrollable-tabs").find(".nav-tabs");
        var tabs_obj = tabs_container_obj.children("li");
        var current_position = parseInt(tabs_container_obj.css("left"));
        var px_movement = 0;

        tabs_obj.each(function (index) {
            px_movement = $(this).position().left;
            if (px_movement < container_width + Math.abs(current_position)) {

                tabs_container_obj.css("left", -$(this).position().left);
                return false;
            }
        });

    }).ready(function () {
        var tabs_obj = $(this).find(".nav-tabs");
        tabs_obj.appendTo($(this).find(".scrollable-tabs-inner"));
        tabs_obj.css("display", "inline-block");

        $(window).resize(function () {
            var container = $(".scrollable-tabs");
            var container_width = parseInt(container.width());
            var tabs_container_obj = container.closest(".scrollable-tabs").find(".nav-tabs");
            var btn_arrows = container.find(".scroll-tab-left, .scroll-tab-right");

            if (tabs_container_obj.outerWidth() > container_width) {
                btn_arrows.show();
                container.css("padding", "0 22px");
            } else {
                btn_arrows.hide();
                container.css("padding", "0");
            }
        }).resize();

    });


    var preload_installed_bomb_data = null;

    $('select[name=pozo]').change(function () {

        var datex = new Date().getTime();

        $.ajax({
            url: 'includes/ajax.php?action=last_installed_bomb&id=' + $(this).val() + '#' + datex,
            beforeSend: function () {

            },
            success: function (resp) {

                var installed_bomb = $.parseJSON(resp);
                var bomba = typeof(installed_bomb.instalacion_bomba) != 'undefined' ? installed_bomb.instalacion_bomba : null;
                var motor = typeof(installed_bomb.instalacion_motor) != 'undefined' ? installed_bomb.instalacion_motor : null;
                var acc = typeof(installed_bomb.instalacion_acc) != 'undefined' ? installed_bomb.instalacion_acc : null;
                if (bomba != null || motor != null || acc != null) {
                    preload_installed_bomb_data = {
                        bomba: bomba,
                        motor: motor,
                        acc: acc
                    };
                } else {
                    preload_installed_bomb_data = null;
                }

            }
        });


    }).change();

});

