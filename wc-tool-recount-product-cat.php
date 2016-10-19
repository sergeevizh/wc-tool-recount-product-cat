<?php
/**
 * Plugin Name: WC Product Category Recount Tool
 * Description: Recount posts for product category (wc-tool-recount-product-cat). See 'WooCommerce / Tools' and section 'Recount category posts'
 */


class wc_tool_recount_product_cat {

    function __construct() {

        add_filter( 'woocommerce_debug_tools', array( $this,'woocommerce_debug_tools_callback' ) );

        $plugin_url = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin_url", array($this,'add_fast_link_to_list_plugins'));

    }



    function wc_tool_recount_product_cat_btn_callback(){ ?>

        <div class="updated wc_tool_recount_product_cat_wrapper">
            <?php echo $this::recount_product_categories(); ?>
        </div>

    <?php
    }



    function recount_product_categories(){

        ob_start();

        $args = array(
            'orderby'    => 'name',
            'order'      => 'ASC',
            'hide_empty' => false
        );
        $product_categories = get_terms( 'product_cat', $args );

        $i = 0;

        //setup save iteration
        if(empty(get_transient('recount_product_categories_i'))){
            $i_save = 0;
        } else {
            $i_save = get_transient('recount_product_categories_i');
        }

        foreach ($product_categories as $item) {
            $i++;

            //if the iteration has been the continue
            if($i <= $i_save)
                continue;

            $count_v1 = $item->count;

            wp_update_term_count( $item->term_id, $item->taxonomy);

            $count_v2 = $item->count;

            if($count_v1 != $count_v2) {
                printf('<p>Term %s, id %s, before count %s, after count %s</p>',
                    $item->name,
                    $item->term_id,
                    $count_v1,
                    $count_v2
                );
            }

            //if this iteration of 10 greater than i_save - break
            if($i = $i_save + 10) {
                break;
            }
        }

        set_transient('recount_product_categories_i', $i);

        printf('<p>Recount is completed. The number of processed elements: %s</p>', $i);

        return ob_get_clean();
    }










    /**
     * debug_button function.
     *
     * @access public
     * @param mixed $old
     * @return void
     */
    function woocommerce_debug_tools_callback( $old ) {
        $new = array(
            'wc_tool_recount_product_cat_btn' => array(
                'name'		=> __( 'Recount category posts', '' ),
                'button'	=> __( 'Run!', '' ),
                'desc'		=> __( 'The helper category recount posts and save count value as WordPress.', '' ),
                'callback'	=> array( $this, 'wc_tool_recount_product_cat_btn_callback' ),
            ),
        );
        $tools = array_merge( $old, $new );

        return $tools;
    }

    /**
     * hook plugin_action_links
     * @param $links
     * @return $links + $link for tool
     */
    public function add_fast_link_to_list_plugins($links){

        $link = array(
            'settings' => sprintf('<a href="%s">Link for tool</a>', admin_url( 'admin.php?page=wc-status&tab=tools'))
        );

        return array_merge( $links, $link );

    }
}
$GLOBALS['wc_tool_recount_product_cat'] = new wc_tool_recount_product_cat();
