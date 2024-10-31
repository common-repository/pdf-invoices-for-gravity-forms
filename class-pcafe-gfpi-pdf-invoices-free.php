<?php 

defined( 'ABSPATH' ) || exit;

GFForms::include_feed_addon_framework();

class PCAFE_GFPI_Pdf_Invoices_Free extends GFFeedAddOn {
	protected $_version                  	= PCAFE_GFPI_VERSION_FREE;
	protected $_min_gravityforms_version	= '1.9.16';
	protected $_slug 						= 'pdf_invoices_free';
    protected $_path 						= 'pdf-invoices-for-gravity-forms/pdf-invoices.php';
	protected $_full_path                	= __FILE__;
	protected $_title                   	= 'PDF Invoices For Gravity Forms';
	protected $_short_title             	= 'PDF Invoices';
    protected $_multiple_feeds           	= false;
	private static $_instance = null;
    protected $_capabilities_form_settings = 'gravityforms_edit_forms';
    protected $_capabilities_settings_page = 'gravityforms_edit_settings';

	/**
	 * Get an instance of this class.
	 *
	 * @return PCAFE_GFPI_Pdf_Invoices_Free
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new PCAFE_GFPI_Pdf_Invoices_Free();
		}

		return self::$_instance;
	}

    
    public function init() {
		parent::init();
		require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Add data to PDF invoice only when payment is received.', 'pdf-invoices-for-gravity-forms' ),
			)
		);

		add_filter( 'gform_notification', array( &$this, 'attach_pdf' ), 10, 3 );        
	}
	
	public function init_admin() {
		parent::init_admin();
        
		add_filter( 'gform_entry_detail_meta_boxes', array($this, 'download_pdf_invoices'), 10, 3 );
    }

    public function get_menu_icon() {
		return file_get_contents( $this->get_base_path().'/assets/images/pdf-invoice.svg' ); //phpcs:ignore
	}

	/**
	 * Configures the settings which should be rendered on the add-on settings tab.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Your Company Address', 'pdf-invoices-for-gravity-forms' ),
				'fields' => array(
					array(
						'name'      => 'gfpi_company_name',
						'label'     => esc_html__( 'Company name', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company name', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
                        'required'  =>  true
					),
					array(
						'name'      => 'gfpi_company_street',
						'label'     => esc_html__( 'Company Street Address', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company street address', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
                        'required'  =>  true
					),
					array(
						'name'      => 'gfpi_company_street_2',
						'label'     => esc_html__( 'Company Address Line 2', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company address line 2', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small'
					),
					array(
						'name'      => 'gfpi_company_city',
						'label'     => esc_html__( 'Company City', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company city', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
						'required'  =>  true
					),
					array(
						'name'      => 'gfpi_company_state',
						'label'     => esc_html__( 'Company State', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company state', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
						'required'  =>  true
					),
					array(
						'name'      => 'gfpi_company_zip_code',
						'label'     => esc_html__( 'Company Zip / Postal Code', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company zip / postal code', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
						'required'  =>  true
					),
					array(
						'name'      => 'gfpi_company_country',
						'label'     => esc_html__( 'Company Country', 'pdf-invoices-for-gravity-forms' ),
						'tooltip'   => esc_html__( 'Please enter your company country', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
						'required'  =>  true
					),		
				),
			),
            array(
				'title'  => esc_html__( 'Invoice Settings', 'pdf-invoices-for-gravity-forms' ),
                'fields' => array(
                    array(
                        'name'      => 'gfpi_template_name',
                        'label'     => esc_html__( 'Invoice File Name', 'pdf-invoices-for-gravity-forms' ),
                        'tooltip'   => esc_html__( 'Please enter your invoice name', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
                        'required'  =>  true
                    ),
                    array(
                        'name'      => 'gfpi_template_number',
                        'label'     => esc_html__( 'Invoice Number Prefix', 'pdf-invoices-for-gravity-forms' ),
                        'tooltip'   => esc_html__( 'Please enter your invoice prefix.<br/> Example: INV. In invoice, it will<br/> be shown as INV-{entry_id}. Example: INV-123.', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'     => 'small',
                        'required'  =>  true
                    ),
                    array(
                        'name'    => "gfpi_template_format",
                        'tooltip' => __( "Please select the ouput format for your invoice", 'pdf-invoices-for-gravity-forms' ),
                        'label'   => __( "Invoice Format", 'pdf-invoices-for-gravity-forms' ),
                        'type'    => "select",
                        'choices'	=> array(
                            array(
                                'value' => '',
                                'label' => 'Choose Format'
                            ),
                            array(
                                'value' => 'A4',
                                'label' => 'A4'
                            ),
                            array(
                                'value' => 'Letter',
                                'label' => 'Letter'
                            ),
                            array(
                                'value' => 'Legal',
                                'label' => 'Legal'
                            )
                        )
                    ),
                    array(
                        'name'      => 'gfpi_template_logo',
                        'label'     => esc_html__( 'Invoice Logo', 'pdf-invoices-for-gravity-forms' ),
                        'tooltip'   => esc_html__( 'Please enter your logo url', 'pdf-invoices-for-gravity-forms' ),
						'type'      => 'text',
						'class'		=> 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    ),
                    array(
                        'name'    => "gfpi_template_footer",
                        'tooltip' => esc_html__( "Please enter a text for the invoice footer, if needed", 'pdf-invoices-for-gravity-forms' ),
                        'label'   => esc_html__( "Invoice Footer", 'pdf-invoices-for-gravity-forms' ),
                        'type'    => "textarea",
						'class'         => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    )
                )
            )
		);
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page in the Form Settings > Simple Feed Add-On area.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Recipient Address', 'pdf-invoices-for-gravity-forms' ),
				'fields' => array(
					array(
						'name'      => 'recipient',
						'type'      => 'field_map',
						'field_map' => array(
							array(
								'name'       => 'first_name',
								'label'      => esc_html__( 'First Name', 'pdf-invoices-for-gravity-forms' ),
								'required'   => true,
								'tooltip'    => esc_html__( 'Please choose the recipient first name', 'pdf-invoices-for-gravity-forms' ),
							),
							array(
								'name'       => 'middle_name',
								'label'      => esc_html__( 'Middle Name', 'pdf-invoices-for-gravity-forms' ),
								'required'   => false,
								'tooltip'    => esc_html__( 'Please choose the recipient Middle name', 'pdf-invoices-for-gravity-forms' ),
							),
							array(
								'name'       => 'last_name',
								'label'      => esc_html__( 'Last Name', 'pdf-invoices-for-gravity-forms' ),
								'required'   => false,
								'tooltip'    => esc_html__( 'Please choose the recipient last name', 'pdf-invoices-for-gravity-forms' ),
							),
							array(
								'name'     => 'street_address',
								'label'    => esc_html__( 'Street Address', 'pdf-invoices-for-gravity-forms' ),
                                'required'   => true,
							),
							array(
								'name'       => 'street_address_2',
								'label'      => esc_html__( 'Address Line 2 ', 'pdf-invoices-for-gravity-forms' ),
								'required'   => false
							),
                            array(
								'name'     	=> 'city',
								'label'    	=> esc_html__( 'City', 'pdf-invoices-for-gravity-forms' ),
                                'required'	=> true,
							),
                            array(
								'name'     	=> 'state',
								'label'    	=> esc_html__( 'State', 'pdf-invoices-for-gravity-forms' ),
                                'required'	=> true,
							),
                            array(
								'name'     	=> 'zip',
								'label'    	=> esc_html__( 'ZIP / Postal Code', 'pdf-invoices-for-gravity-forms' ),
                                'required'	=> true,
							),
                            array(
								'name'     	=> 'country',
								'label'    	=> esc_html__( 'Country', 'pdf-invoices-for-gravity-forms' ),
                                'required'	=> true,
							),
							
						),
					),
					
				),
			),
            array(
                'title'     => esc_html__( 'Choose Notification', 'pdf-invoices-for-gravity-forms' ),
				'tooltip'   => esc_html__( 'Please select a notification option. Choose "None" if you do not want to generate or add an invoice with notification.', 'pdf-invoices-for-gravity-forms' ),
                'fields' => array(
                    array(
                        'name'      => 'gfpi_add_notifiactions',
                        'type'      => 'radio',
                        'choices' => $this->get_notification_list(),
                        'required'	=> true
                    )
                )
            )
		);
	}

    public function get_notification_list() {
        $form = $this->get_current_form();

        $notification = array(
			array(
				'label'     => esc_html__( 'None', 'pdf-invoices-for-gravity-forms' ),
				'value'     => 'none'
			),
		);

		if( $form['notifications'] && $form['notifications'] != '' ) {
			foreach ($form['notifications'] as $key => $value) {
				$notification[] = array(
					'label'     => $value['name'],
					'value'     => $key
				);
			}
		}

        return $notification;
    }

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'  => esc_html__( 'Name', 'pdf-invoices-for-gravity-forms' ),
			'mytextbox' => esc_html__( 'PDF Invoices', 'pdf-invoices-for-gravity-forms' ),
		);
	}

	/**
	 * Format the value to be displayed in the mytextbox column.
	 *
	 * @param array $feed The feed being included in the feed list.
	 *
	 * @return string
	 */
	public function get_column_value_mytextbox( $feed ) {
		return '<b>' . rgars( $feed, 'meta/mytextbox' ) . '</b>';
	}

