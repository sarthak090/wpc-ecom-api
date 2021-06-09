<?php

/**
 * Plugin Name: Wpc Ecom Api
 * Plugin URI: https://github.com/sarthak090/wpc-ecom-api
 * Description: Advance Rest Api to reduce your server load
 * Version: 1.0.0
 * Author: Sarthak Kaushik
 * Author URI: https://sarthakkaushik.dev
 * Text Domain: wpcecomapi
 
 */


function wl_get_all_products()
{
    $products = wc_get_products(array(
        "status" => "published"
    ));
    return get_all_data_in_format_wc_products($products);
}

function wl_get_all_categories()
{
    $data = [];
    $i = 0;
    $orderby = 'name';

    $order = 'asc';
    $hide_empty = false;
    $args = array(
        'orderby'    => $orderby,
        'order'      => $order,
        'hide_empty' => $hide_empty,

    );

    $product_categories = get_terms('product_cat', $args);
    foreach ($product_categories as $catg) {
        $thumbnail_id = get_term_meta($catg->term_id, 'thumbnail_id', true);
        $data[$i]['id'] = $catg->term_id;
        $data[$i]['name'] = $catg->name;
        $data[$i]['slug'] = $catg->slug;
        $data[$i]['totalProducts'] = $catg->count;
        $data[$i]['featuredImage'] = wp_get_attachment_url($thumbnail_id);
        $i++;
    }
    return $data;
}
function wl_get_categories_products($req)
{
    $slug = $req['slug'];
    $products = wc_get_products(array(
        'category' => array($slug),
    ));
    if (count($products) == 0) {
        return wpc_error_404();
    }
    return get_all_data_in_format_wc_products($products);
}



function wl_get_product($req)
{
    $slug = $req['slug'];
    $productId = get_page_by_path($slug, OBJECT, 'product');
    if (!empty($productId)) {
        $product = wc_get_product($productId);
        return single_product_data($product);
    }
    return wpc_error_404();
}
function wl_get_all_customers()
{
    $args = array(
        'role'    => 'customer',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );

    $customer = new WC_Customer(15);

    $username     = $customer->get_username(); // Get username
    $user_email   = $customer->get_email(); // Get account email
    $first_name   = $customer->get_first_name();
    $last_name    = $customer->get_last_name();
    $display_name = $customer->get_display_name();

    // Customer billing information details (from account)
    $billing_first_name = $customer->get_billing_first_name();
    $billing_last_name  = $customer->get_billing_last_name();
    $billing_company    = $customer->get_billing_company();
    $billing_address_1  = $customer->get_billing_address_1();
    $billing_address_2  = $customer->get_billing_address_2();
    $billing_city       = $customer->get_billing_city();
    $billing_state      = $customer->get_billing_state();
    $billing_postcode   = $customer->get_billing_postcode();
    $billing_country    = $customer->get_billing_country();

    // Customer shipping information details (from account)
    $shipping_first_name = $customer->get_shipping_first_name();
    $shipping_last_name  = $customer->get_shipping_last_name();
    $shipping_company    = $customer->get_shipping_company();
    $shipping_address_1  = $customer->get_shipping_address_1();
    $shipping_address_2  = $customer->get_shipping_address_2();
    $shipping_city       = $customer->get_shipping_city();
    $shipping_state      = $customer->get_shipping_state();
    $shipping_postcode   = $customer->get_shipping_postcode();
    $shipping_country    = $customer->get_shipping_country();


    return $shipping_country;
}

