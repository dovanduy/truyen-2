/*
 --------------------------------------
 ---------- Input Group File ----------
 --------------------------------------
 */

$.fn.inputFile = function () {
    var $this = $(this);
    $this.find('input[type="file"]').on('change', function () {
        $this.find('input[type="text"]').val($(this).val());
    });
}

$('.input-group-file').inputFile();


/*
 --------------------------------------
 ---------- Date Time Picker ----------
 --------------------------------------
 */

if ($.fn.datetimepicker) {

    $('.datePicker').datetimepicker({
        keepOpen: true,
        format: 'YYYY-MM-DD'
    });

    $('.timePicker').datetimepicker({
        keepOpen: true,
        format: 'LT'
    });

    $('.dateTimePicker').datetimepicker({
        keepOpen: true
    });
}