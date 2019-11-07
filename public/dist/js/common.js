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