function wl_get_all_orders_by_id($req)
{

    $user_id = 15;
    $customer_orders = get_posts(array(
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
        'post_status' => array_keys(wc_get_order_statuses()),
        'numberposts' => -1
    ));
    $Order_Array = []; //
    foreach ($customer_orders as $customer_order) {
        $orderq = wc_get_order($customer_order);
        $Order_Array[] = [
            "ID" => $orderq->get_id(),
            "Value" => $orderq->get_total(),
            "Test" => $orderq->get_view_order_url(),
            "Date" => $orderq->get_date_created()->date_i18n('Y-m-d'),
        ];
    }
    return $Order_Array;
}
function wpc_error_404()
{
    $error = [];
    $error['msg'] = "Error Not Found";
    $error['status'] = 404;
    return $error;
}
function get_all_data_in_format_wc_products($products)
{
    $data = [];
    $i = 0;
    foreach ($products as $product) {

        $data[$i]['id'] = $product->get_id();
        $data[$i]['name'] = $product->get_title();
        $data[$i]['slug'] = $product->get_slug();
        $data[$i]['price'] =  intval($product->get_price());
        $data[$i]['sale_price'] = intval($product->get_sale_price());
        $data[$i]['featuredImage'] = wp_get_attachment_image_url($product->get_image_id(), 'full');
        $data[$i]['ratings'] = intval($product->get_average_rating());
        $data[$i]['seller'] = get_userdata(get_post_field("post_author", $product->get_id()))->user_nicename;

        $i++;
    }

    return $data;
}

function single_product_data($product)
{
    $data = [];

    $i = 0;
    $g = 0;
    $img = 0;
    $data['id'] = $product->get_id();
    $data['name'] = $product->get_title();
    $data['slug'] = $product->get_slug();
    $data['type'] = $product->get_type();
    $data['price'] = $product->get_price();
    $data['salePrice'] = $product->get_sale_price();
    $data['featuredImage'] = wp_get_attachment_image_url($product->get_image_id(), 'full');
    $data['ratings'] = $product->get_average_rating();
    $data['shortDescription'] = $product->get_short_description();
    $data['description'] = $product->get_description();
    $data['categories'] = get_the_terms($product->get_id(), 'product_cat');
    $data['seller'] = get_userdata(get_post_field("post_author", $product->get_id()))->user_nicename;
    $data['isDownloadable'] = $product->is_downloadable();
    $data['crossSellCount'] = count($product->get_cross_sell_ids());
    $attachment_ids = $product->get_gallery_image_ids();
    $data['gallImgCOunt'] = count($attachment_ids);


    foreach ($attachment_ids as $attachment_id) {

        $data["galleryImgs"][$img] = wp_get_attachment_url($attachment_id);

        $img++;
    }
    if (!empty($product->get_cross_sell_ids())) {
        foreach ($product->get_cross_sell_ids() as $crsId) {
            $data['crossSellProducts'][$g] = wpc_get_related_products($crsId);
            $g++;
        }
    }
    if (!empty($product->get_upsells())) {

        foreach ($product->get_upsells() as $rpd) {
            $data['upsellProducts'][$i] = wpc_get_related_products($rpd);
            $i++;
        }
    }

    $attributes = $product->get_attributes();

    if ($attributes) {

        foreach ($attributes as $attribute) {

            $data['attributes'][$attribute['name']] =  $product->get_attribute($attribute['name']);
        }
    }
    if ($product->is_type('variable')) {
        $data['variations'] = $product->get_available_variations();
    }
    if ($product->has_dimensions()) {
        $data['dimension']['width'] = $product->get_width();
        $data['dimension']['length'] = $product->get_length();
        $data['dimension']['height'] = $product->get_height();
    }

    return $data;
}

function wpc_get_related_products($productId)
{
    $data = [];

    $i = 0;
    $product = wc_get_product($productId);
    $data['id'] = $product->get_id();
    $data['name'] = $product->get_title();
    $data['slug'] = $product->get_slug();
    $data['price'] = $product->get_price();
    $data['featuredImage'] = wp_get_attachment_image_url($product->get_image_id(), 'full');


    return $data;
}

