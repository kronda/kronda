/**
 * Short Pixel WordPress Plugin javascript
 */

jQuery(document).ready(function($){
    //are we on media list?
    if( jQuery('table.wp-list-table.media').length > 0) {
        //register a bulk action
        jQuery('select[name^="action"] option:last-child').before('<option value="short-pixel-bulk">Optimize with ShortPixel</option>');
    }    
    //
    jQuery(window).unload(function(){
        if(ShortPixel.bulkProcessor == true) {        
            clearBulkProcessor();
        }
    });
    //check if  bulk processing
    checkQuotaExceededAlert();
    checkBulkProgress();
});


var ShortPixel = function() {

    function setOptions(options) {
        for(var opt in options) {
            ShortPixel[opt] = options[opt];
        }
    }
    
    return {
        setOptions: setOptions
    }
}();

function showToolBarAlert($status, $message) {
    switch($status) {
        case ShortPixel.STATUS_QUOTA_EXCEEDED:
            jQuery("li.shortpixel-toolbar-processing").addClass("shortpixel-alert");
            jQuery("li.shortpixel-toolbar-processing").addClass("shortpixel-quota-exceeded");
            jQuery("li.shortpixel-toolbar-processing a").attr("href", "http://shortpixel.com/login/" + ShortPixel.API_KEY);
            jQuery("li.shortpixel-toolbar-processing a div").attr("title", "ShortPixel quota exceeded. Click to top-up");
            break;
        case ShortPixel.STATUS_FAIL:        
            jQuery("li.shortpixel-toolbar-processing").addClass("shortpixel-alert shortpixel-processing"); 
            jQuery("li.shortpixel-toolbar-processing a div").attr("title", $message);
            break;
    }
    jQuery("li.shortpixel-toolbar-processing").removeClass("shortpixel-hide");
}
function hideToolBarAlert () {
    jQuery("li.shortpixel-toolbar-processing.shortpixel-processing").addClass("shortpixel-hide");
}

function hideQuotaExceededToolBarAlert () {
    jQuery("li.shortpixel-toolbar-processing.shortpixel-quota-exceeded").addClass("shortpixel-hide");
}

function checkQuotaExceededAlert() {
    if(typeof shortPixelQuotaExceeded != 'undefined') {
        if(shortPixelQuotaExceeded == 1) {
             showToolBarAlert(ShortPixel.STATUS_QUOTA_EXCEEDED);
        } else {
            hideQuotaExceededToolBarAlert();
        }
    }
}
/**
 * JavaScript image processing - this method gets executed on every footer load and afterwards 
 * calls itself until receives an Empty queue message
 */
function checkBulkProgress() {
    //if I'm not the bulk processor, check every 20 sec. if the bulk processor is running, otherwise take the role
    if(ShortPixel.bulkProcessor == true || typeof localStorage.bulkTime == 'undefined' || Math.floor(Date.now() / 1000) -  localStorage.bulkTime > 90) {
        ShortPixel.bulkProcessor = true;
        localStorage.bulkTime = Math.floor(Date.now() / 1000);
        console.log(localStorage.bulkTime);
        checkBulkProcessingCallApi();
    } else {
        console.log("not the bulk processor");
        setTimeout(checkBulkProgress, 5000);
    }
}

function checkBulkProcessingCallApi(){
    var data = { 'action': 'shortpixel_image_processing' };
    // since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) 
    {
        if(response.length > 0) {
            var data = null;
            try {
                var data = JSON.parse(response);
            } catch (e) {
                ShortPixel.retries++;
                if(isNaN(ShortPixel.retries)) ShortPixel.retries = 1;
                if(ShortPixel.retries < 6) {
                    console.log("Invalid response from server. Retrying pass " + (ShortPixel.retries + 1) +  "...");
                    setTimeout(checkBulkProgress, 2000);
                } else {
                    console.log("Invalid response from server 6 times. Giving up.");                    
                }
                return;
            }
            var id = data["ImageID"];
            
            var isBulkPage = (jQuery("div.short-pixel-bulk-page").length > 0);
            
            switch (data["Status"]) {
                case ShortPixel.STATUS_QUOTA_EXCEEDED:
                    setCellMessage(id, data["Message"] + " | <a href=\"https://shortpixel.com/login/" 
                                   + ShortPixel.API_KEY + ")\">Extend Quota</a>");
                    showToolBarAlert(ShortPixel.STATUS_QUOTA_EXCEEDED);
                    break;
                case ShortPixel.STATUS_FAIL:
                    setCellMessage(id, data["Message"]);
                    if(isBulkPage) {
                        showToolBarAlert(ShortPixel.STATUS_FAIL, data["Message"]);
                    }
                    console.log(data["Message"]);
                    break;
                case ShortPixel.STATUS_EMPTY_QUEUE:
                    console.log(data["Message"]);
                    clearBulkProcessor(); //nothing to process, leave the role. Next page load will check again
                    hideToolBarAlert();
                    var progress = jQuery("#bulk-progress");
                    if(isBulkPage && progress.length && data["BulkStatus"] != '2') {
                        progressUpdate(100, "Bulk finished!");
                        jQuery("a.bulk-cancel").attr("disabled", "disabled");
                        hideSlider();
                        //showStats();
                        setTimeout(function(){
                            window.location.reload();
                        }, 3000);
                    }
                    break;
                case ShortPixel.STATUS_SUCCESS:
                    var percent = data["PercentImprovement"];
                    setCellMessage(id, "Reduced by <span class='percent'>" + percent + "%</span> | <a href=\"admin.php?action=shortpixel_restore_backup&attachment_ID=" + id + ")\">Restore backup</a>");
                    var animator = new PercentageAnimator("#sp-msg-" + id + " span.percent", percent);
                    animator.animate(percent);
                    if(isBulkPage && typeof data["Thumb"] !== 'undefined' && data["PercentImprovement"] > 0) {
                        progressUpdate(data["BulkPercent"], data["BulkMsg"]);
                        if(data["Thumb"].length > 0){
                            sliderUpdate(id, data["Thumb"], data["BkThumb"], data["PercentImprovement"]);
                        }
                    }                    
                    //fall through
                case ShortPixel.STATUS_ERROR: //for error and skip also we retry
                case ShortPixel.STATUS_SKIP:
                    console.log('Server response: ' + response);
                    setTimeout(checkBulkProgress, 2000);
            }
        }
    });
}

