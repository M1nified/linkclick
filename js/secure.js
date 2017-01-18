'using strict';
(function ($, jQuery) {
    $(function () {
        $("#linkclick-dialog-1").dialog({
            autoOpen: false,
            title: "Security"
        })
        $(".linkclick-btn-secure").on('click', function () {
            // console.log($(this).data('post-id'))
            $("#linkclick-dialog-1").dialog("open");
            $("#linkclick-dialog-1-post-id").val($(this).data('post-id'));
        })
    })
})(jQuery, jQuery);