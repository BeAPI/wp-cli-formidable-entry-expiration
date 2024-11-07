<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Clean_Formidable_Entries extends \WP_CLI_Command {

	/**
	 * Update forms to set settings for Formidable Forms
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * ## OPTIONS
	 *
	 * <expire_time>
	 * : specifies the length of time entries must be kept.
	 *
	 * [--dry-run]
	 *  : Perform a dry run, showing which entries would be deleted without actually deleting them.
	 *
	 * ## EXAMPLES
	 *
	 * wp clean-formidable-entries 6months
	 * wp clean-formidable-entries 6months --dry-run
	 *
	 * @return void
	 */
	public function __invoke( $args, $assoc_args ): void {
		global $wpdb;

		// Get assoc_args to set entries expire limit and dry run.
		$dry_run       = isset( $assoc_args['dry-run'] );
		$expire_period = isset( $args[0] ) ? '-' . $args[0] : '';

		if ( empty( $expire_period ) ) {
			WP_CLI::error( 'Expired time is empty. Please give an expired time. Ex : "6months", "1year", "90days".' );

			return;
		}

		// Check if $expired_time is convertible as a timestamp.
		$expired_time = strtotime( $expire_period, time() );
		if ( false === $expired_time ) {
			WP_CLI::error( 'Expired time is not readable. Use a valid format like "6months", "1year", "90days".' );

			return;
		}

		$formatted_expired_time = date( 'Y-m-d H:i:s', $expired_time );
		WP_CLI::log( 'Start the cleaning process for entries before : ' . $formatted_expired_time );

		// Count entries to delete
		$total_entries = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(id) FROM {$wpdb->prefix}frm_items WHERE created_at < %s",
			$formatted_expired_time
		) );

		if ( 0 === $total_entries ) {
			WP_CLI::warning( 'No entries to delete.' );

			return;
		}

		// Get entry IDs to delete
		$entry_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}frm_items WHERE created_at < %s",
			$formatted_expired_time
		) );

		if ( $dry_run ) {
			WP_CLI::warning( sprintf( 'Dry run: %d entries would be deleted.', $total_entries ) );
		} else {

			// Delete entries
			$rows_affected = $wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}frm_items WHERE created_at < %s",
				$formatted_expired_time
			) );

			// Delete metadata associated with entries
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}frm_item_metas WHERE item_id IN (" . implode( ',', $entry_ids ) . ")"
			) );

			WP_CLI::success( sprintf( '%d entries deleted.', $rows_affected ) );
		}

		WP_CLI::log( 'End cleaning of Formidable Forms expired entries and associated metas' );

	}
}

WP_CLI::add_command( 'clean-formidable-entries', Clean_Formidable_Entries::class );
