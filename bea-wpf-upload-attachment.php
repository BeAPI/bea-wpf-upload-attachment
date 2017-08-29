<?php class BEA_WPForms_Upload_Attachment {
	function __construct() {
		add_filter( 'wpforms_email_attachments', [ $this, 'add_files_as_attachment_to_mail' ], 20, 2 );
		add_filter( 'wpforms_email_send_after', [ $this, 'delete_files_from_upload' ], 20 );
		add_filter( 'wpforms_html_field_value', [ $this, 'render_upload_file_into_mail' ], 20, 2 );
	}

	public function add_files_as_attachment_to_mail( $attachments, $wpf_obj ) {
		if ( empty( $wpf_obj->fields ) ) {
			return $attachments;
		}

		foreach ( $wpf_obj->fields as $field ) {
			if ( 'file-upload' !== $field['type'] || empty( $field['value'] ) ) {
				continue;
			}

			$attachment_path = $this->transform_upload_url_into_path( $field['value'] );
			if ( empty( $attachment_path ) ) {
				continue;
			}
			$attachments[] = $attachment_path;
		}

		return $attachments;
	}

	public function delete_files_from_upload( $wpf_obj ) {
		if ( empty( $wpf_obj->fields ) ) {
			return;
		}

		foreach ( $wpf_obj->fields as $field ) {
			if ( 'file-upload' !== $field['type'] || empty( $field['value'] ) ) {
				continue;
			}

			$attachment_path = $this->transform_upload_url_into_path( $field['value'] );
			if ( empty( $attachment_path ) ) {
				continue;
			}
			unlink( $attachment_path );
		}
	}

	// Customize value format for HTML emails.
	public function render_upload_file_into_mail( $val, $field ) {
		if ( ! empty( $field['value'] ) && 'file-upload' === $field['type'] ) {
			return __( sprintf( 'Cf. pièce jointe : %s.', $field['file_original'] ), '' );
		}

		return $val;
	}

	private function transform_upload_url_into_path( $url ) {
		$found = preg_match( '/\/uploads\/(.*)/', $url, $file_path );

		return empty( $found ) ? '' : sprintf( '%s/%s', wp_get_upload_dir()['basedir'], $file_path[1] );
	}
}

new BEA_WPForms_Upload_Attachment();
