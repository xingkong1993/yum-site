function get_width(parent_width, change_width, old_width, offset) {
    var w = parent_width.width();
    var o_w = old_width.width();
    offset = offset ? parseInt(offset) : 0;
    var width = w - o_w - offset;
    change_width.width(width);
}

function get_admin_height() {
    var p_h = $(window).height();
    var o_h = $(".banner-menu").height() + $(".banner").height();
    return (p_h - o_h);
}

function siblings_width() {
    var max = 0;
    $("dt").each(function () {
        var width = $(this).width();
        if (width > max) max = width;
    });
    $("dt").width(max);
}

function options_confirm(objs) {
    var $this = $(objs.obj),
        title = $this.attr("data-name"),
        that = objs.obj,
        href = that.href,
        type = $this.attr("data-type"),
        data = objs.data ? objs.data : '';
    layer.confirm(title, {icon: objs.icon}, function (e) {
        layer.close(e);
        $.ajax({
            type: "post",
            url: href,
            data: data,
            success: function (msg) {
                if (msg.code == 1) {
                    layer.msg(msg.msg, function () {
                        if (type == 'delete') {
                            $this.parents("tr").remove();
                        }
                    })
                } else {
                    layer.msg(msg.msg)
                }
            },
            error: function (e) {
                layer.msg("参数错误！");
            }
        })
    })
    return false;
}

function successCallback(data) {
    if (typeof data != "object") data = JSON.parse(data);
    layer.msg(data.msg, function () {
        if (data.code == 1) {
            if ($("form").data("parent")) {
                parent.location.reload();
            }
            location.href = data.url;
        }
    })
}

function options_open(obj) {
    layer.open({
        type: 2,
        title: obj.innerText,
        shadeClose: false,
        shade: 0.6,
        maxmin: true, //开启最大化最小化按钮
        area: ['893px', '600px'],
        content: obj.href
    });
    return false;
}

document.onmouseup = function (e) {
    var tar = (e.target),
        defaults = document.querySelectorAll(".dropdown-menu-show"),
        dropup = document.querySelectorAll(".dropdown");
    if (defaults.length) {
        defaults.forEach(function (item) {
            var drop = item.parentNode.querySelector(".dropdown");
            if (tar != item) {
                item.classList.remove("dropdown-menu-show");
                drop.classList.remove("dropup");
            }
        })
    } else if (dropup.length) {
        dropup.forEach(function (item) {
            var show = item.parentNode.querySelector(".dropdown-menu");
            if (tar == item) {
                show.classList.add("dropdown-menu-show");
                item.classList.add("dropup");
            }
        })
    }
    var width = document.documentElement.clientWidth;
    if (width <= 1280) {
        var menu = document.querySelector(".mobile-menu-btn");
        if ($(".menu-mobile-list").find(tar).length < 1 && $(".menu-mobile").hasClass("menu-mobile-show")) {
            $(".menu-mobile").removeClass("menu-mobile-show").addClass("menu-mobile-hide");
        } else if (tar == menu || $.inArray(tar, menu) >= 0) {
            $(".menu-mobile").addClass("menu-mobile-show").removeClass("menu-mobile-hide");
        }
    }
    /*
    * 获取到是否有icon
    */
    var class_list = tar.classList;
    if ($.inArray("menu-change", class_list) >= 0) {
        var next = $(".menu-change").siblings(".icon-tab");
        if (next.hasClass("show-icon-menu")) {
            next.addClass("hide-icon-menu").removeClass("show-icon-menu");
        } else {
            next.addClass("show-icon-menu").removeClass("hide-icon-menu");
        }
    } else {
        var tab = document.querySelectorAll(".icon-tab .icon-tabs li");
        var items = document.querySelectorAll(".icon-tab .icons icon");
        var flag = false;
        tab.forEach(function (item, v) {
            if (tar == item) {
                flag = true;
            }
        });
        if (flag) {
            var t = tar.getAttribute("tar");
            var cl = /icon-/.test(t) ? "tar-" + t.split("-")[1] : "";
            var actives = tar.parentNode.querySelector(".actives");
            actives.classList.remove("actives");
            tar.classList.add("actives");
            items.forEach(function (i, v) {
                var class_les = i.classList.value;
                var mag = /icon-[234]x/;
                if (mag.test(class_les)) {
                    i.classList.remove(class_les.match(mag)[0]);
                }
                i.classList.add(t);
                var p = (i.parentNode);
                p.setAttribute("class", cl);
            });
        } else {
            $(".icon-tab").addClass("hide-icon-menu").removeClass("show-icon-menu");
        }
        $(".icon-tab .icons li").click(function () {
            var value = $(this).attr("value");
            $(this).parents(".icon-tab").siblings("input[type=text]").val(value);
            $(this).parents(".icon-tab").addClass("hide-icon-menu").removeClass("show-icon-menu");
        });
        $(".menu-clear").click(function () {
            $(this).siblings("input[type=text]").val("");
            $(this).siblings(".icon-tab").addClass("hide-icon-menu").removeClass("show-icon-menu");
        });
    }
};

function isMobile() {
    var userAgentInfo = navigator.userAgent;
    var Agents = new Array("Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod");
    var flag = false;
    for (var v = 0; v < Agents.length; v++) {
        if (userAgentInfo.indexOf(Agents[v]) > 0) {
            flag = true;
            break;
        }
    }
    return flag;
}