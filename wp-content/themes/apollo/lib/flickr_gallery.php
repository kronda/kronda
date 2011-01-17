<?php 
class Apollo_Flickr_Feed_Gallery {
    var $feed_type;
    var $user_id;
    var $tags;
    var $tag_mode;
    var $image_limit;
    function get_flickr_feed_options() {
        $this->feed_type = 'PUBLIC';
        $this->user_id = get_option("ffg_user_id");
        $this->images_per_contact = get_option("ffg_images_per_contact");
        $this->tags = '';
        $this->tag_mode = 'any';
        $this->image_limit = '_s';
        return $this;
    }
    function get_flickr_json_url($options) {
        switch ($options->feed_type) {
            case 'PUBLIC':
                $id_key = strstr($options->user_id,",")? "ids":"id";
                $url = 'http://api.flickr.com/services/feeds/photos_public.gne?format=json&jsoncallback=?';
                $url = $url."&".$id_key."=".$options->user_id."&language=";
                $url = $url."&tags=".$options->tags."&tagmode=".$options->tag_mode;
                break;
        }
        return $url;
    }
} 
?>