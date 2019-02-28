$(function () {
    siblings_width();
    $(".submit").click(function () {
        $("form.items").append('<input type="submit" hidden class="submit"/>');
        $("form.items .submit").click().remove();
    });
    $(".reset").click(function () {
        $("form.items").append('<input type="reset" hidden class="reset"/>');
        $("form.items .reset").click().remove();
    });
    $(".switch").bootstrapSwitch();
    $("#pid").bind("change blur",function(){
        var level = parseInt($(this).find("option:selected").attr("level"))+1;
        if(level>1){
            $(".icon").hide();
        }else{
            $(".icon").show();
        }
        $("input[name=level]").val(level);
    });
});

$(window).resize(function () {
    siblings_width();
})