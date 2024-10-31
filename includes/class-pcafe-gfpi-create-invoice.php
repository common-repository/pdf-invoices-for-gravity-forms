<?php 
defined( 'ABSPATH' ) || exit;

class PCAFE_GFPI_Create_Invoice {

    public static function pdf_body( $form, $entry, $recipeint, $settings ) {
        $html = '';
        $new_product = PCAFE_GFPI_Helpers::get_products( $form, $entry );

        $invoice_date = new DateTime($entry['date_created']);

        ob_start();
        include 'pdf-templates/header.php';
        ?>
        
        <div class="pdf_top">
            <div class="left_box">
                <?php if( $settings['gfpi_template_logo'] ) : ?>
                    <img class="logo" src="<?php echo esc_url( $settings['gfpi_template_logo'] ); ?>" />
                <?php else : ?>
                    <h1><?php echo esc_html($settings['gfpi_company_name']); ?></h1>
                <?php endif; ?>
            </div>
            <div class="right_box">
                <h2 style="font-size: 30px; margin-bottom:10px;"><?php echo esc_html__('INVOICE', 'pdf-invoices-for-gravity-forms');?></h2>
                <p style="padding-bottom:3px"><?php echo esc_html__('Invoice Date', 'pdf-invoices-for-gravity-forms'); ?>: <?php echo esc_attr($invoice_date->format('F j, Y'));?></p>
                <p><?php echo esc_html__('Invoice Number', 'pdf-invoices-for-gravity-forms')?> : <strong><?php echo esc_html($settings['gfpi_template_number']) . sprintf("%05d", esc_attr($entry['id'])); ?></strong></p>
            </div>
        </div>

        <div class="wrap">
            <div class="div1">
                <p style="padding-bottom: 5px"><strong><?php echo esc_html__('Billing From', 'pdf-invoices-for-gravity-forms');?></strong></p>
                <p><?php echo esc_html( $settings['gfpi_company_name'] ); ?></p>
                <p><?php echo esc_html( $settings['gfpi_company_street'] ); ?></p>
                <p><?php echo esc_html( $settings['gfpi_company_street_2'] ); ?></p>
                <p><?php echo esc_html( $settings['gfpi_company_city'] . ', '); ?><?php echo esc_html($settings['gfpi_company_state']); ?> <?php echo esc_html($settings['gfpi_company_zip_code']); ?></p>
                <p><?php echo esc_html( $settings['gfpi_company_country']); ?></p>
            </div>
            <div class="div2">
                <p style="padding-bottom: 5px"><strong><?php echo esc_html__('Billing To', 'pdf-invoices-for-gravity-forms');?></strong></p>
                <p><?php echo esc_html(rgar( $entry, $recipeint['recipient_first_name'] )); ?></p> 
                <p><?php echo esc_html(rgar( $entry, $recipeint['recipient_street_address'] )); ?></p>
                <p><?php echo esc_html(rgar( $entry, $recipeint['recipient_city'] )) . ', '; ?><?php echo esc_html(rgar( $entry, $recipeint['recipient_state'] )); ?> <?php echo esc_html( rgar( $entry, $recipeint['recipient_zip'] )); ?></p>
                <p><?php echo esc_html(rgar( $entry, $recipeint['recipient_country'] )); ?></p>
            </div>
        </div>

        <table width="100%" class="product">
            <tr>
                <th align="left">Description</th>
                <th align="center" width="15%">Qty</th>
                <th align="center" width="15%">Unit Price</th>
                <th align="center" width="15%">Total</th>
            </tr>
            <?php foreach( $new_product as $product ) : ?>
                <tr>
                    <td>
                        <?php echo esc_html($product['name']); ?>
                        <?php if( !empty( $product['options'] ) ) : echo '<ul>'; foreach ($product['options']  as $option) : ?>
                           <li><?php echo esc_attr( $option ); ?></li>
                        <?php endforeach; echo '</ul>'; endif;?>
                    </td>
                    <td align="center"><?php echo esc_html($product['quantity']); ?></td>
                    <td align="center"><?php echo esc_html($product['unit_price']); ?></td>
                    <td align="center"><?php echo esc_html($product['total']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><?php echo esc_html__('Subtotal', 'pdf-invoices-for-gravity-forms'); ?></td>
                <td align="center"><?php echo esc_attr(GFCommon::to_money(PCAFE_GFPI_Helpers::get_subtotal())); ?></td>
            </tr>
            <?php if( PCAFE_GFPI_Helpers::get_shipping() ) : ?>
                <tr>
                    <td colspan="3"><?php echo esc_html__('Shipping', 'pdf-invoices-for-gravity-forms'); ?></td>
                    <td align="center"><?php echo esc_attr(GFCommon::to_money( PCAFE_GFPI_Helpers::get_shipping())); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td colspan="3"><?php echo esc_html__('Total', 'pdf-invoices-for-gravity-forms'); ?></td>
                <td align="center"><?php echo esc_attr(GFCommon::to_money( PCAFE_GFPI_Helpers::get_total())); ?></td>
            </tr>
        </table>

        <div style="margin-top: 70px; text-align: center;">
            <?php echo esc_html($settings['gfpi_template_footer']); ?>
        </div>

    <?php 
        include 'pdf-templates/footer.php';
        $html .= ob_get_clean();
        return $html;
    }

    public static function render_pdf($form, $entry, $recipeint, $settings) {

        $pdf_format = rgar($settings, 'gfpi_template_format') ? rgar($settings, 'gfpi_template_format') : 'Letter';
        $file_name  = rgar($settings, 'gfpi_template_name') ? rgar($settings, 'gfpi_template_name') : 'Invoice';

        $filename = PCAFE_GFPI_Helpers::get_invoice_upload_root();

        $html = self::pdf_body($form, $entry, $recipeint, $settings);

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $filename . 'temps/',
            'mode' => 'utf-8',
            'format' => $pdf_format
        ]);

        $mpdf->SetTitle($file_name);
        $mpdf->WriteHTML( $html );

        $filename .= $file_name . $entry['id'].'.pdf';

        if( file_exists($filename) ) {
            wp_delete_file($filename);
        }

        $mpdf->Output($filename, 'F');

        return $filename;
    }
}

new PCAFE_GFPI_Create_Invoice;