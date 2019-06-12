(function ($) {

    $.fn.select1 = function (action) {

        return this.each(function ($this) {
            if (action === "disable") {
                var target = $(this).parents(".select1-content").last();
                if (target.length > 0) {
                    if (target.find("input"))
                        $(this).attr("name", target.find("input").attr("name"));
                    $(this).detach().insertBefore(target).removeClass("select1 active");
                    return  target.remove();
                }
                return false;
            }

            if (!$(this).hasClass("active")) {

                var select1 = $(this);
                select1.addClass("select1 active");
                var html = '';
                var divider = select1.attr("data-divider");
                var current_value = select1.children("option:selected").text();

                if (typeof(action) == 'undefined' && select1.attr("data-select1"))
                    action = select1.attr("data-select1");

                if (typeof(divider) != 'undefined') {
                    divider = divider.replace(/ /g, '').split(",");
                }
                select1.children("option").each(function (index) {

                    if ($.inArray(index.toString(), divider) != -1)
                        html += '<li class="divider"></li>';

                    html += '<li><a href="' + $(this).val() + '">' + $(this).text() + '</a></li>';

                });

                if (action === "prompt") {

                    if (typeof(select1.attr("data-prompt-value")) != 'undefined')
                        current_value = select1.attr("data-prompt-value");

                    html += '<li><a href="javascript:void(0);">Otro...</a>' +
                        '<input name="' + select1.attr("name") +
                        '" style="display: none;" type="text" value="' +
                        current_value + '" />' +
                        '</li>';
                    select1.removeAttr("name");
                }


                var select1_content = $('<div class="select1-content input-group-btn">' +
                    '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">' +
                    '<span class="text">' + current_value + '</span> ' +
                    '<span class="caret"></span>' +
                    '</button>' +
                    '<ul class="dropdown-menu">' +
                    html +
                    '</ul>' +
                    '</div> ');

                select1_content.find("input").change(function () {
                    select1_content.find("button > span.text").text($(this).val());
                });

                if (action === "input") {

                    var default_input_value = '';

                    if (typeof(select1.attr("data-input-value")) != 'undefined') {
                        default_input_value = select1.attr("data-input-value");
                        select1.removeAttr("data-input-value");

                        var select1_values = $.map(select1.find('option'), function (e) {
                            return e.value;
                        });

                        if ($.inArray(default_input_value, select1_values) != -1) {
                            select1.removeClass("select1 active");
                            return false;
                        }


                    }
                    select1_content = $('<div class="select1-content input-group">' +
                        '<input type="text" name="' + select1.attr("name") +
                        '" class="form-control" value="' + default_input_value + '" />' +
                        '</div>').prepend(select1_content);

                    select1.removeAttr("name");


                }

                select1_content.find("button").attr("style", select1.attr("style"));
                select1.before(select1_content);
                select1.appendTo(select1_content.find(".select1-content"));

                select1_content.find(".dropdown-menu a").click(function (event) {
                    event.preventDefault();
                    if (action == 'prompt') {
                        if ($(this).attr("href") == "javascript:void(0);") {
                            var prompt_response = prompt("Ingrese un valor:");
                            if (prompt_response != null && prompt_response.length > 0) {
                                select1_content.find("button > span.text").text(prompt_response);
                                select1_content.find("input").val(prompt_response);
                            }
                        } else {
                            select1_content.find("button > span.text").text($(this).text());
                            select1_content.find("input").val($(this).attr("href"));
                        }
                    } else {
                        select1_content.find("button > span.text").text($(this).text());
                        select1.val($(this).attr("href")).change();
                    }
                });

                return true;
            }
            return false;
        });

    };

    $.fn.datepicker1 = function (options) {
        $(this).each(function () {

            var target = $(this);

            var dateinput = $('<div class="input-group datepicker1">' +
                '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
                '</div>');

            dateinput.insertBefore($(this));
            target.detach().prependTo(dateinput);
            target.datepicker(options);

            dateinput.find(".input-group-addon").click(function () {
                target.focus();
            });

        });

    };

}(jQuery));