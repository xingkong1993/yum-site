$(".sign_out").click(function () {
    layer.confirm("是否退出？", {icon: 3}, function () {
        $.ajax({
            type: "post",
            url: "/logout",
            success: function (d) {
                if (d.code == 1)
                    location.reload();
                else
                    layer.msg(d.msg);
            }
        })
    })
});

$(function () {
    $(".banner-menu li.on").append('<icon class="icon-caret-up icons-item "></icon>');

});

$(window).resize(function () {
    var height = get_admin_height();
    $(".banner-menu li.on").append('<icon class="icon-caret-up icons-item "></icon>');
});

$(".show-hide").click(function () {
    if ($(this).find("icon").hasClass("icon-angle-up")) {
        $(this).find("icon").removeClass("icon-angle-up").addClass("icon-angle-down");
        $(this).parent().addClass("hide-menu");
        var height = get_admin_height();
    } else {
        $(this).find("icon").removeClass("icon-angle-down").addClass("icon-angle-up");
        $(this).parent().removeClass("hide-menu");
        var height = get_admin_height();
    }
    $(".contents").height(height);
    $(".content").height(height);
});

function get_menu_list() {
    $(".banner-menu ul:first").empty();
    $.post("/menu_list", function (msg) {
        var html = "<li onclick='get_index()' class='on'><a href='javascript:;'><icon class='icon-home icon-2x'></icon><p>主页</p></a><icon class='icon-caret-up icons-item'></icon></li>";
        var mob_html = '';
        if (msg) {
            $.each(msg, function (i, v) {
                var child = '';
                var cName = "";
                if (v.children.length) {
                    cName = "mobile-menu-user";
                    $.each(v.children, function (item, value) {
                        child += '<dd onclick="mobile_children_menu(this);"><a href="' + value.href + '" onclick="return false;">' + value.name + '</a></dd>'
                    })
                }
                html += '<li onclick="change_first_menu(this)" data-children-menu=\'' + JSON.stringify(v.children) + '\'>\n' +
                    '            <a href="javascript:;">\n' +
                    '                <icon class="' + v.icon + ' icon-2x"></icon>\n' +
                    '                <p>' + v.name + '</p>\n' +
                    '            </a>\n' +
                    '        </li>';
                mob_html += '<dl class="mobile-menu-content ' + cName + '">' +
                    '<dt onclick="mobile_menu_btn_click(this)">' +
                    '<icon class="icon-lg ' + v.icon + '"></icon> ' + v.name +
                    '</dt>' +
                    child +
                    '</dl>'
            });
        }
        $(".banner-menu ul:first").html(html);
        $(".mobile-menu-cont").empty().html(mob_html);
    })
}

function mobile_menu_btn_click(obj) {
    var parent = $(obj).parent();
    if (parent.hasClass("mobile-menu-active")) {
        parent.removeClass("mobile-menu-active");
        parent.find("dd").removeClass("show-mobile-menu");
    } else {
        $(".mobile-menu-active").removeClass("mobile-menu-active");
        parent.addClass("mobile-menu-active");
        parent.find("dd").addClass("show-mobile-menu")
    }
}

function mobile_children_menu(obj) {
    $(".active-menu").removeClass("active-menu");
    $(obj).addClass("active-menu");
    var href = $(obj).find("a").attr("href");
    if (href != "javascript:;" && href != "#") {
        $(".ifarm iframe").attr("src", href);
        $(".menu-mobile").removeClass("menu-mobile-show").addClass("menu-mobile-hide");
    }
    return false;
}

function get_index() {
    $(".banner-menu ul:first li:first").addClass("on").siblings("li").removeClass("on");
    $(".icons-item").remove();
    $(".banner-menu ul:first li:first").append('<icon class="icon-caret-up icons-item "></icon>');
    $(".menus").empty();
    $(".ifarm iframe").attr("src", "/index/index?webs=welcome");
}

function change_first_menu(object) {
    // console.log(object.innerText);
    var that = $(object);
    that.addClass("on").siblings("li").removeClass("on");
    $(".icons-item").remove();
    that.append('<icon class="icon-caret-up icons-item "></icon>');
    var child = that.attr("data-children-menu");
    child = eval('(' + child + ')');
    $(".menus").empty();
    var html = '';
    if (child.length > 0) {
        $.each(child, function (i, v) {
            html += '<li onclick="change_children_menu(this)"><a href="' + v.href + '" onclick="return false"><icon class="icon-radio"></icon> ' + v.name + '</a></li>'
        });
        $(".menus").html(html);
        $(".menus li:first").click();
    } else {
        $(".ifarm iframe").attr("src", "")
    }
    return false;
}

function change_children_menu(object) {
    // var click_menu = sessionStorage.getItem("click_menu") ? sessionStorage.getItem("click_menu") : [];
    var href = $(object).find("a").attr("href");
    // if(html){
    //     $("#options-list").append(html);
    // }
    $(object).addClass("on").siblings("li").removeClass("on");
    if (href != "javascript:;" && href != "#") {
        $(".ifarm iframe").attr("src", href)
    }
    return false;
}

function go_mobile_index() {
    $(".menu-mobile").removeClass("menu-mobile-show").addClass("menu-mobile-hide");
    var parent = $(".mobile-menu-index");
    // if (parent.hasClass("mobile-menu-active")) {
    //     parent.removeClass("mobile-menu-active");
    //     parent.find("dd").removeClass("show-mobile-menu");
    // } else {
    $(".mobile-menu-active").removeClass("mobile-menu-active");
    parent.addClass("mobile-menu-active");
    parent.find("dd").addClass("show-mobile-menu")
    // }
    $(".ifarm iframe").attr("src", "/index/index?webs=welcome");
    $(".menu-mobile").removeClass("menu-mobile-show").addClass("menu-mobile-hide");
    return false;
}