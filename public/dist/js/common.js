$(function () {
    if (typeof (plupload) !== "undefined") {
        let type = $('#uploadImg').data('type');
        let uploader = new plupload.Uploader({
            browse_button: 'uploadImg', //触发文件选择对话框的按钮，为那个元素id
            url: '/index/upload/img.html?type='+type, //服务器端的上传页面地址
            flash_swf_url: '/dist/plugins/plupload/Moxie.swf', //swf文件，当需要使用swf方式进行上传时需要配置该参数
            silverlight_xap_url: '/dist/plugins/plupload/Moxie.xap', //silverlight文件，当需要使用silverlight方式进行上传时需要配置该参数
            headers: {
                'token': localStorage.getItem('token')
            },
            filters: {
                mime_types: [ //只允许上传图片
                    {
                        title: "Image files",
                        extensions: "jpg,gif,png,jpeg,bmp"
                    },
                ]
            },
            resize: {
                width: 350
            }
        });
        //在实例对象上调用init()方法进行初始化
        uploader.init();
        uploader.bind('FilesAdded', function(uploader, file) {
            uploader.start();
        });
        uploader.bind('Error', function(uploader, errObject) {
            alertMsg('error', errObject.message);
        });
        uploader.bind('FileUploaded', function(uploader, file, responseObject) {
            let result = JSON.parse(responseObject.response);
            if (result.code != 0) {
                alertMsg('error', result.msg);
                return false;
            } else {
                $('#uploadImg').attr('src', result.data);
                $('#uploadImgInput').val(result.data);
            }
        });
    }
});

function alertMsg(type, content) {
    if($('#alertMsg').length != 0) {
        return false;
    }
    if (type == 'error') {
        type = 'danger';
    }
    var html = '<div id="alertMsg" style="display: none" class="alert alert-'+type+' alert-dismissible">' +
        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + content + '</div>';
    $('body').append(html);
    $('#alertMsg').slideDown();
    setTimeout(function(){
        $('#alertMsg').slideUp();
        setTimeout(function(){
            $('#alertMsg').remove();
        }, 1000);
    }, 2000);
}
function pageHtml(page, count, link) {
    let html = '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination">';
    html += '<li class="paginate_button previous ';
    if (parseInt(page) === 1) {
        html += 'disabled';
    }
    html += '"><a href="'+(parseInt(page) === 1 ? '#' : link.replace(new RegExp("{page}","g"), page - 1))+'">上一页</a></li>';
    for(var i = 1;i<= parseInt(count);i++) {
        html += '<li class="paginate_button '+(parseInt(page) === i ? 'active' : '')+'"><a href="'+(parseInt(page) === i ? '#' : link.replace(new RegExp("{page}","g"), i))+'">'+i+'</a></li>';
    }
    html += '<li class="paginate_button next ';
    if (parseInt(page) === parseInt(count)) {
        html += 'disabled';
    }
    html += '"><a href="'+(parseInt(page) === parseInt(count) ? '#' : link.replace(new RegExp("{page}","g"), page + 1))+'">下一页</a></li>';
    html += '</ul></div>';
    $('#pageList').html(html);
}