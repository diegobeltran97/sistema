(function (a) {
    function f(a) {
        document.location.href = a
    }

    function g() {
        return a(".mnav").length ? !0 : !1
    }

    function h(b) {
        var c = !0;
        b.each(function () {
            if (!a(this).is("ul") && !a(this).is("ol")) {
                c = !1;
            }
        });
        return c
    }

    function i() {
        return a(window).width() < b.switchWidth
    }

    function j(b) {
        return a.trim(b.clone().children("ul, ol").remove().end().text())
    }

    function k(b) {
        return a.inArray(b, e) === -1 ? !0 : !1
    }

    function l(b) {
        b.find(" > li").each(function () {
            var c = a(this), d = c.find("a").attr("href"), f = function () {
                return c.parent().parent().is("li") ? c.parent().parent().find("a").attr("href") : null
            };
            c.find(" ul, ol").length && l(c.find("> ul, > ol"));
            c.find(" > ul li, > ol li").length || c.find("ul, ol").remove();
            !k(f(), e) && k(d, e) ? c.appendTo(b.closest("ul#mmnav").find("li:has(a[href=" + f() + "]):first ul")) : k(d) ? e.push(d) : c.remove()
        })
    }

    function m() {
        var b = a('<ul id="mmnav" />');
        c.each(function () {
            a(this).children().clone().appendTo(b)
        });
        l(b);
        return b
    }

    function n(b, c, d) {
        d ? a('<option value="' + b.find("a:first").attr("href") + '">' + d + "</option>").appendTo(c) : a('<option value="' + b.find("a:first").attr("href") + '">' + a.trim(j(b)) + "</option>").appendTo(c)
    }

    function o(c, d) {
        var e = a('<optgroup label="' + a.trim(j(c)) + '" />');
        n(c, e, b.groupPageText);
        c.children("ul, ol").each(function () {
            a(this).children("li").each(function () {
                n(a(this), e)
            })
        });
        e.appendTo(d)
    }

    function p(c) {
        var e = a('<select id="mm' + d + '" class="mnav" />');
        d++;
        b.topOptionText && n(a("<li>" + b.topOptionText + "</li>"), e);
        c.children("li").each(function () {
            var c = a(this);
            c.children("ul, ol").length && b.nested ? o(c, e) : n(c, e)
        });
        e.change(function () {
            f(a(this).val())
        }).prependTo(b.prependTo)
    }

    function q() {
        if (i() && !g())if (b.combine) {
            var d = m();
            p(d)
        } else c.each(function () {
            p(a(this))
        });
        if (i() && g()) {
            a(".mnav").show();
            c.hide()
        }
        if (!i() && g()) {
            a(".mnav").hide();
            c.show()
        }
    }

    var b = {combine: !0, groupPageText: "Main", nested: !0, prependTo: "body", switchWidth: 767, topOptionText: "Seleccione una p&aacute;gina"}, c, d = 0, e = [];
    a.fn.mobileMenu = function (d) {
        d && a.extend(b, d);
        if (h(a(this))) {
            c = a(this);
            q();
            a(window).resize(function () {
                q()
            })
        } else alert("mobileMenu only works with <ul>/<ol>")
    }
})(jQuery);