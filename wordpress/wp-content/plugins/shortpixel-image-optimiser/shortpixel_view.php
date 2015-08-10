<?php

class ShortPixelView {
    
    private $ctrl;
    
        //handling older
    public function ShortPixelView($controller) {
        $this->__construct($controller);
    }

    public function __construct($controller) {
        $this->ctrl = $controller;
    }
    
    public function displayQuotaExceededAlert($quotaData) 
    { ?>    
        <br/>
        <div class="wrap" style="background-color: #fff; border-left: 4px solid #ff0000; box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1); padding: 1px 12px;">
            <h3>Quota Exceeded</h3>
            <p>The plugin has optimized <?=number_format($quotaData['APICallsMadeNumeric'] + $quotaData['APICallsMadeOneTimeNumeric'])?> images and stopped because it reached the available quota limit.</p>
            <p>It’s simple to upgrade, just <a href='https://shortpixel.com/login/<?=$this->ctrl->getApiKey()?>' target='_blank'>log into your account</a> and see the available options.</p>
            <p>You can immediately start processing 5,000 images/month for &#36;4,99, choose another plan that suits you or <a href='https://shortpixel.com/contact' target='_blank'>contact us</a> for larger compression needs.</p>
            <input type='button' name='checkQuota' class='button button-primary' value='Confirm New Quota' onclick="javascript:window.location.reload();" style="margin-bottom:12px;">
        </div> <?php 
    }
    
    public function displayApiKeyAlert() 
    { ?>
        <p>In order to start the optimization process, you need to validate your API key in the <a href="options-general.php?page=wp-shortpixel">ShortPixel Settings</a> page in your WordPress Admin.</p>
        <p>If you don’t have an API Key, you can get one delivered to your inbox, for free.</p>
        <p>Please <a href="https://shortpixel.com/wp-apikey" target="_blank">sign up</a> to get your API key.</p>
    <?php
    }
    
    public function displayBulkProcessingForm($imageCount, $imgProcessedCount,  $thumbsProcessedCount, $under5PercentCount, $bulkRan, $averageCompression, $filesOptimized, $savedSpace, $percent) {
        ?>
        <div class="wrap short-pixel-bulk-page">
            <h1>Bulk Image Optimization by ShortPixel</h1>
        <?php
        if ( !$bulkRan ) { ?>
            <p>You have <?=number_format($imageCount['mainFiles'])?> images in your Media Library and <?=number_format($imageCount['totalFiles'] - $imageCount['mainFiles'])?> smaller thumbnails, associated to these images.</p>
            <?php if($imgProcessedCount["totalFiles"] > 0) { ?>
            <p>From these, <?=number_format($imgProcessedCount['mainFiles'])?> images and <?=number_format($imgProcessedCount['totalFiles'] - $imgProcessedCount['mainFiles'])?> thumbnails were already processed by ShorPixel</p>
            <?php } ?>
            <p>If the box below is checked, <b>ShortPixel will process a total of <?=number_format($imageCount['totalFiles'] - $imgProcessedCount['totalFiles'])?> images.</b> However, images with less than 5% optimization will not be counted out of your quota, so the final number of counted images could be smaller.</p>
            <p>Thumbnails are important because they are displayed on most of your website's pages and they may generate more traffic than the originals. Optimizing thumbnails will improve your overall website speed. However, if you don't want to optimize thumbnails, please uncheck the box below.</p>

            <form action='' method='POST' >
                <input type='checkbox' name='thumbnails' <?=$this->ctrl->processThumbnails() ? "checked":""?>> Include thumbnails
                <p>The plugin will replace the original images with the optimized ones starting with the newest images added to your Media Library. You will be able to pause the process anytime.</p>
                <?=$this->ctrl->backupImages() ? "<p>Your original images will be stored in a separate back-up folder.</p>" : ""?> 
                <input type='submit' name='bulkProcess' id='bulkProcess' class='button button-primary' value='Start Optimizing'>
            </form>
            <?php
        } elseif($percent) // bulk is paused
        { ?>
            <p>Bulk processing is paused until you resume the optimization process.</p>
            <?=$this->displayBulkProgressBar(false, $percent, "")?>
            <p>Please see below the optimization status so far:</p>
            <?=$this->displayBulkStats($filesOptimized, $thumbsProcessedCount, $under5PercentCount, $averageCompression, $savedSpace)?>
            <p>You can continue optimizing your Media Gallery from where you left, by clicking the Resume processing button. Already optimized images will not be reprocessed.</p>
        <?php
        } else { ?>
            <p>Congratulations, your media library has been successfully optimized!</p>
            <?=$this->displayBulkStats($filesOptimized, $thumbsProcessedCount, $under5PercentCount, $averageCompression, $savedSpace)?>
            <p>Go to the ShortPixel <a href='<?=get_admin_url()?>options-general.php?page=wp-shortpixel#facts'>Stats</a> and see all your websites' optimized stats. Download your detailed <a href="https://api.shortpixel.com/v2/report.php?key=<?=$this->ctrl->getApiKey()?>">Optimization Report</a> to check your image optimization statistics for the last 40 days</p>
            <?php if($imgProcessedCount['totalFiles'] < $imageCount['totalFiles']) { ?>
            <p><?=number_format($imageCount['mainFiles'] - $imgProcessedCount['mainFiles'])?> images and <?=number_format(($imageCount['totalFiles'] - $imageCount['mainFiles']) - ($imgProcessedCount['totalFiles'] - $imgProcessedCount['mainFiles']))?> thumbnails are not yet optimized by ShortPixel.</p>
            <?php } ?>
            <p>Restart the optimization process for new images added to your library by clicking the button below. Already optimized images will not be reprocessed.
            <form action='' method='POST' >
                <input type='checkbox' name='thumbnails' <?=$this->ctrl->processThumbnails() ? "checked":""?>> Include thumbnails<br><br>
                <input type='submit' name='bulkProcess' id='bulkProcess' class='button button-primary' value='Restart Optimizing'>
            </form>
        <?php } ?>
        </div>
        <?php
    }

