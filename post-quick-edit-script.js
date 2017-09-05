/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

jQuery(document).ready(function($){

    //Prepopulating our quick-edit post info
    var $inline_editor = inlineEditPost.edit;
    inlineEditPost.edit = function(id){

        //call old copy
        $inline_editor.apply( this, arguments);

        //our custom functionality below
        var post_id = 0;
        if( typeof(id) == 'object'){
            post_id = parseInt(this.getId(id));
        }

        //if we have our post
        if(post_id != 0){

            //find our row
            $row = $('#edit-' + post_id);

            //post featured
            $product_featured = $('#product_featured_' + post_id);
            product_featured_value = $product_featured.text();
            if(product_featured_value === 'featured'){
                $row.find('#product_featured_quick').attr('checked', true);
            }else{
                $row.find('#product_featured_quick').attr('checked', false);
            }

            // product ranking order
            $product_ranking_order = $('#product_ranking_order_' + post_id);
            $product_ranking_order_value = $product_ranking_order.text();
            $row.find('#product_ranking_order_quick').val($product_ranking_order_value);
            $row.find('#old_product_ranking_order_quick').val($product_ranking_order_value);

            // product featured order
            $product_featured_order= $('#product_featured_order_' + post_id);
            $product_featured_order_value = $product_featured_order.text();
            $row.find('#product_featured_order_quick').val($product_featured_order_value);
            $row.find('#old_product_featured_order_quick').val($product_featured_order_value);

            // product descendant order
            $product_descendant_order= $('#product_descendant_order_' + post_id);
            $product_descendant_order_value = $product_descendant_order.text();
            $row.find('#product_descendant_order_quick').val($product_descendant_order_value);
            $row.find('#old_product_descendant_order_quick').val($product_descendant_order_value);

            // product brand order
            $product_brand_order= $('#product_brand_order_' + post_id);
            $product_brand_order_value = $product_brand_order.text();
            $row.find('#product_brand_order_quick').val($product_brand_order_value);
            $row.find('#old_product_brand_order_quick').val($product_brand_order_value);

            // product price
            $product_price= $('#product_price_' + post_id);
            $product_price_value = $product_price.text();
            $row.find('#product_price_quick').val($product_price_value);
            
        }

    }

});