function clearBulkProcessor(){
    ShortPixel.bulkProcessor = false; //nothing to process, leave the role. Next page load will check again
    localStorage.bulkTime = 0;
}

function setCellMessage(id, message){
    var msg = jQuery("#sp-msg-" + id);
    if(typeof msg !== "undefined") {
        msg.html(message);
    }    
}

function manualOptimization(id) {
    setCellMessage(id, "<img src='" + ShortPixel.WP_PLUGIN_URL + "/shortpixel-image-optimiser/img/loading.gif'>Image waiting to be processed");
    jQuery("li.shortpixel-toolbar-processing").removeClass("shortpixel-hide");
    jQuery("li.shortpixel-toolbar-processing").addClass("shortpixel-processing");
    var data = { action  : 'shortpixel_manual_optimization',
                 image_id: id};
    jQuery.get(ajaxurl, data, function(response) {
        data = JSON.parse(response);
        if(data["Status"] == ShortPixel.STATUS_SUCCESS) {
            setTimeout(checkBulkProgress, 2000);
        } else {
            setCellMessage(id, "This content is not processable.");
        }
        //aici e aici
    });
}

function PercentageAnimator(outputSelector, targetPercentage) {
    this.animationSpeed = 10;
    this.increment = 2;
    this.curPercentage = 0;
    this.targetPercentage = targetPercentage;
    this.outputSelector = outputSelector;
    
    this.animate = function(percentage) {
        this.targetPercentage = percentage;
        setTimeout(PercentageTimer.bind(null, this), this.animationSpeed);
    }
}

function PercentageTimer(animator) {
    if (animator.curPercentage - animator.targetPercentage < -animator.increment) {
        animator.curPercentage += animator.increment;    
    } else if (animator.curPercentage - animator.targetPercentage > animator.increment) {
        animator.curPercentage -= animator.increment;    
    } else {
        animator.curPercentage = animator.targetPercentage;
    }

    jQuery(animator.outputSelector).text(animator.curPercentage + "%");

    if (animator.curPercentage != animator.targetPercentage) {
        setTimeout(PercentageTimer.bind(null,animator), animator.animationSpeed)    
    }
}

function progressUpdate(percent, message) {
    var progress = jQuery("#bulk-progress");
    if(progress.length) {
        jQuery(".progress-left", progress).css("width", percent + "%");
        jQuery(".progress-img", progress).css("left", percent + "%");
        if(percent > 24) {
            jQuery(".progress-img span", progress).html("");
            jQuery(".progress-left", progress).html(percent + "%");
        } else {
            jQuery(".progress-img span", progress).html(percent + "%");
            jQuery(".progress-left", progress).html("");        
        }
        jQuery(".bulk-estimate").html(message);
    }
}

function sliderUpdate(id, thumb, bkThumb, percent){
    var oldSlide = jQuery(".bulk-slider div.bulk-slide:first-child");
    var newSlide = oldSlide.clone();
    newSlide.attr("id", "slide-" + id);
    if(oldSlide.attr("id") != "empty-slide") {
        newSlide.hide();
    }
    oldSlide.css("z-index", 1000);
    jQuery(".bulk-img-opt", newSlide).attr("src", thumb);
    if(bkThumb.length > 0) {
        jQuery(".img-original", newSlide).css("display", "inline-block");
        jQuery(".bulk-img-orig", newSlide).attr("src", bkThumb);
    } else {
        jQuery(".img-original", newSlide).css("display", "none");
    }
    jQuery(".bulk-opt-percent", newSlide).text(percent + "%");
    
    jQuery(".bulk-slider").append(newSlide);
    if(oldSlide.attr("id") == "empty-slide") {
        oldSlide.remove();
        jQuery(".bulk-slider-container").css("display", "block");
    } else {
        oldSlide.animate({ left: oldSlide.width() }, 'slow', 'swing', function(){
            oldSlide.remove();
            newSlide.fadeIn("slow");
        });
    }
}

function hideSlider() {
    jQuery(".bulk-slider-container").css("display", "none");
}

function showStats() {
    var statsDiv = jQuery(".bulk-stats");
    if(statsDiv.length > 0) {
        
    }
}
