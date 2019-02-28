window.verTable = (function () {
    var table = function () {
        this.data;
        this.tag;
        this.html;
        this.option;
        this.callback = function () {

        }
    }
    table.prototype = {
        init: function (data) {
            this.tar = $(data.tar);
            this.data = data.data;
            if (data.callback) this.callback = data.callback;
            this.option = data.option ? data.option : {};
            this.get_table_item();
        },
        get_table_item: function () {
            var dataList = this.tar.find("thead tr:last").find("th,td"),
                _self = this;
            _self.html = "";
            $.each(this.data, function (item, datas) {
                _self.html += "<tr>";
                dataList.each(function () {
                    var name = $(this).data("field");
                    var style = $(this).data("style") ? 'style="' + $(this).data("style") + '"' : '';
                    if(name=="option"){
                        var rule = $(this).data("rules");
                        if(rule){
                            rule = typeof rule != "object"?eval('('+rule+')'):rule;
                        }else{
                            rule = [];
                        }
                        var rels = $(this).data("rels");
                        if((rels && $.inArray(datas.status,rels) >= 0) || !rels){
                            datas["option"] = _self.options(datas,rule);
                        }
                    }
                    if($(this).data("image")){
                        var image = ($(this).data("image"));
                        datas[name] = '<img src="'+datas[name]+'" width="'+image.width+'" height="'+image.height+'"/>';
                    }
                    if (datas[name]) {
                        var eq = $(this).data("eq") ? eval('(' + $(this).data("eq") + ')') : '';
                        if (eq)
                            datas[name] = eq[datas[name]];
                        _self.html += '<td ' + style + '>' + datas[name] + '</td>';
                    } else {
                        var def = $(this).data("default") ? $(this).data("default") : "";
                        _self.html += '<td ' + style + '>' + def + '</td>';
                    }
                });
                _self.html += "</tr>";
            });
            _self.tar.find("tbody").empty().html(_self.html);
        },
        options:function (data,rule) {
            if(this.option){
                var html = "";
                $.each(this.option,function (i,v) {
                    var uri = decodeURI(v.uri);
                    $.each(rule,function (a,b) {
                        uri = uri.replace(new RegExp(a,"g"),data[b]);
                    });
                    var clicks = "";
                    if(v.clickFunc){
                        clicks = 'onclick="'+v.clickFunc+'"'
                    }
                    var datas = [];
                    if(v.data){
                        var count = 0
                        $.each(v.data,function (item,value) {
                            if(v.dataRule){
                                $.each(v.dataRule,function (c,d) {
                                    value = value.replace(new RegExp(c,"g"),data[d])
                                });
                                datas[count] = 'data-'+item+'="'+value+'"';
                                count++;
                            }
                        })
                       datas = datas.join(" ");
                    }
                    html += '<a class="option-btn" href="'+uri+'" '+clicks+' '+datas+'>'+v.name+'</a>';
                });
                return html;
            }else {
                return "";
            }
        }
    }
    return table;
})();