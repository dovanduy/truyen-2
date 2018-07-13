/**
 * Created by SHAMIM on 02-Mar-17.
 */
$(document).ready(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    $(".item-add").on("click", function () {

        var sTable = $("#sender_id_items");
        var RowAppend = ['<tr class="item-row">',
            '<td><input type="text" autocomplete="off" name="sender_id[]"  class="form-control sender_id"></td>'+
            '<td><button class="btn btn-danger" id="RemoveITEM" type="button"><i class="fa fa-trash-o"></i> Remove</button></td>'
            , "</tr>"].join("");
        var sLookup = $(RowAppend);

        var sender_id = sLookup.find(".sender_id");
        $(".item-row:last", sTable).after(sLookup);
        $(sender_id).focus();

        sLookup.find("#RemoveITEM").on("click", function () {
            $(this).parents(".item-row").remove();
            if ($(".item-row").length < 2) $("#deleteRow").hide();
        });

        return false
    });

});
