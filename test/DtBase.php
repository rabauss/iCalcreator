<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
namespace Kigkonsult\Icalcreator;

use DateTime;
use DateTimeInterface;
use Exception;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\IcalXMLFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * class DtBase
 *
 * @since 2.41.44 2022-04-27
 */
abstract class DtBase extends TestCase
{
    use GetPropMethodNamesTrait;

    protected static string $ERRFMT = "Error %sin case #%s, %s <%s>->%s";

    /**
     * The test method, case suffix '-1xx', test prop create-, delete-, get- is- and set-methods
     *
     * @param int     $case
     * @param array   $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     * @since 2.41.47 2022-04-29
     */
    public function thePropTest(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $c       = new Vcalendar();
        $pcInput = $firstLastmodifiedLoad = false;
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp = match ( true ) {
                IcalInterface::PARTICIPANT === $theComp => $c->newVevent()->{$newMethod}()
                    ->setDtstamp( $value, $params ),
                IcalInterface::AVAILABLE === $theComp   => $c->newVavailability()->{$newMethod}(),
                default                                 => $c->{$newMethod}(),
            };
            foreach( $props as $propName ) {
                [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                if( IcalInterface::LAST_MODIFIED === $propName ) {
                    if( ! $firstLastmodifiedLoad ) {
                        $this->assertFalse(
                            $c->{$isMethod}(),
                            sprintf( self::$ERRFMT, null, $case . '-110', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod )
                            . PHP_EOL . $c->createCalendar() // test ###
                        );
                    }
                    $c->setLastmodified( $value, $params );
                    $firstLastmodifiedLoad = true;
                    $this->assertTrue(
                        $c->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-111', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod )
                    );
                } // end if last-mod...

//   echo PHP_EOL . __FUNCTION__ . ' #' . $case . ' start <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true ) . PHP_EOL; // test ###

                if( $propName !== IcalInterface::DTSTAMP ) {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-112', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                if( in_array( $propName, [ IcalInterface::EXDATE, IcalInterface::RDATE ], true )) {
                    $comp->{$setMethod}( [ $value ], $params );
                    $getValue = $comp->{$getMethod}( null, true );
                    if( ! empty( $getValue->value )) {
                        $getValue->value = reset( $getValue->value );
                    }
                }
                else {
                    if( in_array( $propName, [ IcalInterface::DTEND, IcalInterface::DUE, IcalInterface::RECURRENCE_ID, ], true ) ) {
                        $comp->setDtstart( $value, $params );
                    }
                    $comp->{$setMethod}( $value, $params );
                    $getValue = $comp->{$getMethod}( true );
                } // end else
                if( null !== $value ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-113', __FUNCTION__, $theComp, $isMethod )
                    );
                }

                if( $expectedGet->value instanceof DateTime && $getValue->value instanceof DateTime ) {
                    $getValue->removeParam( Util::$ISLOCALTIME );
                    $this->assertEquals(
                        $expectedGet->params,
                        $getValue->params,
                        sprintf( self::$ERRFMT, null, $case . '-114', __FUNCTION__, $theComp, $getMethod )
                    );
                    $fmt = match ( true ) {
                        $expectedGet->hasParamValue( IcalInterface::DATE )
                                => DateTimeFactory::$Ymd,
                        $getValue->hasParamkey( Util::$ISLOCALTIME )
                                => DateTimeFactory::$YmdHis,
                        default => DateTimeFactory::$YMDHISe,
                    };
                    $this->assertEquals(
                        $expectedGet->value->format( $fmt ),
                        $getValue->value->format( $fmt ),
                        sprintf( self::$ERRFMT, null, $case . '-115', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end if
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case . '-116', __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( IcalInterface::DTSTAMP === $propName ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-117', __FUNCTION__, $theComp, $isMethod )
                    );
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case . '-118', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end if
                else {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-119', __FUNCTION__, $theComp, $isMethod )
                    );
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case . '-120', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end else
                if( $pcInput ) {
                    $comp->{$setMethod}( Pc::factory( $value, $params ));
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                if( null !== $value ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-121', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                $pcInput = ! $pcInput;
            } // end foreach  .. => propName
        } // end foreach  comp => props

        $this->parseCalendarTest( $case, $c, $expectedString, $theComp, $propName );
    }

    /**
     * The test method, case suffix '-2xx', test prop get--method without params
     *
     * @param int     $case
     * @param array   $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @throws Exception
     * @since 2.41.44 2022-04-21
     */
    public function propGetNoParamsTest(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet
    ) : void
    {
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp = match ( true ) {
                IcalInterface::PARTICIPANT === $theComp => $c->newVevent()->{$newMethod}()
                    ->setDtstamp( $value, $params ),
                IcalInterface::AVAILABLE === $theComp => $c->newVavailability()->{$newMethod}(),
                default => $c->{$newMethod}(),
            };
            foreach( $props as $propName ) {
                [ , , $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                if( IcalInterface::DTSTAMP !== $propName ) {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        sprintf( self::$ERRFMT, null, $case . '-211', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                if( in_array( $propName, [ IcalInterface::EXDATE, IcalInterface::RDATE ], true )) {
                    $comp->{$setMethod}( [ $value ], $params );
                    $getValue = $comp->{$getMethod}();
                    if( ! empty( $getValue )) {
                        $getValue = reset( $getValue );
                    }
                }
                else {
                    if( in_array( $propName, [ IcalInterface::DTEND, IcalInterface::DUE, IcalInterface::RECURRENCE_ID, ], true ) ) {
                        $comp->setDtstart( $value, $params );
                    }
                    $comp->{$setMethod}( $value, $params );
                    $getValue = $comp->{$getMethod}();
                } // end else
                $this->assertSame(
                    ! empty( $value ),
                    $comp->{$isMethod}(),
                    sprintf( self::$ERRFMT, null, $case . '-212', __FUNCTION__, $theComp, $isMethod )
                       . ', exp ' . empty( $value ) ? Vcalendar::FALSE : Vcalendar::TRUE
                );
                if( $expectedGet->value instanceof DateTime && $getValue instanceof DateTime ) {
                    $this->assertEquals(
                        $expectedGet->value->format( DateTimeFactory::$YmdHis ),
                        $getValue->format( DateTimeFactory::$YmdHis ),
                        sprintf( self::$ERRFMT, null, $case . '-213', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end if
                else {
                    $this->assertEquals(
                        $expectedGet->value ?? '',
                        $getValue,
                        sprintf( self::$ERRFMT, null, $case . '-214', __FUNCTION__, $theComp, $getMethod )
                    );
                }

            } // end foreach props...
        } // end foreach $compsProps...
    }

    /**
     * The test method suffix -1b... , single EXDATE + RDATE, case prefix '-1bx' also multi EXDATE + RDATE
     *
     * @param int|string $case
     * @param array      $compsProps
     * @param mixed      $value
     * @param mixed      $params
     * @param pc         $expectedGet
     * @param string     $expectedString
     * @throws Exception
     */
    public function exdateRdateSpecTest(
        int | string $case,
        array        $compsProps,
        mixed        $value,
        mixed        $params,
        Pc           $expectedGet,
        string       $expectedString
    ) : void
    {
        $c       = new Vcalendar();
        $pcInput = false;
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            if( IcalInterface::AVAILABLE === $theComp ) {
                $comp = $c->newVavailability()->{$newMethod}();
            }
            else {
                $comp = $c->{$newMethod}();
            }
            foreach( $props as $propName ) {
                [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    sprintf( self::$ERRFMT, null, $case . '-1b2', __FUNCTION__, $theComp, $isMethod )
                );
                //              error_log( __FUNCTION__ . ' #' . $case . '-1b1' . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###

                if( $pcInput ) {
                    $comp->{$setMethod}( Pc::factory( $value, $params ));
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                $pcInput = ! $pcInput;
                $this->assertSame(
                    ! empty( $value ),
                    $comp->{$isMethod}(),
                    sprintf( self::$ERRFMT, null, $case . '-1b1', __FUNCTION__, $theComp, $isMethod )
                         . ', exp ' . empty( $value ) ? Vcalendar::FALSE : Vcalendar::TRUE
                );

                $getValue = $comp->{$getMethod}( null, true );
                $getValue->removeParam( Util::$ISLOCALTIME );
                $this->assertEquals(
                    $expectedGet->params,
                    $getValue->params,
                    sprintf( self::$ERRFMT, null, $case . '-1b2', __FUNCTION__, $theComp, $isMethod )
                );
                if( ! empty( $expectedGet->value )) {
                    $expVal = $expectedGet->value;

                    // echo __FUNCTION__ . ' ' . $getMethod . ' ' . var_export( $expectedGet, true ) . PHP_EOL; // test ###

                    switch( true ) {
                        case $expectedGet->hasParamValue(IcalInterface::DATE ) :
                            $fmt = DateTimeFactory::$Ymd;
                            break;
                        case $getValue->hasParamKey( Util::$ISLOCALTIME ) :
                            $fmt = DateTimeFactory::$YmdHis;
                            break;
                        default :
                            $fmt = DateTimeFactory::$YMDHISe;
                            break;
                    }
                    while( is_array( $expVal ) && ! $expVal instanceof DateTime ) {
                        $expVal = reset( $expVal );
                    }
                    $expGet = $expVal->format( $fmt );
                    $getVal = reset( $getValue->value );
                    while( is_array( $getVal ) && ! $getVal instanceof DateTime ) { // exDate/Rdate
                        $getVal = reset( $getVal );
                    }
                    $getVal = $getVal->format( $fmt );
                } // end if
                else {
                    $expGet = $expectedGet;
                    $getVal = $getValue;
                }
                $this->assertEquals(
                    $expGet,
                    $getVal,
                    sprintf( self::$ERRFMT, null, $case . '-1b3', __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case . '-1b4', __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( IcalInterface::DTSTAMP === $propName ) {
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case . '-1b5', __FUNCTION__, $theComp, $getMethod )
                    );
                }
                else {
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case . '-1b6', __FUNCTION__, $theComp, $getMethod )
                    );
                }
                $comp->{$setMethod}( $value, $params );
                if( ! empty( $value ) && ! is_array( $value )) {
                    $comp->{$setMethod}( [ $value, $value ], $params ); // set aray of (single) values
                }
            } // end foreach
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * Testing calendar parse and (-re-)create, case prefix '-3x'
     *
     * @param int|string $case
     * @param Vcalendar   $calendar
     * @param string|null $expectedString
     * @param mixed|null $theComp
     * @param string|null $propName
     * @throws Exception
     */
    public function parseCalendarTest(
        int | string $case,
        Vcalendar    $calendar,
        string       $expectedString = null,
        mixed        $theComp = null,
        string       $propName = null
    ) : void
    {
        static $xmlStartChars = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<icalendar xmlns=\"urn:ietf:params:xml:ns:icalendar-2.0\"><!-- kigkonsult.se iCalcreator";
        static $xmlEndChars   = "</icalendar>\n";

//      error_log( __METHOD__ . ' case ' . $case . ' ' . __FUNCTION__ . ' ' . $theComp . '::' . $propName . ' start' . PHP_EOL ); // test ###

        $calendarStr1 = $calendar->createCalendar();

        if( ! empty( $expectedString )) {
            $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], Util::$SP0, $calendarStr1 );
            $createString = str_replace( '\,', ',', $createString );
            $this->assertNotFalse(
                strpos( $createString, $expectedString ),
                sprintf( self::$ERRFMT, null, $case . '-31', __FUNCTION__, $theComp ?? '?', $propName ?? '?' )
                    . PHP_EOL . $createString . PHP_EOL . $expectedString
            );
        }

        if( ! empty( $expectedString )) {

            // echo 'start convert to XML' . PHP_EOL; // test ###

            $calendarUid = $calendar->getUid();
            $xml         = IcalXMLFactory::iCal2XML( $calendar );

            $this->assertStringStartsWith( $xmlStartChars, $xml );
            $this->assertNotFalse( strpos( $xml, Vcalendar::iCalcreatorVersion()));
            $this->assertStringEndsWith( $xmlEndChars, $xml );
            try {
                $test = new SimpleXMLElement( $xml );
            }
            catch( Exception $e ) {
                $this->fail(
                    sprintf( self::$ERRFMT, null, $case . '-32', __FUNCTION__, $theComp, $propName )
                );
            }

//            if( 5 > $case ) {        // test ###
//            echo __METHOD__ . PHP_EOL . $calendarStr1 . PHP_EOL; // test ###
//            echo __METHOD__ . PHP_EOL . $xml . PHP_EOL; // test ###
//            }                        // test ###

            // echo 'start XML convert to iCal' . PHP_EOL; // test ###
            $c2  = IcalXMLFactory::XML2iCal( $xml );
            $this->assertTrue(
                ( $c2 instanceof Vcalendar ),
                sprintf( self::$ERRFMT, null, $case . '-33', __FUNCTION__, $theComp, $propName )
            );

            // echo 'start create calendar' . PHP_EOL; // test ###

            $c2->setUid( $calendarUid ); // else UID compare error
            $calendarStr2 = $c2->createCalendar();
            $this->assertEquals(
                $calendarStr1,
                $calendarStr2,
                sprintf( self::$ERRFMT, null, $case . '-34', __FUNCTION__, $theComp, $propName )
                . PHP_EOL . str_replace( '><', '>' . PHP_EOL . '<', $xml ) . PHP_EOL
            );
//          echo __FUNCTION__ . ' #' . $case . ' passed test 34' . PHP_EOL; // test ###
        } // end if( ! empty( $expectedString ))
        else {
            $calendarStr2 = $calendarStr1;
        }

//      echo __FUNCTION__ . ' #' . $case . ' calendar2 : ' . $calendarStr2 . PHP_EOL; // test ###

        $calendar3    = new Vcalendar();
        $calendar3->parse( $calendarStr2 );
        $calendarStr3 = $calendar3->createCalendar();

//    echo __FUNCTION__ . ' start #' . $case . ' calendar3 : ' . $calendarStr3 . PHP_EOL; // test ###

        $this->assertEquals(
            $calendarStr1,
            $calendarStr3,
            sprintf( self::$ERRFMT, null, $case . '-35', __FUNCTION__, $theComp, $propName )
        );
        ob_start();
        $calendar3->returnCalendar();
        $hdrs = PHP_SAPI === 'cli' ? xdebug_get_headers() : headers_list();
        $out3 = ob_get_clean();

        $calEnd = 'END:VCALENDAR' . Util::$CRLF;
        $this->assertTrue(
            str_ends_with( $calendarStr1, $calEnd ),
            sprintf( self::$ERRFMT, null, $case . '-36a', __FUNCTION__, $theComp, $propName )
        );
        $this->assertTrue(
            str_ends_with( $out3, $calEnd ),
            sprintf( self::$ERRFMT, null, $case . '-36b', __FUNCTION__, $theComp, $propName )
        );
        $this->assertEquals(
            strlen( $calendarStr1 ),
            strlen( $out3 ),
            sprintf( self::$ERRFMT, null, $case . '-36c', __FUNCTION__, $theComp, $propName )
        );
        $needle = 'Content-Length: ';
        $contentLength = 0;
        foreach( $hdrs as $hdr ) {
            if( str_starts_with( strtolower( $hdr ), strtolower( $needle ))) {
                $contentLength = trim( StringFactory::after( $needle, $hdr ));
                break;
            }
        }
        $exp = strlen( $out3 );
        $this->assertEquals(
            $exp,
            $contentLength,
            'Error in case #' . $case . ' -36d, ' . __FUNCTION__ . ' exp: ' . $exp . ', found: ' . var_export( $hdrs, true )
        );
        // testing end

        $this->assertEquals(
            $calendarStr1,
            $out3,
            sprintf( self::$ERRFMT, null, $case . '-36e', __FUNCTION__, $theComp, $propName )
        );
    }

    /**
     * Return the datetime as (ical create-) long string
     *
     * @param DateTimeInterface $dateTime
     * @param string|null $tz
     * @return string
     */
    public function getDateTimeAsCreateLongString( DateTimeInterface $dateTime, string $tz = null ) : string
    {
        static $FMT1   = ';TZID=%s:';
        $isUTCtimeZone = ! ( empty( $tz ) ) && DateTimeZoneFactory::isUTCtimeZone( $tz );
        $output        = ( empty( $tz ) || $isUTCtimeZone ) ? Util::$COLON : sprintf( $FMT1, $tz );
        $output       .= $dateTime->format( DateTimeFactory::$YmdTHis );
        if( $isUTCtimeZone ) {
            $output   .= 'Z';
        }
        return $output;
    }

    /**
     * Return the datetime as (ical create-) short string
     *
     * @param DateTimeInterface $dateTime
     * @param bool $prefix
     * @return string
     */
    public function getDateTimeAsCreateShortString( DateTimeInterface $dateTime, bool $prefix = true ) : string
    {
        static $FMT1 = ';VALUE=DATE:%d';
        static $FMT2 = ':%d';
        $fmt = $prefix ? $FMT1 : $FMT2;
        return sprintf( $fmt, $dateTime->format( DateTimeFactory::$Ymd ));
    }
}
