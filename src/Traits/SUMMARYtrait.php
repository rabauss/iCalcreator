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
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * SUMMARY property functions
 *
 * @since 2.41.36 2022-04-03
 */
trait SUMMARYtrait
{
    /**
     * @var null|Pc component property SUMMARY value
     */
    protected ? Pc $summary = null;

    /**
     * Return formatted output for calendar component property summary
     *
     * @return string
     */
    public function createSummary() : string
    {
        if( empty( $this->summary )) {
            return self::$SP0;
        }
        if( empty( $this->summary->getValue())) {
            return $this->createSinglePropEmpty( self::SUMMARY );
        }
        return StringFactory::createElement(
            self::SUMMARY,
            ParameterFactory::createParams(
                $this->summary->getParams(),
                self::$ALTRPLANGARR,
                $this->getConfig( self::LANGUAGE )
            ),
            StringFactory::strrep( $this->summary->getValue())
        );
    }

    /**
     * Delete calendar component property summary
     *
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteSummary() : bool
    {
        $this->summary = null;
        return true;
    }

    /**
     * Get calendar component property summary
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     * @since 2.41.36 2022-04-03
     */
    public function getSummary( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->summary )) {
            return false;
        }
        return $inclParam ? clone $this->summary : $this->summary->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isSummarySet() : bool
    {
        return ( ! empty( $this->summary->value ));
    }

    /**
     * Set calendar component property summary
     *
     * @param null|string|Pc   $value
     * @param null|mixed[]  $params
     * @return static
     * @since 2.41.36 2022-04-03
     */
    public function setSummary( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::SUMMARY );
            $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::SUMMARY );
        }
        $this->summary = $value;
        return $this;
    }
}
