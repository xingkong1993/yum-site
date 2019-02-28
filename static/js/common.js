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
    $(".content").height(p_h - o_h);
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

function upload_user_icon(obj) {
    layer.confirm("更改头像将会将之前头像覆盖，是否确认更改头像？", {icon: 3}, function (i) {
        var parent = $(obj).parent(".user-icon").parent();
        $("#upload_icon").remove();
        parent.append("<input type='file' style='opacity: 0' id='upload_icon'/>");
        layer.close(i);
        $("#upload_icon").click();
        var upload = new verUpload();
        $("#upload_icon").change(function () {
            var reg = ["jpg", "png", "jpeg"],
                save = $(obj).data("save");
            upload.init({
                file: this,
                reg: reg,
                success: function (datas) {
                    console.log(datas);
                    $("body").prepend('<div class="parsetcroBox"><div class="crop"> <div class="imageBox"> <div class="imgBoxY"><img id="preview" src="' + datas.code + '"/></div> <div class="imgBoxL"> <img id="previewyulan" src="' + datas.code + '"/> </div> </div> <div class="bottomBox"> <button class="yum-button yum-button-primary queding">确定</button> <button class="yum-button yum-button-primary xuanzhuan">旋转</button> <button class="yum-button yum-button-caution quxiao" onclick="$(\'.parsetcroBox\').remove()">取消</button></div> </div> </div>');
                    var $img = $("#preview");
                    var dataURL;
                    $img.cropper({
                        aspectRatio: 1 / 1,         //1 / 1,  //图片比例,1:1
                        crop: function (data) {
                            var $imgData = $img.cropper('getCroppedCanvas')
                            var dataurl = $imgData.toDataURL('image/png');
                            $("#previewyulan").attr("src", dataurl)
                        },
                        built: function (e) {
                        },
                        maxWidth: 120,
                        maxHeight: 120,
                        minWidth: 60,
                        minHeight: 60
                    });
                    $img.cropper('replace', dataURL)
                    $(".xuanzhuan").on("click", function () {
                        $img.cropper('rotate', 90)
                    });
                    $("body").unbind("click").on("click", ".queding", function () {

                        var $imgData = $img.cropper('getCroppedCanvas')
                        var dataurl = $imgData.toDataURL('image/png');  //dataurl便是base64图片
                        parent.find("img").each(function () {
                            this.src = dataurl;
                        });
                        $(".parsetcroBox").remove();
                        imgReplaceBtn = 1;
                        $.post(save, {files: dataurl}, function (msg) {
                            if (msg.code == 1) {
                                layer.msg("头像上传成功！")
                            } else {
                                layer.msg("头像保存失败！")
                            }
                        });
                    });
                    // $("body").unbind("click").on("click", ".quxiao", function () {
                    //     $(".parsetcroBox").remove();
                    // })
                },
                fail: function (data) {
                    layer.msg(data);
                }
            });
            $("#upload_icon").remove();
        });
    })
}

function checkAll() {
    $("*[data-checked=all]").click(function () {
        var children = $("*[data-checked=children]");
        if ($(this).hasClass("input-chekbox") || $(this).hasClass("input-checkbox-other")) {
            $(this).removeClass("input-chekbox input-checkbox-other").addClass("input-checked");
            children.removeClass("input-chekbox").addClass("input-checked");
        } else {
            $(this).addClass("input-chekbox").removeClass("input-checked input-checkbox-other");
            children.addClass("input-chekbox").removeClass("input-checked");
        }
    });

    $("*[data-checked=children]").click(function () {
        var parent = $("*[data-checked=all]");
        var len = $("*[data-checked=children]").length;
        if ($(this).hasClass("input-chekbox")) {
            $(this).removeClass("input-chekbox").addClass("input-checked");
        } else {
            $(this).addClass("input-chekbox").removeClass("input-checked");
        }
        var length = $("*[data-checked=children].input-checked").length;
        if (length == len) {
            parent.removeClass("input-chekbox input-checkbox-other").addClass("input-checked");
        } else if (length == 0) {
            parent.addClass("input-chekbox").removeClass("input-checked input-checkbox-other");
        } else {
            parent.addClass("input-checkbox-other").removeClass("input-checked input-checked")
        }
    });
}

$(function () {
    $(".contractile-card-title>icon").click(function () {
        if ($(this).hasClass("icon-angle-down")) {
            $(this).removeClass("icon-angle-down").addClass("icon-angle-right");
            $(this).parent().siblings(".contractile-card-content").addClass("contractile-card-content-hide").removeClass("contractile-card-content-show");
            $(this).parent().addClass("contractile-card-border-bottom");
        } else {
            $(this).addClass("icon-angle-down").removeClass("icon-angle-right");
            $(this).parent().siblings(".contractile-card-content").removeClass("contractile-card-content-hide").addClass("contractile-card-content-show");
            $(this).parent().removeClass("contractile-card-border-bottom");
        }
    });
});
