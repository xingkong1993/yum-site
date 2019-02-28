window.onscroll = function (ev) {
    scollor();
}

function get_scollor() {
    return window.pageYOffset;
}

function get_top() {
    var djs = window.setInterval(function () {
        var pos = window.pageYOffset;
        if (pos > 0) {
            window.scrollTo(0, pos - 10);
        } else {
            window.clearInterval(djs);
        }
    }, 10);
}

function show_search_input(obj) {
    if ($(obj).siblings("input[name=search]").data("show")) {

    } else {
        $(obj).siblings("input[name=search]").data("show", true).addClass("show_search").focus();
        $(obj).addClass("right");
    }
}

function search_blur(obj) {
    var value = obj.value;
    if (value == '') {
        obj.placeholder = '搜索热门博客/博主';
        $(obj).removeClass("show_search").removeData("show");
        $(obj).siblings("i").removeClass("right");
        $(obj).siblings("icon").removeClass("right");
    }
}

function siblings_width() {
    var max = 0;
    $("dt").each(function () {
        var width = $(this).width();
        max = Math.max(width, max);
    });
    $("dt").width(max);
}

function gotPwd() {
    $("#gotPwd").find("dt").removeAttr("style");
    $("#login").hide();
    $("#gotPwd").show();
    $("#register").hide();
    $("#gotPwd .captcha").click();
    $("#gotPwd").append("<input type='reset' hidden class='onreset'>");
    $("#gotPwd .onreset").click().remove();
    siblings_width();
}

function registerBox() {
    $("#register").find("dt").removeAttr("style");
    $("#login").hide();
    $("#register").show();
    $("#gotPwd").hide();
    $("#register .captcha").click();
    $("#register").append("<input type='reset' hidden class='onreset'>");
    $("#register .onreset").click().remove();
    siblings_width();
}

function loginBox() {
    $("#login").show();
    $("#register").hide();
    $("#gotPwd").hide();
    $("#login").append("<input type='reset' hidden class='onreset'>");
    $("#login .onreset").click().remove();
    siblings_width();
}

function scollor() {
    var get_scollors = get_scollor();
    if (get_scollors >= 55) {
        $(".header").addClass("post-fix-header");
        $("#scollorTop").show();
    } else {
        $(".header").removeClass("post-fix-header");
        $("#scollorTop").hide();
    }

    document.onmouseup = function (e) {
        var width = document.documentElement.clientWidth;
        if (width <= 980) {
            var tar = e.target;
            if ($(".mobile-menu").find(tar).length < 1 && $(".mobile-menu-list").hasClass("mobile-menu-list-show")) {
                $(".mobile-menu-list").removeClass("mobile-menu-list-show").addClass("mobile-menu-list-hide")
            }
        }
    };

    document.ontouchmove = function (e) {
        var width = document.documentElement.clientWidth;
        if (width <= 980) {
            var tar = e.target;
            if ($(".mobile-menu").find(tar).length < 1 && $(".mobile-menu-list").hasClass("mobile-menu-list-show")) {
                $(".mobile-menu-list").removeClass("mobile-menu-list-show").addClass("mobile-menu-list-hide")
            }
        }
    }
}

scollor();
$(window).resize(function () {
    scollor();
});

function goLogin() {
    $("#gologin").show("fast");
    $(document.body).css("overflow-y", "hidden");
    $("html").css("overflow-y", "hidden");
    $(".closed").click(function () {
        $("#gologin").hide("fast");
        $(document.body).css("overflow-y", "auto");
        $("html").css("overflow-y", "auto");
        loginBox();
    });
}

function sign_out() {
    layer.confirm("是否退出登录？", {icon: 3}, function () {
        layer.closeAll();
        $.post("/index/sign/sign_out", function (data) {
            layer.msg(data.msg, function () {
                if (data.code == 1) location.reload();
            })
        });
    });
}
