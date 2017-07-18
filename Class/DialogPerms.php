<?php namespace linkclick;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class DialogPerms
{
    public $printForm = true;
    public $postId = null;

    public function printOut()
    {
        $db = new \linkclick\DB();
        $locks = $db->getLocks();
        print_r($locks);
    }

    public function saveFromPost()
    {
        $this->saveFromArray($_POST);
    }
    public function saveFromArray(array $data)
    {
        if (isset($data[PostMetaFields::POST_ID]) && isset($data['linkclick-action'])) {
            $post_id = $data[PostMetaFields::POST_ID];
            $lock_id = $data[PostMetaFields::LOCK_ID];
            $cate_id = $data[PostMetaFields::CATEGORY_ID];
            $date = $data[PostMetaFields::DATE];

            update_post_meta($post_id, PostMetaFields::LOCK_ID, $lock_id);
            update_post_meta($post_id, PostMetaFields::CATEGORY_ID, $cate_id);
            update_post_meta($post_id, PostMetaFields::DATE, $date);
        } else {
            return false;
        }
    }
}
