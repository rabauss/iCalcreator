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

use function sprintf;
use function strtoupper;

/**
 * iCalcreator Vlocation component class
 *
 * @since 2.41.29 2022-02-24
 */
final class Vlocation extends CalendarComponent
{
    /* The following are REQUIRED but MUST NOT occur more than once. */
    use Traits\UIDrfc7986trait;

    /* The following are OPTIONAL but MUST NOT occur more than once. */
    use Traits\DESCRIPTIONtrait;
    use Traits\GEOtrait;                          // opt removal ??
    use Traits\LOCATIONTYPErfc9073trait;
    use Traits\NAMErfc7986trait;
    use Traits\URLtrait;

    /* The following are OPTIONAL and MAY occur more than once. */
    use Traits\STRUCTURED_DATArfc9073trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'vl';

    /**
     * Destructor
     *
     * @since 2.41.5 2022-01-19
     */
    public function __destruct()
    {
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->uid,
            $this->description,
            $this->geo,
            $this->locationtype,
            $this->name,
            $this->structureddata,
            $this->url
        );
    }

    /**
     * Return formatted output for calendar component VALARM object instance
     *
     * @return string
     * @throws Exception  (on Duration/Trigger err)
     * @since 2.41.29 2022-02-24
     */
    public function createComponent() : string
    {
        $compType    = strtoupper( $this->getCompType());
        return
            sprintf( self::$FMTBEGIN, $compType ) .
            $this->createUid() .
            $this->createDescription() .
            $this->createGeo() .
            $this->createUrl() .
            $this->createLocationtype() .
            $this->createName() .
            $this->createStructureddata() .
            $this->createXprop() .
            sprintf( self::$FMTEND, $compType );
    }
}