    public function can_create_feed() {
		return $this->is_pdf_invoice_set() && $this->feed_allowed_for_current_form();
	}

    private function feed_allowed_for_current_form(){
        $form	= $this->get_current_form();
        $fields = GFAPI::get_fields_by_type( $form, array( 'product' ) );

		return empty( $fields ) ? false : $fields[0];
    }

    public function is_pdf_invoice_set() {
        $settings           = $this->get_plugin_settings();
        $company_name       = rgar($settings, 'gfpi_company_name');
        $company_street     = rgar($settings, 'gfpi_company_street');
        $invoice_name       = rgar($settings, 'gfpi_template_name');
        $invoice_number     = rgar($settings, 'gfpi_template_number');

        if( ! empty( $company_name ) && ! empty( $company_street ) && ! empty( $invoice_name ) && ! empty( $invoice_number ) ) {
            return true;
        }

        return false;
    }
    

    public function feed_list_message() {
		if ( ! $this->can_create_feed() ) {
			return $this->configure_addon_message();
		}

		return false;
	}

	public function configure_addon_message() {
		/* translators: %s: PDF Invoices */
        $settings_label = sprintf( __('%s Settings', 'pdf-invoices-for-gravity-forms'), $this->get_short_title() ); 
		$settings_link  = sprintf( '<a href="%s">%s</a>', esc_url( $this->get_plugin_settings_url() ), $settings_label );
        
        if( ! $this->feed_allowed_for_current_form() ) {
            return esc_html__( 'To get started, please add product fields on this form.', 'pdf-invoices-for-gravity-forms' );
        }
		/* translators: %s: PDF Invoices */
		return sprintf( __( 'To get started, please configure your %s.', 'pdf-invoices-for-gravity-forms' ), $settings_link ); 

	}

