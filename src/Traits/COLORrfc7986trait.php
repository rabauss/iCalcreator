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

use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * COLOR property functions
 *
 * @since 2.41.36 2022-04-03
 * @see https://www.w3.org/TR/css-color-3/#svg-color
 */
trait COLORrfc7986trait
{
    /**
     * @var null|Pc component property COLOR value
     */
    protected ? Pc $color = null;

    /**
     * Return formatted output for calendar (component property color
     *
     * @return string
     * @since 2.41.36 2022-04-03
     */
    public function createColor() : string
    {
        if( empty( $this->color )) {
            return self::$SP0;
        }
        if( empty( $this->color->value )) {
            return $this->createSinglePropEmpty( self::COLOR );
        }
        return StringFactory::createElement(
            self::COLOR,
            ParameterFactory::createParams( $this->color->params ),
            $this->color->value
        );
    }

    /**
     * Delete calendar component property color
     *
     * @return bool
     * @since 2.29.5 2019-06-16
     */
    public function deleteColor() : bool
    {
        $this->color = null;
        return true;
    }

    /**
     * Get calendar component property color
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getColor( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->color )) {
            return false;
        }
        return $inclParam ? clone $this->color : $this->color->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.35 2022-03-28
     */
    public function isColorSet() : bool
    {
        return ! empty( $this->color->value );
    }

    /**
     * Set calendar component property color
     *
     * @param null|string|Pc   $value
     * @param null|mixed[]  $params
     * @return static
     * @since 2.41.36 2022-04-03
     */
    public function setColor( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::COLOR );
            $value->setEmpty();
        }
        else {
            $value->value = Util::assertString( $value->value, self::COLOR );
            $value->value = StringFactory::trimTrailNL( $value->value );
        }
        $this->color = $value;
        return $this;
    }
}
