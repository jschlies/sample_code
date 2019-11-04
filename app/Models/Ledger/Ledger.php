<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model as ModelBase;

/**
 * Class Ledger
 */
class Ledger extends ModelBase
{

    const CALENDAR_YEAR_ABBREV  = 'CY';
    const TRAILING_12_ABBREV    = 'T12';
    const YEAR_TO_DATE_ABBREV   = 'YTD';
    const CALENDAR_YEAR_VERBOSE = 'Calendar Year';
    const TRAILING_12_VERBOSE   = 'Trailing 12';
    const YEAR_TO_DATE_VERBOSE  = 'Year To Date';
    const ANALYTICS_CONFIG_KEY  = 'ANALYTICS';

    /**
     * @param string $subject
     * @return mixed|string
     */
    public function formatName($subject)
    {

        $subject = strtolower($subject);

        // uppercase naming exceptions
        $subject = str_replace('hvac', 'HVAC', $subject);

        // uppercase words after slash in name
        if (strpos($subject, '/') !== false)
        {
            $splitSubject = explode('/', $subject);
            $fixedSubject = array_map(
                function ($item)
                {
                    return ucfirst(trim($item));
                }, $splitSubject
            );
            $subject      = implode('/', $fixedSubject);
        }

        // uppercase words regular
        $subject = ucwords($subject);

        return $subject;
    }

    /**
     * @param $subject
     * @return mixed|string
     */
    public function formatTitleCase($subject)
    {

        // uppercase naming exceptions
        $subject = strtolower($subject);
        $subject = str_replace('hvac', 'HVAC', $subject);
        $subject = ucwords($subject);

        return $subject;
    }

    /**
     * @param string $subject
     * @return string
     */
    public function formatReportTypeString($subject)
    {
        return ucwords(strtolower($subject));
    }

    /**
     * @param string $subject
     * @return string
     */
    public function formatPeriodTypeString($subject = '')
    {
        if ($subject == self::CALENDAR_YEAR_ABBREV)
        {
            return self::CALENDAR_YEAR_VERBOSE;
        }
        elseif ($subject == self::YEAR_TO_DATE_ABBREV)
        {
            return self::YEAR_TO_DATE_VERBOSE;
        }
        elseif ($subject == self::TRAILING_12_ABBREV)
        {
            return self::TRAILING_12_VERBOSE;
        }
        else
        {
            throw new GeneralException('incorrect period');
        }
    }

    /**
     * @param string $subject
     * @return string
     */
    public function formatAreaTypeString($subject = '')
    {
        return $subject;
    }
}