	public function download_pdf_invoices( $meta_boxes, $entry, $form ) {

		$meta_boxes['pdf_invoices_free'] = array(
            'title'         => esc_html__( 'Download Invoice', 'pdf-invoices-for-gravity-forms' ),
            'callback'      => array($this, 'add_invoice_download_link'),
            'context'       => 'side'
        );

		return $meta_boxes;
	}

	public function add_invoice_download_link( $args ) {

		$settings = $this->get_plugin_settings();

		$file_name  = rgar($settings, 'gfpi_template_name') ? rgar($settings, 'gfpi_template_name') : 'Invoice';

		$file_url 	= PCAFE_GFPI_Helpers::get_invoice_upload_root_url() . $file_name .$args['entry']['id'].'.pdf';
		$file_path 	= PCAFE_GFPI_Helpers::get_invoice_upload_root() . $file_name .$args['entry']['id'].'.pdf';

		if( ! file_exists($file_path) ) {
			echo esc_html__('PDF invoice not found', 'pdf-invoices-for-gravity-forms');
			return;
		}

		echo '<a target="_blank" href="'. esc_url($file_url) .'">'. esc_html__("View", 'pdf-invoices-for-gravity-forms') .'</a> | <a download href="'. esc_url($file_url) .'">'. esc_html__("Download", 'pdf-invoices-for-gravity-forms') .'</a>';
	}

	public function attach_pdf( $notification, $form, $entry ) {

        $settings 	= $this->get_plugin_settings();
        $form_id  	= $form['id'];
        $feed_data	= PCAFE_GFPI_Helpers::get_recipient_details_from_feed($form_id);

        if( ! $feed_data ) {
            return $notification;
        }

        $result = (array) json_decode($feed_data[0]['meta']);
        $notification_id = $result['gfpi_add_notifiactions'];

        if( $notification_id == 'none' || $notification_id != $notification['id'] ) {
            return $notification;
        }

        $notification['attachments'] = PCAFE_GFPI_Create_Invoice::render_pdf($form, $entry, $result, $settings);

        return $notification;
    }
}
