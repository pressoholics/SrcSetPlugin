;
var TNSrcSetData = TNSrcSetData || {};

(function($, localized){
	
	// Elements - stored for reuse
	var $regenButton = $('.os-srcset-regen'),
		$progress = $('#os-srcset-regen-status .progress');
		$message = $('#os-srcset-regen-status .message');
	
	var currentPage = 0, // current page index
		completedPosts = 0, // Completed count
		totalPosts = 0, // total count
		regenAction = 0;
	
	
	// Document ready
	$(function(){
		$regenButton.on('click', initRegeneration);
	});
	
	/**
	 * Initialize the regeneration process
	 * 
	 * @returns {undefined}
	 */
	function initRegeneration(){
		$regenButton.hide();
		$message.html(localized.messages.start);
		
		//Cache unistall checkbox val
		regenAction = $('.uninstall-srcset').val();
		
		regenBatch();
	}
	
	/**
	 * Update the progess message of the regeneration.
	 * 
	 * @returns {undefined}
	 */
	function updateProgress() {
		var message = localized.messages.progress;
		message = message.replace(/\%d/, Math.min(totalPosts,completedPosts)).replace(/\%d/, totalPosts);
		$progress.html(message);
		$message.html('');
	}
	
	/**
	 * Run an AJAX regeneration batch.
	 * @returns {undefined}
	 */
	function regenBatch(){
		$.ajax({
			url: localized.ajaxUrl,
			dataType: 'json',
			data: {
				action: localized.ajaxAction,
				current_page: currentPage,
				regen_action: regenAction
			},
			success: function(response){
				totalPosts = response.data.totalPosts;
				completedPosts += response.data.postsPerPage;
				currentPage++;
				
				updateProgress();
					
				if ( completedPosts < totalPosts )  {
					regenBatch();
				} else {
					// Set completed message
					$message.addClass('success').append(localized.messages.complete);
				}
			},
			error: function(jqXHR, textStatus){
				// Set error message
				$progress.addClass('error').html(localized.messages.error);
			}
		});
	}
	
	/**
	 * 
	 */
	
})(jQuery, TNSrcSetData);