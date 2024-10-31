<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PCAFE_GFPI_Helpers {
    private static $products = array();
    private static $shipping = 0;
    private static $total = 0;

    public static function get_invoice_upload_root() {
        $uploads        = wp_upload_dir();
        $invoice_dir    = $uploads['basedir'] . '/pdf_invoices/';

        if ( ! file_exists( $invoice_dir ) ) {
            wp_mkdir_p( $invoice_dir );
        }

        return $invoice_dir;
    }

    public static function get_invoice_upload_root_url() {
        $uploads        = wp_upload_dir();
        $invoice_dir    = $uploads['baseurl'] . '/pdf_invoices/';

        return $invoice_dir;
    }

    public static function get_products( $form, $entry) {
        $products = [];

        $lead = GFCommon::get_product_fields( $form, $entry, true, true );

        if( $lead['shipping']['price'] ) {
            self::$shipping +=  GFCommon::to_number( $lead['shipping']['price'] );
        }

        foreach ($lead['products'] as $product) {
            $new_arr = [];
            $price = GFCommon::to_number( $product[ 'price' ] );

            $new_arr['name'] = $product['name'];
            $new_arr['quantity'] = $product['quantity'];

            if( is_array( rgar( $product, 'options' ) ) ) {
                $sub_option = [];
                foreach ($product['options'] as $option ) {
                    $price +=  GFCommon::to_number( $option['price'] );
                    $sub_option[] = $option['option_label'];
                }
                $new_arr['options'] = $sub_option;
            }

            $new_arr['unit_price'] =  GFCommon::to_money($price);

            $new_arr['total'] =  GFCommon::to_money( $price * $product['quantity'] );

            $products[] = $new_arr;
        }

        self::$products = $products;

        return $products;
    }

    public static function get_subtotal(){
        $sub_total = 0;

        foreach (self::$products as $product) {
            $sub_total +=  GFCommon::to_number( $product[ 'unit_price' ] ) * $product[ 'quantity' ];
        }
        return $sub_total;
    }

    public static function get_shipping() {
        return self::$shipping;
    }

    public static function get_total(){
        self::$total += self::get_subtotal();

        self::$total += self::$shipping;
        
        return self::$total;
    }

    public static function get_recipient_details_from_feed($form_id){
		global $wpdb;
        $slug = 'pdf_invoices_free';
		$table_name = $wpdb->prefix . "gf_addon_feed";
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE form_id = %d AND addon_slug = %s", $form_id, $slug), ARRAY_A );
		
        if( $result > 0 ) {
            return $result;
        }

        return false;
	}
}