function wpc_get_products_for_cart_by_id($req)
{

    $productsParam = json_decode($req->get_body());


    if (count($productsParam->items) === 0) {

        return  wpc_error_404();
    }
    $data = [];

    $i = 0;
    foreach ($productsParam->items as $item) {
        $product = wc_get_product($item->id);


        $data["items"][$i]['id'] = $product->get_id();
        $data["items"][$i]['name'] = $product->get_name();
        $data["items"][$i]['price'] = intval($product->get_price());
        $data["items"][$i]['featuredImage'] = wp_get_attachment_image_url($product->get_image_id(), 'full');

        $data["items"][$i]['quantity'] = intval($item->quantity);
        $data["items"][$i]['total'] = intval($product->get_price() * $item->quantity);

        $i++;
    }
    if ($productsParam->couponCode) {
        $c = new WC_Coupon($productsParam->couponCode);
        if ($c->is_valid()) {
            $data["discountData"]["amount"] = $c->get_amount();
            $data["discountData"]["code"] = $c->get_code();
            $data["discountData"]["isValid"] = $c->is_valid();
            $data["discountData"]["type"] = $c->get_discount_type();
        }
        $data["discountData"]["isValid"] = $c->is_valid();

        $data["discountData"]["msg"] = "Coupon is valid";
    }
    if (count($data) == 0) {
        return wpc_error_404();
    }
    return $data;
}

function wpc_check_coupon_valid($req)
{

    if ($req['couponcode']) {
        $coupon = new WC_Coupon($req['couponcode']);
        return $coupon->is_valid();
    }
    return wpc_error_404();
}

function wpc_get_all_available_payment_gateways()
{
    $payemntsGateway = new WC_Payment_Gateways();
    $data = [];
    $i = 0;
    foreach ($payemntsGateway->get_available_payment_gateways() as $gateway) {
        if ($gateway->enabled == "yes") {
            $data[$i]['id'] = $gateway->id;
            $data[$i]['title'] = $gateway->title;
            $data[$i]['description'] = $gateway->description;

            $i++;
        }
    }

    return $data;
}
function wpc_get_all_orders_id_of_customer($req)
{
    $customerId = $req['cid'];

    if ($customerId) {
        $customer_orders = get_posts(array(
            'meta_key'    => '_customer_user',
            'meta_value'  => $customerId,
            'post_type'   => 'shop_order',
            'post_status' => array_keys(wc_get_order_statuses()),
            'numberposts' => -1
        ));
        $ordersId = [];
        $i = 0;
        foreach ($customer_orders as $order) {
            $ordersId[$i] = $order->ID;
            $i++;
        }
        return $ordersId;
    }
    return wpc_error_404();
}
add_action('rest_api_init', function () {
    register_rest_route('wpc/v1', 'products', array(
        'methods' => 'GET',
        'callback' => 'wl_get_all_products',
    ));
    register_rest_route('wpc/v1', 'categories', array(
        'methods' => 'GET',
        'callback' => 'wl_get_all_categories',
    ));
    register_rest_route('wpc/v1', 'categories/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'wl_get_categories_products',
    ));
    register_rest_route('wpc/v1', 'products/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'wl_get_product',
    ));

    register_rest_route('wpc/v1', 'customers', array(
        'methods' => 'GET',
        'callback' => 'wl_get_all_customers',
    ));
    register_rest_route('wpc/v1', 'check-coupon', array(
        'methods' => 'GET',
        'callback' => 'wpc_check_coupon_valid',
    ));
    register_rest_route('wpc/v1', 'customers/orders/(?P<id>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'wl_get_all_orders_by_id',
    ));

    register_rest_route('wpc/v1', 'cart/products', array(
        'methods' => 'POST',
        'callback' => 'wpc_get_products_for_cart_by_id',
    ));
    register_rest_route('wpc/v1', 'available-payment-gateways', array(
        'methods' => 'GET',
        'callback' => 'wpc_get_all_available_payment_gateways',
    ));
    register_rest_route('wpc/v1', 'orders-id', array(
        'methods' => 'GET',
        'callback' => 'wpc_get_all_orders_id_of_customer',
    ));
});
