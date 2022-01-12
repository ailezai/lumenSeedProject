(function ($) {

    $.admin = $.admin || {version: "v1.0.1"};

    /**
     * 设置token，防御CSRF攻击
     */
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /**
     * 刷新验证码
     */
    $.admin.reloadCaptchaCode = function () {
        var $el = $('#captcha_code_img');
        $el.attr('src', $el.attr('src').replace(/_=(.*)/i, '_=' + Math.random()));
    };

    /**
     * ajax回调函数
     *
     * @param json  处理数据
     */
    $.admin.ajaxCallback = function (json) {
        if (json.code === 200) {
            $.admin.notify(json.msg, 'success');
            if(typeof ajaxSuccessCallback !== 'undefined' && ajaxSuccessCallback instanceof Function) {
                ajaxSuccessCallback(json);
            }
        } else {
            $.admin.notify(json.msg, 'error');
            if(typeof ajaxFailCallback !== 'undefined' && ajaxFailCallback instanceof Function) {
                ajaxFailCallback(json);
            }
        }

        if (json.type === "tips") {
            $.admin.afterResponse(json);
        } else {
            window.setTimeout(function () {
                $.admin.afterResponse(json);
            }, 1500);
        }
    };

    /**
     * response后续处理
     *
     * @param json 处理数据
     */
    $.admin.afterResponse = function (json) {
        if ("reload" === json.type) {
            window.location.reload();
        } else if ("redirect" === json.type) {
            window.location.href = json.url;
        } else if ("reload_captcha_code" === json.type) {
            $.admin.reloadCaptchaCode();
            $('#captcha_code').val('');
        } else if ("back" === json.type) {
            window.history.go(-1);
        } else if ("tips" === json.type) {
            swal({
                title: json.data['title'],
                text: json.data['msg'],
                type: json.data['type'],
                showCancelButton: false,
                confirmButtonColor: "#ED5565",
                confirmButtonText: "确定",
                closeOnConfirm: true
            }, function () {
                if (json.url !== '') {
                    window.location.href = json.url;
                }
            });
        }
    };

    /**
     * ajax Get请求
     *
     * @param url      请求路由
     * @param callback 回调函数
     */
    $.admin.ajaxGet = function (url, callback) {

        callback = callback || function () {};

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: callback,
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            }
        });
    };

    /**
     * pjax a标签请求，只支持get请求
     */
    $('body').delegate('a[data-pjax-request]', 'click', function () {
        var a = $(this);
        var url = a.attr("data-href");
        $("#modal-pjax-content").html('');
        $.ajax({
            url: url,
            type: 'get',
            dataType: 'html',
            success: function(result) {
                try {
                    result = JSON.parse(result);
                    if(result.code != 200) {
                        $.admin.notify(result.msg, 'error');
                    }
                } catch(e) {
                    $('#modal-pjax').modal('show');
                    $("#modal-pjax-content").html(result);
                }
            },
            error:function(result){
                // $.admin.notify('请求失败，请重试或联系管理员', 'error');
                $("#modal-pjax-content").html(result.responseText);
            }
        });
    });

    /**
     * pjax 提交表单
     */
    $('body').delegate('form[data-pjax-form]', 'submit', function () {
        var form = $(this);
        var btn = $('[data-id="' + form.attr('id') + '"]');
        var type = form.attr('data-submit-type');
        var successFunction = function(result) {
            try {
                result = JSON.parse(result);
                if(result.code != 200) {
                    $.admin.notify(result.msg, 'error');
                }
            } catch(e) {
                $('#modal-pjax').modal('show');
                $("#modal-pjax-content").html(result);
            }
        };
        if ('file' === type) {
            $.admin.pjaxFileForm(form, btn,  successFunction);
        } else {
            $.admin.pjaxTextForm(form, btn,  successFunction);
        }
        return false;
    });

    /**
     * ajax a标签get请求
     */
    $('body').delegate('a[data-ajax-get]', 'click', function () {
        var a = $(this);
        var url = a.attr("href");
        $.admin.ajaxGet(url, function (json) {
            $.admin.ajaxCallback(json)
        });
        return false;
    });

    /**
     * ajax 提交表单
     */
    $('body').delegate('form[data-ajax-form]', 'submit', function () {
        var form = $(this);
        var btn = $('[data-id="' + form.attr('id') + '"]');
        var type = form.attr('data-submit-type');
        if ('file' === type) {
            $.admin.ajaxFileForm(form, btn,  function (json) {
                $.admin.ajaxCallback(json)
            });
        } else {
            $.admin.ajaxTextForm(form, btn,  function (json) {
                $.admin.ajaxCallback(json)
            });
        }
        return false;
    });

    /**
     * 表单提交（文件）
     *
     * @param $form
     * @param btn
     * @param callback
     */
    $.admin.ajaxFileForm = function ($form, btn, callback) {
        var form = $form.get(0);

        var formData = new FormData(form);

        callback = callback || function () {};

        $.ajax({
            url: $form.attr("action"),
            type: form.method || 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            context: $form,
            beforeSend: function () {
                btn.button('loading');
                btn.prepend('<i class="fa fa-spinner fa-spin"></i> ');
            },
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            },
            success: callback
        }).always(function () {
            window.setTimeout(function () {
                btn.button('reset');
                btn.find('i.fa').remove();
            }, 1500);
        });
    };

    /**
     * 表单提交（文本）
     *
     * @param $form
     * @param btn
     * @param callback
     */
    $.admin.ajaxTextForm = function ($form, btn, callback) {
        var form = $form.get(0);

        callback = callback || function () {};

        $.ajax({
            url: $form.attr("action"),
            type: form.method || 'POST',
            data: $form.serializeArray(),
            dataType: 'json',
            context: $form,
            beforeSend: function () {
                btn.button('loading');
                btn.prepend('<i class="fa fa-spinner fa-spin"></i> ');
            },
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            },
            success: callback
        }).always(function () {
            window.setTimeout(function () {
                btn.button('reset');
                btn.find('i.fa').remove();
            }, 1500);
        });
    };

    /**
     * pjax表单提交（文件）
     *
     * @param $form
     * @param btn
     * @param callback
     */
    $.admin.pjaxFileForm = function ($form, btn, callback) {
        var form = $form.get(0);

        var formData = new FormData(form);

        callback = callback || function () {};

        $.ajax({
            url: $form.attr("action"),
            type: form.method || 'POST',
            data: formData,
            headers: {"X-PJAX": true},
            dataType: 'html',
            contentType: false,
            processData: false,
            context: $form,
            beforeSend: function () {
                btn.button('loading');
                btn.prepend('<i class="fa fa-spinner fa-spin"></i> ');
            },
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            },
            success: callback
        }).always(function () {
            window.setTimeout(function () {
                btn.button('reset');
                btn.find('i.fa').remove();
            }, 1500);
        });
    };

    /**
     * pjax表单提交（文本）
     *
     * @param $form
     * @param btn
     * @param callback
     */
    $.admin.pjaxTextForm = function ($form, btn, callback) {
        var form = $form.get(0);

        callback = callback || function () {};

        $.ajax({
            url: $form.attr("action"),
            type: form.method || 'POST',
            data: $form.serializeArray(),
            headers: {"X-PJAX": true},
            dataType: 'html',
            context: $form,
            beforeSend: function () {
                btn.button('loading');
                btn.prepend('<i class="fa fa-spinner fa-spin"></i> ');
            },
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            },
            success: callback
        });
    };

    /**
     * ajax提交前提示
     *
     * @param url    请求路由
     * @param title  标题
     * @param text   内容
     * @param type   显示类型
     */
    $.admin.tipBeforeAjax = function (url, title, text, type) {
        // 确认弹框配置
        swal({
            title: title,
            text: text,
            type: type,
            showCancelButton: true,
            cancelButtonText: "取消",
            confirmButtonColor: "#ED5565",
            confirmButtonText: "确定",
            closeOnConfirm: true
        }, function () {
            $.admin.ajaxGet(url, function (json) {
                $.admin.ajaxCallback(json)
            })
        })
    };

    /**
     * 吐司配置
     *
     * @param $msg  文字内容
     * @param $type 类型
     */
    $.admin.notify = function ($msg, $type) {

        // toastr 配置
        toastr.options = {
            "debug": false,
            "preventDuplicates": false,
            "positionClass": "toast-top-full-width",
            "onclick": null,
            "showDuration": "400",
            "hideDuration": "1000",
            "timeOut": "1500",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        toastr[$type]($msg);
    };

    /**
     * summernote（富文本编辑器）配置
     *
     * @type {{toolbar: [*], height: number, callbacks: {onInit: onInit, onImageUpload: onImageUpload, onBlur: onBlur}}}
     */
    $.admin.summernoteConfig = {
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['picture', 'link', 'table', 'hr']],
            ['misc', ['codeview', 'undo', 'redo']]
        ],
        height: 250,
        callbacks: {
            onInit: function() {
                var inputName = $(this).attr('id');
                var inputCode = $('input[name="' + inputName + '"]').val();
                $(this).summernote('code', inputCode);
            },
            onImageUpload: function(files) {
                for (var i = files.length - 1; i >= 0; i--) {
                    $.admin.sendFile(files[i], $(this));
                }
            },
            onBlur: function () {
                var inputName = $(this).attr('id');
                $('input[name="' + inputName + '"]').val($(this).summernote('code'));
            }
        }
    };

    /**
     * summernote（富文本编辑器）上传文件
     *
     * @param file
     * @param editor
     */
    $.admin.sendFile = function (file, editor) {
        var data = new FormData();
        data.append("file", file);

        $.ajax({
            data: data,
            type: "POST",
            url: editor.attr('data-url'),
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.code === 200) {
                    editor.summernote('insertImage', response.data.url, '');
                }
            },
            error: function () {
                $.admin.notify("服务异常，请联系管理员", 'error');
            }
        });
    };

    /**
     * 后置吐司显示
     *
     * @param method
     * @param message
     * @param type
     */
    $.admin.preAction = function (method, message, type) {
        if (method === 'notify') {
            $.admin.notify(message, type);
        }
    }

})(jQuery);