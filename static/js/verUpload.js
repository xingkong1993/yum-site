window.verUpload = (function () {
    var upload = function () {
        this.files;
        this.reg;
        this.name;
        this.format;
        this.success = function (data) {
            console.log(data);
            alert("上传成功！");
        };
        this.fail = function (data) {
            console.log(data);
            alert("上传失败！");
        }
        this.upload_file;
        this.save = false;
    };
    upload.prototype = {
        init: function (params) {
            this.files = params.file.files;
            this.parents = params.file.parentNode;
            // console.log(this.parents);
            var toarray = params.reg ? params.reg : ['jpg', 'png', 'gif'];
            toarray = toarray.join("|");
            this.reg = new RegExp('(' + toarray + ')$', 'i');
            if (params.success) {
                this.success = params.success;
            }

            if (params.fail) {
                this.fail = params.fail;
            }
            if(params.save){
                this.save = params.save;
            }
            this.name = params.name;
            if (this.files && this.files[0]) {
                var file = this.files[0].name.split(".");
                this.ext = file[file.length - 1];
                if (!this.reg.test(this.ext)) {
                    this.fail("不支持的上传格式【" + this.ext + "】");
                    return false;
                } else {
                    this.uploads();
                }
            } else {
                this.fail("文件上传失败");
                return false;
            }
        },
        uploads: function () {
            // 判断上传文件资源
            var _self = this;
            var up_type = this.files[0].type;
            // console.log(up_type.indexOf("image"));
            if (up_type.indexOf("image") >= 0) {
                //图片资源，将图片解码成base64返回给页面
                var reader = new FileReader();
                reader.onload = function (e) {
                    //
                    _self.upload_file = e.target.result;
                    if(_self.save){
                        _self.save_uploads();
                    }else{
                        _self.success({message: "上传成功", "code": e.target.result});
                    }
                    var img = _self.parents.querySelectorAll("img"),
                        input = _self.parents.querySelectorAll("input");
                    if (img.length > 0) {
                        img.forEach(function (i) {
                            i.src = e.target.result;
                        });
                    }

                    if (input.length > 0) {
                        input.forEach(function (i) {
                            i.value = e.target.result;
                        });
                    }
                }
                reader.readAsDataURL(_self.files[0]);
            }
        },
        save_uploads:function () {
            var _self = this;
            var data = "files="+this.upload_file;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", this.save, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With","XMLHttpRequest");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200 || xhr.status == 304) {
                        _self.success(xhr.responseText);
                    } else {
                        _self.fail(xhr.responseText);
                    }
                }
            };
            xhr.send(data);
        }
    };
    return upload;
})();