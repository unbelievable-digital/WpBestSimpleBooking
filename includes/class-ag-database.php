<?php
/**
 * Database helper sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database sınıfı
 */
class AG_Database {

	/**
	 * WPDB instance
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Tablo prefix
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'ag_';
	}

	/**
	 * Tablo adını döndür
	 *
	 * @param string $table Tablo adı.
	 *
	 * @return string
	 */
	public function table( $table ) {
		return $this->prefix . $table;
	}

	/**
	 * Insert işlemi
	 *
	 * @param string $table Tablo adı.
	 * @param array  $data  Veri.
	 *
	 * @return int|false
	 */
	public function insert( $table, $data ) {
		$result = $this->wpdb->insert( $this->table( $table ), $data );

		if ( false === $result ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Update işlemi
	 *
	 * @param string $table Tablo adı.
	 * @param array  $data  Veri.
	 * @param array  $where Where koşulları.
	 *
	 * @return int|false
	 */
	public function update( $table, $data, $where ) {
		return $this->wpdb->update( $this->table( $table ), $data, $where );
	}

	/**
	 * Delete işlemi
	 *
	 * @param string $table Tablo adı.
	 * @param array  $where Where koşulları.
	 *
	 * @return int|false
	 */
	public function delete( $table, $where ) {
		return $this->wpdb->delete( $this->table( $table ), $where );
	}

	/**
	 * Tek satır getir
	 *
	 * @param string $table  Tablo adı.
	 * @param int    $id     ID.
	 * @param string $output Output tipi.
	 *
	 * @return object|array|null
	 */
	public function get_by_id( $table, $id, $output = OBJECT ) {
		$sql = $this->wpdb->prepare(
			"SELECT * FROM {$this->table( $table )} WHERE id = %d",
			$id
		);

		return $this->wpdb->get_row( $sql, $output );
	}

	/**
	 * Tüm satırları getir
	 *
	 * @param string $table   Tablo adı.
	 * @param array  $args    Argümanlar.
	 * @param string $output  Output tipi.
	 *
	 * @return array
	 */
	public function get_all( $table, $args = array(), $output = OBJECT ) {
		$defaults = array(
			'where'    => array(),
			'orderby'  => 'id',
			'order'    => 'ASC',
			'limit'    => 0,
			'offset'   => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "SELECT * FROM {$this->table( $table )}";

		// Where koşulları
		if ( ! empty( $args['where'] ) ) {
			$conditions = array();
			foreach ( $args['where'] as $key => $value ) {
				$conditions[] = $this->wpdb->prepare( "{$key} = %s", $value );
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		// Sıralama
		$sql .= sprintf(
			' ORDER BY %s %s',
			esc_sql( $args['orderby'] ),
			esc_sql( $args['order'] )
		);

		// Limit
		if ( $args['limit'] > 0 ) {
			$sql .= $this->wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
		}

		return $this->wpdb->get_results( $sql, $output );
	}

	/**
	 * Sayı getir
	 *
	 * @param string $table Tablo adı.
	 * @param array  $where Where koşulları.
	 *
	 * @return int
	 */
	public function count( $table, $where = array() ) {
		$sql = "SELECT COUNT(*) FROM {$this->table( $table )}";

		if ( ! empty( $where ) ) {
			$conditions = array();
			foreach ( $where as $key => $value ) {
				$conditions[] = $this->wpdb->prepare( "{$key} = %s", $value );
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Raw query çalıştır
	 *
	 * @param string $sql SQL sorgusu.
	 *
	 * @return array
	 */
	public function query( $sql ) {
		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Prepared query çalıştır
	 *
	 * @param string $sql  SQL sorgusu.
	 * @param array  $args Argümanlar.
	 *
	 * @return array
	 */
	public function prepared_query( $sql, $args = array() ) {
		if ( ! empty( $args ) ) {
			$sql = $this->wpdb->prepare( $sql, $args );
		}

		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Son hatayı getir
	 *
	 * @return string
	 */
	public function last_error() {
		return $this->wpdb->last_error;
	}
}
