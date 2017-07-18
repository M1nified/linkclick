'using strict';
(function ($, jQuery) {
    $(function () {
        $("#linkclick-dialog-1").dialog({
            autoOpen: false,
            title: "Security"
        })
        $(".linkclick-btn-secure").on('click', function () {

            $("#linkclick-dialog-1").dialog("open");
            $("#linkclick-dialog-1-post-id").val($(this).data('post-id'));
            
            var lock_id = $(this).data('linkclick-lock-id');
            var category_id = $(this).data('linkclick-category-id');
            var date = $(this).data('linkclick-date');

            $("#linkclick-dialog-1 select[name=\"linkclick-lock-id\"]>option").removeAttr('selected').each(function(){
                if($(this).val() == lock_id){
                    $(this).attr('selected',true);
                }
            });
            $("#linkclick-dialog-1 select[name=\"linkclick-category-id\"]>option").removeAttr('selected').each(function(){
                if($(this).val() == category_id){
                    $(this).attr('selected',true);
                }
            });

            $("#linkclick-dialog-1 input.linkclick-date").val(date);
        })
    })
})(jQuery, jQuery);