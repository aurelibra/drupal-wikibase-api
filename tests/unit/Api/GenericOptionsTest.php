<?php

namespace Wikibase\Api\Test;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Wikibase\Api\GenericOptions;

/**
 * @covers Wikibase\Api\GenericOptions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Addshore
 */
class GenericOptionsTest extends TestCase {

	public function testConstructor() {
		$options = [
			'foo' => 42,
			'bar' => 4.2,
			'baz' => [ 'o_O', false, null, '42' => 42, [] ]
		];

		$genericOptions = new GenericOptions( $options );

		$this->assertEquals( $options, $genericOptions->getOptions() );
		$this->assertFalse( $genericOptions->hasOption( 'ohi' ) );
	}

	public function testConstructorFail() {
		$options = [
			'foo' => 42,
			'bar' => 4.2,
			42 => [ 'o_O', false, null, '42' => 42, [] ]
		];

		$this->expectException( 'Exception' );

		new GenericOptions( $options );
	}

	public function setOptionProvider() {
		$argLists = [];

		$genericOptions = new GenericOptions();

		$argLists[] = [ $genericOptions, 'foo', 42 ];
		$argLists[] = [ $genericOptions, 'bar', 42 ];
		$argLists[] = [ $genericOptions, 'foo', 'foo' ];
		$argLists[] = [ $genericOptions, 'foo', null ];

		return $argLists;
	}

	/**
	 * @dataProvider setOptionProvider
	 *
	 * @param GenericOptions $options
	 * @param string $option
	 * @param mixed $value
	 */
	public function testSetAndGetOption( GenericOptions $options, $option, $value ) {
		$options->setOption( $option, $value );

		$this->assertEquals(
			$value,
			$options->getOption( $option ),
			'Setting an option should work'
		);
	}

	public function testHashOption() {
		$options = [
			'foo' => 42,
			'bar' => 4.2,
			'baz' => [ 'o_O', false, null, '42' => 42, [] ]
		];

		$genericOptions = new GenericOptions( $options );

		foreach ( array_keys( $options ) as $option ) {
			$this->assertTrue( $genericOptions->hasOption( $option ) );
		}

		$this->assertFalse( $genericOptions->hasOption( 'ohi' ) );
		$this->assertFalse( $genericOptions->hasOption( 'Foo' ) );
	}

	public function testSetOption() {
		$genericOptions = new GenericOptions( [ 'foo' => 'bar' ] );

		$values = [
			[ 'foo', 'baz' ],
			[ 'foo', 'bar' ],
			[ 'onoez', '' ],
			[ 'hax', 'zor' ],
			[ 'nyan', 9001 ],
			[ 'cat', 4.2 ],
			[ 'spam', [ '~=[,,_,,]:3' ] ],
		];

		foreach ( $values as $value ) {
			$genericOptions->setOption( $value[0], $value[1] );
			$this->assertEquals( $value[1], $genericOptions->getOption( $value[0] ) );
		}
	}

	public function testForSomeReasonPhpSegfaultsIfThereIsOneMethodLess() {
		$this->assertTrue( (bool)'This is fucking weird' );
	}

	/**
	 * @dataProvider nonExistingOptionsProvider
	 */
	public function testGetOption( $nonExistingOption ) {
		$this->assertTrue( true );
		$genericOptions = new GenericOptions( [ 'foo' => 'bar' ] );

		$this->expectException( OutOfBoundsException::class );

		$genericOptions->getOption( $nonExistingOption );
	}

	public function nonExistingOptionsProvider() {
		$argLists = [];

		$argLists[] = [ 'bar' ];
		$argLists[] = [ 'Foo' ];
		$argLists[] = [ 'FOO' ];
		$argLists[] = [ 'spam' ];
		$argLists[] = [ 'onoez' ];

		return $argLists;
	}

	public function testRequireOption() {
		$options = [
			'foo' => 42,
			'bar' => 4.2,
			'baz' => [ 'o_O', false, null, '42' => 42, [] ]
		];

		$genericOptions = new GenericOptions( $options );

		foreach ( array_keys( $options ) as $option ) {
			$genericOptions->requireOption( $option );
		}

		$this->expectException( 'Exception' );

		$genericOptions->requireOption( 'Foo' );
	}

	public function testDefaultOption() {
		$options = [
			'foo' => 42,
			'bar' => 4.2,
			'baz' => [ 'o_O', false, null, '42' => 42, [] ]
		];

		$genericOptions = new GenericOptions( $options );

		foreach ( $options as $option => $value ) {
			$genericOptions->defaultOption( $option, 9001 );

			$this->assertEquals(
				serialize( $value ),
				serialize( $genericOptions->getOption( $option ) ),
				'Defaulting a set option should not affect its value'
			);
		}

		$defaults = [
			'N' => 42,
			'y' => 4.2,
			'a' => false,
			'n' => [ '42' => 42, [ '' ] ]
		];

		foreach ( $defaults as $option => $value ) {
			$genericOptions->defaultOption( $option, $value );

			$this->assertEquals(
				serialize( $value ),
				serialize( $genericOptions->getOption( $option ) ),
				'Defaulting a not set option should affect its value'
			);
		}
	}

}
