<?php
/**
 * Interface Optin_Monster_Datastore_Interface
 *
 * @package Optin_Monster
 * @author  J. Aaron Eaton <aaron@awesomemotive.com>
 * @since   2.0.0
 */
interface Optin_Monster_Datastore_Interface {

	public function save( $data );
	public function remove( $id );
	public function find( $ids );
	public function find_all();
	public function find_where( $key, $value, $strict = true );
	public function create_table();
	public function remove_table();
	public function get_table_name();
	public function get_available_fields();
	public function get_required_fields();

} 