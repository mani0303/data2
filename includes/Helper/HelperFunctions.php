<?php
/**
 * Helper functions
 *
 * @package MintMailPro
 */

namespace MintMailPro;

use Mint\MRM\DataBase\Models\ContactModel;

/**
 * Helper functions
 *
 * @package MintMailPro;
 */
class Mint_Pro_Helper { //phpcs:ignore
	/**
	 * Check if edd is installed.
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	public static function is_edd_active() {
		if ( in_array( 'easy-digital-downloads/easy-digital-downloads.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { //phpcs:ignore
			return true;
		} elseif ( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Check if gravity form is installed.
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	public static function is_gform_active() {
		if ( in_array( 'gravityforms/gravityforms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { //phpcs:ignore
			return true;
		} elseif ( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Check if tutor lms is installed.
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	public static function is_tutor_active() {
		if ( in_array( 'tutor/tutor.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { //phpcs:ignore
			return true;
		} elseif ( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'tutor/tutor.php' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if edd is installed.
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	public static function is_jetform_active() {
        if ( in_array( 'jetformbuilder/jet-form-builder.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { //phpcs:ignore
			return true;
		} elseif ( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'jetformbuilder/jet-form-builder.php' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the Fluent Forms plugin is active on the WordPress site.
	 *
	 * @return bool Returns true if the Fluent Forms plugin is active, false otherwise.
	 * @since  1.2.7
	 */
	public static function is_fluentform_active() {
		if ( defined( 'FLUENTFORM' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if LearnDash LMS is active.
	 *
	 * @return bool True if LearnDash is active, false otherwise.
	 * @since 1.7.1
	 */
	public static function is_learndash_lms_active() {
		// Check if LearnDash version constant is defined.
		if ( defined( 'LEARNDASH_VERSION' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the Contact Form 7 plugin is active on the WordPress site.
	 *
	 * @return bool Returns true if the Contact Form 7 plugin is active, false otherwise.
	 * @since  1.5.12
	 */
	public static function is_contact_form_7_active() {
		if ( defined( 'WPCF7_VERSION' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get all gravity forms.
	 */
	public static function get_all_gform_forms() {
		if ( self::is_gform_active() ) {
			if ( class_exists( 'GFFormsModel' ) ) {
				$forms = \GFFormsModel::get_forms( true, 'title', 'ASC', false );
				if ( is_array( $forms ) ) {
					$formatted_forms = array(
						array(
							'value' => '',
							'label' => 'Select Form',
						),
					);
					foreach ( $forms as $form ) {
						if ( isset( $form->id, $form->title ) ) {
							$array = array(
								'value' => $form->id,
								'label' => $form->title,
							);
							array_push( $formatted_forms, $array );
						}
					}
					return $formatted_forms;
				}
			}
		}
		return false;
	}


	/**
	 * Encrypt key with AES.
	 *
	 * @param string $key key.
	 * @return string
	 */
	public static function encrypt_key( $key ) {
		$encrypted_key = \MintMailPro\Mint_AES_Encription\Mint_Aes_Ctr::encrypt( $key, '', 256 );
		return $encrypted_key;
	}


	/**
	 * Decrypt a key with AES.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	public static function decrypt_key( $key ) {
		$encrypted_key = \MintMailPro\Mint_AES_Encription\Mint_Aes_Ctr::decrypt( $key, '', 256 );
		return $encrypted_key;
	}


	/**
	 * Fetch product by term.
	 *
	 * @param string $term Search term.
	 * @param string $type Search type.
	 * @return array
	 * @since 1.0.0
	 */
	public static function retrieve_product( $term = '', $type = 'wc' ) {
		$products = array();
		if ( $term && $type ) {
			$function_name = 'get_' . $type . '_product';
			$products      = self::$function_name( $term );
		}
		return $products;
	}



	/**
	 * Get Wc products.
	 *
	 * @param string $term Get WC Term.
	 * @return array
	 */
	public static function get_wc_product( $term ) {
		$products = array();
		if ( $term ) {
			$data_store      = \WC_Data_Store::load( 'product' );
			$ids             = $data_store->search_products( $term, '', false, false, 10 );
			$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
			if ( is_array( $product_objects ) ) {
				foreach ( $product_objects as $product_object ) {
					if ( $product_object && ( ( $product_object->managing_stock() && $product_object->get_stock_quantity() > 0 ) || ( !$product_object->managing_stock() && $product_object->get_stock_status() !== 'outofstock' ) ) ) {
						$formatted_name = $product_object->get_name();

						if ( $product_object->get_type() === 'variable' || $product_object->get_type() === 'variable-subscription' ) {
							$variations = $product_object->get_available_variations();
							$parent_id  = $product_object->get_id();
							$products[] = array(
								'value' => $parent_id,
								'label' => $formatted_name,
							);

							if ( !empty( $variations ) ) {
								foreach ( $variations as $variation ) {
									$variation_product = wc_get_product( $variation['variation_id'] );

									if ( $variation_product ) {
										if ( ( $variation_product->managing_stock() && $variation_product->get_stock_quantity() > 0 ) || ( !$variation_product->managing_stock() && $variation_product->get_stock_status() !== 'outofstock' ) ) {
											$products[] = array(
												'value' => $variation['variation_id'],
												'label' => self::get_formated_product_name( $variation_product ),
											);
										}
									}
								}
							}
						} else {
							$products[] = array(
								'value' => $product_object->get_id(),
								'label' => rawurldecode( $formatted_name ),
							);
						}
					}
				}
			}
		}
		return $products;
	}

	/**
	 * Get Wc products.
	 *
	 * @param string $term Term for EDD product.
	 * @return array
	 */
	public static function get_edd_product( $term ) {
		$formatted_products = array();
		$posts              = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'download',
				'post_status' => 'publish',
				'fields'      => 'ID',
				's'           => $term,
				'orderby'     => 'date',
				'order'       => 'ASC',
			)
		);
		if ( is_array( $posts ) ) {
			foreach ( $posts as $product ) {
				if ( isset( $product->ID ) ) {
					$formatted_products[] = array(
						'value' => $product->ID,
						'label' => $product->post_title,
					);
				}
			}
		}

		return $formatted_products;
	}


	/**
	 * Get formated product name.
	 *
	 * @param Object $product Get product object.
	 * @param array  $formatted_attr Get formatted array.
	 *
	 * @return String
	 */
	public static function get_formated_product_name( $product, $formatted_attr = array() ) {
		$_product        = wc_get_product( $product );
		$each_child_attr = array();
		$_title          = '';
		if ( $_product ) {
			if ( !$formatted_attr ) {
				if ( 'variable' === $_product->get_type() || 'variation' === $_product->get_type() || 'subscription_variation' === $_product->get_type() || 'variable-subscription' === $_product->get_type() ) {
					$attr_summary = $_product->get_attribute_summary();
					$attr_array   = explode( ',', $attr_summary );

					foreach ( $attr_array as $ata ) {
						$attr              = strpbrk( $ata, ':' );
						$each_child_attr[] = $attr;
					}
				}
			} else {
				foreach ( $formatted_attr as $attr ) {
					$each_child_attr[] = ucfirst( $attr );
				}
			}
			if ( $each_child_attr ) {
				$each_child_attr_two = array();
				foreach ( $each_child_attr as $eca ) {
					$each_child_attr_two[] = str_replace( ': ', ' ', $eca );
				}
				$_title = $_product->get_title() . ' - ';
				$_title = $_title . implode( ', ', $each_child_attr_two );
			} else {
				$_title = $_product->get_title();
			}
		}

		return $_title;
	}


	/**
	 * Check if wpfunnels is installed
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	public static function is_wpf_free_pro_active() {
		$is_wpf_pro_activated  = apply_filters( 'is_wpf_pro_active', false );
		$is_wpf_free_activated = false;
        if ( in_array( 'wpfunnels/wpfnl.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { //phpcs:ignore
			$is_wpf_free_activated = true;
		} elseif ( function_exists( 'is_plugin_active' ) ) {
			if ( is_plugin_active( 'wpfunnels/wpfnl.php' ) ) {
				$is_wpf_free_activated = true;
			}
		}
		if ( $is_wpf_pro_activated && $is_wpf_free_activated ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieves group IDs associated with a specific email and type.
	 *
	 * @param string $email The email address to search for.
	 * @param string $type The type of contact group to filter by.
	 *
	 * @return array An array of group IDs associated with the given email and type.
	 * @since 1.3.1
	 */
	public static function get_group_ids_by_email( $email, $type ) {
		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT distinct(cg.id)
			FROM {$wpdb->prefix}mint_contacts AS c
			INNER JOIN {$wpdb->prefix}mint_contact_group_relationship AS cgr ON c.id = cgr.contact_id
			INNER JOIN {$wpdb->prefix}mint_contact_groups AS cg ON cgr.group_id = cg.id
			WHERE cg.type IN (%s) and c.email = %s",
			$type,
			$email
		);

		return array_values( array_column( $wpdb->get_results( $query, ARRAY_A ), 'id' ) ); //phpcs:ignore
	}

	/**
	 * Retrieves the name associated with a specific email and type.
	 *
	 * @param string $email The email address to search for.
	 * @param string $type The type of contact to filter by.
	 *
	 * @return string The name associated with the given email and type. Returns an empty string if not found.
	 * @since 1.3.1
	 */
	public static function get_name_by_email( $email, $type ) {
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query  = $wpdb->prepare(
			"SELECT {$type}
			FROM {$wpdb->prefix}mint_contacts WHERE email = %s",
			$email
		);
		$result = $wpdb->get_row( $query, ARRAY_A ); //phpcs:ignore
		return isset( $result[ $type ] ) ? $result[ $type ] : '';
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Fetch product by term.
	 *
	 * @param string $term Search term.
	 * @param string $type Search type.
	 * @return array
	 * @since 1.0.0
	 */
	public static function retrieve_category( $term = '', $type = 'wc' ) {
		$category = array();
		if ( $term && $type ) {
			$function_name = 'get_' . $type . '_category';
			$category      = self::$function_name( $term );
		}
		return $category;
	}
	/**
	 * Fetch WC by term.
	 *
	 * @param string $cat_name Search term.
	 * @return array
	 * @since 1.5.5
	 */
	public static function get_wc_category( $cat_name ) {
		global $wpdb;
		$data       = array();
		$cat_args   = "SELECT * 
             FROM $wpdb->terms AS t 
             INNER JOIN $wpdb->term_taxonomy AS tx ON t.term_id = tx.term_id 
             WHERE tx.taxonomy = 'product_cat' 
             AND t.name LIKE '%" . $cat_name . "%' "; //phpcs:ignore
		$categories = $wpdb->get_results( $cat_args, OBJECT ); //phpcs:ignore
		if ( !empty( $categories ) && is_array( $categories ) ) {
			foreach ( $categories as $category ) {
				$data[] = array(
					'value' => $category->term_id,
					'label' => rawurldecode( $category->name ),
				);
			}
		}
		return $data;
	}

	/**
	 * Check if MemberPress is active.
	 *
	 * @return bool True if MemberPress is active, false otherwise.
	 * @since 1.8.0
	 */
	public static function is_memberpress_active() {
		// Check if LearnDash version constant is defined.
		if ( defined( 'MEPR_PLUGIN_NAME' ) ) {
			return true;
		}
		return false;
	}
}


