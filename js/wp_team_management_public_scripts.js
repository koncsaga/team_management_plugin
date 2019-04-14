$( document ).ready(function() {
    $('.team-members button').click(function(){
        $(this).prev().toggleClass("d-block", 3000);

        if ($(this).prev().hasClass('d-block')) {
            $(this).text('Read less');
        }else{
            $(this).text('Read more');
        }
    });
});