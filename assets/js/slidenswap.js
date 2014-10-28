/*!
 * slideNswap Plugin for jQuery
 *
 * @author Asaf Zamir
 * @link http://jquery.kidsil.net
 * @version 0.01
 * @date 26/02/2011
 *
 * Description:
 * get a really nice sliding effect (you have to see it to understand)
 * a one of a kind :)
 * 
 * Usage:
 * create a <div class="swapWrapper"> that holds the <img> (could be any class you want),
 * then just add	jQuery(document).ready(function() {
 *						slidenswapprepare(jQuery(".swapWrapper"));
 *					});
 * an example is available at http://jquery.kidsil.net
 */

function slidenswapprepare(theobj) {
	jQuery(theobj).children('img').hide();
	jQuery(theobj).children('img').wrap(function() {
		return '<div class="swapMe" style="display:none;position:absolute;background-repeat:no-repeat;width:100%;height:100%;background-image:url('+jQuery(this).attr('src')+')" />';
	});
	jQuery(theobj).children('div.swapMe:first').show();
	jQuery(window).load(function() {
		slidenswap(jQuery(".swapWrapper"),0);
	});	
	
}
function slidenswap(theobj,cont) {
	theobj = theobj.children('div.swapMe:visible');
	movwidth = theobj.children('img').get(0).width-theobj.width();
	movheight = theobj.children('img').get(0).height-theobj.height();
	movheight = Math.abs(movheight)*(-1);
	movwidth = Math.abs(movwidth)*(-1);
	if (cont != 1) {
		theobj.css("background-position","0px 0px");
	}
	theobj.animate({ backgroundPosition : movwidth + "px 0px"} ,3000, function() {
		jQuery(this).animate({ backgroundPosition : movwidth + "px " + movheight + "px" },2000, 
			function() {
				switchtonext( jQuery(this).parent(), false, 1 );
			});
		});
	/*return false;*/
}
/* theobj is the .swapWrapper, manual tells you if the call is done by the user or by slidenswap
   and direction is obvious.																		*/
function switchtonext(theobj,manual,direction) {
	hideme = theobj.children('div.swapMe:visible');
	if (direction == -1) {
		showme = theobj.children('div.swapMe:visible').prev('.swapMe');
	}
	else {
		showme = theobj.children('div.swapMe:visible').next('.swapMe');
	}
	if (showme.length == 0) {
		if (direction == -1) {
			showme = theobj.children('div.swapMe:last');
		}
		else {
			showme = theobj.children('div.swapMe:first');
		}
	}	
	showme.css("z-index", "9");
	showme.fadeIn(1500, function() {
		jQuery(this).parent().children('div.swapMe:visible').not(this).hide().css("background-position","0px 0px");
		showme.css("z-index","");
		/* if manually called, don't call slidenswap */
		if (!manual) {
			slidenswap(jQuery(this).parent(),0);
		}
		return false;
		}
	);
	return false;
}

function slidenswapControllers(theobj) {
	theobj.append('<div id="slide-n-swap-controllers"><div id="back_cont"> &larr; </div><div id="pause_cont"> || </div><div id="forward_cont"> &rarr; </div>');
	theobj.hover(function() {
		jQuery(this).children('#slide-n-swap-controllers').fadeIn();
	},function () {
		jQuery(this).children('#slide-n-swap-controllers').fadeOut();
		
	});
	stopped = false;
	/*Back Controller */
	theobj.find('#slide-n-swap-controllers #back_cont').click( function() {
		/* only enable forward while 1 picture is shown (not while fading) */
		swapwrap = jQuery(this).parent().parent()
		if (swapwrap.children('.swapMe:visible').length <= 1) {
			swapwrap.children('.swapMe').stop();
			switchtonext(swapwrap,false,-1);
			/* disable pause if paused */
			jQuery(this).siblings('#pause_cont').text('||');
			stopped = false;
		}
		
	});

	/* Pause Controller */
	theobj.find('#slide-n-swap-controllers #pause_cont').click( function() {
		/* only enable pause while 1 picture is shown (not while fading) */
		swapwrap = jQuery(this).parent().parent()
		if (swapwrap.children('.swapMe:visible').length <= 1) {
			if (stopped) {
				slidenswap(swapwrap,1);
				jQuery(this).text('||');
			}
			else {
				swapwrap.children('.swapMe').stop();
				jQuery(this).text('>');
			}
			stopped = !stopped;
		}
	});

	/* Forward Controller */
	theobj.find('#slide-n-swap-controllers #forward_cont').click( function() {
		/* only enable forward while 1 picture is shown (not while fading) */
		swapwrap = jQuery(this).parent().parent()
		if (swapwrap.children('.swapMe:visible').length <= 1) {
			swapwrap.children('.swapMe').stop();
			switchtonext(swapwrap,false,1);
			jQuery(this).siblings('#pause_cont').text('||');
			stopped = false;

		}
	});

}

jQuery( document ).ready( function() {
	var wrapperElement = jQuery( '.swapWrapper' );
	slidenswapprepare( wrapperElement );
	if ( controllers ) {
		slidenswapControllers( wrapperElement );
	}
})
