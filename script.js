(function($) {

    $(document).ready(function(){

        $(".gt_heart_like").click( function(e) {

           //custom event

            e.preventDefault(); 

            var postId  =  $(this).data('post-id');
            var $count  =  $(this).find('.count');
            var $heart  =  $(this).find('.heart');
            var sign    = 'positive';

            $heart.toggleClass('active');

            // Set cookie to keep track of likes

            //toggle
            if(!$heart.hasClass('active')){
               sign = 'negative';
            }
      
            $.ajax({
               type : "post",
               dataType : "json",
               url : myAjax.ajaxurl,
               data : {action: "gt_hl_heart_like", postId: postId, sign: sign},
               success: function(response) {
                  if(response.success) {
                    $count.html(response.data.likes);
                  }
               }
            })   
      
         })

    });


})(jQuery);