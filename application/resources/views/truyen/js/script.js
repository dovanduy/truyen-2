 var Pos = function() {
     "use strict";
     var noticePopup = function(mess) {
         bootbox.dialog({
             message: mess,
             title: "Thông báo",
             buttons: {
                 main: {
                     label: "Ok",
                     className: "btn-primary",
                     callback: function() {}
                 }
             }
         });
     }
     var runLoadingAjax = function(isShow, id) {
         if (isShow == 1) {
             $('#' + id).block({
                 message: '<i class="fa fa-spinner fa-spin"></i> Dữ liệu đang load...',
                 css: {
                     border: 'none',
                     padding: '15px',
                     backgroundColor: '#000',
                     '-webkit-border-radius': '10px',
                     '-moz-border-radius': '10px',
                     opacity: .5,
                     color: '#fff'
                 }
             });
         } else $('#' + id).unblock();
     }
     var runGetImg = function() {
         $('#btnGetImg').click(function() {
             var title = $('#title').val();
             var url = $('#url').val();
             var websiteId = $('#website_id').val();
             if ($.trim(title).length == 0) {
                 noticePopup('Chưa nhập tên truyện');
                 return false;
             }
             if ($.trim(url).length == 0) {
                 noticePopup('Chưa nhập url');
                 return false;
             }
             if ($.trim(websiteId).length == 0) {
                 noticePopup('Chưa chọn nguồn');
                 return false;
             }
             runLoadingAjax(1, 'showLoading');
             $.ajax({
                 url: _url + "/client/get-img-avatar",
                 dataType: 'json',
                 data: {
                     title: title,
                     url: url,
                     websiteId: websiteId
                 },
                 method: 'get',
                 success: function(result) {
                     console.log(result);
                     if (result.info == 'success') {
                         $('#imgAvatar').attr('src', result.data.imgUrl);
                         $('#linkFile').val(result.data.linkFile);
                     } else {
                         noticePopup(result.message);
                     }
                     runLoadingAjax(0, 'showLoading');
                 }
             });
             return false;
         });
     }
     var runGetTotalChap = function() {
         $('#btnTotalChap').click(function() {
             var title = $('#title').val();
             var url = $('#url').val();
             var websiteId = $('#website_id').val();
             if ($.trim(title).length == 0) {
                 noticePopup('Chưa nhập tên truyện');
                 return false;
             }
             if ($.trim(url).length == 0) {
                 noticePopup('Chưa nhập url');
                 return false;
             }
             if ($.trim(websiteId).length == 0) {
                 noticePopup('Chưa chọn nguồn');
                 return false;
             }
             runLoadingAjax(1, 'showLoading');
             $.ajax({
                 url: _url + "/client/get-total-chap",
                 dataType: 'json',
                 data: {
                     url: url,
                     websiteId: websiteId
                 },
                 method: 'get',
                 success: function(result) {
                     console.log(result);
                     if (result.info == 'success') {
                         $('#total_chap').val(result.totalChap);
                     } else {
                         noticePopup(result.message);
                     }
                     runLoadingAjax(0, 'showLoading');
                 }
             });
             return false;
         });
     }
     return {
         initGetImg: function() {
             runGetImg();
         },
         initGetTotalChap: function() {
             runGetTotalChap();
         }
     };
 }();