    public function displayBulkProcessingRunning($percent, $message) {
        ?>
        <div class="wrap short-pixel-bulk-page">
            <h1>Bulk Image Optimization by ShortPixel</h1>
            <p>Bulk optimization has started.<br>
               This process will take some time, depending on the number of images in your library. In the meantime, you can continue using the admin as usual.<br>
               However, <strong>if you close the WordPress admin, the bulk processing will pause</strong> until you open the admin again. </p>
            <?=$this->displayBulkProgressBar(true, $percent, $message)?>
            <div class="bulk-progress bulk-slider-container">
                <div style="margin-bottom: 10px;"><span class="short-pixel-block-title">Just optimized:</span></div>
                <div class="bulk-slider">
                    <div class="bulk-slide" id="empty-slide">
                        <div class="img-original">
                            <div><img class="bulk-img-orig" src=""></div>
                          <div>Original image</div>
                        </div>
                        <div class="img-optimized">
                            <div><img class="bulk-img-opt" src=""></div>
                          <div>Optimized image</div>
                        </div>
                        <div class="img-info">
                            <div style="font-size: 14px; line-height: 10px; margin-bottom:16px;">Optimized by:</div>
                            <span class="bulk-opt-percent"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function displayBulkProgressBar($running, $percent, $message) {
        $percentBefore = $percentAfter = '';
        if($percent > 24) {
            $percentBefore = $percent . "%";
        } else {
            $percentAfter = $percent . "%";
        }
        ?>
            <div class="bulk-progress">
                <div id="bulk-progress" class="progress" >
                    <div class="progress-img" style="left: <?=$percent?>%;">
                        <img src="<?=WP_PLUGIN_URL?>/shortpixel-image-optimiser/img/slider.png">
                        <span><?=$percentAfter?></span>
                    </div>
                    <div class="progress-left" style="width: <?=$percent?>%"><?=$percentBefore?></div>
                </div>
                <div class="bulk-estimate">
                    &nbsp;<?=$message?>
                </div>
            <form action='' method='POST' style="display:inline;">
                <input type="submit" class="button button-primary bulk-cancel"  onclick="clearBulkProcessor();"
                       name="<?=$running ? "bulkProcessPause" : "bulkProcessResume"?>" value="<?=$running ? "Pause" : "Resume Processing"?>"/>
            </form>
            </div>
        <?php
    }
    
    public function displayBulkStats($filesOptimized, $thumbsProcessedCount, $under5PercentCount, $averageCompression, $savedSpace) {
        ?>
            <div class="bulk-progress bulk-stats">
                <div class="label">Processed Images and PDFs:</div><div class="stat-value"><?=number_format($filesOptimized - $thumbsProcessedCount)?></div><br>
                <div class="label">Processed Thumbnails:</div><div class="stat-value"><?=number_format($thumbsProcessedCount)?></div><br>
                <div class="label totals">Total files processed:</div><div class="stat-value"><?=number_format($filesOptimized)?></div><br>
                <div class="label totals">Files with <5% optimization (free):</div><div class="stat-value">-<?=number_format($under5PercentCount)?></div><br><br>
                <div class="label totals">Used quota:</div><div class="stat-value"><?=number_format($filesOptimized - $under5PercentCount)?></div><br>
                <br>
                <div class="label">Average optimization:</div><div class="stat-value"><?=$averageCompression?>%</div><br>
                <div class="label">Saved space:</div><div class="stat-value"><?=$savedSpace?></div>
            </div>
        <?php
    }
     
}
