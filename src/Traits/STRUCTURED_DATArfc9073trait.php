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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Traits;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * STRUCTURED-DATA property functions
 *
 * @since 2.41.36 2022-04-09
 */
trait STRUCTURED_DATArfc9073trait
{
    /**
     * @var null|Pc[] component property structureddata value
     */
    protected ? array $structureddata = null;

    /**
     * Return formatted output for calendar component property structureddata
     *
     * @return string
     */
    public function createStructureddata() : string
    {
        if( empty( $this->structureddata )) {
            return self::$SP0;
        }
        $output  = self::$SP0;
        foreach( $this->structureddata as $part ) {
            if( empty( $part->value )) {
                if( $this->getConfig( self::ALLOWEMPTY )) {
                    $output .= StringFactory::createElement( self::STRUCTURED_DATA );
                }
                continue;
            }
            $output .= StringFactory::createElement(
                self::STRUCTURED_DATA,
                ParameterFactory::createParams( $part->params ),
                StringFactory::strrep( $part->value )
            );
        } // end foreach
        return $output;
    }

    /**
     * Delete calendar component property structureddata
     *
     * @param null|int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     */
    public function deleteStructureddata( ? int $propDelIx = null ) : bool
    {
        if( empty( $this->structureddata )) {
            unset( $this->propDelIx[self::STRUCTURED_DATA] );
            return false;
        }
        return self::deletePropertyM(
            $this->structureddata,
            self::STRUCTURED_DATA,
            $this,
            $propDelIx
        );
    }

    /**
     * Get calendar component property structureddata
     *
     * @param null|int $propIx specific property in case of multiply occurrence
     * @param bool $inclParam
     * @return bool|string|Pc
     */
    public function getStructureddata( int $propIx = null, bool $inclParam = false ) : bool | string |Pc
    {
        if( empty( $this->structureddata )) {
            unset( $this->propIx[self::STRUCTURED_DATA] );
            return false;
        }
        return self::getMvalProperty(
            $this->structureddata,
            self::STRUCTURED_DATA,
            $this,
            $propIx,
            $inclParam
        );
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isStructureddataSet() : bool
    {
        return self::isMvalSet( $this->structureddata );
    }

    /**
     * Set calendar component property structureddata
     *
     * Set default param DERIVED to FALSE if missing (default)
     *
     * fmttypeparam/ schemaparam are OPTIONAL for a URI value, REQUIRED for a TEXT or BINARY value
     * and MUST NOT occur more than once
     *
     * @param null|string|Pc   $value
     * @param null|int|mixed[] $params   VALUE TEXT/URI
     * @param null|int         $index
     * @return static
     * @throws InvalidArgumentException
     */
    public function setStructureddata(
        null|string|Pc $value = null,
        null|int|array $params = [],
        ? int $index = null
    ) : static
    {
        $value = self::marshallInputMval( $value, $params, $index );
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::STRUCTURED_DATA );
            $value->setEmpty();
        }
        else {
            $value->value  = Util::assertString( $value->value, self::STRUCTURED_DATA );
            $value->addParamValue( self::TEXT, false ); // must have VALUE
            if( $value->hasParamValue( self::BINARY ) &&
                ! $value->hasParamKey( self::ENCODING )) {
                $value->addParam( self::ENCODING, self::BASE64 );
            }
        }
        self::setMval( $this->structureddata, $value, $index );
        return $this;
    }
}
