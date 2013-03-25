/* basic js */
jQuery(function($){
	$(document).ready(function(){
		$('.alert').alert();
	});

    $('body').on('click', 'a', function(){
        if($(this).attr('href').substring(0,1) == '#') {
            $('.left-menu a').removeClass('active');
            //alert('li a[href="'+$(this).attr('href')+'"]');
            $('.left-menu').find('li a[href="'+$(this).attr('href')+'"]').addClass('active');
        }
    });
});
