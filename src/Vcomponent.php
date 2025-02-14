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
namespace Kigkonsult\Icalcreator;

use Exception;

/**
 * iCalcreator Vcomponents base class
 *
 * @since  2.27.6 - 2018-12-28
 */
abstract class Vcomponent extends CalendarComponent
{
    use Traits\DTSTAMPtrait;

    /**
     * Constructor for calendar component
     *
     * @overrides
     * @param null|mixed[] $config
     * @throws Exception
     * @since  2.27.6 - 2018-12-28
     */
    public function __construct( ? array $config = [] )
    {
        parent::__construct( $config );
        $this->setDtstamp();
    }

    /**
     * Return Vlocation object instance
     */
    use Traits\NewVlocationTrait;

    /**
     * Return Vresource object instance
     *
     * @param null|string $resourceType property RESOURCE-TYPE value
     * @param null|string $name property NAME value
     * @return Vresource
     * @throws Exception
     * @since  2.41.21 - 2022-02-18
     */
    public function newVresource( ? string $resourceType = null, ? string $name = null ) : Vresource
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = new Vresource( $this->getConfig());
        $this->components[$ix]->getUid();
        if( null !== $resourceType ) {
            $this->components[$ix]->setResourcetype( $resourceType );
        }
        if( null !== $name ) {
            $this->components[$ix]->setName( $name );
        }
        return $this->components[$ix];
    }
}
